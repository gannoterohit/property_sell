@extends('layouts.broker')

@section('title', 'Leads & Enquiries')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
<style>
.lead-filter-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 16px 0 24px;
}
.lead-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
    text-decoration: none;
    transition: all 0.2s ease;
}
.lead-filter-pill:hover {
    border-color: #cbd5e1;
    color: #0f172a;
}
.lead-filter-pill.active {
    background: #4f46e5;
    color: #ffffff;
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
}
.lead-filter-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 7px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    background: rgba(0, 0, 0, 0.08);
}
.lead-filter-pill.active .lead-filter-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}
.lead-status-select {
    padding: 6px 10px;
    border-radius: 10px;
    font-size: 11.5px;
    font-weight: 800;
    border: 1px solid #cbd5e1;
    background-color: #f8fafc;
    cursor: pointer;
    outline: none;
    transition: all 0.2s ease;
}
.lead-status-select:focus {
    border-color: #6366f1;
    background-color: #ffffff;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
}
.lead-badge-new { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
.lead-badge-contacted { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.lead-badge-visit_scheduled { background: #fdf4ff; color: #9333ea; border-color: #f0abfc; }
.lead-badge-closed { background: #f0fdf4; color: #16a34a; border-color: #86efac; font-weight: 900; }
.lead-badge-lost { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }
</style>
@endpush

@section('broker-content')
@php
    $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
@endphp
<div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-page-header">
        <div>
            <h2 class="agent-page-header-title">Leads &amp; Enquiries</h2>
            <p class="agent-page-header-sub">Track potential tenants, schedule visits and close rental deals.</p>
        </div>
    </div>

    {{-- Stat Tiles Funnel --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold mb-1">
                <span>Total Leads</span>
                <i class="fas fa-users text-indigo-500"></i>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $statusCounts['all'] ?? 0 }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold mb-1">
                <span>New / Uncontacted</span>
                <i class="fas fa-bell text-amber-500"></i>
            </div>
            <div class="text-2xl font-black text-amber-600">{{ $statusCounts['new'] ?? 0 }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold mb-1">
                <span>Visits Scheduled</span>
                <i class="fas fa-calendar-check text-purple-500"></i>
            </div>
            <div class="text-2xl font-black text-purple-600">{{ $statusCounts['visit_scheduled'] ?? 0 }}</div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-bold mb-1">
                <span>Deals Closed</span>
                <i class="fas fa-trophy text-emerald-500"></i>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $statusCounts['closed'] ?? 0 }}</div>
        </div>
    </div>

    {{-- Search & Filter Toolbar --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mt-5 mb-3">
        <form action="{{ route('agent.enquiries') }}" method="GET" class="relative flex-1 max-w-md">
            @if(request('lead_status'))
                <input type="hidden" name="lead_status" value="{{ request('lead_status') }}">
            @endif
            <div class="relative flex items-center">
                <i class="fas fa-search absolute left-3.5 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Search by tenant name, phone, or property..." 
                       class="w-full pl-9 pr-20 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-2xs">
                @if(request('search'))
                    <a href="{{ route('agent.enquiries', request()->except('search', 'page')) }}" 
                       class="absolute right-16 text-slate-400 hover:text-slate-600 text-xs" 
                       title="Clear search">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <button type="submit" class="absolute right-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-bold transition shadow-xs">
                    Search
                </button>
            </div>
        </form>

        @if(request('search') || request('lead_status'))
            <a href="{{ route('agent.enquiries') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:text-rose-600 hover:border-rose-200 transition shadow-2xs shrink-0 self-start sm:self-auto">
                <i class="fas fa-rotate-left text-[10px]"></i> Clear Filters
            </a>
        @endif
    </div>

    {{-- Filter Pills --}}
    <div class="lead-filter-pills">
        <a href="{{ route('agent.enquiries', array_merge(request()->except('lead_status', 'page'), [])) }}" class="lead-filter-pill {{ !request('lead_status') ? 'active' : '' }}">
            <span>All Leads</span>
            <span class="lead-filter-count">{{ $statusCounts['all'] ?? 0 }}</span>
        </a>
        <a href="{{ route('agent.enquiries', array_merge(request()->except('lead_status', 'page'), ['lead_status' => 'new'])) }}" class="lead-filter-pill {{ request('lead_status') === 'new' ? 'active' : '' }}">
            <span>New Leads</span>
            <span class="lead-filter-count">{{ $statusCounts['new'] ?? 0 }}</span>
        </a>
        <a href="{{ route('agent.enquiries', array_merge(request()->except('lead_status', 'page'), ['lead_status' => 'contacted'])) }}" class="lead-filter-pill {{ request('lead_status') === 'contacted' ? 'active' : '' }}">
            <span>Contacted</span>
            <span class="lead-filter-count">{{ $statusCounts['contacted'] ?? 0 }}</span>
        </a>
        <a href="{{ route('agent.enquiries', array_merge(request()->except('lead_status', 'page'), ['lead_status' => 'visit_scheduled'])) }}" class="lead-filter-pill {{ request('lead_status') === 'visit_scheduled' ? 'active' : '' }}">
            <span>Visit Scheduled</span>
            <span class="lead-filter-count">{{ $statusCounts['visit_scheduled'] ?? 0 }}</span>
        </a>
        <a href="{{ route('agent.enquiries', array_merge(request()->except('lead_status', 'page'), ['lead_status' => 'closed'])) }}" class="lead-filter-pill {{ request('lead_status') === 'closed' ? 'active' : '' }}">
            <span>Deal Closed</span>
            <span class="lead-filter-count">{{ $statusCounts['closed'] ?? 0 }}</span>
        </a>
        <a href="{{ route('agent.enquiries', array_merge(request()->except('lead_status', 'page'), ['lead_status' => 'lost'])) }}" class="lead-filter-pill {{ request('lead_status') === 'lost' ? 'active' : '' }}">
            <span>Lost</span>
            <span class="lead-filter-count">{{ $statusCounts['lost'] ?? 0 }}</span>
        </a>
    </div>

    {{-- Leads Grid --}}
    <section class="owner-listing-section">
        <div class="owner-listing-heading">
            <div>
                <h2 class="owner-listing-title">Enquiry List</h2>
                <p class="owner-listing-sub">Direct interested tenants who unlocked or sent inquiries for your properties.</p>
            </div>
            <span class="owner-listing-count">
                {{ $enquiries->total() }} {{ Str::plural('lead', $enquiries->total()) }}
            </span>
        </div>

        @if($enquiries->count())
            <div class="owner-room-grid">
                @foreach($enquiries as $enquiry)
                    @php
                        $room    = $enquiry->room;
                        $seeker  = $enquiry->user;
                        $gateway = ucfirst(str_replace('_', ' ', $enquiry->payment?->gateway ?? 'direct'));
                        $currentStatus = $enquiry->status ?: 'new';
                    @endphp
                    <article class="owner-room-card" id="enquiry-card-{{ $enquiry->id }}">
                        {{-- Room thumbnail --}}
                        <div class="owner-room-media">
                            <div class="owner-room-placeholder"><i class="fas fa-home"></i></div>
                            @if($room && $room->photo_url)
                                <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="owner-room-status-badge {{ $enquiry->unlocked ? 'badge-active' : 'badge-pending' }}">
                                <span class="badge-dot"></span>{{ $enquiry->unlocked ? 'Contact Unlocked' : 'Direct Inquiry' }}
                            </span>
                        </div>

                        <div class="owner-room-body">
                            {{-- Seeker Info --}}
                            <div class="owner-lead-info">
                                <div class="owner-lead-avatar">
                                    {{ strtoupper(substr($seeker->name ?? 'U', 0, 1)) }}
                                </div>
                                <div style="min-width:0" class="flex-1">
                                    <p class="owner-lead-name font-bold text-slate-900">{{ $seeker->name ?? 'Tenant User' }}</p>
                                    <p class="owner-lead-prop truncate text-xs text-slate-500">
                                        <i class="fas fa-home text-indigo-500 mr-1"></i>
                                        {{ $room->title ?? 'Property' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Details Grid --}}
                            <div class="owner-lead-grid">
                                <div class="owner-lead-row">
                                    <i class="fas fa-location-dot" style="color:#f43f5e"></i>
                                    <span>{{ $room?->city ?? '—' }}</span>
                                </div>
                                <div class="owner-lead-row">
                                    <i class="fas fa-rupee-sign" style="color:#10b981"></i>
                                    <span>&#8377;{{ number_format($room?->rent ?? 0) }}/mo</span>
                                </div>
                                <div class="owner-lead-row">
                                    <i class="fas fa-calendar" style="color:#6366f1"></i>
                                    <span>{{ ($enquiry->unlocked_at ?? $enquiry->created_at)->format('d M Y, h:i A') }}</span>
                                </div>
                                <div class="owner-lead-row">
                                    <i class="fas fa-phone" style="color:#0ea5e9"></i>
                                    <span>{{ $seeker?->phone ?: 'Phone not provided' }}</span>
                                </div>
                            </div>

                            {{-- Lead Status & Followup Dropdown --}}
                            <div class="my-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Status:</span>
                                <select onchange="updateLeadStatus({{ $enquiry->id }}, this.value)"
                                        class="lead-status-select lead-badge-{{ $currentStatus }}"
                                        id="status-select-{{ $enquiry->id }}">
                                    <option value="new" {{ $currentStatus === 'new' ? 'selected' : '' }}>🟢 New Lead</option>
                                    <option value="contacted" {{ $currentStatus === 'contacted' ? 'selected' : '' }}>🔵 Contacted</option>
                                    <option value="visit_scheduled" {{ $currentStatus === 'visit_scheduled' ? 'selected' : '' }}>🟣 Visit Scheduled</option>
                                    <option value="closed" {{ $currentStatus === 'closed' ? 'selected' : '' }}>🏆 Deal Closed</option>
                                    <option value="lost" {{ $currentStatus === 'lost' ? 'selected' : '' }}>⚫ Lost</option>
                                </select>
                            </div>

                            {{-- Actions --}}
                            @if($room)
                            <div class="owner-room-actions" style="grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .35rem;">
                                <a href="{{ route('agent.rooms.show', $room) }}" target="_blank" class="owner-room-btn owner-room-btn-outline" title="View Property">
                                    <i class="fas fa-eye"></i> View
                                </a>

                                @if(!empty($seeker?->phone))
                                    @php
                                        $seekerDigits = preg_replace('/[^0-9]/', '', $seeker->phone);
                                        if(strlen($seekerDigits) === 10) { $seekerDigits = '91' . $seekerDigits; }
                                        elseif(strlen($seekerDigits) === 11 && str_starts_with($seekerDigits, '0')) { $seekerDigits = '91' . substr($seekerDigits, 1); }
                                        $roomUrl = route('rooms.show', $room);
                                        $priceLine = $room->isForSell() ? "💰 Price: " . $room->displayPrice() : "💰 Rent: ₹" . number_format($room->rent ?? 0) . "/month";
                                        $propType = $room->isForSell() ? 'Property for Sale' : 'Rental Property';
                                        $waMsg = "Hello " . ($seeker->name ?? 'Sir/Madam') . "! 👋\n\nAapne hamari property me interest show kiya tha:\n🏠 *" . ($room->title ?? $propType) . "*\n📍 *" . ($room->city ?? '') . "*\n" . $priceLine . "\n\nIs property ki complete photos & details yahan dekhein:\n" . $roomUrl . "\n\nAap kab visit karna chahenge? Please batayein.";
                                    @endphp
                                    <a href="tel:{{ $seeker->phone }}" class="owner-room-btn owner-room-btn-indigo" title="Call Seeker">
                                        <i class="fas fa-phone-alt"></i> Call
                                    </a>
                                    <a href="https://wa.me/{{ $seekerDigits }}?text={{ rawurlencode($waMsg) }}" target="_blank" rel="noopener" class="owner-room-btn owner-room-btn-green" title="Send WhatsApp Brochure" style="background:#10b981;color:#fff;">
                                        <i class="fa-brands fa-whatsapp"></i> Chat
                                    </a>
                                @else
                                    <a href="mailto:{{ $seeker->email ?? '' }}" class="owner-room-btn owner-room-btn-indigo" style="grid-column: span 2;" title="Send Email">
                                        <i class="fas fa-envelope"></i> Email
                                    </a>
                                @endif
                            </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="agent-empty-state text-center py-16 bg-white rounded-3xl border border-slate-200 shadow-xs">
                <i class="fas fa-inbox text-slate-300 text-4xl mb-3 block"></i>
                <h2 class="text-base font-black text-slate-800 mb-1">No leads found</h2>
                <p class="text-xs text-slate-500">
                    @if(request('lead_status'))
                        No leads match the status "{{ ucfirst(str_replace('_', ' ', request('lead_status'))) }}".
                        <br><a href="{{ route('agent.enquiries') }}" class="text-indigo-600 font-bold hover:underline mt-2 inline-block">View all leads</a>
                    @else
                        When tenants show interest or contact you for your listings, their inquiries will appear here.
                    @endif
                </p>
            </div>
        @endif

        @if($enquiries->hasPages())
            <div style="margin-top:2rem">{{ $enquiries->links() }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
function updateLeadStatus(enquiryId, newStatus) {
    const select = document.getElementById(`status-select-${enquiryId}`);
    if (!select) return;

    select.disabled = true;

    fetch(`{{ url('agent/enquiries') }}/${enquiryId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        select.disabled = false;
        if (data.success) {
            // Update select styling class
            select.className = `lead-status-select lead-badge-${newStatus}`;

            // Toast feedback
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message || 'Status updated!',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } else {
            alert(data.message || 'Could not update status');
        }
    })
    .catch(err => {
        select.disabled = false;
        console.error('Error updating status:', err);
    });
}
</script>
@endpush
