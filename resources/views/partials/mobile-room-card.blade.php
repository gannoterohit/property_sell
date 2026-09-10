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
        
        <!-- Minimal Image Tags: Purpose & Featured Only -->
        <div class="absolute top-3 left-3 flex items-center gap-1.5 z-10">
            @if($room->isForSell())
                <span class="bg-violet-600 text-white px-2.5 py-1 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider flex items-center gap-1 backdrop-blur-md">
                    <i class="fas fa-tag text-[9px]"></i> For Sale
                </span>
            @else
                <span class="bg-emerald-600 text-white px-2.5 py-1 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider flex items-center gap-1 backdrop-blur-md">
                    <i class="fas fa-key text-[9px]"></i> For Rent
                </span>
            @endif
            @if($room->is_featured)
                <span class="bg-amber-500 text-white px-2 py-1 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-wider">
                    <i class="fas fa-star text-[9px]"></i> Featured
                </span>
            @endif
        </div>

        <!-- Wishlist heart top-right -->
        <div class="absolute top-3 right-3 z-10">
            <button type="button" onclick="toggleWishlist(event, {{ $room->id }})" 
                    class="w-8 h-8 rounded-full bg-white/95 text-slate-400 hover:text-rose-500 shadow-md flex items-center justify-center text-xs active:scale-90 transition-all">
                <i class="far fa-heart"></i>
            </button>
        </div>

        <!-- Bottom Gradient Overlay -->
        <div class="absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div>
    </a>
    
    <!-- Content Section -->
    <div class="p-4">
        <!-- Meta Row: Category & Lister Status -->
        <div class="flex items-center justify-between gap-2 mb-2">
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700">
                <i class="fas fa-building text-[10px] text-slate-400"></i>
                {{ $room->roomTypeLabel() ?: ($room->propertyCategory?->name ?: ($room->propertyType?->name ?? 'Property')) }}
            </span>
            @if($room->listing_type === 'broker')
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-md">
                    <i class="fas fa-certificate text-indigo-500 text-[9px]"></i> Verified Agent
                </span>
            @else
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-md">
                    <i class="fas fa-shield-check text-emerald-500 text-[9px]"></i> Direct Owner (0%)
                </span>
            @endif
        </div>

        <!-- Title & Location -->
        <div class="mb-3">
            <h2 class="font-extrabold text-base text-slate-900 leading-snug line-clamp-1 mb-1">
                <a href="{{ route('rooms.show', $room->id) }}" class="hover:text-indigo-600 transition-colors">{{ $room->title }}</a>
            </h2>
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <div class="flex items-center min-w-0">
                    <i class="fas fa-location-dot mr-1.5 text-rose-500 text-xs shrink-0"></i>
                    <span class="truncate">{{ $room->landmarks[0] ?? ($room->address ?? $room->city) }}, {{ $room->city }}</span>
                </div>
                @if($room->listing_type === 'broker' && $room->user)
                    <span class="text-[11px] font-semibold text-indigo-600 shrink-0 ml-2">
                        {{ $room->user->agency_name ?: $room->user->name }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Quick Specs Row -->
        <div class="grid grid-cols-3 gap-1.5 py-2 px-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs mb-3 text-slate-600">
            <div class="flex items-center gap-1 min-w-0">
                <i class="{{ $room->isPlot() ? 'fas fa-compass' : 'fas fa-couch' }} text-slate-400 text-xs shrink-0"></i>
                <span class="truncate font-semibold text-[11px]">{{ $room->isPlot() ? ($room->feature('facing') ? $room->feature('facing') . ' Face' : 'Open') : $room->furnishingTypeLabel() }}</span>
            </div>
            <div class="flex items-center gap-1 min-w-0">
                <i class="{{ $room->isForSell() ? 'fas fa-clock' : 'fas fa-users' }} text-slate-400 text-xs shrink-0"></i>
                <span class="truncate font-semibold text-[11px]">{{ $room->isForSell() ? $room->possessionLabel() : $room->tenantTypeLabel() }}</span>
            </div>
            <div class="flex items-center gap-1 min-w-0 justify-end">
                <i class="fas fa-ruler-combined text-slate-400 text-xs shrink-0"></i>
                <span class="truncate font-bold text-[11px] text-slate-800">{{ $room->area_sqft ? number_format((float)$room->area_sqft) . ' sqft' : 'On call' }}</span>
            </div>
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
