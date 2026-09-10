<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\BrandedMessageMail;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['user', 'room', 'assignee']);

        if ($request->status === 'open') {
            $query->whereNotIn('status', ['resolved', 'rejected', 'closed']);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->sla === 'overdue') $query->whereNotIn('status', ['resolved', 'rejected', 'closed'])->where('due_at', '<', now());
        if ($request->sla === 'escalated') $query->whereNotNull('escalated_at')->whereNotIn('status', ['resolved', 'rejected', 'closed']);
        if ($request->sla === 'due_today') $query->whereNotIn('status', ['resolved', 'rejected', 'closed'])->whereDate('due_at', today());

        // Staff Assignment Filter
        if ($request->assigned === 'me') {
            $query->where('assigned_to', auth()->id());
        } elseif ($request->assigned === 'unassigned') {
            $query->whereNull('assigned_to');
        } elseif ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_to', auth()->id());
            } elseif ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->integer('assigned_to'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(fn ($q) => $q->where('ticket_number', 'like', "%{$search}%")->orWhere('subject', 'like', "%{$search}%"));
        }

        $complaints = $query->latest()->paginate(20)->withQueryString();

        $complaintStats = [
            'open' => Complaint::whereNotIn('status', ['resolved', 'rejected', 'closed'])->count(),
            'my_assigned' => Complaint::where('assigned_to', auth()->id())->whereNotIn('status', ['resolved', 'rejected', 'closed'])->count(),
            'unassigned' => Complaint::whereNull('assigned_to')->whereNotIn('status', ['resolved', 'rejected', 'closed'])->count(),
            'overdue' => Complaint::whereNotIn('status', ['resolved', 'rejected', 'closed'])->where('due_at', '<', now())->count(),
            'escalated' => Complaint::whereNotNull('escalated_at')->whereNotIn('status', ['resolved', 'rejected', 'closed'])->count(),
            'resolved' => Complaint::where('status', 'resolved')->count(),
        ];

        $staffMembers = User::where('role', 'admin')->where('is_blocked', false)->orderBy('name')->get(['id', 'name']);

        return view('admin.complaints.index', compact('complaints', 'complaintStats', 'staffMembers'));
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $request->validate([
            'assigned_to' => ['nullable', 'string'],
        ]);

        $input = $request->input('assigned_to');
        $assigneeId = null;

        if ($input === 'me') {
            $assigneeId = auth()->id();
        } elseif ($input === '' || $input === 'unassigned' || $input === null) {
            $assigneeId = null;
        } else {
            $staff = User::where('id', (int) $input)->where('role', 'admin')->where('is_blocked', false)->firstOrFail();
            $assigneeId = $staff->id;
        }

        $oldAssignee = $complaint->assignee?->name ?? 'Unassigned';
        $complaint->update(['assigned_to' => $assigneeId]);
        $complaint->refresh();
        $newAssignee = $complaint->assignee?->name ?? 'Unassigned';

        $complaint->activities()->create([
            'actor_id' => $request->user()->id,
            'type' => 'assignment',
            'description' => $assigneeId ? "Ticket assigned to {$newAssignee} (was {$oldAssignee})." : 'Ticket unassigned.',
            'is_internal' => true,
        ]);

        return back()->with('success', $assigneeId ? "Ticket assigned to {$newAssignee}." : 'Ticket marked as unassigned.');
    }

    public function show(Complaint $complaint)
    {
        $complaint->load(['user', 'againstUser', 'room', 'assignee', 'replies.user', 'activities.actor']);
        $admins = User::where('role', 'admin')->where('is_blocked', false)->orderBy('name')->get(['id', 'name']);
        return view('admin.complaints.show', compact('complaint', 'admins'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(Complaint::STATUSES))],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'admin')->where('is_blocked', false))],
            'resolution' => ['nullable', 'string', 'max:10000'],
            'resolution_category' => ['nullable', 'in:' . implode(',', array_keys(Complaint::RESOLUTION_CATEGORIES))],
            'due_at' => ['nullable','date'],
            'escalated' => ['nullable','boolean'],
        ]);
        $oldStatus = $complaint->status;
        $data['closed_at'] = in_array($data['status'], ['resolved', 'rejected', 'closed'], true) ? now() : null;
        $data['escalated_at'] = $request->boolean('escalated') ? ($complaint->escalated_at ?: now()) : null;
        $complaint->update($data);
        $complaint->activities()->create(['actor_id' => $request->user()->id, 'type' => 'status', 'status_from' => $oldStatus, 'status_to' => $complaint->status, 'description' => 'Ticket management details updated.', 'is_internal' => false]);
        try {
            $statusLabel = Complaint::STATUSES[$complaint->status] ?? ucfirst(str_replace('_', ' ', $complaint->status));
            Mail::to($complaint->user->email)->send(new BrandedMessageMail(
                "Complaint update: {$complaint->ticket_number}", 'Your complaint has been updated',
                $complaint->resolution ?: 'Our support team has updated your complaint. Open the ticket to see the latest information.',
                'Support update', 'View complaint', route('complaints.show', $complaint),
                ['Ticket' => $complaint->ticket_number, 'Status' => $statusLabel],
                in_array($complaint->status, ['resolved','closed'], true) ? 'success' : 'primary'
            ));
        } catch (\Throwable $e) {
            report($e);
        }
        \App\Services\NotificationService::notifyComplaintUpdated(
            $complaint->user_id,
            $complaint->ticket_number,
            $complaint->status,
            route('complaints.show', $complaint)
        );
        return back()->with('success', 'Complaint status updated.');
    }

    public function reopen(Request $request, Complaint $complaint)
    {
        abort_unless(in_array($complaint->status,['resolved','rejected','closed']),422,'Only closed complaints can be reopened.');
        $complaint->update(['status'=>'under_review','closed_at'=>null,'reopened_at'=>now(),'due_at'=>now()->addHours(24)]);
        $complaint->activities()->create(['actor_id'=>$request->user()->id,'type'=>'status','status_from'=>'closed','status_to'=>'under_review','description'=>'Complaint reopened for further investigation.','is_internal'=>false]);
        return back()->with('success','Complaint reopened.');
    }

    public function reply(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'is_internal' => ['nullable', 'boolean'],
        ]);
        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('complaints/replies', 'local');
        }
        unset($data['attachment']);
        $data['user_id'] = $request->user()->id;
        $data['is_internal'] = $request->boolean('is_internal');
        $complaint->replies()->create($data);
        $complaint->activities()->create(['actor_id' => $request->user()->id, 'type' => $data['is_internal'] ? 'note' : 'reply', 'description' => $data['is_internal'] ? 'Internal investigation note added.' : 'Support replied to the reporter.', 'is_internal' => $data['is_internal']]);
        if (!$data['is_internal'] && $complaint->status === 'submitted') {
            $complaint->update(['status' => 'under_review', 'assigned_to' => $complaint->assigned_to ?: $request->user()->id]);
        }
        if (!$data['is_internal']) {
            try {
                Mail::to($complaint->user->email)->send(new BrandedMessageMail(
                    "New reply to {$complaint->ticket_number}", 'ApnaNest Support replied', $data['message'],
                    'Support reply', 'Open complaint', route('complaints.show', $complaint),
                    ['Ticket' => $complaint->ticket_number], 'primary'
                ));
            } catch (\Throwable $e) {
                report($e);
            }

            try {
                \App\Models\UserNotification::send(
                    $complaint->user_id,
                    'complaint_reply',
                    "Support replied to #{$complaint->ticket_number}",
                    \Illuminate\Support\Str::limit($data['message'], 100),
                    route('complaints.show', $complaint),
                    'fa-comments'
                );
            } catch (\Throwable $ue) {
                report($ue);
            }
        }
        return back()->with('success', $data['is_internal'] ? 'Internal note added.' : 'Reply sent to complainant.');
    }
}
