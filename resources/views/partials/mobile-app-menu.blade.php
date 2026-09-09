<!-- Mobile App Menu Drawer -->
@php
    if (!isset($cmsPageLive)) {
        try {
            $publishedCmsSlugs = \App\Models\CmsPage::published()->pluck('slug')->flip();
        } catch (\Throwable $exception) {
            $publishedCmsSlugs = collect();
        }
        $cmsPageLive = fn (string $slug): bool => $publishedCmsSlugs->has($slug);
    }
    @endphp
    <style>
        #mobile-app-menu .text-indigo-500 { color: var(--primary) !important; }
        #mobile-app-menu .text-indigo-600 { color: var(--primary) !important; }
        #mobile-app-menu .text-indigo-900 { color: var(--primary) !important; }
        #mobile-app-menu .bg-indigo-50 { background-color: rgba(var(--primary-rgb), 0.06) !important; }
        #mobile-app-menu .hover\:bg-indigo-50:hover { background-color: rgba(var(--primary-rgb), 0.08) !important; }
        #mobile-app-menu .bg-indigo-100 { background-color: rgba(var(--primary-rgb), 0.1) !important; }
    </style>
    <div id="mobile-app-menu" class="fixed top-0 left-0 bottom-0 w-[85%] max-w-[350px] bg-white z-[2000] shadow-2xl transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden h-full flex flex-col">
    <!-- Drawer Header with App Branding -->
    <div class="p-6 text-white relative flex flex-col items-center text-center" style="background-color: var(--primary);">
        <button id="close-mobile-menu" 
                class="absolute top-4 right-4 w-8 h-8 bg-white/20 rounded-full flex items-center justify-center hover:bg-white/30 transition-colors"
                aria-label="Close navigation menu">
            <i class="fas fa-times text-white" aria-hidden="true"></i>
        </button>
        
        @php $menuLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo'); @endphp
        @if($menuLogo)
            <a href="{{ route('home') }}" class="mb-3">
                <img src="{{ \App\Models\Setting::mediaUrl($menuLogo) }}" alt="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}" class="h-14 w-auto rounded-xl shadow-lg">
            </a>
        @else
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-3 shadow-lg">
                <i class="fas fa-home text-3xl text-white"></i>
            </div>
        @endif
        <h3 class="font-bold text-lg mb-0.5">{{ \App\Models\Setting::get('website_name', 'RoomRental') }}</h3>
        <p class="text-xs text-white/70 uppercase font-black tracking-widest">Find Your Perfect Stay</p>
    </div>
    
    <!-- Drawer Body with Navigation -->
    <div class="flex-1 overflow-y-auto p-4 space-y-6">
        <!-- User Section -->
        <div class="pt-4 border-t border-gray-100">
            @auth
                <div class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-indigo-50">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" class="w-12 h-12 rounded-xl object-cover border-2 border-white shadow-lg" alt="User avatar">
                    @else
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-user text-xl text-indigo-600"></i>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h4 class="font-bold text-indigo-900">{{ Auth::user()->name }}</h4>
                        <p class="text-xs text-indigo-600 uppercase font-black tracking-widest">{{ Auth::user()->role }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-3 mb-4 p-3 rounded-xl bg-indigo-50">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-xl text-indigo-600"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-indigo-900">Welcome Guest</h4>
                        <p class="text-xs text-indigo-600">Find your best room today</p>
                    </div>
                </div>
            @endauth
        </div>
        
        <!-- Main Navigation -->
        <div>
            <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest mb-3 ml-2">Navigation</p>
            <div class="space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('home') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-home w-5 text-indigo-500"></i> Home
                </a>
                <a href="{{ route('rooms.index', ['purpose' => 'sell']) }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-purple-50 transition font-bold {{ request('purpose') === 'sell' ? 'bg-purple-50 text-purple-700' : 'text-purple-700' }}">
                    <i class="fas fa-tag w-5 text-purple-600"></i> Properties For Sale (Buy)
                </a>
                <a href="{{ route('rooms.index', ['purpose' => 'rent']) }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request('purpose') === 'rent' ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-key w-5 text-indigo-500"></i> Properties For Rent
                </a>
                <a href="{{ route('rooms.index') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('rooms.index') && !request()->filled('purpose') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-search w-5 text-indigo-500"></i> All Properties
                </a>
                <a href="{{ route('rooms.map') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('rooms.map') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-map-marked-alt w-5 text-indigo-500"></i> Map View
                </a>
                <a href="{{ route('agencies.index') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('agencies.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-building-user w-5 text-indigo-500"></i> Verified Agencies
                </a>
                <a href="{{ route('blogs.index') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('blogs.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-newspaper w-5 text-indigo-500"></i> Blog
                </a>
                @if($cmsPageLive('how-it-works'))
                <a href="{{ route('pages.how-it-works') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('pages.how-it-works') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-lightbulb w-5 text-indigo-500"></i> How It Works
                </a>
                @endif
                <a href="{{ route('register', ['role' => 'owner']) }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('register') && request('role') === 'owner' ? 'bg-indigo-50 text-indigo-600' : '' }}">
                    <i class="fas fa-user-plus w-5 text-indigo-500"></i> For Owners
                </a>
                @auth
                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'owner' || Auth::user()->role === 'broker')
                        <a href="{{ match(Auth::user()->role) { 'owner' => route('owner.dashboard'), 'broker' => route('agent.dashboard'), 'admin' => route('admin.dashboard'), default => route('dashboard') } }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('dashboard', 'agent.dashboard', 'owner.dashboard', 'admin.dashboard') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-tachometer-alt w-5 text-indigo-500"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('profile.edit') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                            <i class="fas fa-user-circle w-5 text-indigo-500"></i> My Profile
                        </a>
                        <a href="{{ route('unlocks.index') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-emerald-50 transition text-gray-700 font-bold {{ request()->routeIs('unlocks.index') ? 'bg-emerald-50 text-emerald-700' : '' }}">
                            <i class="fas fa-address-book w-5 text-emerald-500"></i> My Unlocked Contacts
                        </a>
                        @if(\App\Models\Setting::isEnabled('wallet_enabled', true))
                            <a href="{{ route('wallet') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('wallet') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                <i class="fas fa-wallet w-5 text-indigo-500"></i> My Wallet
                            </a>
                        @endif
                    @endif
                    <a href="{{ route('wishlist.index') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold {{ request()->routeIs('wishlist.index') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                        <i class="fas fa-heart w-5 text-red-500"></i> My Wishlist
                    </a>
                @endauth
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div>
            <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest mb-3 ml-2">Quick Actions</p>
            <div class="space-y-1">
                @auth
                    @if(Auth::user()->role === 'owner')
                        <a href="{{ route('owner.rooms.create') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-green-50 transition text-gray-700 font-bold">
                            <i class="fas fa-plus-circle w-5 text-green-500"></i> Post Property (Rent / Sell)
                        </a>
                    @endif
                    @if(\App\Models\Setting::isEnabled('referral_enabled', true))
                        <a href="{{ route('referral.index') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-emerald-50 transition text-gray-700 font-bold">
                            <i class="fas fa-gift w-5 text-emerald-500"></i> Refer & Earn
                        </a>
                    @endif
                    <a href="{{ route('complaints.index') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-red-50 transition text-gray-700 font-bold">
                        <i class="fas fa-shield-halved w-5 text-red-500"></i> My Complaints
                    </a>
                @else
                    <a href="{{ route('register') }}?role=owner" class="flex items-center gap-4 p-3 rounded-xl hover:bg-green-50 transition text-gray-700 font-bold">
                        <i class="fas fa-plus-circle w-5 text-green-500"></i> Post Property
                    </a>
                @endauth
            </div>
        </div>
        
        <!-- App Info & Support -->
        <div>
            <p class="text-[10px] text-gray-500 font-black uppercase tracking-widest mb-3 ml-2">App Info & Support</p>
            <div class="space-y-1">
                @if($cmsPageLive('contact-us'))<a href="{{ route('pages.contact') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold">
                    <i class="fas fa-headset w-5 text-blue-500"></i> Support Center
                </a>@endif
                @if($cmsPageLive('owner-guidelines'))<a href="{{ url('/owner-guidelines') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold">
                    <i class="fas fa-house-user w-5 text-indigo-500"></i> Owner Guidelines
                </a>@endif
                @if($cmsPageLive('user-guidelines'))<a href="{{ url('/user-guidelines') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold">
                    <i class="fas fa-person-circle-check w-5 text-indigo-500"></i> User Guidelines
                </a>@endif
                @if($cmsPageLive('privacy-policy'))<a href="{{ url('/page/privacy-policy') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold">
                    <i class="fas fa-shield-alt w-5 text-teal-500"></i> Privacy Policy
                </a>@endif
                @if($cmsPageLive('terms-and-conditions'))<a href="{{ url('/page/terms-and-conditions') }}" class="flex items-center gap-4 p-3 rounded-xl hover:bg-indigo-50 transition text-gray-700 font-bold">
                    <i class="fas fa-file-contract w-5 text-amber-500"></i> Terms of Service
                </a>@endif
            </div>
        </div>
    </div>
    
    <!-- Auth Actions -->
    <div class="p-4 border-t border-gray-100">
        @auth
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-4 p-3 rounded-xl text-red-600 font-bold bg-red-50 hover:bg-red-100 transition">
                    <i class="fas fa-sign-out-alt w-5"></i> Logout
                </button>
            </form>
        @else
            <div class="grid grid-cols-2 gap-3 p-2">
                <button type="button" data-auth-trigger="login" class="text-white font-bold py-3 rounded-xl text-center text-sm shadow-lg app-btn border-0" style="background-color: var(--primary);">Login</button>
                <a href="{{ route('register') }}" class="bg-gray-100 text-gray-700 font-bold py-3 rounded-xl text-center text-sm app-btn">Sign Up</a>
            </div>
        @endauth
    </div>
</div>

<!-- Mobile Menu Overlay -->
<div id="mobile-menu-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[1999] hidden transition-opacity duration-300"></div>

<script>
    // Mobile App Menu Toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('mobile-menu-toggle-app');
        const menuDrawer = document.getElementById('mobile-app-menu');
        const menuOverlay = document.getElementById('mobile-menu-overlay');
        const closeMenuBtn = document.getElementById('close-mobile-menu');
        
        function openMobileMenu() {
            if (menuDrawer && menuOverlay) {
                menuDrawer.classList.remove('-translate-x-full');
                menuOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeMobileMenu() {
            if (menuDrawer && menuOverlay) {
                menuDrawer.classList.add('-translate-x-full');
                menuOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        
        if (menuToggle) {
            menuToggle.addEventListener('click', openMobileMenu);
        }
        
        if (menuOverlay) {
            menuOverlay.addEventListener('click', closeMobileMenu);
        }
        
        if (closeMenuBtn) {
            closeMenuBtn.addEventListener('click', closeMobileMenu);
        }
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });
    });
</script>
