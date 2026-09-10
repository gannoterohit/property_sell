@extends('layouts.public')

@section('title', ($room->title ?? 'Property') . ' in ' . $room->city . ' | ' . \App\Models\Setting::get('website_name', 'ApnaNest'))
@section('description', 'Looking for ' . ($room->title ?? 'a property') . ' in ' . $room->city . ($room->landmarks ? ' near ' . implode(', ', $room->landmarks) : '') . '? ' . ($room->isForSell() ? 'Sale price ' . $room->displayPrice() . '. Verified property with photos and seller contact.' : 'Rent starts at ₹' . number_format($room->rent) . '. Verified listings with photos, facilities, and owner contact.'))
@section('keywords', ($room->isForSell() ? 'property for sale in ' : 'pg in ') . $room->city . ', ' . ($room->isForSell() ? 'flat for sale ' : 'room on rent in ') . $room->city . ', ' . ($room->roomTypeLabel() !== 'N/A' ? $room->roomTypeLabel() : 'property') . ' in ' . $room->city . ($room->landmarks ? ', ' . implode(', ', $room->landmarks) : ''))
@section('og_title', ($room->title ?? 'Property') . ' in ' . $room->city . ' - ' . $room->displayPrice())
@section('og_description', Str::limit(($room->description ?? ($room->isForSell() ? 'Property for sale in ' : 'Find your perfect room in ') . $room->city) . ($room->landmarks ? '. Nearby: ' . implode(', ', $room->landmarks) : ''), 155))
@section('og_url', route('rooms.show', $room))
@section('og_image', $room->photo_url)
@section('canonical', route('rooms.show', $room))

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@php
    $publicAmenities = $room->publicAmenities();
    $ld = [
        "@context" => "https://schema.org",
        "@type" => "Accommodation",
        "name" => ($room->title ?? 'Room') . ' in ' . $room->city,
        "description" => Str::limit($room->description ?? '', 200),
        "image" => $room->photo_url ?: asset('assets/images/default-room.svg'),
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => $room->city ?? '',
            "addressRegion" => $room->state ?? '',
            "addressCountry" => "IN"
        ],
        "offers" => [
            "@type" => "Offer",
            "price" => (string) ($room->isForSell() ? ($room->price ?? 0) : ($room->rent ?? 0)),
            "priceCurrency" => "INR",
            "availability" => ($room->status === 'active') ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ]
    ];

    if (!empty($publicAmenities)) {
        $ld['amenityFeature'] = array_map(function($a) {
            return ["@type" => "LocationFeatureSpecification", "name" => $a, "value" => true];
        }, $publicAmenities);
    }

    if (!empty($room->latitude) && !empty($room->longitude)) {
        $ld['geo'] = [
            "@type" => "GeoCoordinates",
            "latitude" => (string) $room->latitude,
            "longitude" => (string) $room->longitude,
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<link rel="preload" href="{{ asset('css/room-show.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/room-show.css') }}"></noscript>
</push>

@section('content')
<div class="room-detail-page bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-4">
    <div class="container mx-auto px-4 max-w-7xl">
        
        @include('rooms.partials.show.breadcrumb')

        <div class="room-detail-grid grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- LEFT COLUMN - Main Content --}}
            <div class="lg:col-span-2 space-y-4">
                
                {{-- TOP ACTIONS BAR --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-2 bg-white rounded-2xl p-3 border border-slate-200 shadow-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <i class="fas fa-building text-[10px]"></i> {{ $room->roomTypeLabel() }}
                        </span>
                        {{-- Purpose badge --}}
                        @if($room->isForSell())
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                                <i class="fas fa-tag text-[10px]"></i> For Sale
                            </span>
                            @if($room->feature('price_negotiable'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200">
                                    <i class="fas fa-handshake text-[10px]"></i> Negotiable
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fas fa-key text-[10px]"></i> For Rent
                            </span>
                        @endif
                        @if($room->feature('gated_community'))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fas fa-shield-alt text-[10px]"></i> Gated Society
                            </span>
                        @endif
                        @if($room->is_featured)
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                                <i class="fas fa-star text-amber-500 text-[10px]"></i> Featured
                            </span>
                        @endif
                        @if($room->listing_type === 'broker')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                Broker Fee: ₹{{ $room->broker_fee }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Zero Brokerage
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Compare Button --}}
                        <button type="button" 
                                data-compare-id="{{ $room->id }}"
                                data-compare-title="{{ $room->title }}"
                                data-compare-rent="{{ $room->isForSell() ? (float)$room->price : (float)$room->rent }}"
                                data-compare-image="{{ $room->photo_url ?: asset('assets/images/default-room.svg') }}"
                                data-compare-url="{{ route('rooms.show', $room->slug ?: $room->id) }}"
                                onclick="handleCompareClick(this, event)"
                                data-detail-compare-btn="{{ $room->id }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-indigo-50 hover:border-indigo-200 text-xs font-bold text-slate-700 hover:text-indigo-600 transition shadow-2xs cursor-pointer"
                                aria-label="Compare {{ $room->title }}"
                                title="Compare this property">
                            <i class="fas fa-code-compare text-sm text-slate-400"></i>
                            <span>Compare</span>
                        </button>

                        {{-- Save / Wishlist Button --}}
                        @php
                            $isSaved = Auth::check() && Auth::user()->hasInWishlist($room->id);
                        @endphp
                        <button type="button" onclick="toggleWishlist({{ $room->id }})"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border {{ $isSaved ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 bg-white text-slate-700 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600' }} text-xs font-bold transition shadow-2xs cursor-pointer"
                                id="wishlist-btn-{{ $room->id }}"
                                aria-label="Toggle wishlist for {{ $room->title }}"
                                title="{{ $isSaved ? 'Remove from wishlist' : 'Save to wishlist' }}">
                            <i id="wishlist-icon-{{ $room->id }}" class="{{ $isSaved ? 'fas text-rose-500' : 'far text-slate-400' }} fa-heart text-sm"></i>
                            <span id="wishlist-text-{{ $room->id }}">{{ $isSaved ? 'Saved' : 'Save' }}</span>
                        </button>

                        {{-- WhatsApp Share Property Card Button --}}
                        <button type="button"
                                onclick="openShareModal()"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs cursor-pointer"
                                aria-label="Share {{ $room->title }} summary on WhatsApp"
                                title="Share WhatsApp Property Card">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>WhatsApp Card</span>
                        </button>

                        {{-- Facebook Share Button --}}
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('rooms.show', $room->id)) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white hover:bg-blue-50 hover:border-blue-200 text-slate-600 hover:text-blue-600 transition shadow-2xs"
                           aria-label="Share {{ $room->title }} on Facebook"
                           title="Share on Facebook">
                            <i class="fa-brands fa-facebook-f text-sm"></i>
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-xl overflow-hidden shadow-xl">
                    {{-- Image Section - Reduced Height --}}
                    @if($room->photos && count($room->photos) > 0)
                        <div class="relative h-[300px] lg:h-[450px] overflow-hidden group cursor-zoom-in" onclick="openLightbox(0)">
                             <img src="{{ $room->photo_url }}"
                                  alt="{{ $room->title }} in {{ $room->city }} - Property Details"
                                  id="mainImage"
                                  width="800"
                                  height="600"
                                  class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                  loading="eager"
                                  decoding="async"
                                  onerror="this.onerror=null; this.src='https://placehold.co/800x400?text=No+Image';">
                             
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                             
                            @if($room->is_featured)
                                <span class="absolute top-3 right-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            @endif

                            {{-- Info Overlay --}}
                            <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                <h1 class="text-2xl lg:text-3xl font-black mb-1">{{ $room->title }}</h1>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-orange-400"></i> {{ $room->city }}</span>
                                    
                                    @if($room->listing_type === 'broker')
                                        <span style="background-color: #f97316 !important;" class="text-white px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border border-white/20 shadow-lg">
                                            Broker Fee: ₹{{ $room->broker_fee }}
                                        </span>
                                    @else
                                        <span class="bg-emerald-600 text-white px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-400 shadow-lg">
                                            No Broker Fee
                                        </span>
                                    @endif

                                    <span class="distance-tag hidden px-2 py-0.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-bold" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                        <i class="fas fa-walking mr-1"></i><span class="distance-km">...</span> km away
                                    </span>
                                    <span class="capitalize bg-white/10 px-2 py-0.5 rounded-full text-[10px] font-medium border border-white/20 tracking-wide">{{ $room->roomTypeLabel() }}</span>
                                </div>
                            </div>
                        </div>
                         
                        {{-- Compact Thumbnail Gallery --}}
                        @if(count($room->photo_urls) > 1)
                        <div class="flex gap-3 p-3 bg-slate-50 overflow-x-auto hide-scrollbar">
                            @foreach($room->photo_urls as $index => $photoUrl)
                                <div class="flex-shrink-0 w-24 h-24 rounded-xl overflow-hidden cursor-pointer hover:ring-2 ring-indigo-500 transition-all shadow-sm border border-white"
                                     onclick="openLightbox({{ $index }})">
                                    <img src="{{ $photoUrl }}"
                                         alt="Gallery {{ $index + 1 }}"
                                         class="w-full h-full object-cover"
                                         loading="lazy"
                                         onerror="this.src='https://placehold.co/100?text=No+Image';">
                                </div>
                            @endforeach
                        </div>
                        @endif
                    @else
                        <div class="h-[300px] bg-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">No Images</p>
                            </div>
                        </div>
                    @endif
                     
                    {{-- Compact & Balanced Info Grid --}}
                    <div class="p-4 sm:p-5 border-t border-slate-100 bg-white">
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            {{-- 1. Monthly Rent --}}
                        <div class="stat-card flex items-start gap-3 p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                                <div class="stat-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 text-sm font-black shadow-xs">
                                    <i class="fas fa-indian-rupee-sign"></i>
                                </div>
                                <div class="min-w-0">
                                    @if($room->isForSell())
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Sale Price</div>
                                        <div class="text-lg font-black text-slate-900 leading-snug">
                                            {{ $room->displayPrice() }}
                                        </div>
                                    @else
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Monthly Rent</div>
                                        <div class="text-lg font-black text-slate-900 leading-snug">
                                            ₹{{ number_format($room->rent) }}<span class="text-[11px] font-medium text-slate-400">/mo</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- 2. Deposit (Rent) / Possession Status (Sell) --}}
                            <div class="stat-card flex items-start gap-3 p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                                <div class="stat-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 text-sm font-black shadow-xs">
                                    @if($room->isForSell())
                                        <i class="fas fa-home"></i>
                                    @else
                                        <i class="fas fa-shield-halved"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    @if($room->isForSell())
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Possession</div>
                                        <div class="text-base font-extrabold text-slate-900 leading-snug">
                                            {{ $room->possessionLabel() }}
                                        </div>
                                    @else
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Deposit</div>
                                        <div class="text-base font-extrabold text-slate-900 leading-snug">
                                            {{ $room->deposit ? '₹' . number_format($room->deposit) : 'Nil / Negotiable' }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- 3. Furnishing / Facing Status --}}
                            <div class="stat-card flex items-start gap-3 p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                                <div class="stat-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-700 text-sm font-black shadow-xs">
                                    @if($room->isPlot())
                                        <i class="fas fa-compass"></i>
                                    @else
                                        <i class="fas fa-couch"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                        {{ $room->isPlot() ? 'Facing' : 'Furnishing' }}
                                    </div>
                                    <div class="text-sm font-bold text-slate-900 capitalize truncate leading-snug">
                                        @if($room->isPlot())
                                            {{ $room->feature('facing') ? $room->feature('facing') . ' Facing' : 'Open Facing' }}
                                        @else
                                            {{ $room->furnishingTypeLabel() }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- 4. Property Type --}}
                            <div class="stat-card flex items-start gap-3 p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                                <div class="stat-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700 text-sm font-black shadow-xs">
                                    <i class="fas fa-building"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Property Type</div>
                                    <div class="text-sm font-bold text-slate-900 truncate leading-snug">
                                        {{ $room->propertyType?->name ?? 'Property' }}
                                        @if($room->roomTypeLabel() !== 'N/A') · {{ $room->roomTypeLabel() }} @endif
                                    </div>
                                </div>
                            </div>

                            {{-- 5. Area (Plot Area or Built-up Area) --}}
                            <div class="stat-card flex items-start gap-3 p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                                <div class="stat-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-700 text-sm font-black shadow-xs">
                                    <i class="fas fa-ruler-combined"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                        {{ $room->isPlot() ? 'Plot Dimensions' : 'Built-up Area' }}
                                    </div>
                                    <div class="text-base font-extrabold text-slate-900 leading-snug">
                                        @if($room->isPlot() && $room->feature('plot_area'))
                                            {{ $room->feature('plot_area') }} {{ strtoupper($room->feature('plot_area_unit', 'sqft')) }}
                                        @elseif($room->area_sqft)
                                            {{ number_format((float)$room->area_sqft) }} sqft
                                        @else
                                            Standard
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- 6. Ownership Title (Sell) or Preferred Tenants (Rent) --}}
                            <div class="stat-card flex items-start gap-3 p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                                <div class="stat-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $room->isForSell() ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }} text-sm font-black shadow-xs">
                                    <i class="fas {{ $room->isForSell() ? 'fa-file-contract' : 'fa-users' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    @if($room->isForSell())
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Ownership Title</div>
                                        <div class="text-sm font-bold text-slate-900 capitalize truncate leading-snug">
                                            {{ $room->ownership_type ?: ($room->feature('ownership_type') ?: 'Freehold') }}
                                        </div>
                                    @else
                                        <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Available For</div>
                                        <div class="text-sm font-bold text-slate-900 capitalize truncate leading-snug">
                                            {{ $room->tenantTypeLabel() }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PROPERTY OVERVIEW & KEY SPECIFICATIONS --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 mb-4">
                    <h2 class="text-base font-extrabold text-slate-900 mb-4 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                <i class="fas fa-list-check"></i>
                            </span>
                            Property Overview & Specifications
                        </span>
                        @if($room->isForSell() && $room->ratePerSqft())
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-200">
                                ₹{{ number_format($room->ratePerSqft()) }} / sq.ft
                            </span>
                        @endif
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 text-xs">
                        {{-- Carpet & Super Built-up Area --}}
                        @if($room->feature('carpet_area'))
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Carpet Area</span>
                                <span class="font-extrabold text-slate-900 text-sm">{{ number_format($room->feature('carpet_area')) }} sqft</span>
                            </div>
                        @endif

                        @if($room->feature('super_builtup_area'))
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Super Built-up</span>
                                <span class="font-extrabold text-slate-900 text-sm">{{ number_format($room->feature('super_builtup_area')) }} sqft</span>
                            </div>
                        @endif

                        @if($room->feature('plot_area'))
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Plot Dimensions</span>
                                <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('plot_area') }} {{ strtoupper($room->feature('plot_area_unit', 'sqft')) }}</span>
                            </div>
                        @endif

                        {{-- Bathrooms & Balconies (Residential / Commercial only, not Plot) --}}
                        @if(!$room->isPlot())
                            @if($room->feature('bathrooms', $room->bathrooms))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Bathrooms</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('bathrooms', $room->bathrooms) }} Baths</span>
                                </div>
                            @endif

                            @if($room->feature('balconies', $room->balconies))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Balconies</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('balconies', $room->balconies) }} Balconies</span>
                                </div>
                            @endif

                            {{-- Floor Number / Total Floors --}}
                            @if($room->feature('floor_no') !== null || $room->feature('total_floors') !== null)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Floor</span>
                                    <span class="font-extrabold text-slate-900 text-sm">
                                        {{ $room->feature('floor_no') !== null ? ($room->feature('floor_no') == 0 ? 'Ground' : $room->feature('floor_no') . 'th') : '—' }}
                                        @if($room->feature('total_floors')) of {{ $room->feature('total_floors') }} Floors @endif
                                    </span>
                                </div>
                            @endif
                        @endif

                        {{-- Facing / Vastu --}}
                        @if($room->feature('facing'))
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Facing / Vastu</span>
                                <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('facing') }} Facing</span>
                            </div>
                        @endif

                        {{-- Gated Community / Boundary Wall --}}
                        @if($room->feature('gated_community'))
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Security</span>
                                <span class="font-extrabold text-slate-900 text-sm">Gated Society / Boundary</span>
                            </div>
                        @endif

                        {{-- Parking --}}
                        @if($room->feature('parking_type'))
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Parking</span>
                                <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('parking_type') }}</span>
                            </div>
                        @endif

                        {{-- Water Supply --}}
                        @if($room->feature('water_supply'))
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Water Supply</span>
                                <span class="font-extrabold text-slate-900 text-sm truncate">{{ $room->feature('water_supply') }}</span>
                            </div>
                        @endif

                        {{-- Possession Date / Age / Sell Specific Attributes --}}
                        @if($room->isForSell())
                            @if($room->feature('property_age'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Property Age</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('property_age') }}</span>
                                </div>
                            @endif

                            @if($room->feature('possession_date'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Possession Time</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('possession_date') }}</span>
                                </div>
                            @endif

                            @if($room->feature('ownership_type') ?: $room->ownership_type)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Ownership Title</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('ownership_type') ?: $room->ownership_type }}</span>
                                </div>
                            @endif

                            @if($room->feature('price_negotiable'))
                                <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/80">
                                    <span class="text-emerald-600 block font-medium uppercase text-[10px]">Price Negotiation</span>
                                    <span class="font-extrabold text-emerald-900 text-sm">Negotiable</span>
                                </div>
                            @endif

                            @if($room->feature('is_bank_loan_approved'))
                                <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200/80">
                                    <span class="text-emerald-600 block font-medium uppercase text-[10px]">Bank Loan</span>
                                    <span class="font-extrabold text-emerald-900 text-sm">Approved / Available</span>
                                </div>
                            @endif

                            @if($room->feature('rera_id'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60 col-span-2">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">RERA Registered ID</span>
                                    <span class="font-extrabold text-slate-900 text-sm font-mono">{{ $room->feature('rera_id') }}</span>
                                </div>
                            @endif
                        @else
                            {{-- Rent Specific Attributes --}}
                            @if($room->feature('available_from'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Available From</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ date('d M, Y', strtotime($room->feature('available_from'))) }}</span>
                                </div>
                            @endif

                            @if($room->feature('maintenance_charges'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Maintenance</span>
                                    <span class="font-extrabold text-slate-900 text-sm">₹{{ number_format($room->feature('maintenance_charges')) }}/mo ({{ $room->feature('maintenance_type', 'extra') }})</span>
                                </div>
                            @endif

                            @if($room->feature('lockin_period_months'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Lock-in Period</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('lockin_period_months') }} Months</span>
                                </div>
                            @endif

                            @if($room->feature('notice_period_days'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Notice Period</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('notice_period_days') }} Days</span>
                                </div>
                            @endif

                            @if($room->feature('food_preference'))
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                    <span class="text-slate-400 block font-medium uppercase text-[10px]">Food Preference</span>
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('food_preference') }}</span>
                                </div>
                            @endif

                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/60">
                                <span class="text-slate-400 block font-medium uppercase text-[10px]">Pet Friendly</span>
                                <span class="font-extrabold text-slate-900 text-sm">{{ $room->feature('pet_friendly') ? 'Yes (Pets Allowed)' : 'No Pets' }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Commercial Highlights (if applicable) --}}
                    @if($room->isCommercial() || $room->feature('commercial_type') || $room->feature('is_main_road_facing') || $room->feature('frontage_width_ft'))
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <h3 class="text-xs font-black uppercase tracking-wider text-amber-900 mb-3 flex items-center gap-1.5">
                                <i class="fas fa-briefcase text-amber-600"></i> Commercial Property Highlights
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                @if($room->feature('commercial_type'))
                                    <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 font-bold text-xs">
                                        <i class="fas fa-store mr-1 text-amber-600"></i> {{ $room->feature('commercial_type') }}
                                    </span>
                                @endif
                                @if($room->feature('is_main_road_facing'))
                                    <span class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-900 border border-indigo-200 font-bold text-xs">
                                        <i class="fas fa-road mr-1 text-indigo-600"></i> Main Road Facing
                                    </span>
                                @endif
                                @if($room->feature('is_corner_property'))
                                    <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200 font-bold text-xs">
                                        <i class="fas fa-arrows-up-down-left-right mr-1 text-emerald-600"></i> Corner Property (2 Sides Open)
                                    </span>
                                @endif
                                @if($room->feature('frontage_width_ft'))
                                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 font-bold text-xs">
                                        Frontage: {{ $room->feature('frontage_width_ft') }} Ft
                                    </span>
                                @endif
                                @if($room->feature('washroom_type'))
                                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 font-bold text-xs">
                                        {{ $room->feature('washroom_type') }}
                                    </span>
                                @endif
                                @if($room->feature('power_backup'))
                                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-800 font-bold text-xs">
                                        Backup: {{ $room->feature('power_backup') }}
                                    </span>
                                @endif
                            </div>

                            @if($room->feature('suitable_for') && is_array($room->feature('suitable_for')) && count($room->feature('suitable_for')) > 0)
                                <div class="mt-3">
                                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Suitable For:</span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($room->feature('suitable_for') as $suit)
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold">
                                                {{ $suit }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- 1st Ad Slot: Above Description/Facilities --}}
                <div class="mb-4">
                     @include('partials.adsense-slot', ['placement' => 'room_content'])
                </div>

                {{-- COMBINED AMENITIES & DESCRIPTION --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    @if($room->description)
                        <div class="mb-5">
                            <h2 class="text-base font-extrabold text-slate-900 mb-2 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                    <i class="fas fa-align-left"></i>
                                </span>
                                Property Description
                            </h2>
                            <p class="text-slate-600 text-sm leading-relaxed">{{ $room->description }}</p>
                        </div>
                    @endif
                     
                    @if(!empty($publicAmenities))
                        <div class="{{ $room->description ? 'border-t border-slate-100 pt-5' : '' }}">
                            <h2 class="text-base font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                                    <i class="fas fa-check-double"></i>
                                </span>
                                Amenities & Facilities
                            </h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                @foreach($publicAmenities as $amenity)
                                <div class="flex items-center gap-2.5 px-3 py-2.5 bg-slate-50 border border-slate-200/70 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100/80 transition-colors">
                                    <i class="fas fa-circle-check text-emerald-500 text-sm"></i>
                                    <span>{{ $amenity }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- NEARBY LANDMARKS Section --}}
                @if($room->landmarks && count($room->landmarks) > 0)
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <h2 class="text-base font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                            <i class="fas fa-location-dot"></i>
                        </span>
                        Nearby Landmarks & Connectivity
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @foreach($room->landmarks as $landmark)
                        <div class="bg-slate-50 hover:bg-slate-100/80 text-slate-800 px-3.5 py-2.5 rounded-xl text-xs font-bold flex items-center gap-3 border border-slate-200/70 transition-colors">
                             <div class="w-7 h-7 bg-white rounded-lg flex items-center justify-center shadow-2xs text-indigo-600 shrink-0">
                                <i class="fas fa-map-pin text-xs"></i>
                             </div>
                             <span class="truncate">{{ $landmark }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- VIDEO (if exists) --}}
                @if($room->video || $room->video_url)
                <div class="bg-white rounded-xl p-4 shadow-xl">
                    <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                        <i class="fas fa-video text-pink-600"></i>
                        Video Tour
                    </h2>
                    @if($room->video)
                        <video src="{{ asset('storage/'.$room->video) }}" controls class="w-full rounded-lg" style="max-height: 400px;"></video>
                    @elseif($room->video_url)
                        @php
                            $videoId = null;
                            $isYouTube = false;
                            if (str_contains($room->video_url, 'youtube.com/watch?v=')) {
                                $videoId = explode('v=', $room->video_url)[1];
                                $videoId = explode('&', $videoId)[0]; // Handle additional parameters
                                $isYouTube = true;
                            } elseif (str_contains($room->video_url, 'youtu.be/')) {
                                $videoId = explode('youtu.be/', $room->video_url)[1];
                                $isYouTube = true;
                            }
                        @endphp
                        @if($isYouTube && $videoId)
                            <!-- =================================================================
                            <!-- IMPORTANT: Lite YouTube Embed (No Third-Party Cookies)      
                            <!-- =================================================================
                            -->
                            <div class="video-container">
                                <div class="youtube-lite-embed" data-video-id="{{ $videoId }}">
                                    <img class="youtube-thumbnail" src="{{ route('youtube.proxy', ['videoId' => $videoId]) }}" alt="YouTube video thumbnail for {{ $room->title }}">
                                    <div class="youtube-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Fallback for Vimeo or other video types --}}
                            @php
                                $embedUrl = $room->video_url;
                                if (str_contains($embedUrl, 'vimeo.com/')) {
                                    $embedUrl = str_replace('vimeo.com/', 'player.vimeo.com/video/', $embedUrl);
                                }
                            @endphp
                            <div class="aspect-video w-full rounded-lg overflow-hidden shadow-inner bg-gray-100">
                                <iframe src="{{ $embedUrl }}"
                                        class="w-full h-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                </iframe>
                            </div>
                        @endif
                    @endif
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN - Compact Sidebar --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 space-y-4">

                    {{-- 2nd Ad Slot: Top of Sidebar --}}
                    <div>
                         @include('partials.adsense-slot', ['placement' => 'room_sidebar'])
                    </div>
                    
                    {{-- COMPACT CONTACT CARD --}}
                    <div id="unlock-card-mobile" class="bg-white rounded-xl shadow-2xl overflow-hidden">
                        <div class="p-4 text-white" style="background-color: var(--primary);">
                            <h2 class="font-bold flex items-center gap-2">
                                <i class="fas fa-user-circle text-lg"></i>
                                {{ $room->listing_type === 'broker' ? 'Contact Verified Broker' : 'Contact Direct Owner' }}
                            </h2>
                        </div>
                         
                        <div class="p-4">
                            @php
                                $ownerPhoneRaw = trim((string) ($room->owner?->phone ?? ''));
                                $ownerPhoneDigits = preg_replace('/\D+/', '', $ownerPhoneRaw);
                                $localPhoneDigits = strlen($ownerPhoneDigits) === 12 && str_starts_with($ownerPhoneDigits, '91')
                                    ? substr($ownerPhoneDigits, 2)
                                    : $ownerPhoneDigits;
                                $hasOwnerPhone = strlen($localPhoneDigits) >= 6;
                                $maskedOwnerPhone = $hasOwnerPhone
                                    ? substr($localPhoneDigits, 0, 2) . str_repeat('*', max(4, strlen($localPhoneDigits) - 4)) . substr($localPhoneDigits, -2)
                                    : '98******42';

                                // WhatsApp expects standard international number (e.g. 919876543210 for India)
                                $waPhone = $localPhoneDigits;
                                if (strlen($waPhone) === 10) {
                                    $waPhone = '91' . $waPhone;
                                } elseif (strlen($waPhone) === 11 && str_starts_with($waPhone, '0')) {
                                    $waPhone = '91' . substr($waPhone, 1);
                                }
                                $ownerDisplayName = $room->owner?->name ?: ($room->listing_type === 'broker' ? 'Property Agent' : 'Home Owner');
                                $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
                                if ($room->isForSell()) {
                                    $waPreMessage = "Hello {$ownerDisplayName} ji! Maine aapka property '{$room->title}' {$siteName} par dekha hai. Kya price negotiate ho sakta hai? Mujhe details chahiye: " . route('rooms.show', $room->id);
                                } else {
                                    $waPreMessage = "Hello {$ownerDisplayName} ji! Maine aapka room '{$room->title}' {$siteName} par dekha hai. Kya yeh room abhi available hai? Mujhe visit karni hai: " . route('rooms.show', $room->id);
                                }
                                $waLink = "https://wa.me/{$waPhone}?text=" . rawurlencode($waPreMessage);
                            @endphp
                            @if($isUnlocked)
                                <div class="bg-emerald-50/80 border border-emerald-200 rounded-2xl p-4 mb-3 shadow-xs">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-600 text-white font-extrabold text-[10px] uppercase tracking-wider shadow-xs">
                                            <i class="fas fa-unlock-keyhole text-[10px]"></i> {{ $room->listing_type === 'broker' ? 'Broker Unlocked' : 'Owner Unlocked' }}
                                        </span>
                                        <span class="text-[11px] font-extrabold text-emerald-700 flex items-center gap-1">
                                            <i class="fas fa-circle-check text-emerald-600"></i> Verified Listing
                                        </span>
                                    </div>

                                    <div class="space-y-2.5 text-sm">
                                        @if($hasOwnerPhone)
                                            {{-- Display Phone Box --}}
                                            <div class="rounded-xl border border-emerald-200/80 bg-white p-3.5 text-center shadow-2xs">
                                                <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400">
                                                    {{ $room->listing_type === 'broker' ? 'Broker Contact Number' : 'Owner Contact Number' }}
                                                </p>
                                                <a href="tel:{{ $localPhoneDigits }}" class="mt-1 block text-2xl font-black tracking-wide text-slate-900 hover:text-emerald-700 transition">
                                                    {{ $ownerPhoneRaw }}
                                                </a>
                                                <p class="text-xs text-slate-600 mt-1 font-semibold flex items-center justify-center gap-1">
                                                    <i class="fas fa-user-circle text-slate-400"></i> {{ $ownerDisplayName }}
                                                </p>
                                            </div>

                                            {{-- Direct WhatsApp Connect Button with Pre-filled Message --}}
                                            <a href="{{ $waLink }}"
                                               target="_blank"
                                               rel="noopener"
                                               class="btn-whatsapp-connect group">
                                                <div class="flex items-center gap-2.5">
                                                    <span class="btn-wa-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white text-xl">
                                                        <i class="fa-brands fa-whatsapp"></i>
                                                    </span>
                                                    <div class="text-left leading-tight">
                                                        <span class="block text-sm font-black text-white">Chat on WhatsApp</span>
                                                        <span class="block text-[10px] text-white/90 font-semibold btn-wa-subtitle">Fast reply · Direct visit chat</span>
                                                    </div>
                                                </div>
                                                <i class="fas fa-arrow-right text-xs text-white group-hover:translate-x-1 transition-transform"></i>
                                            </a>

                                            {{-- Direct Phone Call Button --}}
                                            <a href="tel:{{ $localPhoneDigits }}"
                                               class="btn-call-direct">
                                                <i class="fas fa-phone-alt text-xs"></i>
                                                <span>Call Directly</span>
                                            </a>
                                        @else
                                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-center text-xs font-semibold text-amber-800">
                                                <i class="fas fa-circle-exclamation mr-1"></i>Phone number not provided by owner
                                            </div>
                                        @endif

                                        @if($room->listing_type === 'broker')
                                        <div class="mt-3 p-3 bg-amber-50/70 border border-amber-200/80 rounded-xl shadow-2xs">
                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-amber-600 text-white text-[10px]"><i class="fas fa-id-badge"></i></span>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold text-amber-950 truncate">{{ $room->owner?->agency_name ?: ($room->owner?->name . ' (Agent)') }}</p>
                                                        @if($room->owner?->broker_license)
                                                            <p class="text-[10px] text-amber-700 font-semibold">Lic: {{ $room->owner->broker_license }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($room->owner)
                                                <a href="{{ route('agency.show', $room->owner) }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-800 hover:text-amber-950 hover:underline shrink-0">
                                                    <span>Profile</span>
                                                    <i class="fas fa-arrow-right text-[9px]"></i>
                                                </a>
                                                @endif
                                            </div>
                                            <div class="pt-1.5 border-t border-amber-200/60 text-[11px] text-amber-900 leading-tight">
                                                <span class="font-bold">Brokerage:</span> {{ $room->broker_fee ? '₹' . number_format($room->broker_fee) : 'As per agreement' }}
                                                <span class="text-[10px] text-amber-700 block mt-0.5">(Payable only after deal finalization)</span>
                                            </div>
                                            @if($room->owner)
                                            <div class="mt-2.5 pt-2 border-t border-amber-200/60">
                                                <a href="{{ route('agency.show', $room->owner) }}" class="flex items-center justify-center gap-1.5 py-1.5 px-2.5 rounded-lg bg-amber-100/90 hover:bg-amber-200/90 text-amber-950 text-xs font-bold transition-colors">
                                                    <i class="fas fa-building text-amber-700"></i>
                                                    <span>View Agency & All Listings</span>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        @endif

                                        @if($room->owner?->email)
                                        <div class="pt-2 border-t border-slate-200/60 text-center">
                                            <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Email Reference</p>
                                            <p class="font-medium text-slate-700 text-xs truncate mt-0.5">{{ $room->owner->email }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="mb-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/80 px-4 py-3.5 text-center">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Owner mobile number</p>
                                    <p class="mt-1 text-2xl font-black tracking-[.18em] text-slate-800">{{ $maskedOwnerPhone }}</p>
                                    <p class="mt-1 text-xs text-slate-500 font-medium"><i class="fas fa-lock mr-1 text-slate-400"></i>Unlock to view complete number & WhatsApp</p>
                                </div>
                                @auth
                                    @php
                                        $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())->where('status', 'active')->whereHas('plan', fn ($q) => $q->where('type', 'user')->where('is_active', true))->with('plan')->first();
                                        $subscriptionRemaining = 0;
                                        if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'user') {
                                            $usedContacts = $activeSubscription->usages()->where('usage_type', 'contact')->count();
                                            $subscriptionRemaining = max(0, ($activeSubscription->plan->contacts_limit ?? 0) - $usedContacts);
                                        }
                                    @endphp
                                     
                                    @if($subscriptionRemaining > 0)
                                        <div class="bg-emerald-50 border border-emerald-200 p-2.5 rounded-xl mb-3 text-xs text-emerald-800 flex items-center justify-center gap-1.5 font-bold">
                                            <i class="fas fa-crown text-emerald-600"></i> {{ $subscriptionRemaining }} contacts remaining in active plan
                                        </div>
                                    @endif

                                    @if(!filter_var(\App\Models\Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN))
                                        <div class="bg-emerald-50 border border-emerald-100 p-2.5 rounded-xl mb-3 text-xs text-emerald-800 flex items-center gap-2">
                                            <i class="fas fa-unlock text-emerald-600"></i>
                                            <span>Contact unlock is currently <strong>free</strong>.</span>
                                        </div>
                                        <button onclick="unlockContact({{ $room->id }})"
                                                class="w-full text-white font-extrabold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition text-sm flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700">
                                            <i class="fas fa-unlock"></i> Unlock Contact Free
                                        </button>
                                    @elseif(auth()->user()->free_unlocks > 0)
                                        <div class="bg-indigo-50 border border-indigo-100 p-2.5 rounded-xl mb-3 text-xs text-indigo-800 flex items-center gap-2">
                                            <i class="fas fa-gift text-indigo-600"></i>
                                            <span>You have <strong>{{ auth()->user()->free_unlocks }}</strong> free unlock credit{{ auth()->user()->free_unlocks > 1 ? 's' : '' }}!</span>
                                        </div>
                                        <button onclick="unlockContact({{ $room->id }})"
                                                class="w-full text-white font-extrabold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition text-sm flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700">
                                            <i class="fas fa-unlock"></i> Unlock with Free Credit
                                        </button>
                                    @else
                                        <div class="flex items-center justify-between mb-3 px-1 text-xs">
                                            <span class="text-slate-500 font-medium">One-time fee:</span>
                                            <span class="font-black text-slate-900 text-sm">₹{{ \App\Models\Setting::get('unlock_fee', 49) }}</span>
                                        </div>
                                        <button onclick="unlockContact({{ $room->id }})"
                                                class="w-full text-white font-extrabold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition text-sm flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700">
                                            <i class="fas fa-unlock"></i> Unlock Contact Now
                                        </button>
                                    @endif
                                    <a href="{{ route('plans') }}" class="block mt-2.5 text-center text-xs text-indigo-600 font-semibold hover:underline">
                                        <i class="fas fa-crown mr-1"></i>View Multi-Contact Passes & Plans
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                       class="block w-full text-white text-center font-extrabold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition text-sm bg-indigo-600 hover:bg-indigo-700">
                                        <i class="fas fa-arrow-right-to-bracket mr-2"></i>Login to Unlock Contact
                                    </a>
                                @endauth
                            @endif

                            <div class="border-t border-slate-100 pt-3 mt-3 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-slate-400 text-[11px] block">Listed By</span>
                                    <span class="font-bold text-slate-800">{{ $room->owner?->name ?? 'Property Owner' }}</span>
                                </div>
                                <a href="{{ Auth::check() ? route('complaints.create', ['room' => $room->id]) : route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:text-rose-700 transition">
                                    <i class="fas fa-flag text-[11px]"></i> Report listing
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- COMPACT LOCATION --}}
                    @if($isUnlocked || ($isOwner ?? false))
                        @if($room->address || ($room->latitude && $room->longitude))
                        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                            <h2 class="text-base font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                                    <i class="fas fa-map-location-dot"></i>
                                </span>
                                Property Location
                            </h2>
                            @if($room->address)
                                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 mb-3 relative overflow-hidden">
                                    <div class="distance-tag hidden absolute top-0 right-0 bg-slate-900 text-white px-2.5 py-0.5 rounded-bl-lg text-[10px] font-bold shadow-xs" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                        <i class="fas fa-location-arrow mr-1 text-emerald-400"></i><span class="distance-km">...</span> km away
                                    </div>
                                    @if($room->latitude && $room->longitude)
                                        <a href="https://www.google.com/maps?q={{ $room->latitude }},{{ $room->longitude }}"
                                           target="_blank"
                                           class="text-slate-800 font-semibold flex items-center gap-2 hover:text-indigo-600 transition group text-sm">
                                            <i class="fas fa-location-dot text-indigo-500"></i>
                                            <span class="group-hover:underline">{{ $room->address }}, {{ $room->city }}@if($room->feature('pincode')) - {{ $room->feature('pincode') }}@endif</span>
                                            <i class="fas fa-arrow-up-right-from-square text-xs text-slate-400 group-hover:text-indigo-600"></i>
                                        </a>
                                    @else
                                        <p class="text-sm text-slate-700"><i class="fas fa-location-dot text-indigo-500 mr-2"></i>{{ $room->address }}, {{ $room->city }}@if($room->feature('pincode')) - {{ $room->feature('pincode') }}@endif</p>
                                    @endif
                                </div>
                            @endif
                            @if($room->latitude && $room->longitude)
                                <div id="roomMap" class="rounded-xl overflow-hidden shadow-xs border border-slate-200" style="height: 220px;"></div>
                            @elseif($room->address || $room->city)
                                <iframe
                                    class="rounded-xl overflow-hidden shadow-xs border border-slate-200"
                                    style="height:220px;width:100%;"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.google.com/maps?q={{ urlencode(trim(($room->address ? $room->address . ', ' : '') . ($room->city ?? '') . ', ' . ($room->state ?? '') . ', ' . ($room->country ?? 'India'))) }}&output=embed">
                                </iframe>
                            @endif
                        </div>
                        @endif
                    @else
                        <div class="bg-indigo-900 rounded-2xl overflow-hidden shadow-2xl relative min-h-[260px] flex items-center justify-center group border border-indigo-800">
                            <div class="absolute inset-0 z-0">
                                <div id="lockedMap" class="w-full h-full opacity-40 blur-md grayscale scale-110"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/80 to-purple-950/80"></div>
                            </div>

                            <div class="relative z-10 p-6 text-center">
                                <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-full flex items-center justify-center mx-auto mb-4 shadow-2xl ring-4 ring-white/5 group-hover:scale-110 transition-all duration-500">
                                    <i class="fas fa-lock text-3xl text-white"></i>
                                </div>
                                <h3 class="font-black text-xl text-white mb-2 uppercase tracking-tight">Location Locked</h3>
                                <p class="text-indigo-200 text-xs mb-6 max-w-xs mx-auto leading-relaxed">
                                    Unlock to see house number, street name, and precise navigation.
                                </p>

                                <button onclick="unlockContact({{ $room->id }})"
                                        class="bg-indigo-600 text-white font-black py-3 px-6 rounded-xl hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center gap-2 mx-auto uppercase text-xs tracking-widest shadow-xl ring-2 ring-indigo-400/50">
                                    <i class="fas fa-unlock-alt"></i> Unlock Address
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- COMPACT FEATURED CARD --}}
                    @auth
                        @if(Auth::id() === $room->user_id && !$room->is_featured)
                        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl p-4 text-white shadow-xl">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-rocket text-xl"></i>
                                <h3 class="font-bold">Feature This</h3>
                            </div>
                            <p class="text-xs mb-3 text-white/90">10x visibility for ₹{{ \App\Models\Setting::get('featured_fee', 99) }}</p>
                            <button onclick="makeFeatured({{ $room->id }})"
                                    class="w-full bg-white text-yellow-600 font-bold py-2 px-4 rounded-lg hover:bg-yellow-50 transition text-sm">
                                <i class="fas fa-star mr-2"></i>Make Featured
                            </button>
                        </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

