<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="format-detection" content="telephone=no">

    @vite(['resources/css/app.css', 'resources/css/mobile-app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/auth-modal-reference.css') }}">
    <title>@yield('title', \App\Models\Setting::get('website_name', 'RoomRental') . ' - Find Your Perfect Room')</title>
    <meta name="description" content="@yield('description', \App\Models\Setting::get('seo_meta_description', 'Find your perfect room in your city. Browse verified room listings.'))">
    <meta name="keywords" content="@yield('keywords', \App\Models\Setting::get('seo_meta_keywords', 'room rental, apartment, house, property'))">
    <meta name="author" content="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}">
    <meta name="robots" content="{{ request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*') ? 'noindex, nofollow' : 'index, follow' }}">
    <meta name="theme-color" content="{{ \App\Models\Setting::get('primary_color', '#4F46E5') }}">
    
    
    <!-- Favicon -->
    @php
        $favicon = \App\Models\Setting::get('website_favicon');
        $faviconUrl = \App\Models\Setting::mediaUrl($favicon);
    @endphp
    @if($faviconUrl)
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
    @endif
    
    @yield('layout-seo')
    @yield('layout-structured-data')
    @yield('layout-tracking')

    <!-- Preconnect to external domains - Mobile Optimized -->
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <!-- Resource Hints for Mobile Performance -->
    <meta http-equiv="x-dns-prefetch-control" content="on">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"></noscript>
    
    <style>
        :root {
            --primary: {{ \App\Models\Setting::get('primary_color', '#4F46E5') }};
            --primary-dark: {{ \App\Models\Setting::get('primary_color', '#4F46E5') }};
            --accent: #F59E0B;
            --secondary: {{ \App\Models\Setting::get('secondary_color', '#10B981') }};
            --danger: #EF4444;
            --primary-rgb: {{ implode(',', sscanf(ltrim(\App\Models\Setting::get('primary_color', '#4F46E5'), '#'), '%02x%02x%02x')) }};
            --secondary-rgb: {{ implode(',', sscanf(ltrim(\App\Models\Setting::get('secondary_color', '#10B981'), '#'), '%02x%02x%02x')) }};
            --gray-light: #F8FAFC;
            --bg-premium: #F8FAFC;
            --text-main: #1E293B;
            --text-dark: #0F172A;
            --border: #E2E8F0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
        }
    </style>

    <!-- Anti-Inspection Shield (Only for guests/users in production) -->
    @if(app()->environment('production'))
        @auth
            @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'owner')
                <script>
                    document.addEventListener('contextmenu', event => event.preventDefault());
                    document.onkeydown = function(e) {
                        if(e.keyCode == 123) return false; // F12
                        if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) return false; 
                        if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) return false; 
                        if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) return false; 
                        if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false; 
                    };
                    setInterval(function() { console.clear(); }, 1000);
                </script>
            @endif
        @else
            <script>
                document.addEventListener('contextmenu', event => event.preventDefault());
                document.onkeydown = function(e) {
                    if(e.keyCode == 123) return false; 
                    if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) return false; 
                    if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) return false; 
                    if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) return false; 
                    if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false; 
                };
                setInterval(function() { console.clear(); }, 1000);
            </script>
        @endauth
    @endif

    
    <!-- Critical Inline CSS (Prevents FOUC when Tailwind is deferred) - Mobile Optimized -->
    <style>
        :root { --primary: {{ \App\Models\Setting::get('primary_color', '#4F46E5') }}; --secondary: {{ \App\Models\Setting::get('secondary_color', '#10B981') }}; }
        @media (max-width: 1023px) {
            .hero-mobile { background: var(--primary); min-height: 300px; display: flex; align-items: center; justify-content: center; }
            img[loading="lazy"] { content-visibility: auto; }
        }
        @media (min-width: 1024px) {
            .hero-mobile { display: none !important; }
        }
        .loading-overlay { position: fixed; inset: 0; background: #fff; z-index: 9999; display: flex; align-items: center; justify-content: center; }
        @font-face { font-family: 'Font Awesome 6 Free'; font-display: swap; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-premium); color: var(--text-main); overflow-x: clip; -webkit-tap-highlight-color: transparent; }
        .font-heading { font-family: 'Plus Jakarta Sans', sans-serif; }
        html, body { display: flex; flex-direction: column; min-height: 100vh; overflow-x: clip; }
        main { flex: 1; }
        footer { margin-top: auto; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">

    <!-- Custom Styles -->


    @stack('styles')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen mobile-app-view {{ request()->routeIs('admin.*') ? 'admin-page' : '' }} {{ request()->routeIs('rooms.index') ? 'rooms-listing-page' : '' }}">
    <div id="page-scroll-progress" class="page-scroll-progress" role="progressbar" aria-label="Page scroll progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
    @yield('layout-top-banner')
    @yield('layout-navigation')
    @yield('layout-loading')

    <!-- Main Content -->
    <main class="pt-16 md:pt-0 {{ Route::is('pages.*', 'cms-pages.show') ? 'cms-content-main' : '' }}">
        @yield('content')
    </main>

    @yield('layout-footer')
    @yield('layout-bottom-navigation')
    @include('partials.auth-modal')

    <!-- Google Ads Conversion Tracking (Fire after successful payment) -->
    @if(session('google_ads_conversion') && app()->environment('production') && \App\Models\Setting::get('google_ads_enabled') == '1')
        @php
            $conv = session('google_ads_conversion');
            $convLabel = \App\Models\Setting::get('google_ads_conversion_label');
            $adsId = \App\Models\Setting::get('google_ads_tag_id');
        @endphp
        @if($adsId && $convLabel && isset($conv['amount']))
        <script>
            if (typeof trackAdsConversion === 'function') {
                trackAdsConversion('{{ $convLabel }}', {{ $conv['amount'] }}, 'INR');
            } else if (typeof gtag !== 'undefined') {
                gtag('event', 'conversion', {
                    'send_to': '{{ $adsId }}/{{ $convLabel }}',
                    'value': {{ $conv['amount'] }},
                    'currency': 'INR'
                });
            }
        </script>
        @endif
        {{ session()->forget('google_ads_conversion') }}
    @endif

    <!-- Scripts Loaded in Footer for Performance -->
    @stack('sweetalert') {{-- Only load SweetAlert2 when needed --}}
    <script defer>
        // Suppress console warnings from third-party libraries (Tailwind, Google Maps)
        const originalConsoleWarn = console.warn;
        console.warn = function (message) {
            if (typeof message === 'string' && (
                message.includes('cdn.tailwindcss.com should not be used in production') ||
                message.includes('google.maps.places.Autocomplete is not available to new customers') ||
                message.includes('google.maps.Marker is deprecated')
            )) {
                return;
            }
            originalConsoleWarn.apply(console, arguments);
        };

        // Global Utility for Distance Calculation (Haversine Formula)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            if (!lat1 || !lon1 || !lat2 || !lon2) return null;
            const R = 6371; // Radius of the earth in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return (R * c).toFixed(1);
        }

        // Global User Location Tracking
        let userCoords = null;
        function detectUserLocation(callback) {
            if (sessionStorage.getItem('user_lat') && sessionStorage.getItem('user_lng')) {
                userCoords = {
                    lat: parseFloat(sessionStorage.getItem('user_lat')),
                    lng: parseFloat(sessionStorage.getItem('user_lng'))
                };
                if (callback) callback(userCoords);
                return;
            }

            const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

            if (navigator.geolocation && isSecure) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        userCoords = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        sessionStorage.setItem('user_lat', userCoords.lat);
                        sessionStorage.setItem('user_lng', userCoords.lng);

                        if (callback) callback(userCoords);
                    },
                    (error) => {
                        getLocationByIP(callback);
                    },
                    { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                );
            } else {
                getLocationByIP(callback);
            }
        }

        function getLocationByIP(callback) {
            fetch('https://ipapi.co/json/')
                .then(res => res.json())
                .then(data => {
                    if (data.latitude && data.longitude) {
                        userCoords = { lat: data.latitude, lng: data.longitude };
                        sessionStorage.setItem('user_lat', userCoords.lat);
                        sessionStorage.setItem('user_lng', userCoords.lng);

                        if (callback) callback(userCoords);
                    }
                })
                .catch(err => {
                    fetch('http://ip-api.com/json/')
                        .then(res => res.json())
                        .then(data => {
                            if (data.lat && data.lon) {
                                userCoords = { lat: data.lat, lng: data.lon };
                                sessionStorage.setItem('user_lat', userCoords.lat);
                                sessionStorage.setItem('user_lng', userCoords.lng);
                                if (callback) callback(userCoords);
                            }
                        });
                });
        }

        // Detect the visitor city on the home page as well as the rooms index.
        @if(request()->routeIs('home') && !request()->filled('city') && !session('no_auto'))
        document.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('apnanest_location_checked')) return;

            const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            if (!navigator.geolocation || !isSecure) return;

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                sessionStorage.setItem('apnanest_location_checked', '1');

                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
                    const data = await response.json();
                    const city = data.address?.city || data.address?.town || data.address?.village || data.address?.suburb || data.address?.state_district;

                    if (city) {
                        await fetch(`{{ route('set-city') }}?city=${encodeURIComponent(city)}&lat=${lat}&lng=${lng}&verified=true`);
                        window.location.href = `{{ route('home') }}?lat=${lat}&lng=${lng}&city=${encodeURIComponent(city)}`;
                    }
                } catch (error) {
                    console.warn('Location detection failed:', error);
                }
            }, () => {
                sessionStorage.setItem('apnanest_location_checked', '1');
            }, { enableHighAccuracy: false, timeout: 6000, maximumAge: 300000 });
        });
        @endif

        // Global Razorpay Loader
        window.loadRazorpaySDK = function() {
            return new Promise((resolve, reject) => {
                if (window.Razorpay) {
                    resolve(window.Razorpay);
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://checkout.razorpay.com/v1/checkout.js';
                script.async = true;
                script.onload = () => resolve(window.Razorpay);
                script.onerror = () => reject(new Error('Razorpay SDK failed to load'));
                document.body.appendChild(script);
            });
        };

        document.addEventListener('DOMContentLoaded', () => detectUserLocation());
    </script>

    <script>
        window.renderFormErrors = function (form, errors) {
            if (!form || !errors || typeof errors !== 'object') return;

            form.querySelectorAll('[data-validation-error]').forEach(node => node.remove());
            form.querySelectorAll('[data-validation-invalid]').forEach(field => {
                field.removeAttribute('data-validation-invalid');
                field.classList.remove('border-red-500', 'ring-1', 'ring-red-200');
                field.removeAttribute('aria-invalid');
            });

            Object.entries(errors).forEach(([errorKey, messages]) => {
                const baseKey = errorKey.split('.')[0];
                const fields = Array.from(form.elements).filter(field => {
                    if (!field.name) return false;
                    return field.name === errorKey ||
                        field.name === baseKey ||
                        field.name === `${baseKey}[]` ||
                        field.name.startsWith(`${baseKey}[`);
                });
                const field = fields[0];
                if (!field) return;

                fields.forEach(item => {
                    item.dataset.validationInvalid = 'true';
                    item.classList.add('border-red-500', 'ring-1', 'ring-red-200');
                    item.setAttribute('aria-invalid', 'true');
                });

                const message = document.createElement('p');
                message.dataset.validationError = 'true';
                message.className = 'mt-1 text-xs font-semibold text-red-600';
                message.setAttribute('role', 'alert');
                message.textContent = Array.isArray(messages) ? messages[0] : messages;

                const anchor = field.closest('label') || field;
                anchor.insertAdjacentElement('afterend', message);
            });
        };

        window.restoreOldFormInput = function (oldInput) {
            if (!oldInput || typeof oldInput !== 'object') return;

            const flatten = (value, prefix = '', result = {}) => {
                if (Array.isArray(value) && value.some(item => item !== null && typeof item === 'object')) {
                    value.forEach((child, index) => flatten(child, `${prefix}[${index}]`, result));
                } else if (value !== null && typeof value === 'object' && !Array.isArray(value)) {
                    Object.entries(value).forEach(([key, child]) => flatten(child, prefix ? `${prefix}[${key}]` : key, result));
                } else {
                    result[prefix] = value;
                }
                return result;
            };

            const flattened = flatten(oldInput);
            Object.entries(flattened).forEach(([name, value]) => {
                const baseName = name.split('[')[0];
                const fields = Array.from(document.querySelectorAll('[name]')).filter(field =>
                    field.name === name || field.name === `${baseName}[]`
                );

                fields.forEach(field => {
                    if (['file', 'password'].includes(field.type) || ['_token', '_method'].includes(field.name)) return;
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        const values = Array.isArray(value) ? value.map(String) : [String(value)];
                        field.checked = values.includes(String(field.value));
                    } else if (field.multiple && Array.isArray(value)) {
                        Array.from(field.options).forEach(option => option.selected = value.map(String).includes(option.value));
                    } else if (!Array.isArray(value)) {
                        field.value = value ?? '';
                    }
                });
            });
        };

        document.addEventListener('DOMContentLoaded', function () {
            if (typeof toastr === 'undefined') return;

            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            @if(Session::has('success'))
                toastr.success(@json(session('success')), 'Success');
            @endif

            @if(Session::has('error'))
                toastr.error(@json(session('error')), 'Error');
            @endif

            @if(Session::has('info'))
                toastr.info(@json(session('info')), 'Info');
            @endif

            @if(Session::has('warning'))
                toastr.warning(@json(session('warning')), 'Warning');
            @endif

            @if(isset($errors) && $errors->any())
                @foreach($errors->all() as $validationError)
                    toastr.error(@json($validationError), 'Please check the form');
                @endforeach

                const validationErrors = @json($errors->toArray());
                const oldInput = @json(session()->getOldInput());
                window.restoreOldFormInput(oldInput);

                document.querySelectorAll('form').forEach(form => {
                    const relevantErrors = {};
                    Object.entries(validationErrors).forEach(([key, messages]) => {
                        const baseKey = key.split('.')[0];
                        if (Array.from(form.elements).some(field => field.name === key || field.name === baseKey || field.name === `${baseKey}[]` || field.name?.startsWith(`${baseKey}[`))) {
                            relevantErrors[key] = messages;
                        }
                    });
                    window.renderFormErrors(form, relevantErrors);
                });
            @endif
        });
    </script>
    <script>
        (() => {
            const progress = document.getElementById('page-scroll-progress');
            if (!progress) return;

            let ticking = false;
            const updateProgress = () => {
                const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
                const value = documentHeight > 0
                    ? Math.min(100, Math.max(0, (window.scrollY / documentHeight) * 100))
                    : 0;

                progress.style.width = value + '%';
                progress.setAttribute('aria-valuenow', Math.round(value));
                ticking = false;
            };

            const requestUpdate = () => {
                if (!ticking) {
                    window.requestAnimationFrame(updateProgress);
                    ticking = true;
                }
            };

            document.addEventListener('scroll', requestUpdate, { passive: true });
            window.addEventListener('resize', requestUpdate, { passive: true });
            requestUpdate();
        })();
    </script>
    @stack('scripts')
    @yield('layout-popup')
    {{-- Google Ads Signup Conversion --}}
    @if(session('signup_success') && app()->environment('production') && \App\Models\Setting::get('google_ads_enabled') == '1')
        @php
            $signupLabel = \App\Models\Setting::get('google_ads_signup_label');
        @endphp
        @if($signupLabel)
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof trackAdsConversion === 'function') {
                        trackAdsConversion('{{ $signupLabel }}', 0, 'INR');
                    }
                });
            </script>
        @endif
        {{ session()->forget('signup_success') }}
    @endif
