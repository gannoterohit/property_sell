<section class="market-section soft">
    <div class="market-wrap">
        <div class="market-section-head">
            <div>
                <span class="market-kicker">Verified Properties</span>
                <h2>{{ $text('home_latest_title','Explore available verified rooms by type') }}</h2>
                <p>{{ $text('home_latest_description','Browse curated verified listings grouped by property type so you can find relevant rooms faster.') }}</p>
            </div>
        </div>

        @if(!empty($otherRoomGroups) && $otherRoomGroups->count())
            @foreach($otherRoomGroups as $group)
                <div class="market-section-head mt-10 mb-4">
                    <div>
                        <span class="market-kicker text-indigo-600 font-bold uppercase tracking-wider text-[11px]">Handpicked Collections</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 mt-1">Verified {{ $group->label }}</h2>
                        <p class="text-slate-500 text-xs sm:text-sm">Explore curated {{ strtolower($group->label) }} listings in {{ $displayCity ?? 'your city' }}.</p>
                    </div>
                    <a href="{{ route('rooms.index', $group->params) }}" class="inline-flex items-center gap-1.5 text-xs font-black text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-2 rounded-xl transition-colors">
                        <span>View all {{ strtolower($group->label) }}</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="market-room-grid">
                    @foreach($group->rooms as $room)
                        <div class="market-room-card group bg-white rounded-2xl border border-slate-200/90 hover:border-slate-300 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col h-full hover:-translate-y-1">
                            <!-- Image Area -->
                            <a href="{{ route('rooms.show', $room) }}" class="room-image relative block overflow-hidden bg-slate-100 aspect-[16/10]">
                                @if($room->photo_url)
                                    <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="260" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-room.svg') }}'">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                                        <i class="fas fa-image text-3xl mb-1"></i>
                                        <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">No Image</span>
                                    </div>
                                @endif

                                <!-- Minimal Top-Left Badges: Purpose & Featured -->
                                <div class="absolute top-3 left-3 flex items-center gap-1.5 z-10">
                                    @if($room->isForSell())
                                        <span class="inline-flex items-center gap-1 bg-violet-600 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-sm backdrop-blur-md">
                                            <i class="fas fa-tag text-[9px]"></i> For Sale
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-sm backdrop-blur-md">
                                            <i class="fas fa-key text-[9px]"></i> For Rent
                                        </span>
                                    @endif
                                    @if($room->is_featured)
                                        <span class="inline-flex items-center gap-1 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-lg shadow-sm">
                                            <i class="fas fa-star text-[9px]"></i> Featured
                                        </span>
                                    @endif
                                </div>

                                <!-- Quick Actions (Top Right) -->
                                <div class="absolute top-3 right-3 flex items-center gap-1.5 z-10">
                                    <button type="button" 
                                            data-compare-id="{{ $room->id }}"
                                            data-compare-title="{{ $room->title }}"
                                            data-compare-rent="{{ $room->isForSell() ? (float)$room->price : (float)$room->rent }}"
                                            data-compare-image="{{ $room->photo_url ?: asset('assets/images/default-room.svg') }}"
                                            data-compare-url="{{ route('rooms.show', $room->slug ?: $room->id) }}"
                                            onclick="event.preventDefault(); event.stopPropagation(); handleCompareClick(this, event);"
                                            title="Compare this property"
                                            class="w-8 h-8 rounded-full bg-white/95 hover:bg-white text-slate-500 hover:text-indigo-600 shadow-md backdrop-blur-md flex items-center justify-center text-xs transition-all active:scale-90 border border-white/60 cursor-pointer">
                                        <i class="fas fa-code-compare"></i>
                                    </button>
                                    <button type="button" 
                                            onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(event, {{ $room->id }});" 
                                            id="wishlist-btn-home-{{ $room->id }}"
                                            title="Save property"
                                            class="w-8 h-8 rounded-full bg-white/95 hover:bg-white text-slate-400 hover:text-rose-500 shadow-md backdrop-blur-md flex items-center justify-center text-xs transition-all active:scale-90 border border-white/60 cursor-pointer">
                                        <i class="{{ (Auth::check() && Auth::user()->hasInWishlist($room->id)) ? 'fas text-rose-500' : 'far' }} fa-heart"></i>
                                    </button>
                                </div>
                            </a>

                            <!-- Card Body -->
                            <div class="p-4 flex flex-col flex-grow">
                                <!-- Meta Row: Property Type & Lister Type -->
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700">
                                        <i class="fas fa-building text-[10px] text-slate-400"></i>
                                        {{ $room->roomTypeLabel() ?: ($room->propertyCategory?->name ?: ($room->propertyType?->name ?? 'Property')) }}
                                    </span>

                                    @if($room->listing_type === 'broker')
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-lg">
                                            <i class="fas fa-certificate text-indigo-500 text-[9px]"></i> Verified Agent
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-lg">
                                            <i class="fas fa-shield-check text-emerald-500 text-[9px]"></i> Direct Owner (0%)
                                        </span>
                                    @endif
                                </div>

                                <!-- Price & Negotiation -->
                                <div class="flex items-baseline justify-between gap-2 mb-1.5">
                                    @if($room->isForSell())
                                        <div class="flex items-baseline gap-1.5 min-w-0">
                                            <span class="text-xl font-black text-slate-900 tracking-tight leading-none">{{ $room->displayPrice() }}</span>
                                            @if($room->ratePerSqft())
                                                <span class="text-[11px] font-semibold text-slate-400 truncate">₹{{ number_format($room->ratePerSqft()) }}/sqft</span>
                                            @endif
                                        </div>
                                        @if($room->feature('price_negotiable'))
                                            <span class="shrink-0 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">Negotiable</span>
                                        @endif
                                    @else
                                        <div class="flex items-baseline gap-1 min-w-0">
                                            <span class="text-xl font-black text-slate-900 tracking-tight leading-none">₹{{ number_format($room->rent) }}</span>
                                            <span class="text-xs font-semibold text-slate-400">/month</span>
                                        </div>
                                        @if($room->deposit)
                                            <span class="shrink-0 text-[11px] font-medium text-slate-500">Dep: ₹{{ number_format($room->deposit) }}</span>
                                        @endif
                                    @endif
                                </div>

                                <!-- Property Title -->
                                <h3 class="font-bold text-sm text-slate-800 hover:text-indigo-600 line-clamp-1 mb-1 transition-colors leading-snug">
                                    <a href="{{ route('rooms.show', $room) }}">{{ $room->title }}</a>
                                </h3>

                                <!-- Location & Distance -->
                                <div class="flex items-center text-slate-500 text-xs mb-3 min-w-0">
                                    <i class="fas fa-location-dot text-rose-500 mr-1.5 text-[11px] shrink-0"></i>
                                    <span class="truncate">{{ $room->landmarks[0] ?? ($room->address ?? $room->city) }}, {{ $room->city }}</span>
                                </div>

                                <!-- Specifications Strip (3-column neat grid) -->
                                <div class="grid grid-cols-3 gap-1.5 py-2 px-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs mb-3 text-slate-600">
                                    <div class="flex items-center gap-1 min-w-0" title="Furnishing">
                                        <i class="{{ $room->isPlot() ? 'fas fa-compass' : 'fas fa-couch' }} text-slate-400 text-xs shrink-0"></i>
                                        <span class="truncate font-semibold text-[11px]">{{ $room->isPlot() ? ($room->feature('facing') ? $room->feature('facing') . ' Face' : 'Open') : $room->furnishingTypeLabel() }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 min-w-0" title="{{ $room->isForSell() ? 'Possession' : 'Preferred Tenant' }}">
                                        <i class="{{ $room->isForSell() ? 'fas fa-clock' : 'fas fa-users' }} text-slate-400 text-xs shrink-0"></i>
                                        <span class="truncate font-semibold text-[11px]">{{ $room->isForSell() ? $room->possessionLabel() : $room->tenantTypeLabel() }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 min-w-0 justify-end" title="Area">
                                        <i class="fas fa-ruler-combined text-slate-400 text-xs shrink-0"></i>
                                        <span class="truncate font-bold text-[11px] text-slate-800">{{ $room->area_sqft ? number_format((float)$room->area_sqft) . ' sqft' : 'On call' }}</span>
                                    </div>
                                </div>

                                <!-- Card Footer: Lister Info & Action Button -->
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 mt-auto">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if($room->user?->avatar)
                                            <img src="{{ asset('storage/'.$room->user->avatar) }}" alt="{{ $room->user?->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-extrabold flex items-center justify-center text-xs shrink-0 border border-slate-200">
                                                {{ strtoupper(substr($room->user?->name ?? 'O', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[11px] font-bold text-slate-800 truncate leading-tight">
                                                @if($room->listing_type === 'broker' && $room->user)
                                                    {{ $room->user->agency_name ?: $room->user->name }}
                                                @else
                                                    {{ $room->user?->name ?? 'Direct Owner' }}
                                                @endif
                                            </p>
                                            <p class="text-[10px] text-slate-400 font-medium">
                                                {{ $room->listing_type === 'broker' ? 'Agent' : 'Owner' }}
                                            </p>
                                        </div>
                                    </div>

                                    <a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-indigo-600 transition-colors shadow-xs shrink-0">
                                        <span>{{ $room->isForSell() ? 'View Deal' : 'Details' }}</span>
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @elseif(isset($rooms) && $rooms->count() > 0)
            <div class="market-room-grid mt-6">
                @foreach($rooms as $room)
                    <div class="market-room-card group bg-white rounded-2xl border border-slate-200/90 hover:border-slate-300 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col h-full hover:-translate-y-1">
                        <!-- Image Area -->
                        <a href="{{ route('rooms.show', $room) }}" class="room-image relative block overflow-hidden bg-slate-100 aspect-[16/10]">
                            @if($room->photo_url)
                                <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="260" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-room.svg') }}'">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                                    <i class="fas fa-image text-3xl mb-1"></i>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">No Image</span>
                                </div>
                            @endif

                            <div class="absolute top-3 left-3 flex items-center gap-1.5 z-10">
                                @if($room->isForSell())
                                    <span class="inline-flex items-center gap-1 bg-violet-600 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-sm backdrop-blur-md">
                                        <i class="fas fa-tag text-[9px]"></i> For Sale
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg shadow-sm backdrop-blur-md">
                                        <i class="fas fa-key text-[9px]"></i> For Rent
                                    </span>
                                @endif
                                @if($room->is_featured)
                                    <span class="inline-flex items-center gap-1 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded-lg shadow-sm">
                                        <i class="fas fa-star text-[9px]"></i> Featured
                                    </span>
                                @endif
                            </div>

                            <div class="absolute top-3 right-3 flex items-center gap-1.5 z-10">
                                <button type="button" 
                                        data-compare-id="{{ $room->id }}"
                                        data-compare-title="{{ $room->title }}"
                                        data-compare-rent="{{ $room->isForSell() ? (float)$room->price : (float)$room->rent }}"
                                        data-compare-image="{{ $room->photo_url ?: asset('assets/images/default-room.svg') }}"
                                        data-compare-url="{{ route('rooms.show', $room->slug ?: $room->id) }}"
                                        onclick="event.preventDefault(); event.stopPropagation(); handleCompareClick(this, event);"
                                        title="Compare this property"
                                        class="w-8 h-8 rounded-full bg-white/95 hover:bg-white text-slate-500 hover:text-indigo-600 shadow-md backdrop-blur-md flex items-center justify-center text-xs transition-all active:scale-90 border border-white/60 cursor-pointer">
                                    <i class="fas fa-code-compare"></i>
                                </button>
                                <button type="button" 
                                        onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist(event, {{ $room->id }});" 
                                        id="wishlist-btn-home-{{ $room->id }}"
                                        title="Save property"
                                        class="w-8 h-8 rounded-full bg-white/95 hover:bg-white text-slate-400 hover:text-rose-500 shadow-md backdrop-blur-md flex items-center justify-center text-xs transition-all active:scale-90 border border-white/60 cursor-pointer">
                                    <i class="{{ (Auth::check() && Auth::user()->hasInWishlist($room->id)) ? 'fas text-rose-500' : 'far' }} fa-heart"></i>
                                </button>
                            </div>
                        </a>

                        <div class="p-4 flex flex-col flex-grow">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 text-slate-700">
                                    <i class="fas fa-building text-[10px] text-slate-400"></i>
                                    {{ $room->roomTypeLabel() ?: ($room->propertyCategory?->name ?: ($room->propertyType?->name ?? 'Property')) }}
                                </span>

                                @if($room->listing_type === 'broker')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-lg">
                                        <i class="fas fa-certificate text-indigo-500 text-[9px]"></i> Verified Agent
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-lg">
                                        <i class="fas fa-shield-check text-emerald-500 text-[9px]"></i> Direct Owner (0%)
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-baseline justify-between gap-2 mb-1.5">
                                @if($room->isForSell())
                                    <div class="flex items-baseline gap-1.5 min-w-0">
                                        <span class="text-xl font-black text-slate-900 tracking-tight leading-none">{{ $room->displayPrice() }}</span>
                                        @if($room->ratePerSqft())
                                            <span class="text-[11px] font-semibold text-slate-400 truncate">₹{{ number_format($room->ratePerSqft()) }}/sqft</span>
                                        @endif
                                    </div>
                                    @if($room->feature('price_negotiable'))
                                        <span class="shrink-0 text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200/60">Negotiable</span>
                                    @endif
                                @else
                                    <div class="flex items-baseline gap-1 min-w-0">
                                        <span class="text-xl font-black text-slate-900 tracking-tight leading-none">₹{{ number_format($room->rent) }}</span>
                                        <span class="text-xs font-semibold text-slate-400">/month</span>
                                    </div>
                                    @if($room->deposit)
                                        <span class="shrink-0 text-[11px] font-medium text-slate-500">Dep: ₹{{ number_format($room->deposit) }}</span>
                                    @endif
                                @endif
                            </div>

                            <h3 class="font-bold text-sm text-slate-800 hover:text-indigo-600 line-clamp-1 mb-1 transition-colors leading-snug">
                                <a href="{{ route('rooms.show', $room) }}">{{ $room->title }}</a>
                            </h3>

                            <div class="flex items-center text-slate-500 text-xs mb-3 min-w-0">
                                <i class="fas fa-location-dot text-rose-500 mr-1.5 text-[11px] shrink-0"></i>
                                <span class="truncate">{{ $room->landmarks[0] ?? ($room->address ?? $room->city) }}, {{ $room->city }}</span>
                            </div>

                            <div class="grid grid-cols-3 gap-1.5 py-2 px-2.5 bg-slate-50 rounded-xl border border-slate-100 text-xs mb-3 text-slate-600">
                                <div class="flex items-center gap-1 min-w-0" title="Furnishing">
                                    <i class="{{ $room->isPlot() ? 'fas fa-compass' : 'fas fa-couch' }} text-slate-400 text-xs shrink-0"></i>
                                    <span class="truncate font-semibold text-[11px]">{{ $room->isPlot() ? ($room->feature('facing') ? $room->feature('facing') . ' Face' : 'Open') : $room->furnishingTypeLabel() }}</span>
                                </div>
                                <div class="flex items-center gap-1 min-w-0" title="{{ $room->isForSell() ? 'Possession' : 'Preferred Tenant' }}">
                                    <i class="{{ $room->isForSell() ? 'fas fa-clock' : 'fas fa-users' }} text-slate-400 text-xs shrink-0"></i>
                                    <span class="truncate font-semibold text-[11px]">{{ $room->isForSell() ? $room->possessionLabel() : $room->tenantTypeLabel() }}</span>
                                </div>
                                <div class="flex items-center gap-1 min-w-0 justify-end" title="Area">
                                    <i class="fas fa-ruler-combined text-slate-400 text-xs shrink-0"></i>
                                    <span class="truncate font-bold text-[11px] text-slate-800">{{ $room->area_sqft ? number_format((float)$room->area_sqft) . ' sqft' : 'On call' }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 mt-auto">
                                <div class="flex items-center gap-2 min-w-0">
                                    @if($room->user?->avatar)
                                        <img src="{{ asset('storage/'.$room->user->avatar) }}" alt="{{ $room->user?->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-extrabold flex items-center justify-center text-xs shrink-0 border border-slate-200">
                                            {{ strtoupper(substr($room->user?->name ?? 'O', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold text-slate-800 truncate leading-tight">
                                            @if($room->listing_type === 'broker' && $room->user)
                                                {{ $room->user->agency_name ?: $room->user->name }}
                                            @else
                                                {{ $room->user?->name ?? 'Direct Owner' }}
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-slate-400 font-medium">
                                            {{ $room->listing_type === 'broker' ? 'Agent' : 'Owner' }}
                                        </p>
                                    </div>
                                </div>

                                <a href="{{ route('rooms.show', $room) }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-indigo-600 transition-colors shadow-xs shrink-0">
                                    <span>{{ $room->isForSell() ? 'View Deal' : 'Details' }}</span>
                                    <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
async function toggleWishlist(event, roomId) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    @guest
        window.location.href = "{{ route('login') }}";
        return;
    @endguest

    try {
        const response = await fetch(`{{ url('/wishlist/toggle') }}/${roomId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Failed to toggle wishlist');
        const data = await response.json();

        document.querySelectorAll(`[id^="wishlist-btn-home-${roomId}"]`).forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                if (data.status === 'added') {
                    icon.className = 'fas text-rose-500 fa-heart';
                    btn.classList.add('text-rose-500');
                } else {
                    icon.className = 'far fa-heart';
                    btn.classList.remove('text-rose-500');
                }
            }
        });
        
        if (typeof showToast === 'function') {
            showToast(data.message || (data.status === 'added' ? 'Added to wishlist' : 'Removed from wishlist'), 'success');
        }
    } catch (error) {
        console.error('Error toggling wishlist:', error);
    }
}
</script>
@endpush
