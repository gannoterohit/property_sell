@extends('layouts.public')

@section('title', 'Compare Properties Side-by-Side | ' . \App\Models\Setting::get('website_name', 'ApnaNest'))
@section('description', 'Compare rent, deposit, furnishing, and amenities of shortlisted rental properties side-by-side.')

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="container mx-auto px-4 max-w-7xl">
        
        {{-- Breadcrumb & Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                    <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
                    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                    <a href="{{ route('rooms.index') }}" class="hover:text-indigo-600 transition">Rooms</a>
                    <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
                    <span class="text-slate-800 font-bold">Compare Properties</span>
                </nav>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-indigo-600 text-white shadow-md shadow-indigo-200">
                        <i class="fas fa-scale-balanced text-lg"></i>
                    </span>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Property Comparison Matrix</h1>
                        <p class="text-xs sm:text-sm text-slate-500">Compare rent, security deposit, specifications and amenities side-by-side.</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700 transition shadow-2xs">
                    <i class="fas fa-plus text-[11px] text-indigo-600"></i> Add More Rooms
                </a>
                @if($rooms->isNotEmpty())
                    <button type="button" onclick="clearAllAndReload()" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-xs font-bold text-rose-700 transition shadow-2xs cursor-pointer">
                        <i class="fas fa-trash-can text-[11px]"></i> Clear All
                    </button>
                @endif
            </div>
        </div>

        @if($rooms->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-14 text-center max-w-xl mx-auto shadow-sm my-8">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-code-compare text-2xl"></i>
                </div>
                <h2 class="text-xl font-black text-slate-900 mb-2">No Properties to Compare Yet</h2>
                <p class="text-xs sm:text-sm text-slate-500 mb-6 leading-relaxed">
                    Select up to 3 properties while browsing to compare their rent, amenities, furnishing status and location side-by-side.
                </p>
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold shadow-md hover:shadow-lg transition">
                    <i class="fas fa-magnifying-glass text-xs"></i> Browse Rooms to Compare
                </a>
            </div>
        @else
            @php
                $minRent = $rooms->min('rent');
            @endphp

            {{-- 1 Room Alert to add more --}}
            @if($rooms->count() === 1)
                <div class="mb-6 p-4 rounded-2xl border border-indigo-200 bg-indigo-50/70 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-indigo-900">
                    <div class="flex items-center gap-2.5">
                        <i class="fas fa-circle-info text-indigo-600 text-base"></i>
                        <span>You have selected <strong>1 property</strong>. Add 1 or 2 more properties to compare them side-by-side!</span>
                    </div>
                    <a href="{{ route('rooms.index') }}" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white font-extrabold text-[11px] shadow-xs hover:bg-indigo-700 transition shrink-0">
                        + Select Another Room
                    </a>
                </div>
            @endif

            {{-- Comparison Table Wrapper --}}
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70">
                                <th class="p-4 sm:p-5 w-48 sm:w-60 min-w-[180px] align-top text-xs font-extrabold uppercase tracking-wider text-slate-400">
                                    Property
                                </th>
                                @foreach($rooms as $room)
                                    <th class="p-4 sm:p-5 min-w-[260px] max-w-[320px] align-top border-l border-slate-200/80">
                                        <div class="relative">
                                            {{-- Remove button --}}
                                            <button type="button" 
                                                    onclick="removeRoomAndReload({{ $room->id }})" 
                                                    class="absolute top-2 right-2 z-10 w-7 h-7 rounded-full bg-slate-900/70 hover:bg-rose-600 text-white flex items-center justify-center text-xs transition cursor-pointer"
                                                    title="Remove from comparison">
                                                <i class="fas fa-times"></i>
                                            </button>

                                            {{-- Thumbnail --}}
                                            <div class="relative h-44 rounded-2xl overflow-hidden bg-slate-100 shadow-2xs mb-3">
                                                <img src="{{ $room->photo_url ?: asset('assets/images/default-room.svg') }}" 
                                                     alt="{{ $room->title }}" 
                                                     class="w-full h-full object-cover"
                                                     onerror="this.onerror=null; if(window.DEFAULT_COMPARE_FALLBACK) this.src=window.DEFAULT_COMPARE_FALLBACK;">
                                                
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-50"></div>
                                                
                                                {{-- Badge --}}
                                                @if($room->isForSell())
                                                    <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-purple-600 text-white shadow-2xs">
                                                        For Sale
                                                    </span>
                                                @elseif($room->listing_type === 'broker')
                                                    <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-indigo-600 text-white shadow-2xs">
                                                        Broker Verified
                                                    </span>
                                                @else
                                                    <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-2xs">
                                                        Zero Brokerage
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="font-extrabold text-sm text-slate-900 line-clamp-2 mb-1" title="{{ $room->title }}">
                                                <a href="{{ route('rooms.show', $room->slug ?: $room->id) }}" class="hover:text-indigo-600 transition">
                                                    {{ $room->title }}
                                                </a>
                                            </h3>

                                            <p class="text-xs text-slate-500 flex items-center gap-1 mb-3 truncate">
                                                <i class="fas fa-location-dot text-indigo-500 text-[11px]"></i>
                                                {{ $room->city }}
                                            </p>

                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('rooms.show', $room->slug ?: $room->id) }}" class="flex-1 py-2 rounded-xl text-center text-xs font-black bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-2xs">
                                                    View Details
                                                </a>
                                            </div>
                                        </div>
                                    </th>
                                @endforeach

                                {{-- Placeholder column if less than 3 --}}
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <th class="p-6 min-w-[220px] align-middle text-center border-l border-slate-200/80 bg-slate-50/30">
                                        <div class="p-6 rounded-2xl border-2 border-dashed border-slate-300 text-center">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-plus text-sm"></i>
                                            </div>
                                            <p class="text-xs font-bold text-slate-700 mb-1">Add Another Room</p>
                                            <p class="text-[11px] text-slate-400 mb-3">Compare up to 3 rooms together</p>
                                            <a href="{{ route('rooms.index') }}" class="inline-block px-3 py-1.5 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold text-[11px] transition">
                                                Select Room
                                            </a>
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200/80 text-xs">
                            
                            {{-- SECTION: PRICING --}}
                            <tr class="bg-slate-100/70">
                                <td colspan="{{ 1 + max(3, $rooms->count()) }}" class="py-2.5 px-4 sm:px-5 font-black uppercase tracking-wider text-[11px] text-slate-700 flex items-center gap-2">
                                    <i class="fas fa-indian-rupee-sign text-emerald-600"></i> Financials & Pricing
                                </td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Price / Rent</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80">
                                        @if($room->isForSell())
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-lg font-black text-purple-700">{{ $room->displayPrice() }}</span>
                                                <span class="text-purple-600 text-[10px] font-bold bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">Sale Price</span>
                                            </div>
                                        @else
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="text-lg font-black text-slate-900">₹{{ number_format($room->rent) }}</span>
                                                <span class="text-slate-400 text-[10px]">/month</span>
                                            </div>
                                            @if($rooms->count() > 1 && (float)$room->rent === (float)$minRent)
                                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <i class="fas fa-award text-[9px]"></i> Lowest Rent
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Security Deposit</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 font-bold text-slate-800">
                                        @if($room->isForSell())
                                            <span class="text-slate-400 font-normal">N/A (Property Sale)</span>
                                        @else
                                            {{ $room->deposit ? '₹' . number_format($room->deposit) : 'Nil / Negotiable' }}
                                        @endif
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Brokerage / Commission</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80">
                                        @if($room->listing_type === 'broker')
                                            <span class="font-bold text-purple-700">₹{{ number_format((float)$room->broker_fee) }} (Broker Fee)</span>
                                        @else
                                            <span class="font-extrabold text-emerald-700 flex items-center gap-1">
                                                <i class="fas fa-check-circle text-emerald-500"></i> ₹0 (Zero Brokerage)
                                            </span>
                                        @endif
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            {{-- SECTION: PROPERTY SPECS --}}
                            <tr class="bg-slate-100/70">
                                <td colspan="{{ 1 + max(3, $rooms->count()) }}" class="py-2.5 px-4 sm:px-5 font-black uppercase tracking-wider text-[11px] text-slate-700 flex items-center gap-2">
                                    <i class="fas fa-sliders text-indigo-600"></i> Property Specifications
                                </td>
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Room / Flat Type</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 font-bold text-slate-800">
                                        {{ $room->roomTypeOption?->label ?? $room->type ?? 'Room' }}
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Furnishing Status</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 font-bold text-slate-800">
                                        {{ $room->furnishingOption?->label ?? 'Semi-Furnished' }}
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Tenant Preference</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 font-bold text-slate-800">
                                        {{ $room->tenantOption?->label ?? 'Any (Bachelors/Family)' }}
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Carpet / Super Area</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 font-bold text-slate-800">
                                        {{ $room->area_sqft ? number_format($room->area_sqft) . ' sq.ft.' : 'Not specified' }}
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            <tr>
                                <td class="p-4 sm:p-5 font-bold text-slate-500 bg-slate-50/40">Availability</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 font-bold text-slate-800">
                                        {{ $room->availability_from ? \Carbon\Carbon::parse($room->availability_from)->format('d M, Y') : 'Ready to Move' }}
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                @endfor
                            </tr>

                            {{-- SECTION: AMENITIES MATRIX --}}
                            <tr class="bg-slate-100/70">
                                <td colspan="{{ 1 + max(3, $rooms->count()) }}" class="py-2.5 px-4 sm:px-5 font-black uppercase tracking-wider text-[11px] text-slate-700 flex items-center gap-2">
                                    <i class="fas fa-circle-check text-indigo-600"></i> Amenities Comparison Matrix
                                </td>
                            </tr>

                            @foreach($allAmenities as $amenity)
                                @php
                                    $amenityLower = mb_strtolower((string)$amenity);
                                @endphp
                                <tr>
                                    <td class="p-4 sm:p-5 font-semibold text-slate-600 bg-slate-50/40">
                                        {{ $amenity }}
                                    </td>
                                    @foreach($rooms as $room)
                                        @php
                                            $roomAmenities = collect($room->amenities ?? [])->map(fn($a) => mb_strtolower((string)$a))->all();
                                            $hasAmenity = in_array($amenityLower, $roomAmenities, true);
                                        @endphp
                                        <td class="p-4 sm:p-5 border-l border-slate-200/80">
                                            @if($hasAmenity)
                                                <span class="inline-flex items-center gap-1.5 font-bold text-emerald-600">
                                                    <i class="fas fa-check-circle text-emerald-500"></i> Available
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-slate-400">
                                                    <i class="fas fa-times-circle text-slate-300"></i> Not Available
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                    @for($i = $rooms->count(); $i < 3; $i++)
                                        <td class="p-4 sm:p-5 border-l border-slate-200/80 text-slate-300 text-center">—</td>
                                    @endfor
                                </tr>
                            @endforeach

                            {{-- BOTTOM ACTIONS ROW --}}
                            <tr class="bg-slate-50/90">
                                <td class="p-4 sm:p-5 font-black text-slate-700">Actions</td>
                                @foreach($rooms as $room)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80">
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ route('rooms.show', $room->slug ?: $room->id) }}" class="w-full py-2 rounded-xl text-center text-xs font-black bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-xs">
                                                View Room
                                            </a>
                                            <button type="button" onclick="removeRoomAndReload({{ $room->id }})" class="w-full py-1.5 rounded-xl text-center text-[11px] font-bold text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                @endforeach
                                @for($i = $rooms->count(); $i < 3; $i++)
                                    <td class="p-4 sm:p-5 border-l border-slate-200/80 text-center">
                                        <a href="{{ route('rooms.index') }}" class="inline-block px-4 py-2 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 font-extrabold text-xs transition">
                                            + Add Room
                                        </a>
                                    </td>
                                @endfor
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
function removeRoomAndReload(id) {
    id = Number(id);
    if (typeof removeCompareRoom === 'function') {
        removeCompareRoom(id);
    } else {
        try {
            let rooms = JSON.parse(localStorage.getItem('compare_rooms') || '[]');
            rooms = rooms.filter(r => Number(r.id) !== id);
            localStorage.setItem('compare_rooms', JSON.stringify(rooms));
        } catch(e) {}
    }

    // Refresh with updated IDs
    const currentRooms = typeof getCompareRooms === 'function' ? getCompareRooms() : JSON.parse(localStorage.getItem('compare_rooms') || '[]');
    const ids = currentRooms.map(r => r.id);
    if (ids.length > 0) {
        window.location.href = `{{ route('rooms.compare') }}?ids=${ids.join(',')}`;
    } else {
        window.location.href = `{{ route('rooms.compare') }}`;
    }
}

function clearAllAndReload() {
    if (typeof clearCompareRooms === 'function') {
        clearCompareRooms();
    } else {
        try { localStorage.removeItem('compare_rooms'); } catch(e) {}
    }
    window.location.href = `{{ route('rooms.compare') }}`;
}

// Auto-sync query params with localStorage if user visited without query params
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const hasQueryIds = urlParams.has('ids') && urlParams.get('ids').trim() !== '';

    if (!hasQueryIds && typeof getCompareRooms === 'function') {
        const stored = getCompareRooms();
        if (stored.length > 0) {
            const ids = stored.map(r => r.id).join(',');
            window.location.replace(`{{ route('rooms.compare') }}?ids=${ids}`);
        }
    }
});
</script>
@endsection