@auth
@if(Auth::user()->role !== 'admin')
<script>
(function () {
    const btn     = document.getElementById('user-bell-btn');
    const panel   = document.getElementById('user-bell-panel');
    const list    = document.getElementById('user-bell-list');
    const badge   = document.getElementById('user-bell-count');
    const empty   = document.getElementById('user-bell-empty');
    const markAll = document.getElementById('user-bell-mark-all');

    if (!btn) return;

    const urls = {
        fetch:   "{{ route('user.notifications.unreadCount') }}",
        list:    "{{ route('user.notifications.index') }}",
        readAll: "{{ route('user.notifications.readAll') }}",
        read:    (id) => "{{ url('/notifications') }}/" + id + "/read",
        csrf:    "{{ csrf_token() }}"
    };

    // Update badge count
    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }
    }

    // Build notification item HTML
    function buildItem(n) {
        const iconMap = {
            'room_approved':   'fa-check-circle text-emerald-500',
            'room_rejected':   'fa-times-circle text-red-500',
            'contact_unlock':  'fa-key text-indigo-500',
            'payment_success': 'fa-credit-card text-green-500',
            'complaint_update':'fa-headset text-blue-500',
        };
        const iconClass = iconMap[n.type] || 'fa-bell text-slate-400';
        const link = n.link || '#';
        const imageHtml = n.image ? `<img src="${n.image}" class="mt-2 rounded-lg max-h-24 w-full object-cover border border-slate-100 shadow-sm" alt="Offer Image">` : '';
        return `<div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 cursor-pointer notification-item" data-id="${n.id}" data-link="${link}">
            <div class="mt-0.5 h-8 w-8 shrink-0 rounded-full bg-slate-100 flex items-center justify-center">
                <i class="fas ${iconClass} text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-slate-800 truncate">${n.title}</p>
                <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">${n.message || ''}</p>
                ${imageHtml}
                <p class="text-[10px] text-slate-400 mt-1">${n.created_at_human || ''}</p>
            </div>
        </div>`;
    }

    // Load notifications into dropdown
    async function loadNotifications() {
        try {
            const res  = await fetch(urls.list, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            // Remove all existing items (keep empty state div)
            Array.from(list.querySelectorAll('.notification-item')).forEach(el => el.remove());

            if (data.notifications && data.notifications.length > 0) {
                empty.classList.add('hidden');
                data.notifications.forEach(n => {
                    list.insertAdjacentHTML('beforeend', buildItem(n));
                });
                // Attach click handlers
                list.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', () => markRead(item.dataset.id, item.dataset.link));
                });
            } else {
                empty.classList.remove('hidden');
            }
            updateBadge(data.unread_count || 0);
        } catch (e) {}
    }

    // Mark single as read
    async function markRead(id, link) {
        try {
            await fetch(urls.read(id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': urls.csrf, 'X-Requested-With': 'XMLHttpRequest' }
            });
            // Remove from list immediately
            const el = list.querySelector(`[data-id="${id}"]`);
            if (el) el.remove();

            const remaining = list.querySelectorAll('.notification-item').length;
            updateBadge(remaining);
            if (remaining === 0) empty.classList.remove('hidden');
        } catch (e) {}
        if (link && link !== '#') window.location.href = link;
    }

    // Toggle dropdown open/close
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = !panel.classList.contains('hidden');
        panel.classList.toggle('hidden');
        if (!isOpen) loadNotifications();
    });

    // Mark all read
    if (markAll) {
        markAll.addEventListener('click', async function () {
            try {
                await fetch(urls.readAll, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': urls.csrf, 'X-Requested-With': 'XMLHttpRequest' }
                });
                Array.from(list.querySelectorAll('.notification-item')).forEach(el => el.remove());
                empty.classList.remove('hidden');
                updateBadge(0);
            } catch (e) {}
        });
    }

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!document.getElementById('user-bell-wrapper')?.contains(e.target)) {
            panel.classList.add('hidden');
        }
    });

    // Fetch unread count on page load
    fetch(urls.fetch, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => updateBadge(d.unread_count || 0))
        .catch(() => {});
})();
</script>
@if(\App\Models\Setting::get('firebase_push_enabled', '1') === '1' && \App\Models\Setting::get('firebase_web_api_key') && \App\Models\Setting::get('firebase_project_id'))
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js"></script>
<script>
(function() {
    if (!('serviceWorker' in navigator) || !('Notification' in window)) return;

    const firebaseConfig = {
        apiKey:            "{{ \App\Models\Setting::get('firebase_web_api_key') }}",
        authDomain:        "{{ \App\Models\Setting::get('firebase_project_id') }}.firebaseapp.com",
        projectId:         "{{ \App\Models\Setting::get('firebase_project_id') }}",
        storageBucket:     "{{ \App\Models\Setting::get('firebase_project_id') }}.appspot.com",
        messagingSenderId: "{{ \App\Models\Setting::get('firebase_messaging_sender_id') }}",
        appId:             "{{ \App\Models\Setting::get('firebase_app_id') }}"
    };

    try {
        if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();
        const vapidKey  = "{{ \App\Models\Setting::get('firebase_vapid_key') }}";

        navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function(registration) {
            registration.active && registration.active.postMessage({ type: 'FIREBASE_CONFIG', config: firebaseConfig });

            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    messaging.getToken({ serviceWorkerRegistration: registration, vapidKey: vapidKey || undefined })
                        .then(function(currentToken) {
                            if (currentToken) {
                                fetch("{{ route('web.push.store') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: JSON.stringify({ token: currentToken })
                                });
                            }
                        }).catch(function(err) {});
                }
            });
        });

        // Foreground notification handler
        messaging.onMessage(function(payload) {
            if (payload.notification) {
                new Notification(payload.notification.title || 'ApnaNest', {
                    body: payload.notification.body || '',
                    icon: '/assets/images/icon-192.png'
                });
            }
        });
    } catch(e) {}
    })();
    </script>
    @endif
    @endif
    @endauth

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script defer src="{{ asset('assets/js/toastr.min.js') }}"></script>
    
    <script>
    // Lazy load images with blur effect
    document.addEventListener('DOMContentLoaded', function() {
        const lazyImages = document.querySelectorAll('img[loading="lazy"]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.complete) {
                            img.classList.add('loaded');
                        } else {
                            img.addEventListener('load', function() {
                                img.classList.add('loaded');
                            });
                        }
                        imageObserver.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.01
            });
            
            lazyImages.forEach(function(img) {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            lazyImages.forEach(function(img) {
                if (img.complete) {
                    img.classList.add('loaded');
                } else {
                    img.addEventListener('load', function() {
                        img.classList.add('loaded');
                    });
                }
            });
        }
    });
    </script>
</body>
</html>
