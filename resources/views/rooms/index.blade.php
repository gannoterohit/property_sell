@extends('layouts.public')

@section('title', (request('city') ? 'Verified Rooms & PG in ' . request('city') : 'Browse Rooms & PG for Rent') . ' | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@section('description', (request('city') ? 'Find the best verified rooms, apartments, and PG in ' . request('city') . '. Browse listings with photos, rents, and owner contacts.' : 'Browse verified room listings in your city. Find apartments, houses, and rooms for rent with verified owners.'))
@section('keywords', (request('city') ? 'pg in ' . request('city') . ', room for rent in ' . request('city') . ', ' : '') . 'browse rooms, room listings, ' . \App\Models\Setting::get('seo_meta_keywords', 'apartment, house, property'))

@php
    $cityContext = $cityContext ?? ['isFallback' => false, 'activeCityName' => request('city') ?? session('user_city'), 'launchingSoonCityName' => null];
    $displayCity = $cityContext['launchingSoonCityName'] ?? $cityContext['activeCityName'] ?? request('city') ?? session('user_city');
    $hasMetaSearchIntent = request()->hasAny(['city', 'min_rent', 'max_rent', 'min_area_sqft', 'max_area_sqft', 'property_type_id', 'property_category_id', 'tenant_type', 'furnishing_type', 'available_now', 'availability_from']);
@endphp

@push('styles')
@include('partials.listings-ld')
<link rel="preload" href="{{ asset('css/rooms.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/rooms.css') }}"></noscript>
@endpush

@section('content')
@include('rooms.partials.index.search-header')

<!-- ===== MAIN CONTAINER ===== -->
<div class="rooms-main">
    <!-- ===== DESKTOP APP LAYOUT (FIXED SIDEBAR, ONLY ROOMS SCROLL) ===== -->
    <div class="rooms-desktop-app hidden lg:flex">
        <!-- Solid Left Sidebar Pane (Full Height) -->
        <aside class="rooms-desktop-sidebar">
            @include('rooms.partials.index.filter-sidebar')
        </aside>

        <!-- Scrollable Rooms Content Pane -->
        <div class="rooms-desktop-content" id="roomsDesktopScroll">
            @if($cityContext['isFallback'])
                <div class="city-fallback-banner mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <strong class="block text-sm">Launching soon in {{ $cityContext['launchingSoonCityName'] }}</strong>
                        <span class="text-xs">We're currently active in {{ $cityContext['activeCityName'] }}. Showing verified {{ $cityContext['activeCityName'] }} properties for now.</span>
                    </div>
                    <a href="{{ route('rooms.index', ['city' => $cityContext['activeCityName']]) }}" class="rooms-theme-link text-xs font-black">View {{ $cityContext['activeCityName'] }}</a>
                </div>
            @endif

            <!-- Breadcrumb -->
            <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-3.5 font-semibold">
                <a href="{{ url('/') }}" class="rooms-breadcrumb-link transition-colors">Home</a>
                <i class="fas fa-chevron-right text-[8px]"></i>
                <span class="text-slate-600">Rooms in {{ $displayCity ?? 'India' }}</span>
            </div>

            @include('rooms.partials.index.results-header')

            @include('rooms.partials.index.rooms-list')

            <!-- Trust Strip & Recently Viewed inside right scroll pane -->
            <div class="mt-10 space-y-6">
                @include('rooms.partials.index.trust-strip')
                @include('rooms.partials.recently-viewed')
            </div>

            <!-- Modern Enclosed Footer Card -->
            <div class="rooms-desktop-footer-card mt-12 mb-8 rounded-3xl overflow-hidden shadow-2xl border border-slate-800">
                @include('partials.site-footer')
            </div>
        </div>
    </div>

    <!-- ===== MOBILE / TABLET LAYOUT ===== -->
    <div class="lg:hidden container mx-auto px-4 sm:px-6 py-4">
        @if($cityContext['isFallback'])
            <div class="city-fallback-banner mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <strong class="block text-sm">Launching soon in {{ $cityContext['launchingSoonCityName'] }}</strong>
                    <span class="text-xs">We're currently active in {{ $cityContext['activeCityName'] }}. Showing verified {{ $cityContext['activeCityName'] }} properties for now.</span>
                </div>
                <a href="{{ route('rooms.index', ['city' => $cityContext['activeCityName']]) }}" class="rooms-theme-link text-xs font-black">View {{ $cityContext['activeCityName'] }}</a>
            </div>
        @endif

        <!-- Breadcrumb -->
        <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-4 font-semibold">
            <a href="{{ url('/') }}" class="rooms-breadcrumb-link transition-colors">Home</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-slate-600">Rooms in {{ $displayCity ?? 'India' }}</span>
        </div>

        <!-- Mobile Search Bar -->
        <div class="px-4 mb-3">
            <form action="{{ route('rooms.index') }}" method="GET" class="relative">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="city" value="{{ request('city') }}" placeholder="Search locality, area or city..."
                       class="w-full py-3 pl-10 pr-10 bg-white border border-slate-200 text-slate-800 rounded-xl text-sm font-semibold focus:ring-2 outline-none shadow-sm" style="--tw-ring-color: rgba(var(--primary-rgb), 0.2); border-color: var(--primary);">
                @if(request('city'))
                    <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500">
                        <i class="fas fa-times-circle text-sm"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Mobile Filter Button -->
        <div class="px-4 mb-3">
            <button id="mobile-filter-toggle" class="w-full flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl shadow-sm">
                <span class="flex items-center gap-2 text-sm font-bold text-slate-700">
                    <i class="fas fa-sliders" style="color: var(--primary);"></i>
                    Filters
                </span>
                <i class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
            </button>
        </div>

        <!-- Mobile Filter Drawer -->
        <div id="mobile-filter-drawer" class="hidden px-4 mb-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
                <form action="{{ route('rooms.index') }}" method="GET" class="space-y-4">
                    <div>
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block mb-2">Location</label>
                        <input type="text" name="city" value="{{ request('city') }}" placeholder="Enter locality or area..."
                               class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-sm font-semibold focus:ring-2 outline-none" style="--tw-ring-color: rgba(var(--primary-rgb), 0.2); border-color: var(--primary);">
                    </div>
                    <div>
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block mb-2">Property Type</label>
                        <select name="property_type_id" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-sm font-semibold focus:ring-2 outline-none appearance-none" style="--tw-ring-color: rgba(var(--primary-rgb), 0.2); border-color: var(--primary);">
                            <option value="">Any Type</option>
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type->id }}" {{ request('property_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full text-white py-3 rounded-xl font-bold text-sm shadow-lg active:scale-[0.98] transition-all" style="background: var(--primary);">
                        Apply Filters
                    </button>
                </form>
            </div>
        </div>

        @include('rooms.partials.listing-mobile')

        <div class="mt-6 space-y-6">
            @include('rooms.partials.index.trust-strip')
            @include('rooms.partials.recently-viewed')
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof renderRecentlyViewed === 'function') {
        renderRecentlyViewed();
    }

    const hasSearchIntent = @json($hasMetaSearchIntent);
    if (hasSearchIntent) {
        window.trackRoomNestEvent?.('Search', {
            search_string: @json(request('city') ?? session('user_city') ?? ''),
            city: @json($displayCity ?? ''),
            content_type: 'room'
        });
    }

    // Mobile filter toggle
    const filterToggle = document.getElementById('mobile-filter-toggle');
    const filterDrawer = document.getElementById('mobile-filter-drawer');
    if (filterToggle && filterDrawer) {
        filterToggle.addEventListener('click', () => {
            filterDrawer.classList.toggle('hidden');
            const icon = filterToggle.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (icon) {
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }
        });
    }

    // Pull to refresh for mobile rooms listing
    if (window.innerWidth < 1024) {
        let startY = 0;
        let currentY = 0;
        let isPulling = false;
        const roomsList = document.getElementById('mobile-room-list');
        const loader = document.getElementById('infinite-loader');
        
        if (roomsList && loader) {
            document.addEventListener('touchstart', (e) => {
                if (window.scrollY === 0) {
                    startY = e.touches[0].clientY;
                    isPulling = true;
                }
            }, { passive: true });
            
            document.addEventListener('touchmove', (e) => {
                if (!isPulling) return;
                currentY = e.touches[0].clientY;
                const diff = currentY - startY;
                if (diff > 0 && window.scrollY === 0) {
                    loader.style.transform = `translateY(${Math.min(diff * 0.3, 60)}px)`;
                    loader.style.opacity = Math.min(diff / 100, 1);
                }
            }, { passive: true });
            
            document.addEventListener('touchend', () => {
                if (!isPulling) return;
                isPulling = false;
                const diff = currentY - startY;
                if (diff > 80 && window.scrollY === 0) {
                    window.location.reload();
                } else {
                    loader.style.transform = 'translateY(0)';
                    loader.style.opacity = '1';
                }
                startY = 0;
                currentY = 0;
            });
        }
    }
});
</script>
@auth
    @if(Auth::user()->role === 'owner')
        @push('sweetalert')
            <script src="{{ asset('assets/js/sweetalert2.min.js') }}" defer></script>
        @endpush
    @endif
    <script defer>
    async function toggleWishlist(event, roomId) {
        event.preventDefault();
        event.stopPropagation();
        
        @guest
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        try {
            const response = await fetch(`{{ url('/wishlist/toggle') }}/${roomId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to toggle wishlist');
            const data = await response.json();

            if (data.success) {
                const updateBtn = (id) => {
                    const btn = document.getElementById(id);
                    if (btn) {
                        const icon = btn.querySelector('i');
                        if (data.status === 'added') {
                            icon.classList.remove('far');
                            icon.classList.add('fas', 'text-red-500');
                        } else {
                            icon.classList.remove('fas', 'text-red-500');
                            icon.classList.add('far');
                        }
                    }
                };
                updateBtn(`wishlist-btn-${roomId}`);
                updateBtn(`wishlist-btn-mobile-${roomId}`);
            }
        } catch (error) {
            console.error(error);
        }
    }
    const razorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-room-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const result = await Swal.fire({
                    title: 'Delete Room?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (!result.isConfirmed) return;
                
                const formData = new FormData(this);
                const roomId = this.dataset.roomId;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    if (response.status === 419) {
                        throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                    }
                    
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to delete room');
                        }
                        return data;
                    } else {
                        const text = await response.text();
                        throw new Error(text || 'Invalid response from server');
                    }
                })
                .then(data => {
                    if (data.success) {
                        toastr.success('Room deleted successfully', 'Success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(data.message || 'Failed to delete room', 'Error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error(error.message || 'Failed to delete room', 'Error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        });
    });

    async function markBooked(roomId) {
        const result = await Swal.fire({
            title: 'Mark as Rented?',
            text: "This room will be hidden from users.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: ROOM_PRIMARY_COLOR,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark it!'
        });

        if (!result.isConfirmed) return;
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const userRole = '{{ Auth::check() ? Auth::user()->role : "" }}';
            const bookedUrl = userRole === 'broker' ? '{{ route("agent.rooms.markBooked", ":id") }}' : '{{ route("owner.rooms.markBooked", ":id") }}';
            
            const response = await fetch(bookedUrl.replace(':id', roomId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire(
                    'Rented!',
                    'Room has been marked as rented.',
                    'success'
                ).then(() => {
                    location.reload();
                });
            } else {
                toastr.error(data.message || 'Failed to mark room as rented', 'Error');
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong', 'Error');
        }
    }

    async function markAvailable(roomId) {
        const result = await Swal.fire({
            title: 'Make Available?',
            text: "Making this room available will update its status.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: ROOM_SECONDARY_COLOR,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Continue'
        });

        if (!result.isConfirmed) return;
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const userRole = '{{ Auth::check() ? Auth::user()->role : "" }}';
            const availableUrl = userRole === 'broker' ? '{{ route("agent.rooms.markAvailable", ":id") }}' : '{{ route("owner.rooms.markAvailable", ":id") }}';
            
            const response = await fetch(availableUrl.replace(':id', roomId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (data.subscription_used) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Room made available using subscription!',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else if (data.payment_id) {
                    await initiatePayment(data.payment_id, data.amount, 'listing', roomId);
                } else {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Room marked as available.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } else {
                toastr.error(data.message || 'Failed to make room available', 'Error');
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong', 'Error');
        }
    }

    async function initiatePayment(paymentId, amount, type, referenceId) {
        try {
            const Razorpay = await loadRazorpaySDK();

            if (!razorpayKey || razorpayKey === '' || razorpayKey === 'null') {
                toastr.error('Razorpay key not configured. Please add it in Business Settings.', 'Error');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
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
                name: '{{ \App\Models\Setting::get("website_name", "RoomRental") }}',
                description: 'Make Room Available - Listing Fee',
                order_id: orderData.order_id,
                handler: async function(response) {
                    if (!response.razorpay_order_id || !response.razorpay_signature) {
                        alert('Payment failed: Missing order ID or signature. Please try again.');
                        return;
                    }

                    try {
                        const verifyResponse = await fetch('{{ route("razorpay.verify") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id || orderData.order_id,
                                razorpay_signature: response.razorpay_signature,
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
                            Swal.fire({
                                title: 'Success!',
                                text: 'Payment successful! Room is now available.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
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
                    color: ROOM_PRIMARY_COLOR
                },
                method: {
                    upi: true,
                    card: true,
                    netbanking: true,
                    wallet: true
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
    async function subscribeToAlerts(city) {
        @guest
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        const btn = document.getElementById('notify-btn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subscribing...';

        try {
            const response = await fetch('{{ route("city-alerts.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ city })
            });

            const data = await response.json();
            if (data.success) {
                toastr.success(data.message, 'Success');
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Subscribed';
                btn.classList.replace('bg-indigo-50', 'bg-green-50');
                btn.classList.replace('text-indigo-700', 'text-green-700');
            } else {
                toastr.error(data.message || 'Failed to subscribe', 'Error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong. Please try again.', 'Error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
    </script>
@endauth

<!-- Auto-City Detection -->
<script>
const ROOM_PRIMARY_COLOR = '{{ \App\Models\Setting::get("primary_color", "#4F46E5") }}';
const ROOM_SECONDARY_COLOR = '{{ \App\Models\Setting::get("secondary_color", "#10B981") }}';
    async function detectLocation(force = false) {
        if (!navigator.geolocation) return;

        const cityInput = document.getElementById('hero-city-input');
        const originalPlaceholder = cityInput ? cityInput.placeholder : '';
        if (cityInput) cityInput.placeholder = 'Detecting location...';

        navigator.geolocation.getCurrentPosition(async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
                const data = await response.json();
                const city = data.address.city || data.address.town || data.address.village || data.address.suburb || data.address.state_district;
                
                if (city) {
                    sessionStorage.setItem('apnanest_location_checked', '1');
                    if (cityInput) {
                        cityInput.value = city;
                        cityInput.placeholder = originalPlaceholder;
                    }
                    await fetch(`{{ route('set-city') }}?city=${encodeURIComponent(city)}&lat=${lat}&lng=${lng}&verified=true`);
                    window.location.href = window.location.pathname + `?lat=${lat}&lng=${lng}&city=${encodeURIComponent(city)}`;
                } else if (cityInput) {
                    cityInput.placeholder = originalPlaceholder;
                }
            } catch (error) {
                console.error('Location error:', error);
                if (cityInput) cityInput.placeholder = originalPlaceholder;
            }
        }, (error) => {
            console.warn('Geolocation failed:', error);
            if (cityInput) cityInput.placeholder = 'Location denied. Type city manually.';
        });
    }

    @if(!request('city') && !session('no_auto'))
        document.addEventListener('DOMContentLoaded', () => {
            if (!sessionStorage.getItem('apnanest_location_checked')) {
                setTimeout(() => detectLocation(), 1200);
            }
        });
    @endif
</script>

<script defer>
    document.addEventListener('DOMContentLoaded', () => {
        detectUserLocation((coords) => {
            const tags = document.querySelectorAll('.distance-tag');
            tags.forEach(tag => {
                const roomLat = parseFloat(tag.dataset.lat);
                const roomLng = parseFloat(tag.dataset.lng);
                
                if (roomLat && roomLng) {
                    const dist = calculateDistance(coords.lat, coords.lng, roomLat, roomLng);
                    if (dist) {
                        const kmSpan = tag.querySelector('.distance-km');
                        if (kmSpan) kmSpan.textContent = dist;
                        tag.classList.remove('hidden');
                    }
                }
            });
        });
    });
</script>

<!-- Infinite Scroll Script for Mobile -->
<script defer>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileRoomList = document.getElementById('mobile-room-list');
        const loader = document.getElementById('infinite-loader');
        let isLoading = false;
        let hasMore = @json($rooms->hasMorePages());
        let nextPage = @json($rooms->currentPage() + 1);

        if (!mobileRoomList || !loader) return;

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !isLoading && hasMore) {
                loadMoreRooms();
            }
        }, { threshold: 0.1 });

        observer.observe(loader);

        async function loadMoreRooms() {
            isLoading = true;
            loader.classList.remove('hidden');

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('page', nextPage);

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to load rooms');

                const data = await response.json();
                
                if (data.html) {
                    mobileRoomList.insertAdjacentHTML('beforeend', data.html);
                    nextPage++;
                    hasMore = data.hasMore;
                    
                    if (!hasMore) {
                        loader.remove();
                        observer.disconnect();
                    }
                }
            } catch (error) {
                console.error('Error loading more rooms:', error);
            } finally {
                isLoading = false;
                if (hasMore) {
                    loader.classList.add('hidden');
                }
            }
        }
    });
</script>
@endpush
@endsection
