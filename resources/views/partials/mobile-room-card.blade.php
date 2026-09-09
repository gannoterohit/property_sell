<!-- Mobile App Room Card - Enhanced Mobile First Design -->
<div class="mobile-room-card lg:hidden mb-4 bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100 active:scale-[0.98] transition-all duration-200">
    <!-- Image Section -->
    <a href="{{ route('rooms.show', $room) }}" class="block relative h-52 w-full bg-slate-100" aria-label="View details for {{ $room->title }}">
        @if($room->photo_url)
            @php
                $photoUrl = $room->photo_url;
                if (str_contains($photoUrl, 'unsplash.com')) {
                    $baseUrl = strtok($photoUrl, '?');
                    $tinyUrl = $baseUrl . '?w=400&h=300&fit=crop&fm=webp&q=75';
                } else {
                    $tinyUrl = $photoUrl;
                }
            @endphp
            <img src="{{ $tinyUrl }}" 
                 class="w-full h-full object-cover" 
                 alt="Photo of {{ $room->title }} in {{ $room->city }}"
                 loading="lazy"
                 decoding="async"
                 onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=Room+Image';">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50">
                <i class="fas fa-house-chimney text-4xl text-slate-300 mb-2"></i>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">No Image</span>
            </div>
        @endif
        
        <!-- Tags Overlay - Top Left -->
        <div class="absolute top-3 left-3 flex flex-col gap-1.5">
            <div class="flex flex-wrap gap-1.5">
                <span class="bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-lg text-[9px] font-extrabold text-slate-800 shadow-sm uppercase tracking-wider">
                    {{ $room->roomTypeLabel() }}
                </span>
                {{-- Purpose badge --}}
                @if($room->isForSell())
                    <span class="bg-purple-600 text-white px-2 py-1 rounded-md text-[9px] font-black shadow-sm uppercase tracking-wider flex items-center gap-1">
                        <i class="fas fa-tag text-[8px]"></i> For Sale
                    </span>
                @else
                    <span class="bg-emerald-500 text-white px-2 py-1 rounded-md text-[9px] font-black shadow-sm uppercase tracking-wider flex items-center gap-1">
                        <i class="fas fa-key text-[8px]"></i> For Rent
                    </span>
                @endif
                @if($room->listing_type === 'broker')
                    <span class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-2 py-1 rounded-md text-[9px] font-black shadow-sm uppercase tracking-wider flex items-center gap-1">
                        <i class="fas fa-building text-[8px]"></i> Verified Agency
                    </span>
                @else
                    <span class="bg-emerald-500 text-white px-2 py-1 rounded-md text-[9px] font-black shadow-sm uppercase tracking-wider">
                        No Broker Fee
                    </span>
                @endif
                @if($room->is_featured)
                    <span class="bg-amber-400 text-amber-900 px-2 py-1 rounded-md text-[9px] font-black shadow-sm uppercase tracking-wider">
                        Featured
                    </span>
                @endif
            </div>
        </div>

        <!-- Tags Overlay - Top Right -->
        <div class="absolute top-3 right-3">
            @if($room->propertyType?->name)
                <span class="text-white text-[9px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg" style="background: rgba(var(--primary-rgb), 0.9);">
                    {{ $room->propertyType->name }}
                </span>
            @endif
        </div>

        <!-- Bottom Gradient Overlay -->
        <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
    </a>
    
    <!-- Content Section -->
    <div class="p-4">
        <!-- Title & Location -->
        <div class="mb-3">
            <h2 class="font-black text-base text-slate-900 leading-tight line-clamp-2 mb-1.5">
                <a href="{{ route('rooms.show', $room->id) }}" class="transition-colors" style="color: var(--primary);">{{ $room->title }}</a>
            </h2>
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <div class="flex items-center">
                    <i class="fas fa-location-dot mr-1.5 text-[10px]" style="color: var(--primary);"></i>
                    <span class="truncate">{{ $room->city }}</span>
                </div>
                @if($room->listing_type === 'broker' && $room->user)
                    <a href="{{ route('agency.show', $room->user) }}" class="text-[11px] font-extrabold text-indigo-600 hover:underline flex items-center gap-1">
                        <i class="fas fa-building text-[10px]"></i>
                        <span class="truncate max-w-[130px]">{{ $room->user->agency_name ?: $room->user->name }}</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Property Tags -->
        <div class="flex flex-wrap gap-1.5 mb-3">
            @if($room->propertyType?->name)
                <span class="bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-lg">
                    {{ $room->propertyType->name }}
                </span>
            @endif
            @if($room->propertyCategory?->name)
                <span class="text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-lg" style="background: rgba(var(--primary-rgb), 0.08); color: var(--primary);">
                    {{ $room->propertyCategory->name }}
                </span>
            @endif
        </div>

        <!-- Amenities Row -->
        <div class="flex gap-2 mb-4 overflow-x-auto hide-scrollbar">
            <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100 whitespace-nowrap">
                <i class="fas fa-couch text-[9px]" style="color: var(--primary);"></i>
                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $room->furnishingTypeLabel() }}</span>
            </div>
            <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100 whitespace-nowrap">
                <i class="fas fa-users text-[9px]" style="color: var(--primary);"></i>
                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $room->tenantTypeLabel() }}</span>
            </div>
            @if($room->area_sqft)
            <div class="flex items-center gap-1.5 bg-slate-50 px-2.5 py-1.5 rounded-lg border border-slate-100 whitespace-nowrap">
                <i class="fas fa-ruler-combined text-[9px]" style="color: var(--primary);"></i>
                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ number_format((float)$room->area_sqft, 2) }} sqft</span>
            </div>
            @endif
        </div>

        <!-- Price & Action -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <div class="flex flex-col">
                @if($room->isForSell())
                    <span class="text-xl font-black" style="color: var(--primary);">{{ $room->displayPrice() }}</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Sale Price</span>
                @else
                    <span class="text-xl font-black" style="color: var(--primary);">&#x20b9;{{ number_format($room->rent) }}</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Per Month</span>
                @endif
            </div>
            
            <a href="{{ route('rooms.show', $room->id) }}" class="text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md active:scale-95 transition-all flex items-center gap-2 min-h-[44px]" style="background: var(--primary);">
                <i class="fas fa-phone text-[10px]"></i>
                {{ $room->isForSell() ? 'View Details' : 'Contact Owner' }}
            </a>
        </div>
    </div>
</div>
