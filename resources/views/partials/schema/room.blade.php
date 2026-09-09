{{-- Room Listing Schema for Single Room Page --}}
{{-- Usage: @include('partials.schema.room', ['room' => $room]) --}}

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "{{ $room->title }}",
  "description": "{{ $room->description ?? ($room->isForSell() ? 'Property for sale' : 'Room for rent') }}",
  "image": [
    @if($room->photos)
      @foreach(json_decode($room->photos) as $index => $photo)
        "{{ asset('storage/' . $photo) }}"{{ $loop->last ? '' : ',' }}
      @endforeach
    @endif
  ],
  "offers": {
    "@type": "Offer",
    "url": "{{ route('rooms.show', $room) }}",
    "priceCurrency": "INR",
    "price": "{{ $room->isForSell() ? ($room->price ?? 0) : ($room->rent ?? 0) }}",
    "priceValidUntil": "{{ now()->addMonth()->format('Y-m-d') }}",
    "availability": "https://schema.org/InStock",
    "itemCondition": "https://schema.org/UsedCondition"
  },
  "address": {
    "@type": "PostalAddress",
    "addressLocality": "{{ $room->city }}",
    "addressRegion": "{{ $room->state }}",
    "addressCountry": "{{ $room->country ?? 'IN' }}",
    "streetAddress": "{{ $room->address }}"
  },
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "{{ $room->latitude }}",
    "longitude": "{{ $room->longitude }}"
  }
}
</script>
