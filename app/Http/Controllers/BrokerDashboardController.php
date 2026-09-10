<?php

namespace App\Http\Controllers;

use App\Models\BrokerListingCredit;
use App\Models\BrokerPayment;
use App\Models\BrokerTransaction;
use App\Models\BrokerWallet;
use App\Models\Enquiry;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrokerDashboardController extends Controller
{
    public function dashboard()
    {
        $broker = Auth::user();
        abort_if($broker->role !== 'broker', 403);

        $stats = [
            'total_properties' => Room::where('broker_id', $broker->id)->count(),
            'active_properties' => Room::where('broker_id', $broker->id)->where('status', 'active')->where('listing_status', 'approved')->count(),
            'pending_properties' => Room::where('broker_id', $broker->id)->where('listing_status', 'pending')->count(),
            'expired_properties' => Room::where('broker_id', $broker->id)->where('expires_at', '<', now())->count(),
            'featured_properties' => Room::where('broker_id', $broker->id)->where('is_featured', true)->count(),
        ];

        $wallet = $broker->brokerWallet;
        $credits = BrokerListingCredit::where('broker_id', $broker->id)
            ->where('credits_remaining', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        $recentProperties = Room::where('broker_id', $broker->id)->latest()->limit(5)->get();
        $recentTransactions = BrokerTransaction::where('broker_id', $broker->id)->latest()->limit(5)->get();
        $recentPayments = BrokerPayment::where('broker_id', $broker->id)->latest()->limit(5)->get();

        return view('broker.dashboard', compact(
            'broker', 'stats', 'wallet', 'credits',
            'recentProperties', 'recentTransactions', 'recentPayments'
        ));
    }

    public function pending()
    {
        $broker = Auth::user();
        abort_if($broker->role !== 'broker', 403);

        return view('broker.pending', compact('broker'));
    }

    public function properties(Request $request)
    {
        $broker = Auth::user();

        $query = Room::where('broker_id', $broker->id);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($listingStatus = $request->get('listing_status')) {
            $query->where('listing_status', $listingStatus);
        }
        if ($purpose = $request->get('purpose')) {
            if (in_array($purpose, ['rent', 'sell'], true)) {
                $query->where('purpose', $purpose);
            }
        }

        $properties = $query->latest()->paginate(20);

        $roomCounts = [
            'all' => Room::where('broker_id', $broker->id)->count(),
            'active' => Room::where('broker_id', $broker->id)->where('status', 'active')->where('listing_status', 'approved')->count(),
            'pending' => Room::where('broker_id', $broker->id)->where('listing_status', 'pending')->count(),
            'booked' => Room::where('broker_id', $broker->id)->where('status', 'booked')->count(),
        ];

        return view('broker.properties.index', compact('properties', 'roomCounts'));
    }

    public function enquiries(Request $request)
    {
        $broker = Auth::user();

        $query = Enquiry::whereHas('room', function ($q) use ($broker) {
            $q->where('broker_id', $broker->id)
              ->orWhere('user_id', $broker->id);
        })->with(['user', 'room', 'payment']);

        if ($request->filled('status')) {
            $query->where('unlocked', $request->boolean('status'));
        }

        if ($request->filled('lead_status')) {
            $query->where('status', $request->lead_status);
        }

        $search = trim((string) $request->get('search'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('room', function ($rq) use ($search) {
                    $rq->where('title', 'like', "%{$search}%")
                       ->orWhere('city', 'like', "%{$search}%");
                });
            });
        }

        $enquiries = $query->latest()->paginate(20)->withQueryString();

        $baseCountQuery = function () use ($broker, $search) {
            $bQuery = Enquiry::whereHas('room', function ($q) use ($broker) {
                $q->where('broker_id', $broker->id)->orWhere('user_id', $broker->id);
            });
            if ($search !== '') {
                $bQuery->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                           ->orWhere('phone', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('room', function ($rq) use ($search) {
                        $rq->where('title', 'like', "%{$search}%")
                           ->orWhere('city', 'like', "%{$search}%");
                    });
                });
            }
            return $bQuery;
        };

        $statusCounts = [
            'all' => $baseCountQuery()->count(),
            'new' => $baseCountQuery()->where('status', 'new')->count(),
            'contacted' => $baseCountQuery()->where('status', 'contacted')->count(),
            'visit_scheduled' => $baseCountQuery()->where('status', 'visit_scheduled')->count(),
            'closed' => $baseCountQuery()->where('status', 'closed')->count(),
            'lost' => $baseCountQuery()->where('status', 'lost')->count(),
        ];

        return view('broker.enquiries.index', compact('enquiries', 'statusCounts'));
    }

    public function updateEnquiryStatus(Request $request, Enquiry $enquiry)
    {
        $broker = Auth::user();
        abort_if(!$broker->is_broker_active, 403);

        // Verify that the enquiry belongs to one of this broker's listings
        $room = $enquiry->room;
        abort_unless($room && ($room->broker_id === $broker->id || $room->user_id === $broker->id), 403);

        $validated = $request->validate([
            'status' => 'required|in:new,contacted,visit_scheduled,closed,lost',
            'notes' => 'nullable|string|max:1000',
        ]);

        $enquiry->update([
            'status' => $validated['status'],
            'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $enquiry->notes,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead status updated to ' . ucfirst(str_replace('_', ' ', $enquiry->status)),
                'status' => $enquiry->status,
            ]);
        }

        return back()->with('success', 'Lead status updated successfully.');
    }

    public function payments(Request $request)
    {
        $broker = Auth::user();

        $payments = BrokerPayment::where('broker_id', $broker->id)->latest()->paginate(20);

        return view('broker.payments.index', compact('payments'));
    }

    public function transactions(Request $request)
    {
        $broker = Auth::user();

        $transactions = BrokerTransaction::where('broker_id', $broker->id)->latest()->paginate(20);

        return view('broker.transactions.index', compact('transactions'));
    }

    public function profile(Request $request)
    {
        $broker = Auth::user();

        return view('broker.profile.show', compact('broker'));
    }

    public function updateProfile(Request $request)
    {
        $broker = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'agency_name' => 'nullable|string|max:255',
            'agency_address' => 'nullable|string|max:500',
            'agency_gst' => 'nullable|string|max:50',
            'broker_license' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($broker->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($broker->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($broker->avatar);
            }
            if (class_exists(\App\Services\ImageOptimizer::class)) {
                $data['avatar'] = \App\Services\ImageOptimizer::optimize($request->file('avatar'), 'avatar');
            } else {
                $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }
        }

        $broker->update($data);

        return back()->with('success', 'Profile and agency settings updated successfully.');
    }
}
