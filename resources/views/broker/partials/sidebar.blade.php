@php
    $broker = Auth::user();
    $brokerLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo');

    $navGroups = [
        'main' => ['label' => 'Overview', 'icon' => 'fa-chart-pie', 'items' => [
            ['route' => 'agent.dashboard', 'match' => 'agent.dashboard', 'icon' => 'fa-chart-pie', 'label' => 'Dashboard'],
        ]],
        'listings' => ['label' => 'My Listings', 'icon' => 'fa-building', 'items' => [
            ['route' => 'agent.properties', 'match' => 'agent.properties', 'icon' => 'fa-list', 'label' => 'All Properties'],
            ['route' => 'agent.rooms.create', 'match' => 'agent.rooms.create', 'icon' => 'fa-plus-circle', 'label' => 'Add New Property'],
            ['route' => 'agent.rooms.drafts', 'match' => 'agent.rooms.drafts*', 'icon' => 'fa-folder-open', 'label' => 'My Drafts'],
        ]],
        'leads' => ['label' => 'Leads', 'icon' => 'fa-address-card', 'items' => [
            ['route' => 'agent.enquiries', 'match' => 'agent.enquiries', 'icon' => 'fa-inbox', 'label' => 'All Enquiries'],
        ]],
        'plans' => ['label' => 'Plans', 'icon' => 'fa-tags', 'items' => [
            ['route' => 'agent.plans', 'match' => 'agent.plans', 'icon' => 'fa-layer-group', 'label' => 'Listing Plans'],
        ]],
        'financial' => ['label' => 'Financial', 'icon' => 'fa-wallet', 'items' => [
            ['route' => 'agent.payments', 'match' => 'agent.payments', 'icon' => 'fa-credit-card', 'label' => 'Payments'],
            ['route' => 'agent.transactions', 'match' => 'agent.transactions', 'icon' => 'fa-receipt', 'label' => 'Transactions'],
            ['route' => 'wallet', 'match' => 'wallet', 'icon' => 'fa-coins', 'label' => 'Wallet'],
        ]],
        'more' => ['label' => 'More', 'icon' => 'fa-ellipsis', 'items' => [
            ['route' => 'complaints.index', 'match' => 'complaints.*', 'icon' => 'fa-shield-halved', 'label' => 'Complaints'],
            ['route' => 'referral.index', 'match' => 'referral.*', 'icon' => 'fa-gift', 'label' => 'Refer & Earn'],
            ['route' => 'wishlist.index', 'match' => 'wishlist.*', 'icon' => 'fa-heart', 'label' => 'Wishlist'],
            ['route' => 'agent.profile', 'match' => 'agent.profile', 'icon' => 'fa-user-gear', 'label' => 'Profile Settings'],
        ]],
    ];
@endphp

@once
<link rel="stylesheet" href="{{ asset('css/owner-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/owner-theme.css') }}">
@endonce

