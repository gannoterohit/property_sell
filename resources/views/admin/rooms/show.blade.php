@extends('layouts.admin')

@section('title', 'Property Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">
@if($room->latitude && $room->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endif

@endpush

@section('admin-content')
@php
    $photos = collect($room->photo_urls ?? [])->filter()->values();
    $mainPhoto = $photos->first() ?: asset('assets/images/default-room.svg');
    $approvalClass = match($room->listing_status) {
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-red-50 text-red-700 border-red-100',
        default => 'bg-amber-50 text-amber-700 border-amber-100',
    };
    $moderationClass = $room->moderation_status === 'normal'
        ? 'bg-slate-100 text-slate-700 border-slate-200'
        : 'bg-red-50 text-red-700 border-red-100';
    $currentAmenities = is_array($room->amenities) ? $room->amenities : (json_decode($room->amenities, true) ?? []);
    $currentLandmarks = is_array($room->landmarks) ? $room->landmarks : (json_decode($room->landmarks, true) ?? []);
@endphp

<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('admin.all-rooms') }}" class="admin-theme-text text-xs font-bold"><i class="fas fa-arrow-left mr-1"></i>All listings</a>
            <p class="admin-theme-text mt-3 text-[10px] font-extrabold uppercase tracking-[.2em]">Property management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">{{ $room->title }}</h1>
            <p class="text-sm text-slate-500">Internal property review, owner details and publishing controls.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('rooms.show', $room) }}" target="_blank" class="rounded-xl border bg-white px-4 py-2.5 text-xs font-bold text-slate-700"><i class="fas fa-up-right-from-square mr-2"></i>Public preview</a>
            <x-admin.action-icon variant="edit" :href="route('admin.rooms.edit', $room)" title="Edit property" />
        </div>
    </header>

    <div class="admin-room-show-grid">
        <main class="min-w-0 space-y-5">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="admin-room-gallery">
                     <img class="admin-room-gallery-main" src="{{ $mainPhoto }}" alt="{{ $room->title }}" width="800" height="600" loading="eager" decoding="async" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-room.svg') }}'">
                     <div class="admin-room-gallery-side">
                         @foreach($photos->skip(1)->take(2) as $photo)
                             <img src="{{ $photo }}" alt="{{ $room->title }} photo {{ $loop->iteration + 1 }}" width="400" height="300" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-room.svg') }}'">
                         @endforeach
                        @if($photos->count() < 2)
                            <div class="flex min-h-[160px] items-center justify-center rounded-2xl border bg-slate-50 text-slate-300"><i class="fas fa-image text-2xl"></i></div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="admin-room-kpis admin-kpis">
                @if($room->isForSell())
                    <div class="admin-room-kpi"><span>Purpose</span><strong style="color:#7c3aed"><i class="fas fa-tag mr-1"></i>For Sale</strong></div>
                    <div class="admin-room-kpi"><span>Sale Price</span><strong>&#8377;{{ $room->displayPrice() }}</strong></div>
                    <div class="admin-room-kpi"><span>Possession</span><strong>{{ $room->possessionLabel() }}</strong></div>
                @else
                    <div class="admin-room-kpi"><span>Purpose</span><strong style="color:#059669"><i class="fas fa-key mr-1"></i>For Rent</strong></div>
                    <div class="admin-room-kpi"><span>Rent</span><strong>&#8377;{{ number_format((float) $room->rent) }}/mo</strong></div>
                    <div class="admin-room-kpi"><span>Deposit</span><strong>&#8377;{{ number_format((float) $room->deposit) }}</strong></div>
                @endif
                <div class="admin-room-kpi"><span>Property type</span><strong>{{ $room->propertyType?->name ?? 'Not set' }}</strong></div>
                <div class="admin-room-kpi"><span>Availability</span><strong>{{ ucfirst($room->status) }}</strong></div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-950">Listing information</h2>
                        <p class="text-xs text-slate-500">Content visible to users on public room pages.</p>
                    </div>
                    @if($room->is_featured)
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-700">Featured</span>
                    @endif
                </div>
                <dl class="grid gap-4 md:grid-cols-2">
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Purpose</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->isForSell() ? 'For Sale' : 'For Rent' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Property type</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->propertyType?->name ?? 'Not set' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Property category</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->propertyCategory?->name ?? 'Not set' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Room type</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->roomTypeLabel() }}</dd></div>
                    @if($room->isForSell())
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">Sale Price</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->displayPrice() }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">Possession Status</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->possessionLabel() }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">Ownership Type</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->ownership_type ?: 'Not provided' }}</dd></div>
                    @else
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">Monthly Rent</dt><dd class="mt-1 text-sm font-semibold text-slate-800">&#8377;{{ number_format((float)$room->rent) }}/mo</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">Deposit</dt><dd class="mt-1 text-sm font-semibold text-slate-800">&#8377;{{ number_format((float)$room->deposit) }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">Preferred tenant</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->tenantTypeLabel() }}</dd></div>
                    @endif
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Area</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->area_sqft ? number_format((float) $room->area_sqft, 2) . ' sq ft' : 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Address</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->address ?: 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">City</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->city ?: 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">State</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->state ?: 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Country</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->country ?: 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Furnishing</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->furnishingTypeLabel() }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Latitude</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->latitude ?? 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Longitude</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->longitude ?? 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Listing type</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ ucfirst($room->listing_type ?? 'owner') }} @if($room->listing_type === 'broker') - Broker fee &#8377;{{ number_format((float) $room->broker_fee) }} @endif</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Available from</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->availability_from ? \Carbon\Carbon::parse($room->availability_from)->format('d M Y') : 'Not specified' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Video upload</dt><dd class="mt-1 text-sm font-semibold text-slate-800">@if($room->video)<a href="{{ asset('storage/' . $room->video) }}" target="_blank" class="admin-theme-text">View uploaded video</a>@else Not uploaded @endif</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Video URL</dt><dd class="mt-1 break-all text-sm font-semibold text-slate-800">@if($room->video_url)<a href="{{ $room->video_url }}" target="_blank" class="admin-theme-text">{{ $room->video_url }}</a>@else Not provided @endif</dd></div>
                </dl>
                <div class="mt-5 border-t pt-5">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Description</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $room->description ?: 'No description added.' }}</p>
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-950"><i class="fas fa-map-marked-alt mr-2 text-red-600"></i>Location</h2>
                        <p class="text-xs text-slate-500">{{ $room->address ? $room->address . ', ' . $room->city : 'Map preview and saved coordinates.' }}</p>
                    </div>
                    @if($room->latitude && $room->longitude)
                        <a href="https://www.google.com/maps?q={{ $room->latitude }},{{ $room->longitude }}" target="_blank" class="rounded-xl border bg-white px-3 py-2 text-xs font-bold text-slate-700">
                            <i class="fas fa-up-right-from-square mr-1"></i>Open map
                        </a>
                    @elseif($room->address || $room->city)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(trim(($room->address ? $room->address . ', ' : '') . ($room->city ?? ''))) }}" target="_blank" class="rounded-xl border bg-white px-3 py-2 text-xs font-bold text-slate-700">
                            <i class="fas fa-magnifying-glass-location mr-1"></i>Search address
                        </a>
                    @endif
                </div>

                @if($room->latitude && $room->longitude)
                    <div id="adminRoomMap"></div>
                    <div class="mt-3 grid gap-3 text-xs md:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 px-3 py-2"><span class="font-bold text-slate-400">Latitude</span><strong class="ml-2 text-slate-700">{{ $room->latitude }}</strong></div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2"><span class="font-bold text-slate-400">Longitude</span><strong class="ml-2 text-slate-700">{{ $room->longitude }}</strong></div>
                    </div>
                @elseif($room->address || $room->city)
                    <iframe
                        class="admin-address-map"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q={{ urlencode(trim(($room->address ? $room->address . ', ' : '') . ($room->city ?? '') . ', ' . ($room->state ?? '') . ', ' . ($room->country ?? 'India'))) }}&output=embed">
                    </iframe>
                    <p class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                        <i class="fas fa-circle-info mr-1"></i> This map is based on the saved address. Add latitude/longitude in edit mode for an exact marker.
                    </p>
                @else
                    <div class="admin-map-empty">
                        <div>
                            <i class="fas fa-location-crosshairs text-3xl text-slate-300"></i>
                            <h3 class="mt-3 text-sm font-extrabold text-slate-700">Coordinates not saved</h3>
                            <p class="mt-1 max-w-md text-xs leading-5 text-slate-500">Edit this property and save latitude/longitude to show the same embedded map preview here.</p>
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="admin-theme-text mt-3 inline-flex text-xs font-bold">Edit location <i class="fas fa-arrow-right ml-2"></i></a>
                        </div>
                    </div>
                @endif
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-950">Facilities</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse($currentAmenities as $amenity)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-600">{{ $amenity }}</span>
                        @empty
                            <p class="text-sm text-slate-500">No facilities listed.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-950">Nearby landmarks</h2>
                    <div class="mt-4 space-y-2">
                        @forelse($currentLandmarks as $landmark)
                            <p class="rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600"><i class="fas fa-location-dot mr-2 text-slate-400"></i>{{ $landmark }}</p>
                        @empty
                            <p class="text-sm text-slate-500">No landmarks added.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <aside class="admin-room-aside space-y-4">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-slate-950">Review status</h2>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Room ID</span><strong class="text-xs">#{{ $room->id }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border px-3 py-2 {{ $approvalClass }}"><span class="text-xs font-bold">Approval</span><strong class="text-xs uppercase">{{ $room->listing_status }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border px-3 py-2 {{ $moderationClass }}"><span class="text-xs font-bold">Moderation</span><strong class="text-xs uppercase">{{ $room->moderation_status }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Public status</span><strong class="text-xs uppercase">{{ $room->status }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Listing fee</span><strong class="text-xs uppercase">{{ $room->listing_fee_paid ? 'Paid' : 'Unpaid' }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Payment ID</span><strong class="text-xs">{{ $room->listing_payment_id ?? 'Not set' }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Featured</span><strong class="text-xs uppercase">{{ $room->is_featured ? 'Yes' : 'No' }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Expires</span><strong class="text-xs">{{ $room->expires_at?->format('d M Y') ?? 'Not set' }}</strong></div>
                </div>
            </section>

            @php
                $fraudCheck = $room->detectDirectContactInfo();
                $duplicateRooms = $room->getSuspectedDuplicates(3);
            @endphp
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-extrabold text-slate-950 flex items-center gap-1.5">
                        <i class="fas fa-shield-halved text-indigo-600 text-xs"></i>
                        Integrity & Anti-Fraud Audit
                    </h2>
                    @if($fraudCheck['flagged'] || $duplicateRooms->isNotEmpty())
                        <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[9px] font-extrabold text-rose-700">Warning</span>
                    @else
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-extrabold text-emerald-700">Clean</span>
                    @endif
                </div>

                <div class="mt-3 space-y-2 text-xs">
                    {{-- Direct Contact Check --}}
                    @if($fraudCheck['flagged'])
                        <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-3 text-rose-900">
                            <strong class="flex items-center gap-1.5 text-xs font-bold text-rose-700">
                                <i class="fas fa-triangle-exclamation"></i> Direct Contact Detected
                            </strong>
                            <p class="mt-1 text-[11px] text-rose-800 leading-tight">
                                Phone number or bypass phrase in description/title:
                            </p>
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach($fraudCheck['matches'] as $match)
                                    <span class="rounded bg-rose-200/80 px-1.5 py-0.5 font-mono text-[10px] font-bold text-rose-950">{{ $match }}</span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            <span>Phone Bypass Check</span>
                            <strong class="text-emerald-700 font-bold"><i class="fas fa-check-circle mr-1"></i>Passed</strong>
                        </div>
                    @endif

                    {{-- Duplicate Check --}}
                    @if($duplicateRooms->isNotEmpty())
                        <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-amber-900">
                            <strong class="flex items-center gap-1.5 text-xs font-bold text-amber-800">
                                <i class="fas fa-copy"></i> Suspected Duplicate Listing
                            </strong>
                            <p class="mt-1 text-[11px] text-amber-800 leading-tight">
                                Similar property found in the same city:
                            </p>
                            <ul class="mt-2 space-y-1">
                                @foreach($duplicateRooms as $dup)
                                    <li>
                                        <a href="{{ route('admin.rooms.show', $dup->slug ?: $dup->id) }}" target="_blank" class="flex items-center justify-between rounded-lg bg-white/80 border border-amber-200 p-1.5 text-[11px] font-bold text-amber-950 hover:bg-white transition">
                                            <span class="truncate max-w-[150px]">#{{ $dup->id }} - {{ $dup->title }}</span>
                                            <span class="text-indigo-600"><i class="fas fa-external-link-alt text-[9px]"></i> View</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600">
                            <span>Duplicate Check</span>
                            <strong class="text-emerald-700 font-bold"><i class="fas fa-check-circle mr-1"></i>Unique</strong>
                        </div>
                    @endif
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-slate-950">Owner</h2>
                <div class="mt-4 flex items-center gap-3">
                    <div class="admin-theme-soft flex h-11 w-11 items-center justify-center rounded-xl text-sm font-extrabold">{{ strtoupper(substr($room->owner?->name ?? 'O', 0, 1)) }}</div>
                    <div class="min-w-0">
                        <a href="{{ $room->owner ? route('admin.owners.detail', $room->owner) : '#' }}" class="admin-theme-text block truncate text-sm font-extrabold">{{ $room->owner?->name ?? 'Unknown owner' }}</a>
                        <p class="truncate text-xs text-slate-500">{{ $room->owner?->email ?? 'Email unavailable' }}</p>
                    </div>
                </div>
                <dl class="mt-4 space-y-3 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Owner ID</dt><dd class="font-bold text-slate-700">{{ $room->user_id }}</dd></div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-400">Phone</dt>
                        <dd class="flex items-center gap-1.5 font-bold text-slate-700">
                            <span>{{ $room->owner?->phone ?? 'Not provided' }}</span>
                            @if($room->owner?->phone)
                                <x-admin.whatsapp-btn :phone="$room->owner->phone" :name="$room->owner->name" context="Property Listing #{{ $room->id }} ('{{ $room->title }}')" size="xs" />
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">KYC</dt><dd class="font-bold text-slate-700">{{ ucfirst(str_replace('_', ' ', $room->owner?->verification_status ?? 'unknown')) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Created</dt><dd class="font-bold text-slate-700">{{ $room->created_at->format('d M Y') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Updated</dt><dd class="font-bold text-slate-700">{{ $room->updated_at->format('d M Y, h:i A') }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-slate-950">Actions</h2>
                <div class="mt-4 grid gap-2">
                    @if($room->listing_status !== 'approved')
                        <form method="POST" action="{{ route('admin.rooms.approve', $room) }}" class="admin-room-json-form">
                            @csrf
                            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white"><i class="fas fa-check mr-2"></i>Approve listing</button>
                        </form>
                    @endif
                    <x-admin.action-icon variant="edit" :href="route('admin.rooms.edit', $room)" title="Edit listing" />
                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" class="admin-confirm" data-confirm-title="Delete this property listing?" data-confirm-text="This listing and its related data will be permanently removed." data-confirm-button="Yes, delete listing">
                        @csrf @method('DELETE')
                        <x-admin.action-icon variant="delete" type="submit" title="Delete listing" />
                    </form>
                </div>
            </section>

            @if($room->listing_status !== 'rejected')
                <section class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-950">Reject listing</h2>
                    <form method="POST" action="{{ route('admin.rooms.reject', $room) }}" class="admin-room-json-form mt-4 space-y-3">
                        @csrf
                        <div class="max-h-52 space-y-2 overflow-y-auto pr-1">
                            @foreach($rejectionReasons as $reason)
                                <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                    <input type="checkbox" name="reasons[]" value="{{ $reason->id }}" class="mt-0.5 rounded">
                                    <span>{{ $reason->reason }}</span>
                                </label>
                            @endforeach
                        </div>
                        <textarea name="customReason" rows="3" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Optional custom reason"></textarea>
                        <button class="w-full rounded-xl bg-red-600 py-3 text-sm font-bold text-white"><i class="fas fa-ban mr-2"></i>Reject listing</button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
@if($room->latitude && $room->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('adminRoomMap');
    if (!mapElement || typeof L === 'undefined') return;

    const coords = [{{ $room->latitude }}, {{ $room->longitude }}];
    const roomTitle = @json($room->title);
    const roomCity = @json($room->city ?? '');
    const map = L.map('adminRoomMap').setView(coords, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const markerIcon = L.divIcon({
        html: '<div class="flex h-10 w-10 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border-4 border-white bg-indigo-600 shadow-xl"><i class="fas fa-building text-sm text-white"></i></div>',
        className: 'admin-room-marker',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    L.marker(coords, { icon: markerIcon }).addTo(map).bindPopup(`
        <div class="p-2">
            <h3 class="mb-1 font-black text-indigo-700">${roomTitle}</h3>
            <p class="mb-2 text-xs text-slate-600">${roomCity}</p>
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $room->latitude }},{{ $room->longitude }}" target="_blank" class="inline-block rounded-lg bg-indigo-600 px-3 py-1.5 text-[10px] font-bold text-white no-underline">
                Get Directions
            </a>
        </div>
    `).openPopup();
});
</script>
@endif
<script>
document.querySelectorAll('.admin-room-json-form').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"], button:not([type])');
        const original = button?.innerHTML;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
                body: new FormData(form)
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Request failed');
            toastr.success(data.message || 'Listing updated.', 'Success');
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            toastr.error(error.message, 'Error');
            if (button) {
                button.disabled = false;
                button.innerHTML = original;
            }
        }
    });
});
</script>
@endpush
