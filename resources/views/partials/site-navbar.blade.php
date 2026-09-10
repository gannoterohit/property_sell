@php
    try {
        $publishedCmsSlugs = \Illuminate\Support\Facades\Cache::remember('cms.published_slugs', 3600, function () {
            return \App\Models\CmsPage::published()->pluck('slug')->flip();
        });
    } catch (\Throwable $exception) {
        $publishedCmsSlugs = collect();
    }
    $cmsPageLive = fn (string $slug): bool => $publishedCmsSlugs->has($slug);
@endphp
<link rel="stylesheet" href="{{ asset('css/navbar.css') }}?v={{ file_exists(public_path('css/navbar.css')) ? filemtime(public_path('css/navbar.css')) : '2.1' }}">
    <!-- Mobile App Header - Enhanced App Style -->
    <div class="mobile-app-header lg:hidden">
        <div class="header-left">
                    @php $mobileLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo'); @endphp
                    @if($mobileLogo)
                        <a href="{{ route('home') }}" class="mobile-brand-logo-link" aria-label="{{ \App\Models\Setting::get('website_name', 'RoomRental') }} home">
                            <img src="{{ \App\Models\Setting::mediaUrl($mobileLogo) }}" alt="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}" class="mobile-brand-logo">
                        </a>
                    @else
                        <div class="app-icon">
                            <i class="fas fa-home text-white text-xl"></i>
                        </div>
                    @endif
        </div>
        <div class="header-right">
            <button id="mobile-menu-toggle-app"
                    class="menu-toggle"
                    aria-label="Open navigation menu">
                <i class="fas fa-bars text-xl" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile App Menu - Include the new app-style menu -->
    @include('partials.mobile-app-menu')
    
    
    <!-- Compact Desktop Navigation -->
    <!-- Desktop Navigation (Redesigned) -->
    <nav class="hidden lg:block bg-white border-b border-slate-100 shadow-sm sticky top-0 z-50">
        <div class="desktop-navbar-inner">
            <div class="desktop-navbar-row">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="desktop-navbar-logo flex items-center gap-2 overflow-visible">
                    @php
                        $navbarLogo = \App\Models\Setting::get('navbar_logo');
                    @endphp
                     @if($navbarLogo)
                         <img src="{{ \App\Models\Setting::mediaUrl($navbarLogo) }}"
                              alt="ApnaNest Logo"
                              width="200"
                              height="50"
                              class="navbar-brand-logo">
                     @else
                        <div class="flex items-center gap-2">
                            <div class="theme-brand-mark w-10 h-10 rounded-xl flex items-center justify-center shadow-md">
                                <i class="fas fa-home text-lg"></i>
                            </div>
                            <span class="text-xl font-black text-slate-900 tracking-tight">Apna<span class="theme-brand-text">Nest</span></span>
                        </div>
                    @endif
                </a>
                
                <!-- Center Navigation Tabs -->
                <div class="desktop-navbar-center">
                    <nav class="desktop-navbar-menu" aria-label="Main navigation">
                        <a href="{{ route('home') }}" class="nav-tab {{ request()->routeIs('home') ? 'nav-tab-active' : '' }}">Home</a>

                        <a href="{{ route('rooms.index', ['purpose' => 'sell']) }}" class="nav-tab {{ request('purpose') === 'sell' ? 'nav-tab-active' : '' }}">
                            Buy <span class="nav-tab-badge">Hot</span>
                        </a>

                        <a href="{{ route('rooms.index', ['purpose' => 'rent']) }}" class="nav-tab {{ request('purpose') === 'rent' ? 'nav-tab-active' : '' }}">
                            Rent
                        </a>

                        <div class="relative" id="browse-dropdown-wrapper">
                            <button id="browse-dropdown-btn" type="button" class="nav-tab {{ request()->routeIs('rooms.*') && !request()->filled('purpose') ? 'nav-tab-active' : '' }}">
                                Browse <i class="fas fa-chevron-down nav-dropdown-icon"></i>
                            </button>
                            <div id="browse-dropdown-panel" class="hidden absolute left-1/2 -translate-x-1/2 mt-2 w-56 rounded-2xl border border-slate-100 bg-white shadow-xl z-50 overflow-hidden py-1">
                                <a href="{{ route('rooms.index', ['purpose' => 'sell']) }}" class="flex items-center justify-between px-4 py-2.5 text-xs font-bold text-purple-700 hover:bg-purple-50 transition">
                                    <span class="flex items-center gap-2"><i class="fas fa-tag text-purple-600 w-4"></i> Properties For Sale</span>
                                    <span class="bg-purple-100 text-purple-700 text-[9px] px-1.5 py-0.5 rounded font-black">BUY</span>
                                </a>
                                <a href="{{ route('rooms.index', ['purpose' => 'rent']) }}" class="flex items-center justify-between px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition border-t border-slate-50">
                                    <span class="flex items-center gap-2"><i class="fas fa-key text-indigo-500 w-4"></i> Properties For Rent</span>
                                    <span class="bg-indigo-50 text-indigo-700 text-[9px] px-1.5 py-0.5 rounded font-bold">RENT</span>
                                </a>
                                <a href="{{ route('rooms.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition border-t border-slate-50">
                                    <i class="fas fa-th-list text-slate-400 w-4"></i> All Properties
                                </a>
                                <a href="{{ route('rooms.map') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition border-t border-slate-50">
                                    <i class="fas fa-map-marked-alt text-indigo-500 w-4"></i> Map View
                                </a>
                                <a href="{{ route('agencies.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition border-t border-slate-50">
                                    <i class="fas fa-building-user text-indigo-500 w-4"></i> Verified Agencies
                                </a>
                            </div>
                        </div>

                        <a href="{{ Auth::check() ? (Auth::user()->role === 'owner' ? route('owner.dashboard') : route('dashboard')) : route('register', ['role' => 'owner']) }}" class="nav-tab {{ request()->routeIs('owner.*') || (request()->routeIs('register') && request('role') === 'owner') ? 'nav-tab-active' : '' }}">For Owners</a>

                        <a href="{{ route('blogs.index') }}" class="nav-tab {{ request()->routeIs('blogs.*') ? 'nav-tab-active' : '' }}">Blog</a>

                        <a href="{{ route('pages.contact') }}" class="nav-tab {{ request()->routeIs('pages.contact') ? 'nav-tab-active' : '' }}">Contact</a>
                    </nav>
                </div>
                
                <!-- Right Side Actions -->
                <div class="desktop-navbar-actions">
                    <!-- Wishlist Icon (Heart) -->
                    <a href="{{ route('wishlist.index') }}" class="desktop-wishlist-btn" title="My Wishlist">
                        <i class="far fa-heart text-base"></i>
                    </a>
                    
                    @auth
                        @if(Auth::user()->role !== 'admin')
                        {{-- Bell Icon Notification Dropdown --}}
                        <div class="relative" id="user-bell-wrapper">
                            <button id="user-bell-btn"
                                    class="relative h-10 w-10 shrink-0 inline-flex items-center justify-center text-slate-600 hover:text-indigo-600 transition-colors"
                                    title="Notifications"
                                    aria-label="Notifications">
                                <i class="far fa-bell text-lg"></i>
                                <span id="user-bell-count"
                                      class="absolute -top-0.5 -right-0.5 hidden h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-black text-white shadow"></span>
                            </button>

                            {{-- Dropdown Panel --}}
                            <div id="user-bell-panel"
                                 class="hidden absolute right-0 mt-2 w-80 rounded-2xl border border-slate-100 bg-white shadow-2xl z-50 overflow-hidden"
                                 style="top:100%;">
                                <div class="flex items-center justify-between border-b px-4 py-3">
                                    <p class="text-xs font-extrabold text-slate-800">Notifications</p>
                                    <button id="user-bell-mark-all"
                                            class="text-[10px] font-bold text-indigo-600 hover:underline">Mark all read</button>
                                </div>
                                <div id="user-bell-list" class="divide-y max-h-72 overflow-y-auto">
                                    <div id="user-bell-empty" class="py-10 text-center hidden">
                                        <i class="far fa-bell-slash text-2xl text-slate-300"></i>
                                        <p class="mt-2 text-xs font-bold text-slate-400">No new notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endauth

                    @auth
                        <!-- Account Dropdown - Fixed position via JS to avoid clipping -->
                        @php
                            $isRenter = Auth::user()->role === 'user';
                            $accountHome = match(Auth::user()->role) {
                                'owner' => route('owner.dashboard'),
                                'broker' => route('agent.dashboard'),
                                'admin' => route('admin.dashboard'),
                                default => route('home'),
                            };
                        @endphp

                        <div x-data="{
                                open: false,
                                top: 0,
                                right: 0,
                                toggle() {
                                    if (!this.open) {
                                        const r = this.$refs.trigger.getBoundingClientRect();
                                        this.top = r.bottom + 8;
                                        this.right = window.innerWidth - r.right;
                                    }
                                    this.open = !this.open;
                                }
                             }"
                             @click.outside="open = false">
                            <!-- Trigger button -->
                            <button x-ref="trigger"
                                    @click="toggle()"
                                    class="theme-nav-link h-10 flex items-center gap-2 text-slate-700 transition-colors duration-200 bg-slate-50 hover:bg-slate-100 px-3 rounded-xl border border-slate-200/60 whitespace-nowrap">
                                  @if(Auth::user()->avatar)<img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-full object-cover border border-slate-200">@else<div class="w-7 h-7 rounded-full flex items-center justify-center bg-slate-100 text-slate-600 border border-slate-200"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">{{ Auth::user()->name }}</span></div>@endif
                                <span class="hidden xl:inline text-xs font-semibold">{{ Str::limit(Auth::user()->name, 12) }}</span>
                                <i class="fas fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <!-- Dropdown menu — position: fixed, calculated from button rect -->
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 :style="'position:fixed; top:' + top + 'px; right:' + right + 'px; z-index:9999;'"
                                 class="w-56 rounded-xl bg-white border border-slate-100 shadow-xl py-2"
                                 style="display:none;">
                                @if($isRenter)
                                    <a href="{{ route('profile.edit') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-user-circle w-4 text-sm"></i> My Profile
                                    </a>
                                    <a href="{{ route('unlocks.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-address-book text-emerald-500 w-4 text-sm"></i> My Unlocked Contacts
                                    </a>
                                    @if(\App\Models\Setting::isEnabled('wallet_enabled', true))
                                        <a href="{{ route('wallet') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                            <i class="theme-account-icon fas fa-wallet w-4 text-sm"></i> My Wallet
                                        </a>
                                    @endif
                                    <a href="{{ route('plans') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-crown text-amber-400 w-4 text-sm"></i> View Plans
                                    </a>
                                    @if(\App\Models\Setting::isEnabled('referral_enabled', true))
                                        <a href="{{ route('referral.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                            <i class="fas fa-gift text-emerald-400 w-4 text-sm"></i> Refer & Earn
                                        </a>
                                    @endif
                                    <a href="{{ route('complaints.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-headset text-blue-400 w-4 text-sm"></i> Support Tickets
                                    </a>
                                @else
                                    <a href="{{ $accountHome }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-tachometer-alt w-4 text-sm"></i> Dashboard
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-user-circle w-4 text-sm"></i> Profile
                                    </a>
                                @endif

                                <div class="h-px bg-slate-100 my-1 mx-3"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt text-red-400 w-4 text-sm"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Post Property Button for Logged In -->
                        @if(Auth::user()->role === 'owner')
                            <a href="{{ route('owner.rooms.create') }}"
                               class="theme-primary-button h-9 px-3.5 rounded-xl text-xs font-bold transition-all duration-200 shadow-xs flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fas fa-plus text-xs"></i> Post Property
                            </a>
                        @endif
                    @else
                        <!-- Guest Actions -->
                        <button type="button" data-auth-trigger="login" class="nav-btn-login">
                            Login
                        </button>
                        <a href="{{ route('register') }}" class="nav-btn-signup">
                            Sign Up
                        </a>
                        <a href="{{ route('register') }}?role=owner" class="nav-btn-post">
                            Post Property
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @once
    @push('scripts')
    <script>
    (function () {
        var btn = document.getElementById('browse-dropdown-btn');
        var panel = document.getElementById('browse-dropdown-panel');
        var wrapper = document.getElementById('browse-dropdown-wrapper');
        if (!btn || !panel || !wrapper) return;

        function open() { panel.classList.remove('hidden'); }
        function close() { panel.classList.add('hidden'); }
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.contains('hidden') ? open() : close();
        });
        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') close();
        });
    })();
    </script>
    @endpush
    @endonce