<aside class="owner-sidebar hidden lg:flex bg-white border-r border-slate-200 flex-col sticky top-0 h-screen" id="agentSidebar">

    {{-- Logo / Brand --}}
    <div class="px-4 py-3.5 border-b border-slate-100">
        <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-3">
            @if($brokerLogo)
                <img src="{{ asset('storage/' . $brokerLogo) }}" alt="Agent panel"
                     class="w-8 h-8 rounded-xl object-contain border border-slate-200 bg-white p-1">
            @else
                <span class="w-8 h-8 rounded-xl owner-theme-bg flex items-center justify-center text-white shadow-sm">
                    <i class="fas fa-user-tie text-sm"></i>
                </span>
            @endif
            <span class="min-w-0">
                <strong class="block text-[13px] text-slate-900 truncate leading-tight">Agent Workspace</strong>
                <small class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Manage listings</small>
            </span>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 min-h-0 overflow-y-auto pt-3 px-2.5 pb-3 space-y-0.5 overscroll-contain" aria-label="Agent navigation">
        @foreach($navGroups as $groupKey => $group)
            @php
                $groupItems  = $group['items'];
                $groupActive = collect($groupItems)->contains(fn ($item) => request()->routeIs($item['match']));
                $groupOpen   = $groupActive;
            @endphp
            <section class="owner-nav-group" data-group="{{ $groupKey }}">
                <button type="button"
                    class="owner-nav-group-toggle group flex w-full items-center justify-between rounded-xl border px-3 py-2.5 text-[12px] font-bold transition
                        {{ $groupActive ? 'owner-sidebar-group-active' : 'border-transparent bg-white text-slate-700 hover:bg-slate-50' }}"
                    aria-expanded="{{ $groupOpen ? 'true' : 'false' }}">
                    <span class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg
                            {{ $groupActive ? 'owner-sidebar-icon-active shadow-sm' : 'owner-sidebar-icon-idle ring-1 ring-slate-200' }}">
                            <i class="fas {{ $group['icon'] }} text-[11px]"></i>
                        </span>
                        <span class="whitespace-nowrap">{{ $group['label'] }}</span>
                    </span>
                    <i class="owner-nav-chevron fas fa-chevron-down text-[9px] text-slate-400 transition-transform {{ $groupOpen ? 'rotate-180' : '' }}"></i>
                </button>

                <div class="owner-sidebar-submenu owner-nav-group-menu {{ $groupOpen ? '' : 'hidden' }} ml-5 mt-1 mb-1 space-y-0.5 border-l-2 pl-3">
                    @foreach($groupItems as $item)
                        @php 
                            $itemActive = request()->routeIs($item['match']); 
                            $isLocked = ($item['route'] === 'agent.rooms.create' && !$broker->is_broker_active);
                        @endphp
                        <a href="{{ route($item['route']) }}"
                            class="relative flex items-center gap-2.5 rounded-lg px-3 py-2 text-[12px] transition
                                {{ $itemActive ? 'owner-sidebar-subitem-active font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-slate-900' }} {{ $isLocked ? 'opacity-75' : '' }}">
                            <i class="fas {{ $item['icon'] }} w-4 text-center text-[11px]
                                {{ $itemActive ? 'owner-sidebar-active-icon' : 'text-slate-400' }}"></i>
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if($isLocked)
                                <i class="fas fa-lock text-[10px] text-amber-500 ml-auto" title="Pending Admin Approval"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </nav>

    {{-- Bottom: Agent info + Logout --}}
    <div class="p-3 border-t border-slate-100">
        <div class="flex items-center gap-2.5 px-2 py-2 mb-2 rounded-xl hover:bg-slate-50 transition">
            @if($broker?->avatar)<img src="{{ asset('storage/'.$broker->avatar) }}"
                 width="200" height="200"
                 alt="Agent profile"
                 class="w-8 h-8 rounded-full border border-slate-200 object-cover flex-shrink-0">
            @else <div class="w-8 h-8 rounded-full border border-slate-200 bg-slate-100 text-slate-600 flex items-center justify-center"><i class="fas fa-user-tie" aria-hidden="true"></i><span class="sr-only">Agent profile</span></div>@endif
            <span class="min-w-0">
                <strong class="block text-xs text-slate-800 truncate leading-tight">{{ $broker?->name }}</strong>
                <small class="block text-[10px] text-slate-400 font-medium">Agent</small>
            </span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-red-50 border border-red-100 text-red-600 hover:bg-red-600 hover:border-red-600 hover:text-white text-[11px] font-bold transition">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

<script>
(() => {
    const sections = document.querySelectorAll('.owner-nav-group');
    sections.forEach(section => {
        const toggle  = section.querySelector('.owner-nav-group-toggle');
        const menu    = section.querySelector('.owner-nav-group-menu');
        const chevron = section.querySelector('.owner-nav-chevron');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', () => {
            const willOpen = toggle.getAttribute('aria-expanded') !== 'true';
            sections.forEach(other => {
                if (other === section) return;
                other.querySelector('.owner-nav-group-menu')?.classList.add('hidden');
                other.querySelector('.owner-nav-chevron')?.classList.remove('rotate-180');
                other.querySelector('.owner-nav-group-toggle')?.setAttribute('aria-expanded', 'false');
                other.querySelector('.owner-nav-group-toggle')?.classList.remove('owner-sidebar-group-active');
                other.querySelector('.owner-nav-group-toggle')?.classList.add('border-transparent', 'bg-white', 'text-slate-700', 'hover:bg-slate-50');
                other.querySelector('.owner-nav-group-toggle span span')?.classList.remove('owner-sidebar-icon-active', 'shadow-sm');
                other.querySelector('.owner-nav-group-toggle span span')?.classList.add('owner-sidebar-icon-idle', 'ring-1', 'ring-slate-200');
            });
            menu.classList.toggle('hidden', !willOpen);
            chevron.classList.toggle('rotate-180', willOpen);
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            if (willOpen) {
                toggle.classList.add('owner-sidebar-group-active');
                toggle.classList.remove('border-transparent', 'bg-white', 'text-slate-700', 'hover:bg-slate-50');
            } else {
                toggle.classList.remove('owner-sidebar-group-active');
                toggle.classList.add('border-transparent', 'bg-white', 'text-slate-700', 'hover:bg-slate-50');
            }
        });
    });
})();
</script>
