            <!-- Rooms list -->
            <div id="rooms-list-container">
                @if($rooms->count() > 0)
                <!-- Desktop Columns Grid (Flexbox wrapper for guaranteed column layout) -->
                <div class="hidden md:flex flex-wrap -mx-2.5">
                    @foreach($rooms as $room)
                        <div class="w-full md:w-1/2 xl:w-1/3 px-2.5 mb-5 flex flex-col">
                            <div class="room-listing-card group bg-white rounded-2xl border transition-all duration-300 overflow-hidden flex flex-col h-full hover:-translate-y-1">
                                <!-- Image Area -->
                                <a href="{{ route('rooms.show', $room->id) }}" class="room-image relative block overflow-hidden bg-slate-100">
                                     @if($room->photo_url)
                                          <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="300" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-room.svg') }}'">
                                     @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                                            <i class="fas fa-image text-3xl mb-1"></i>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">No Image</span>
                                        </div>
                                    @endif

                                    <!-- Status Badges -->
                                    <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5 z-10">
                                         @if($room->is_featured)
                                             <span class="bg-amber-500 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">Featured</span>
                                         @endif
                                         {{-- Purpose Badge: FOR RENT / FOR SALE --}}
                                         @if($room->isForSell())
                                             <span class="bg-purple-600 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                 <i class="fas fa-tag"></i> For Sale
                                             </span>
                                         @else
                                             <span class="bg-emerald-500 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                 <i class="fas fa-key"></i> For Rent
                                             </span>
                                         @endif
                                         <span class="room-theme-type-badge bg-white/90 backdrop-blur-sm text-[8px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-lg border border-white/40 shadow-sm">
                                             {{ $room->roomTypeLabel() }}
                                         </span>
                                         @if($room->propertyType?->name)
                                             <span class="bg-slate-900 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">
                                                 {{ $room->propertyType->name }}
                                             </span>
                                         @endif
                                         @if($room->propertyCategory?->name)
                                             <span class="bg-indigo-600 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">
                                                 {{ $room->propertyCategory->name }}
                                             </span>
                                         @endif
                                         @if($room->listing_type === 'broker')
                                             <span class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-[8.5px] font-black uppercase tracking-wider px-2.5 py-1 rounded-lg flex items-center gap-1 shadow-md">
                                                 <i class="fas fa-building"></i> Verified Agency
                                             </span>
                                         @else
                                             <span class="bg-emerald-600 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1 shadow-sm">
                                                 <i class="fas fa-shield-check"></i> Direct Owner (0%)
                                             </span>
                                         @endif
                                     </div>

                                    <!-- Compare button -->
                                    <button type="button" 
                                            data-compare-id="{{ $room->id }}"
                                            data-compare-title="{{ $room->title }}"
                                            data-compare-rent="{{ $room->isForSell() ? (float)$room->price : (float)$room->rent }}"
                                            data-compare-image="{{ $room->photo_url ?: asset('assets/images/default-room.svg') }}"
                                            data-compare-url="{{ route('rooms.show', $room->slug ?: $room->id) }}"
                                            onclick="handleCompareClick(this, event)"
                                            title="Compare this property"
                                            class="compare-btn-wrapper absolute top-2.5 right-12 h-8 px-2 rounded-xl bg-white/95 backdrop-blur-sm shadow-md text-slate-500 hover:text-indigo-600 active:scale-90 transition-all flex items-center justify-center gap-1 text-[11px] font-bold border border-slate-100 cursor-pointer">
                                        <i class="fas fa-code-compare text-xs"></i>
                                        <span class="hidden sm:inline">Compare</span>
                                    </button>

                                    <!-- Wishlist heart -->
                                    <button onclick="toggleWishlist(event, {{ $room->id }})" id="wishlist-btn-{{ $room->id }}"
                                            class="absolute top-2.5 right-2.5 w-8 h-8 rounded-xl bg-white/95 backdrop-blur-sm shadow-md text-slate-400 hover:text-red-500 active:scale-90 transition-all flex items-center justify-center">
                                        <i class="{{ (Auth::check() && in_array($room->id, $userWishlistIds)) ? 'fas text-red-500' : 'far' }} fa-heart text-sm"></i>
                                    </button>

                                    <!-- Price tag overlay -->
                                    <div class="absolute bottom-2.5 left-2.5">
                                        <div class="room-price-tag px-3 py-1 rounded-xl">
                                            @if($room->isForSell())
                                                <span class="text-sm font-black">{{ $room->displayPrice() }}</span>
                                                <span class="text-[8px] font-bold">sale</span>
                                            @else
                                                <span class="text-sm font-black">₹{{ number_format($room->rent) }}</span>
                                                <span class="text-[8px] font-bold">/mo</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>

                                <!-- Card content -->
                                <div class="room-card-body flex flex-col flex-grow">
                                    <h3 class="font-bold text-sm text-slate-900 line-clamp-2 mb-2 transition-colors">
                                        <a href="{{ route('rooms.show', $room->id) }}">{{ $room->title }}</a>
                                    </h3>

                                    <div class="flex items-center text-slate-500 text-xs mb-3">
                                        <i class="room-theme-primary-icon fas fa-location-dot mr-1.5"></i>
                                        <span>{{ $room->city }}</span>
                                        <div class="distance-tag hidden ml-2 flex items-center gap-1" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                            <div class="room-theme-secondary-dot w-1 h-1 rounded-full"></div>
                                            <span class="room-theme-secondary-text text-[9px] font-extrabold uppercase tracking-widest"><span class="distance-km">0</span> km</span>
                                        </div>
                                    </div>

                                    <!-- Quick Specs -->
                                    <div class="flex flex-wrap gap-1.5 mb-4 mt-auto">
                                        <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                            <i class="room-theme-primary-icon fas fa-couch"></i> {{ $room->furnishingTypeLabel() }}
                                        </span>
                                        @if($room->isForSell())
                                            @if($room->possession_status)
                                                <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                    <i class="room-theme-primary-icon fas fa-home"></i> {{ $room->possessionLabel() }}
                                                </span>
                                            @endif
                                        @else
                                            @if($room->tenantTypeLabel() !== 'N/A')
                                                <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                    <i class="room-theme-primary-icon fas fa-users"></i> {{ $room->tenantTypeLabel() }}
                                                </span>
                                            @endif
                                        @endif
                                        @if($room->area_sqft)
                                            <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                <i class="room-theme-primary-icon fas fa-ruler-combined"></i> {{ number_format((float)$room->area_sqft, 2) }} sqft
                                            </span>
                                        @endif
                                    </div>

                                    <div class="room-owner-row">
                                        @if($room->user?->avatar)<img src="{{ asset('storage/'.$room->user->avatar) }}" alt="{{ $room->user?->name ?? 'Property Lister' }}" loading="lazy">@else<div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">Property lister</span></div>@endif
                                        @if($room->listing_type === 'broker')
                                            <span>Agent: @if($room->user)<a href="{{ route('agency.show', $room->user) }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline inline-flex items-center gap-1">{{ $room->user->agency_name ?: ($room->user->name ?? 'Verified Agent') }} <i class="fas fa-arrow-up-right-from-square text-[9px]"></i></a>@else<strong>Verified Agent</strong>@endif</span>
                                        @else
                                            <span>Owner: <strong>{{ $room->user?->name ?? 'Verified Owner' }}</strong></span>
                                        @endif
                                    </div>

                                    <!-- Bottom actions -->
                                    @auth
                                        @php
                                            $isMyRoom = Auth::id() === $room->user_id || (Auth::user()->role === 'broker' && Auth::id() === $room->broker_id);
                                            $editRoute = Auth::user()->role === 'broker' ? route('agent.rooms.edit', $room) : (Auth::user()->role === 'owner' ? route('owner.rooms.edit', $room) : null);
                                            $destroyRoute = Auth::user()->role === 'broker' ? route('agent.rooms.destroy', $room) : (Auth::user()->role === 'owner' ? route('owner.rooms.destroy', $room) : null);
                                        @endphp
                                        @if($isMyRoom && $editRoute)
                                            <div class="grid grid-cols-2 gap-2 mt-auto">
                                                <a href="{{ $editRoute }}" class="flex items-center justify-center bg-amber-50 text-amber-700 font-extrabold py-2 rounded-xl hover:bg-amber-100 transition-colors text-xs">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <form action="{{ $destroyRoute }}" method="POST" class="delete-room-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="w-full flex items-center justify-center bg-red-50 text-red-600 font-extrabold py-2 rounded-xl hover:bg-red-100 transition-colors text-xs">
                                                        <i class="fas fa-trash mr-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <a href="{{ route('rooms.show', $room->id) }}" class="room-theme-primary-button w-full py-2 font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1 text-xs mt-auto">
                                                {{ $room->isForSell() ? 'Contact Seller' : ($room->listing_type === 'broker' ? 'Contact Agent' : 'Contact Owner') }} <i class="fas fa-arrow-right text-[10px]"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('rooms.show', $room->id) }}" class="room-theme-primary-button w-full py-2 font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1 text-xs mt-auto">
                                            {{ $room->isForSell() ? 'Contact Seller' : 'Contact Owner' }} <i class="fas fa-arrow-right text-[10px]"></i>
                                        </a>
                                    @endauth
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
