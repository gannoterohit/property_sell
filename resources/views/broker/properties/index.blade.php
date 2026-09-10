@extends('layouts.broker')

@section('title', 'My Properties')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
@endpush

@section('broker-content')
<div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-page-header">
        <div>
            <h2 class="agent-page-header-title">Your Properties</h2>
            <p class="agent-page-header-sub">Manage your listings, pricing, and availability.</p>
        </div>
        @if(Auth::user()->is_broker_active)
            <a href="{{ route('agent.rooms.create') }}" class="agent-page-header-action">
                <i class="fas fa-plus"></i> Add Property
            </a>
        @else
            <button type="button" onclick="alert('Aapka broker account abhi verification me pending hai. Admin approval milne ke baad hi aap nayi property add kar sakte hain.');" class="agent-page-header-action opacity-60 cursor-not-allowed bg-slate-200 text-slate-600 border border-slate-300 hover:bg-slate-200 shadow-none" title="Account Pending Approval">
                <i class="fas fa-lock"></i> Add Property (Locked)
            </button>
        @endif
    </div>

    {{-- Stat Tiles --}}
    <div class="owner-room-stats">
        @foreach([
            [$roomCounts['all'],    'All Properties', 'fa-building',     'stat-indigo'],
            [$roomCounts['active'], 'Active',          'fa-circle-check', 'stat-emerald'],
            [$roomCounts['pending'],'Pending',         'fa-clock',        'stat-amber'],
            [$roomCounts['booked'], 'Rented',          'fa-key',          'stat-rose'],
        ] as [$count, $label, $icon, $color])
            <div class="owner-room-stat">
                <div class="owner-room-stat-row">
                    <i class="fas {{ $icon }} owner-room-stat-icon {{ $color }}"></i>
                    <span class="owner-room-stat-label">{{ $label }}</span>
                </div>
                <div class="owner-room-stat-value">{{ $count }}</div>
            </div>
        @endforeach
    </div>

    {{-- Listing Grid --}}
    <section class="owner-listing-section">
        <div class="owner-listing-heading">
            <div>
                <h2 class="owner-listing-title">All Listings</h2>
                <p class="owner-listing-sub">Click Edit to update details, pricing or availability.</p>
            </div>
            <span class="owner-listing-count">
                {{ $properties->total() }} {{ Str::plural('property', $properties->total()) }}
            </span>
        </div>

        @if($properties->count())
            <div class="owner-room-grid">
                @foreach($properties as $property)
                    @php
                        $st = $property->status;
                        $badgeClass = $st === 'active' ? 'badge-active' : ($st === 'pending' ? 'badge-pending' : ($st === 'booked' ? 'badge-booked' : 'badge-default'));
                        $label = $st === 'booked' ? ($property->isForSell() ? 'Sold' : 'Rented') : ucfirst($st);
                    @endphp
                    <article class="owner-room-card">
                        <div class="owner-room-media">
                            <div class="owner-room-placeholder"><i class="fas fa-house"></i></div>
                            @if($property->photo_url)
                                <img src="{{ $property->photo_url }}" alt="{{ $property->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                            @endif
                            @if($property->isForSell())
                                <span class="owner-room-status-badge" style="left: 12px; right: auto; background: #7c3aed; color: #ffffff; font-weight: 800; font-size: 10px; letter-spacing: 0.5px;">
                                    FOR SALE
                                </span>
                            @endif
                            <span class="owner-room-status-badge {{ $badgeClass }}">
                                <span class="badge-dot"></span>{{ $label }}
                            </span>
                        </div>
                        <div class="owner-room-body">
                            <div class="owner-room-meta">
                                <div style="min-width:0">
                                    <p class="owner-room-name">{{ $property->title }}</p>
                                    <p class="owner-room-loc"><i class="fas fa-location-dot"></i>{{ $property->city }}{{ $property->state ? ', '.$property->state : '' }}</p>
                                </div>
                                <div class="owner-room-price">
                                    <span class="owner-room-price-amt">{{ $property->displayPrice() }}</span>
                                    <span class="owner-room-price-unit">{{ $property->isForSell() ? 'Total Price' : 'per month' }}</span>
                                </div>
                            </div>

                            @if($property->expires_at)
                                @php
                                    $daysRemaining = $property->expiresInDays();
                                @endphp
                                <div class="mt-2 text-xs">
                                    @if($daysRemaining < 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 font-bold text-rose-700">
                                            <i class="fas fa-circle-exclamation text-[10px]"></i> Expired {{ abs($daysRemaining) }}d ago
                                        </span>
                                    @elseif($daysRemaining <= 7)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 font-bold text-amber-700">
                                            <i class="fas fa-triangle-exclamation text-[10px]"></i> Expires in {{ $daysRemaining }} day{{ $daysRemaining == 1 ? '' : 's' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                            <i class="fas fa-calendar-check text-[10px]"></i> Active till {{ $property->expires_at->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                            <div class="owner-room-actions">
                                <a href="{{ route('agent.rooms.show', $property) }}" class="owner-room-btn owner-room-btn-outline" title="Preview listing">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('agent.rooms.edit', $property) }}" class="owner-room-btn owner-room-btn-indigo" title="Edit details">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                <form action="{{ route('agent.rooms.duplicate', $property) }}" method="POST" class="inline" onsubmit="return confirm('Clone this listing to quickly add another unit in the same property?');">
                                    @csrf
                                    <button type="submit" class="owner-room-btn owner-room-btn-outline" title="Quick clone/duplicate unit">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </form>
                                <button type="button" 
                                        data-share-title="{{ $property->title }}"
                                        data-share-price="{{ $property->displayPrice() }}"
                                        data-share-purpose="{{ $property->purpose ?? 'rent' }}"
                                        data-share-deposit="{{ $property->deposit ? number_format($property->deposit) : '' }}"
                                        data-share-city="{{ $property->city }}"
                                        data-share-link="{{ route('rooms.show', $property) }}"
                                        onclick="shareBrochureFromBtn(this)" 
                                        class="owner-room-btn owner-room-btn-outline" 
                                        style="color: #059669; border-color: #a7f3d0;" 
                                        title="Share WhatsApp brochure with clients">
                                    <i class="fa-brands fa-whatsapp text-emerald-600"></i> Share
                                </button>
                                @if($property->status === 'active')
                                    <button type="button" onclick="markRoomRented({{ $property->id }}, '{{ $property->isForSell() ? 'sold' : 'rented' }}')" class="owner-room-btn owner-room-btn-rose owner-room-btn-full">
                                        <i class="fas fa-key"></i> Mark as {{ $property->isForSell() ? 'Sold' : 'Rented' }}
                                    </button>
                                @elseif($property->status === 'booked')
                                    <button type="button" onclick="makeRoomAvailable({{ $property->id }})" class="owner-room-btn owner-room-btn-green owner-room-btn-full">
                                        <i class="fas fa-rotate"></i> Make Available
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="agent-empty-state">
                <i class="fas fa-house-circle-xmark"></i>
                <h2>No properties listed yet</h2>
                <p>Add your first property and start receiving enquiries from clients.</p>
                @if(Auth::user()->is_broker_active)
                    <a href="{{ route('agent.rooms.create') }}" class="agent-empty-btn">
                        <i class="fas fa-plus"></i> Add Your First Property
                    </a>
                @else
                    <button type="button" onclick="alert('Aapka broker account abhi verification me pending hai. Admin approval ke baad hi aap property create kar sakte hain.');" class="agent-empty-btn opacity-60 cursor-not-allowed bg-slate-200 text-slate-600 border border-slate-300 hover:bg-slate-200 shadow-none" title="Account Pending Approval">
                        <i class="fas fa-lock"></i> Add Property (Account Pending Approval)
                    </button>
                @endif
            </div>
        @endif

        @if($properties->hasPages())
            <div style="margin-top:2rem">{{ $properties->links() }}</div>
        @endif
    </section>
</div>

@push('scripts')
<script>
const agentRoomCsrf = '{{ csrf_token() }}';
async function agentRoomPost(url, payload = {}) {
    const response = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': agentRoomCsrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) });
    const data = await response.json().catch(() => ({ success: false, message: 'Invalid server response' }));
    if (!response.ok) throw new Error(data.message || 'Request failed');
    return data;
}
async function markRoomRented(roomId, actionType = 'rented') {
    const isSold = (actionType === 'sold');
    const label = isSold ? 'sold' : 'rented';
    const result = await Swal.fire({ 
        title: `Mark property as ${label}?`, 
        text: 'This property will stop appearing to property seekers.', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonText: `Yes, mark ${label}`, 
        confirmButtonColor: '#e11d48' 
    });
    if (!result.isConfirmed) return;
    try { 
        const data = await agentRoomPost(`{{ route('agent.rooms.markBooked', ':room') }}`.replace(':room', roomId)); 
        await Swal.fire(`Property marked as ${label}`, data.message, 'success'); 
        location.reload(); 
    } catch (error) { 
        Swal.fire('Could not update property', error.message, 'error'); 
    }
}
async function makeRoomAvailable(roomId) {
    const confirmation = await Swal.fire({
        title: 'Make property available?',
        text: 'This will use 1 listing credit from your plan and make the property live again.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Make Available (1 Credit)',
        confirmButtonColor: '#059669'
    });
    if (!confirmation.isConfirmed) return;
    try {
        const data = await agentRoomPost(`{{ route('agent.rooms.markAvailable', ':room') }}`.replace(':room', roomId));
        await Swal.fire('Property Available!', data.message || 'Your property is now live and visible to property seekers.', 'success');
        location.reload();
    } catch (error) {
        Swal.fire({
            title: 'Credits Required',
            text: error.message || 'You have 0 listing credits remaining. Please buy more credits to activate this property.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Buy Listing Credits',
            confirmButtonColor: '#4f46e5'
        }).then((res) => {
            if (res.isConfirmed) {
                window.location.href = '{{ route("agent.plans") }}';
            }
        });
    }
}
function shareBrochureFromBtn(btn) {
    if (!btn) return;
    var title = btn.getAttribute('data-share-title') || 'Property';
    var price = btn.getAttribute('data-share-price') || '0';
    var purpose = btn.getAttribute('data-share-purpose') || 'rent';
    var deposit = btn.getAttribute('data-share-deposit') || '';
    var city = btn.getAttribute('data-share-city') || '';
    var link = btn.getAttribute('data-share-link') || window.location.href;
    sharePropertyBrochure(title, price, deposit, city, link, purpose);
}

function sharePropertyBrochure(title, price, deposit, city, link, purpose) {
    var agency = @json(Auth::user()->agency_name ?: Auth::user()->name);
    var isSell = (purpose === 'sell');
    var priceLine = isSell ? ("💰 *Sale Price:* " + price + "\n") : ("💰 *Rent:* " + price + (deposit ? " | *Deposit:* ₹" + deposit : "") + "\n");
    var text = "🏠 *" + title + "* (" + (isSell ? 'For Sale' : 'For Rent') + ")\n" +
               priceLine +
               (city ? "📍 *Location:* " + city + "\n" : "") +
               "✨ *Managed by:* " + agency + " (Verified Agent)\n\n" +
               "📸 *Photos, Video & Complete Details:* \n" + link + "\n\n" +
               "_Interested? Reply to this message for immediate visit scheduling!_";

    var waUrl = "https://wa.me/?text=" + encodeURIComponent(text);
    window.open(waUrl, '_blank');
}
</script>
@endpush
@endsection
