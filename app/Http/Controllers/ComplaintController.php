<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\Room;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use App\Mail\BrandedMessageMail;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $complaints = $request->user()->complaints()->with('room')->latest()->paginate(12);
        return view('account.complaints.index', compact('complaints'));
    }

    public function create(Request $request)
    {
        $room = $request->filled('room') ? Room::find($request->integer('room')) : null;
        return view('account.complaints.create', compact('room'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'in:' . implode(',', array_keys(Complaint::CATEGORIES))],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'room_id' => [
                'nullable',
                'integer',
                Rule::exists('rooms', 'id')->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                }),
            ],
            'evidence' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('evidence')) {
            $data['evidence_path'] = $request->file('evidence')->store('complaints/evidence', 'local');
        }

        if (!empty($data['room_id'])) {
            $data['against_user_id'] = Room::find($data['room_id'])?->user_id;
        }

        unset($data['evidence']);
        $complaint = $request->user()->complaints()->create($data);
        $complaint->activities()->create(['actor_id' => $request->user()->id, 'type' => 'created', 'status_to' => 'submitted', 'description' => 'Complaint submitted.']);

        try {
            \App\Models\AdminNotification::send(
                'complaint_submitted',
                'New Complaint Filed',
                'Ticket #' . $complaint->ticket_number . ' by ' . ($request->user()?->name ?? 'User'),
                route('admin.complaints.show', $complaint->id),
                'fa-shield-halved'
            );
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            $adminEmail = Setting::get('contact_email', config('mail.from.address'));
            Mail::to($adminEmail)->send(new BrandedMessageMail(
                "New complaint {$complaint->ticket_number}", 'A new complaint needs review',
                'A user has submitted a new complaint. Review the ticket and assign it to the appropriate team member.',
                'Admin notification', 'Review complaint', route('admin.complaints.show', $complaint),
                ['Ticket' => $complaint->ticket_number, 'Subject' => $complaint->subject], 'warning'
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        // Send acknowledgement email & notification to user
        \App\Services\NotificationService::notifyComplaintSubmitted($request->user(), $complaint);

        return redirect()->route('complaints.show', $complaint)->with('success', 'Complaint submitted. Your ticket number is ' . $complaint->ticket_number . '.');
    }

    public function show(Request $request, Complaint $complaint)
    {
        abort_unless($complaint->user_id === $request->user()->id, 403);
        $complaint->load(['room', 'replies' => fn ($query) => $query->where('is_internal', false)->with('user'), 'activities' => fn ($query) => $query->where('is_internal', false)->with('actor')]);
        return view('account.complaints.show', compact('complaint'));
    }

    public function reply(Request $request, Complaint $complaint)
    {
        abort_unless($complaint->user_id === $request->user()->id, 403);
        abort_if(in_array($complaint->status, ['closed', 'rejected'], true), 422, 'This complaint is closed.');

        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);
        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('complaints/replies', 'local');
        }
        unset($data['attachment']);
        $data['user_id'] = $request->user()->id;
        $complaint->replies()->create($data);
        $complaint->activities()->create(['actor_id' => $request->user()->id, 'type' => 'reply', 'description' => 'Reporter added information.']);

        if ($complaint->status === 'need_information') {
            $complaint->update(['status' => 'under_review']);
        }

        try {
            \App\Models\AdminNotification::send(
                'complaint_reply',
                "New Reply on #{$complaint->ticket_number}",
                "Complainant {$request->user()->name} replied to ticket #{$complaint->ticket_number}",
                route('admin.complaints.show', $complaint),
                'fa-comments'
            );
        } catch (\Throwable $ne) {
            report($ne);
        }

        return back()->with('success', 'Your reply has been added.');
    }

    public function evidence(Request $request, Complaint $complaint)
    {
        abort_unless($complaint->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        abort_unless($complaint->evidence_path && Storage::disk('local')->exists($complaint->evidence_path), 404);
        return Storage::disk('local')->download($complaint->evidence_path);
    }

    public function attachment(Request $request, Complaint $complaint, ComplaintReply $reply)
    {
        abort_unless($reply->complaint_id === $complaint->id, 404);
        abort_unless($complaint->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        abort_if($reply->is_internal && $request->user()->role !== 'admin', 403);
        abort_unless($reply->attachment_path && Storage::disk('local')->exists($reply->attachment_path), 404);
        return Storage::disk('local')->download($reply->attachment_path);
    }
}
