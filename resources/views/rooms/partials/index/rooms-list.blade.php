            <!-- Rooms list -->
            <div id="rooms-list-container">
                @if($rooms->count() > 0)
                <!-- Desktop Columns Grid (Flexbox wrapper for 5-column desktop layout) -->
                <div class="hidden md:flex flex-wrap -mx-1.5 lg:-mx-2">
                    @foreach($rooms as $room)
                        <div class="rooms-card-col px-1.5 lg:px-2 mb-4 flex flex-col">
                            <div class="room-listing-card group bg-white rounded-2xl border border-slate-200/90 hover:border-slate-300 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col h-full hover:-translate-y-1">
                                <!-- Image Area -->
                                <a href="{{ route('rooms.show', $room->id) }}" class="room-image relative block overflow-hidden bg-slate-100 aspect-[16/10]">
                                    @if($room->photo_url)
                                        <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="260" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-room.svg') }}'">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                                            <i class="fas fa-image text-2xl mb-1"></i>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">No Image</span>
                                        </div>
                                    @endif

                                    <!-- Minimal Top-Left Badges: Only Purpose & Featured -->
                                    <div class="absolute top-2.5 left-2.5 flex items-center gap-1 z-10">
                                        @if($room->isForSell())
                                            <span class="inline-flex items-center gap-1 bg-violet-600 text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md shadow-sm backdrop-blur-md">
                                                <i class="fas fa-tag text-[8px]"></i> Sale
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-emerald-600 text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md shadow-sm backdrop-blur-md">
                                                <i class="fas fa-key text-[8px]"></i> Rent
                                            </span>
                                        @endif
                                        @if($room->is_featured)
                                            <span class="inline-flex items-center gap-1 bg-amber-500 text-white text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md shadow-sm">
                                                <i class="fas fa-star text-[8px]"></i> Featured
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Quick Actions (Top Right) -->
                                    <div class="absolute top-2.5 right-2.5 flex items-center gap-1 z-10">
                                        <button type="button" 
                                                data-compare-id="{{ $room->id }}"
                                                data-compare-title="{{ $room->title }}"
                                                data-compare-rent="{{ $room->isForSell() ? (float)$room->price : (float)$room->rent }}"
                                                data-compare-image="{{ $room->photo_url ?: asset('assets/images/default-room.svg') }}"
                                                data-compare-url="{{ route('rooms.show', $room->slug ?: $room->id) }}"
                                                onclick="handleCompareClick(this, event)"
                                                title="Compare this property"
                                                class="w-7 h-7 rounded-full bg-white/95 hover:bg-white text-slate-500 hover:text-indigo-600 shadow-md backdrop-blur-md flex items-center justify-center text-[10px] transition-all active:scale-90 border border-white/60 cursor-pointer">
                                            <i class="fas fa-code-compare"></i>
                                        </button>
                                        <button type="button" 
                                                onclick="toggleWishlist(event, {{ $room->id }})" 
                                                id="wishlist-btn-{{ $room->id }}"
                                                title="Save property"
                                                class="w-7 h-7 rounded-full bg-white/95 hover:bg-white text-slate-400 hover:text-rose-500 shadow-md backdrop-blur-md flex items-center justify-center text-[10px] transition-all active:scale-90 border border-white/60 cursor-pointer">
                                            <i class="{{ (Auth::check() && in_array($room->id, $userWishlistIds)) ? 'fas text-rose-500' : 'far' }} fa-heart"></i>
                                        </button>
                                    </div>
                                </a>

                                <!-- Card content (Everything cleanly organized below the image) -->
                                <div class="room-card-body p-3 flex flex-col flex-grow">
                                    <!-- Meta Tag Row: Property Type & Lister Type -->
                                    <div class="flex items-center justify-between gap-1 mb-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 truncate max-w-[58%]">
                                            <i class="fas fa-building text-[9px] text-slate-400 shrink-0"></i>
                                            <span class="truncate">{{ $room->roomTypeLabel() ?: ($room->propertyCategory?->name ?: ($room->propertyType?->name ?? 'Property')) }}</span>
                                        </span>

                                        @if($room->listing_type === 'broker')
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 rounded-md shrink-0">
                                                <i class="fas fa-certificate text-indigo-500 text-[8px]"></i> Agent
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 px-1.5 py-0.5 rounded-md shrink-0">
                                                <i class="fas fa-shield-check text-emerald-500 text-[8px]"></i> Owner (0%)
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Price & Negotiation -->
                                    <div class="flex items-baseline justify-between gap-1.5 mb-1">
                                        @if($room->isForSell())
                                            <div class="flex items-baseline gap-1 min-w-0">
                                                <span class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-none">{{ $room->displayPrice() }}</span>
                                                @if($room->ratePerSqft())
                                                    <span class="text-[10px] font-semibold text-slate-400 truncate">₹{{ number_format($room->ratePerSqft()) }}/sqft</span>
                                                @endif
                                            </div>
                                            @if($room->feature('price_negotiable'))
                                                <span class="shrink-0 text-[9px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60">Neg</span>
                                            @endif
                                        @else
                                            <div class="flex items-baseline gap-1 min-w-0">
                                                <span class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-none">₹{{ number_format($room->rent) }}</span>
                                                <span class="text-[10px] font-semibold text-slate-400">/mo</span>
                                            </div>
                                            @if($room->deposit)
                                                <span class="shrink-0 text-[10px] font-medium text-slate-400 truncate">Dep: ₹{{ number_format($room->deposit) }}</span>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Property Title -->
                                    <h3 class="font-bold text-xs sm:text-[13px] text-slate-800 hover:text-indigo-600 line-clamp-1 mb-1 transition-colors leading-snug">
                                        <a href="{{ route('rooms.show', $room->id) }}">{{ $room->title }}</a>
                                    </h3>

                                    <!-- Location & Distance -->
                                    <div class="flex items-center text-slate-500 text-[11px] mb-2 min-w-0">
                                        <i class="fas fa-location-dot text-rose-500 mr-1 text-[10px] shrink-0"></i>
                                        <span class="truncate">{{ $room->landmarks[0] ?? ($room->address ?? $room->city) }}, {{ $room->city }}</span>
                                        <div class="distance-tag hidden ml-auto shrink-0 flex items-center gap-1 text-[9px] font-bold text-slate-600 bg-slate-100 px-1 py-0.5 rounded" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                            <i class="fas fa-person-walking text-slate-400"></i>
                                            <span class="distance-km">0</span> km
                                        </div>
                                    </div>

                                    <!-- Specifications Strip (3-column neat grid) -->
                                    <div class="grid grid-cols-3 gap-1 py-1.5 px-2 bg-slate-50 rounded-xl border border-slate-100 text-[10px] mb-2.5 text-slate-600">
                                        <div class="flex items-center gap-1 min-w-0" title="Furnishing">
                                            <i class="{{ $room->isPlot() ? 'fas fa-compass' : 'fas fa-couch' }} text-slate-400 text-[10px] shrink-0"></i>
                                            <span class="truncate font-semibold">{{ $room->isPlot() ? ($room->feature('facing') ? $room->feature('facing') . ' Face' : 'Open') : $room->furnishingTypeLabel() }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 min-w-0" title="{{ $room->isForSell() ? 'Possession' : 'Preferred Tenant' }}">
                                            <i class="{{ $room->isForSell() ? 'fas fa-clock' : 'fas fa-users' }} text-slate-400 text-[10px] shrink-0"></i>
                                            <span class="truncate font-semibold">{{ $room->isForSell() ? $room->possessionLabel() : $room->tenantTypeLabel() }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 min-w-0 justify-end" title="Area">
                                            <i class="fas fa-ruler-combined text-slate-400 text-[10px] shrink-0"></i>
                                            <span class="truncate font-bold text-slate-800">{{ $room->area_sqft ? number_format((float)$room->area_sqft) . ' sqft' : 'On call' }}</span>
                                        </div>
                                    </div>

                                    <!-- Card Footer: Lister Info & Action Button -->
                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5 mt-auto">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            @if($room->user?->avatar)
                                                <img src="{{ asset('storage/'.$room->user->avatar) }}" alt="{{ $room->user?->name }}" class="w-6 h-6 rounded-full object-cover border border-slate-200 shrink-0">
                                            @else
                                                <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-extrabold flex items-center justify-center text-[10px] shrink-0 border border-slate-200">
                                                    {{ strtoupper(substr($room->user?->name ?? 'O', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold text-slate-800 truncate leading-tight">
                                                    @if($room->listing_type === 'broker' && $room->user)
                                                        {{ $room->user->agency_name ?: $room->user->name }}
                                                    @else
                                                        {{ $room->user?->name ?? 'Direct Owner' }}
                                                    @endif
                                                </p>
                                                <p class="text-[9px] text-slate-400 font-medium leading-none">
                                                    {{ $room->listing_type === 'broker' ? 'Agent' : 'Owner' }}
                                                </p>
                                            </div>
                                        </div>

                                        @auth
                                            @php
                                                $isMyRoom = Auth::id() === $room->user_id || (Auth::user()->role === 'broker' && Auth::id() === $room->broker_id);
                                                $editRoute = Auth::user()->role === 'broker' ? route('agent.rooms.edit', $room) : (Auth::user()->role === 'owner' ? route('owner.rooms.edit', $room) : null);
                                            @endphp
                                            @if($isMyRoom && $editRoute)
                                                <a href="{{ $editRoute }}" class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-colors shrink-0">
                                                    <i class="fas fa-edit text-[9px]"></i> Edit
                                                </a>
                                            @else
                                                <a href="{{ route('rooms.show', $room->id) }}" class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-white bg-slate-900 hover:bg-indigo-600 transition-colors shadow-2xs shrink-0">
                                                    <span>{{ $room->isForSell() ? 'Deal' : 'Details' }}</span>
                                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('rooms.show', $room->id) }}" class="inline-flex items-center justify-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-white bg-slate-900 hover:bg-indigo-600 transition-colors shadow-2xs shrink-0">
                                                <span>{{ $room->isForSell() ? 'Deal' : 'Details' }}</span>
                                                <i class="fas fa-arrow-right text-[9px]"></i>
                                            </a>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Mobile listing support -->
                <div class="md:hidden">
                    @include('rooms.partials.listing-mobile')
                </div>

                <!-- Custom premium layout pagination -->
                <div class="flex justify-center mt-8">
                    {{ $rooms->withQueryString()->links() }}
                </div>
            @else
                <!-- Empty state fallback -->
                <div class="text-center py-16 bg-white border border-slate-200/80 rounded-2xl shadow-sm">
                    <div class="max-w-md mx-auto">
                        <div class="room-theme-primary-soft inline-flex items-center justify-center w-20 h-20 rounded-full mb-6 shadow-sm">
                            <i class="fas fa-house-circle-xmark text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-2">No Properties Found</h3>
                        <p class="text-slate-500 mb-6 text-sm">We couldn't find any properties matching your search criteria. Try modifying your filters or view all properties.</p>
                        <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="room-theme-primary-button inline-flex items-center justify-center font-extrabold py-2.5 px-6 rounded-xl transition-all shadow-md text-xs">
                            <i class="fas fa-rotate-left mr-1.5"></i> Clear All Filters
                        </a>
                        
                        @if(request('city'))
                            <div class="room-theme-alert-box mt-8 p-5 border rounded-2xl">
                                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Get Alerted</h4>
                                <p class="text-slate-500 text-xs mb-3">Subscribe and we will email you when new rooms open up in <strong>{{ request('city') }}</strong>.</p>
                                <button onclick="subscribeToAlerts('{{ request('city') }}')" id="notify-btn"
                                        class="room-theme-primary-button py-2 px-4 font-extrabold rounded-xl text-xs transition-all shadow-sm">
                                    <i class="fas fa-bell mr-1"></i> Notify Me
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            </div>
            <!-- End rooms-list-container -->
