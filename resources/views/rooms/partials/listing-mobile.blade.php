
<!-- Mobile Layout (Split: Slider + List) -->
<div class="md:hidden">
    <!-- Section Title -->
    <div class="px-4 pt-4 pb-2">
        <h2 class="text-lg font-bold text-gray-800">Featured Stays</h2>
    </div>

    <!-- 1. Horizontal Scroll Section (Top 5 Rooms) -->
    <div class="flex overflow-x-auto gap-4 px-4 pb-4 snap-x snap-mandatory hide-scrollbar mb-2" style="-webkit-overflow-scrolling: touch; scroll-behavior: smooth;">
        @foreach($rooms->take(5) as $room)
            <div class="min-w-[220px] w-[220px] bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 snap-center relative active:scale-[0.97] transition-transform">
                <a href="{{ route('rooms.show', $room) }}" class="block">
                    <div class="h-32 relative bg-gray-100">
                        @php
                            $photoUrl = $room->photo_url ?? asset('assets/images/placeholder.jpg');
                            if (str_contains($photoUrl, 'unsplash.com')) {
                                $baseUrl = strtok($photoUrl, '?');
                                $tinyUrl = $baseUrl . '?w=200&h=150&fm=webp&q=70&fit=crop';
                            } else {
                                $tinyUrl = $photoUrl;
                            }
                        @endphp
                        <img src="{{ $tinyUrl }}" 
                             class="w-full h-full object-cover"
                             alt="{{ $room->title }}"
                             width="200" height="150"
                             loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                             fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                             decoding="async"
                             onerror="this.onerror=null; this.src='https://placehold.co/200x150?text=Room';">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        <div class="absolute bottom-2 left-2 text-white">
                            <p class="text-sm font-bold">{{ $room->isForSell() ? $room->displayPrice() : '₹' . number_format($room->rent) }}</p>
                        </div>
                        @if($room->isForSell())
                            <span class="absolute top-2 left-2 bg-purple-600 text-[9px] font-bold px-1.5 py-0.5 rounded text-white z-10">Sale</span>
                        @endif
                        @if($room->is_featured)
                            <span class="absolute top-2 right-2 bg-yellow-400 text-[9px] font-bold px-1.5 py-0.5 rounded text-yellow-900 z-10">Featured</span>
                        @endif
                    </div>
                    <div class="p-3">
                        <h2 class="font-bold text-gray-800 text-xs truncate">{{ $room->title }}</h2>
                        <p class="text-[10px] text-gray-500 truncate mt-1"><i class="fas fa-map-marker-alt mr-1"></i>{{ $room->city }}</p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

        <!-- Section Title -->
        <div class="px-4 pt-2 pb-2">
            <h2 class="text-lg font-bold text-gray-800">All Properties</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $rooms->total() }} properties found</p>
        </div>



        <!-- 2. Vertical List (Remaining Rooms) -->
        <div id="mobile-room-list" class="px-3 pb-24">
            @foreach($rooms->skip(5) as $room)
                @include('partials.mobile-room-card', ['room' => $room])
            @endforeach
        </div>

        <!-- Infinite Scroll Loader -->
        <div id="infinite-loader" class="px-3 pb-24 {{ $rooms->hasMorePages() ? '' : 'hidden' }}">
            @include('rooms.partials.skeleton')
        </div>
    </div>