@include('rooms.partials.show.related-rooms')
@include('rooms.partials.recently-viewed')
@include('rooms.partials.share-card-modal')

@include('rooms.partials.show.modals')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Record view in Recently Viewed History
    try {
        let recent = JSON.parse(localStorage.getItem('recently_viewed_rooms') || '[]');
        recent = recent.filter(r => String(r.id) !== String({{ $room->id }}));
        recent.unshift({
            id: {{ $room->id }},
            title: @json($room->title),
            slug: @json($room->slug),
            rent: {{ (float) ($room->isForSell() ? ($room->price ?? 0) : ($room->rent ?? 0)) }},
            display_price: @json($room->displayPrice()),
            is_sell: {{ $room->isForSell() ? 'true' : 'false' }},
            city: @json($room->city),
            image: @json($room->photo_url ?: asset('assets/images/default-room.svg')),
            room_type: @json($room->roomTypeOption?->label ?? 'Room'),
            url: @json(route('rooms.show', $room->slug ?: $room->id)),
            viewed_at: Date.now()
        });
        if (recent.length > 8) recent = recent.slice(0, 8);
        localStorage.setItem('recently_viewed_rooms', JSON.stringify(recent));
    } catch (e) {}

    // Render recently viewed on page
    if (typeof renderRecentlyViewed === 'function') {
        renderRecentlyViewed({{ $room->id }});
    }

    window.trackRoomNestEvent?.('ViewContent', {
        content_type: 'room',
        content_ids: [@json((string) $room->id)],
        content_name: @json($room->title),
        city: @json($room->city),
        value: {{ (float) ($room->isForSell() ? ($room->price ?? 0) : ($room->rent ?? 0)) }},
        currency: 'INR'
    });
});
</script>
<script>
    let currentLightboxIndex = 0;
    const lightboxImages = {!! json_encode($room->photo_urls) !!};

    function openLightbox(index) {
        currentLightboxIndex = index;
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImage');
        img.src = lightboxImages[currentLightboxIndex];
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            img.classList.remove('scale-95');
            img.classList.add('scale-100');
        }, 10);
    }

    function closeLightbox() {
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImage');
        img.classList.add('scale-95');
        img.classList.remove('scale-100');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    function navigateLightbox(direction) {
        currentLightboxIndex = (currentLightboxIndex + direction + lightboxImages.length) % lightboxImages.length;
        const img = document.getElementById('lightboxImage');
        img.style.opacity = '0';
        setTimeout(() => {
            img.src = lightboxImages[currentLightboxIndex];
            img.style.opacity = '1';
        }, 200);
    }

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    });
</script>
<!-- =================================================================
<!-- IMPORTANT: CSS and JS for Lite YouTube Embed                        
<!-- =================================================================
-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const liteEmbeds = document.querySelectorAll('.youtube-lite-embed');

    liteEmbeds.forEach(embed => {
        embed.addEventListener('click', function() {
            const videoId = this.dataset.videoId;
            
            // Create the iframe element
            const iframe = document.createElement('iframe');
            iframe.setAttribute('width', '100%');
            iframe.setAttribute('height', '100%');
            iframe.setAttribute('src', `https://www.youtube.com/embed/${videoId}?autoplay=1`);
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', '');

            // Replace the placeholder div with the iframe
            this.parentNode.replaceChild(iframe, this);
        });
    });
});
</script>

