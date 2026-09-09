@php($relatedRooms = $relatedRooms ?? collect())

@if(!request()->routeIs('admin.*') && $relatedRooms->isNotEmpty())
<section class="related-room-section">
    <div class="related-room-container">
        <div class="related-room-head">
            <div>
                <span class="related-tag">Recommended Properties</span>
                <h2>Similar Properties You May Like</h2>
                <p>Compare verified rental options in {{ $room->city }} and nearby locations.</p>
            </div>
            <a href="{{ route('rooms.index', ['city' => $room->city]) }}" class="related-view-all">
                <span>View all in {{ $room->city }}</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="related-room-grid">
            @foreach($relatedRooms as $relatedRoom)
            <a href="{{ route('rooms.show', $relatedRoom) }}" class="related-room-card group">
                <div class="related-room-image">
                    <img src="{{ $relatedRoom->photo_url }}"
                         alt="{{ $relatedRoom->title }} in {{ $relatedRoom->city }}"
                         width="400"
                         height="260"
                         loading="lazy"
                         onerror="this.src='{{ asset('assets/images/default-room.svg') }}'">
                    
                    <div class="related-badges">
                        @if($relatedRoom->isForSell())
                            <span class="badge-featured" style="background:#7c3aed;color:#fff;"><i class="fas fa-tag text-[9px] mr-1"></i>For Sale</span>
                        @endif
                        @if($relatedRoom->is_featured)
                            <span class="badge-featured"><i class="fas fa-star text-[9px] mr-1"></i>Featured</span>
                        @endif
                        @if($relatedRoom->listing_type === 'broker')
                            <span class="badge-broker">Agent</span>
                        @else
                            <span class="badge-owner">Zero Brokerage</span>
                        @endif
                    </div>

                    <div class="related-price-tag">
                        @if($relatedRoom->isForSell())
                            <strong style="color:#7c3aed;">{{ $relatedRoom->displayPrice() }}</strong>
                        @else
                            <strong>₹{{ number_format((float)$relatedRoom->rent) }}</strong>
                            <small>/mo</small>
                        @endif
                    </div>
                </div>

                <div class="related-room-copy">
                    <div class="related-room-type-row">
                        <span class="type-pill">{{ $relatedRoom->roomTypeLabel() !== 'N/A' ? $relatedRoom->roomTypeLabel() : ($relatedRoom->propertyType?->name ?? 'Room') }}</span>
                        <span class="tenant-pill">{{ $relatedRoom->tenantTypeLabel() }}</span>
                    </div>

                    <h3 title="{{ $relatedRoom->title }}">{{ $relatedRoom->title }}</h3>

                    <p class="related-city"><i class="fas fa-location-dot"></i>{{ $relatedRoom->city }}</p>

                    <div class="related-room-meta">
                        <span><i class="fas fa-couch"></i>{{ $relatedRoom->furnishingTypeLabel() }}</span>
                        @if($relatedRoom->area_sqft)
                            <span><i class="fas fa-ruler-combined"></i>{{ number_format((float)$relatedRoom->area_sqft) }} sqft</span>
                        @endif
                        @if($relatedRoom->propertyType?->name)
                            <span><i class="fas fa-building"></i>{{ $relatedRoom->propertyType->name }}</span>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