<script>
const razorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';

let currentActionDetails = null;

function openPaymentSelectionModal(amount, actionType, roomId) {
    document.getElementById('payAmount').textContent = '₹' + amount;
    currentActionDetails = { amount, actionType, roomId };
    document.getElementById('paymentSelectionModal').classList.remove('hidden');
}

function closePaymentSelectionModal() {
    document.getElementById('paymentSelectionModal').classList.add('hidden');
    currentActionDetails = null;
}

async function confirmPaymentSelection(method) {
    if (!currentActionDetails) return;
    
    const { amount, actionType, roomId } = currentActionDetails;
    closePaymentSelectionModal();

    if (actionType === 'unlock') {
        await executeUnlock(roomId, method);
    } else if (actionType === 'feature') {
        await executeFeature(roomId, method);
    }
}

async function toggleWishlist(roomId) {
    @guest
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Login Required',
                text: 'Please log in to save this property to your wishlist.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Log In Now',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route("login") }}';
                }
            });
        } else {
            if (confirm('Please log in to save this property. Would you like to log in now?')) {
                window.location.href = '{{ route("login") }}';
            }
        }
        return;
    @endguest

    const btn = document.getElementById(`wishlist-btn-${roomId}`);
    const icon = document.getElementById(`wishlist-icon-${roomId}`) || (btn ? btn.querySelector('i') : null);
    const text = document.getElementById(`wishlist-text-${roomId}`) || (btn ? btn.querySelector('span') : null);

    try {
        if (btn) btn.disabled = true;
        const response = await fetch(`{{ url('/wishlist/toggle') }}/${roomId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            throw new Error(err.message || 'Failed to toggle wishlist');
        }

        const data = await response.json();

        if (data.success) {
            if (data.status === 'added') {
                if (icon) {
                    icon.className = 'fas fa-heart text-sm text-rose-500';
                }
                if (text) {
                    text.textContent = 'Saved';
                }
                if (btn) {
                    btn.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-600');
                    btn.classList.remove('border-slate-200', 'bg-white', 'text-slate-700');
                    btn.setAttribute('title', 'Remove from wishlist');
                }
                if (typeof toastr !== 'undefined') {
                    toastr.success('Saved to your wishlist!');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Saved to wishlist!',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            } else {
                if (icon) {
                    icon.className = 'far fa-heart text-sm text-slate-400';
                }
                if (text) {
                    text.textContent = 'Save';
                }
                if (btn) {
                    btn.classList.remove('border-rose-200', 'bg-rose-50', 'text-rose-600');
                    btn.classList.add('border-slate-200', 'bg-white', 'text-slate-700');
                    btn.setAttribute('title', 'Save to wishlist');
                }
                if (typeof toastr !== 'undefined') {
                    toastr.info('Removed from wishlist');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'Removed from wishlist',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        }
    } catch (error) {
        console.error('Wishlist toggle error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error(error.message || 'Error updating wishlist');
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Could not update wishlist'
            });
        }
    } finally {
        if (btn) btn.disabled = false;
    }
}

function unlockContact(roomId) {
    const feeEnabled = @json(filter_var(\App\Models\Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN));
    const fee = feeEnabled ? {{ \App\Models\Setting::get('unlock_fee', 49) }} : 0;
    
    @if(Auth::check())
        @php
            $hasFreeOption = !filter_var(\App\Models\Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN)
                || (Auth::user()->free_unlocks ?? 0) > 0
                || ($subscriptionRemaining ?? 0) > 0;
        @endphp
        const hasFreeOption = @json($hasFreeOption);
        if (!feeEnabled || hasFreeOption) {
            executeUnlock(roomId, 'free');
        } else {
            openPaymentSelectionModal(fee, 'unlock', roomId);
        }
    @else
        window.location.href = '{{ route("login") }}';
    @endif
}

async function executeUnlock(roomId, paymentMethod) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        // Add payment_method to URL params or Body? Body is better for POST
        const response = await fetch(`{{ route('unlock.contact', ':id') }}`.replace(':id', roomId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payment_method: paymentMethod }),
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            if (response.status === 419) {
                throw new Error('CSRF token mismatch. Please refresh the page and try again.');
            }
            const errorData = await response.json().catch(() => ({ message: 'Failed to unlock contact' }));
            throw new Error(errorData.message || 'Failed to unlock contact');
        }
        
        const data = await response.json();
        
        if (data.success) {
            if (data.already_unlocked || data.wallet_used || data.free_credit_used) {
                if (data.subscription_used) {
                    toastr.success(`Contact unlocked using subscription! ${data.remaining_contacts} contacts remaining.`, 'Success');
                } else if (data.free_credit_used) {
                    toastr.success(`Contact unlocked using free credit! ${data.remaining_credits} credits remaining.`, 'Success');
                } else if (data.wallet_used) {
                    toastr.success(`Contact unlocked using wallet! New Balance: ₹${data.new_balance}`, 'Success');
                } else if (data.is_owner) {
                    toastr.info('You are the owner of this room', 'Info');
                } else {
                    toastr.success('Contact details unlocked', 'Success');
                }
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                await initiatePayment(data.payment_id, data.amount, 'unlock', roomId);
            }
        } else {
            toastr.error(data.message || 'Failed to unlock', 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        toastr.error(error.message || 'Something went wrong', 'Error');
    }
}

function makeFeatured(roomId) {
    const fee = {{ \App\Models\Setting::get('featured_fee', 99) }};
    openPaymentSelectionModal(fee, 'feature', roomId);
}

async function executeFeature(roomId, paymentMethod) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const userRole = '{{ Auth::check() ? Auth::user()->role : "" }}';
        const featuredUrl = userRole === 'broker' ? '{{ route("agent.rooms.featured", ":id") }}' : '{{ route("owner.rooms.featured", ":id") }}';
        
        const response = await fetch(featuredUrl.replace(':id', roomId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payment_method: paymentMethod }),
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            if (response.status === 419) {
                throw new Error('CSRF token mismatch. Please refresh the page and try again.');
            }
            const errorData = await response.json().catch(() => ({ message: 'Failed to make featured' }));
            throw new Error(errorData.message || 'Failed to make featured');
        }
        
        const data = await response.json();
        
        if (data.success) {
            if (data.free_feature || data.wallet_used) {
                toastr.success(data.message || 'Room featured successfully!', 'Success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                await initiatePayment(data.payment_id, data.amount, 'featured', roomId);
            }
        } else {
            toastr.error(data.message || 'Failed', 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        toastr.error(error.message || 'Something went wrong', 'Error');
    }
}

async function initiatePayment(paymentId, amount, type, referenceId) {
    try {
        if (type === 'unlock') {
            window.trackRoomNestEvent?.('InitiateCheckout', {
                content_type: 'room',
                content_ids: [String(referenceId)],
                value: Number(amount || 0),
                currency: 'INR'
            });
        }
        // Lazy load Razorpay SDK
        await loadRazorpaySDK();
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        // Check if Razorpay key is set
        if (!razorpayKey || razorpayKey === '' || razorpayKey === 'null') {
            toastr.error('Razorpay key not configured. Please add it in Business Settings.', 'Error');
            return;
        }
        
        // Create order
        const orderResponse = await fetch('{{ route("razorpay.createOrder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payment_id: paymentId }),
            credentials: 'same-origin'
        });
        
        if (!orderResponse.ok) {
            const errorData = await orderResponse.json().catch(() => ({ message: 'Failed to create order' }));
            throw new Error(errorData.message || 'Failed to create order');
        }
        
        const orderData = await orderResponse.json();
        
        if (!orderData.success || !orderData.order_id) {
            throw new Error(orderData.message || 'Failed to create order');
        }
        
        const options = {
            key: razorpayKey,
            amount: orderData.amount * 100,
            currency: 'INR',
            name: '{{ \App\Models\Setting::get('website_name', 'RoomRental') }}',
            description: type === 'unlock' ? 'Unlock Contact Details' : 'Feature Room',
            order_id: orderData.order_id,
            handler: async function(response) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    
                    // Verify payment
                    const verifyResponse = await fetch('{{ route("razorpay.verify") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            ...response,
                            payment_id: paymentId,
                            type: type,
                            reference_id: referenceId
                        }),
                        credentials: 'same-origin'
                    });
                    
                    if (!verifyResponse.ok) {
                        const errorData = await verifyResponse.json().catch(() => ({ message: 'Payment verification failed' }));
                        throw new Error(errorData.message || 'Payment verification failed');
                    }
                    
                    const verifyData = await verifyResponse.json();
                    
                    if (verifyData.status === 'success') {
                        if (type === 'unlock') {
                            const conversion = verifyData.conversion_data || {};
                            window.trackRoomNestEvent?.('Purchase', {
                                content_type: 'room',
                                content_ids: [String(referenceId)],
                                value: Number(conversion.amount || amount || 0),
                                currency: 'INR'
                            });
                        }
                        toastr.success('Payment successful! Contact details unlocked.', 'Success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(verifyData.message || 'Payment verification failed', 'Error');
                    }
                } catch (error) {
                    console.error('Verification error:', error);
                    toastr.error(error.message || 'Payment verification failed', 'Error');
                }
            },
            prefill: {
                name: '{{ Auth::user()->name ?? "" }}',
                email: '{{ Auth::user()->email ?? "" }}'
            },
            theme: {
                color: '#2563eb'
            }
        };
        
        const razorpay = new Razorpay(options);
        
        razorpay.on('payment.failed', function(response) {
            toastr.error('Payment failed: ' + (response.error.description || 'Unknown error'), 'Payment Failed');
        });
        
        razorpay.open();
        
    } catch (error) {
        console.error('Payment error:', error);
        toastr.error('Payment initialization failed: ' + error.message, 'Error');
    }
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

// Change Main Image
function changeMainImage(imageSrc, imageId = 'mainImage') {
    const imgElement = document.getElementById(imageId);
    if (imgElement) {
        imgElement.src = imageSrc;
    }
}
</script>

<!-- Google Maps Integration -->
<script>
const googleMapsKey = '{{ trim(\App\Models\Setting::get("google_maps_api_key", "")) }}';
const roomLat = {{ $room->latitude ?? 'null' }};
const roomLng = {{ $room->longitude ?? 'null' }};
</script>
@if($room->latitude && $room->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function initLeafletMap() {
    const coords = [{{ $room->latitude }}, {{ $room->longitude }}];
    const map = L.map('roomMap').setView(coords, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const markerIcon = L.divIcon({
        html: '<div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center border-4 border-white shadow-xl transform -translate-x-1/2 -translate-y-1/2 pulse-glow"><i class="fas fa-house-chimney text-white text-sm"></i></div>',
        className: 'custom-div-icon',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    L.marker(coords, { icon: markerIcon }).addTo(map)
     .bindPopup(`
        <div class="p-2">
            <h3 class="font-black text-indigo-700 mb-1">{{ $room->title }}</h3>
            <p class="text-xs text-slate-600 mb-2">{{ $room->city }}</p>
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $room->latitude }},{{ $room->longitude }}" target="_blank" class="inline-block bg-indigo-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg no-underline hover:bg-indigo-700 transition">
                <i class="fas fa-directions mr-1"></i> Get Directions
            </a>
        </div>
     `).openPopup();
}

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('roomMap')) {
            initLeafletMap();
        }
        if (document.getElementById('lockedMap')) {
             const coords = [{{ $room->latitude ?? 22.75 }}, {{ $room->longitude ?? 75.86 }}];
             const lMap = L.map('lockedMap', {
                zoomControl: false,
                dragging: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                touchZoom: false
            }).setView(coords, 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(lMap);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        detectUserLocation((coords) => {
            const tags = document.querySelectorAll('.distance-tag');
            tags.forEach(tag => {
                const rLat = parseFloat(tag.dataset.lat);
                const rLng = parseFloat(tag.dataset.lng);
                if (rLat && rLng) {
                    const d = calculateDistance(coords.lat, coords.lng, rLat, rLng);
                    if (d) {
                        const s = tag.querySelector('.distance-km');
                        if (s) s.textContent = d;
                        tag.classList.remove('hidden');
                    }
                }
            });
        });
    });
</script>
@endif
@if(app()->environment('production') && \App\Models\Setting::get('google_ads_enabled') == '1')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomViewLabel = '{{ \App\Models\Setting::get("google_ads_room_view_label") }}';
            if (roomViewLabel && typeof trackAdsConversion === 'function') {
                trackAdsConversion(roomViewLabel, 0, 'INR');
            }
        });
    </script>
@endif

@endpush
@endsection
