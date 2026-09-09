@extends('layouts.admin')
@section('title','Admin Dashboard')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-misc.css') }}">@endpush
@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header><p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Your workspace</p><h1 class="mt-1 text-2xl font-extrabold text-slate-900">Good {{ now()->hour<12?'morning':(now()->hour<17?'afternoon':'evening') }}, {{ Auth::user()->name }}</h1><p class="text-sm text-slate-500">Only the modules assigned to your role are shown here.</p></header>

    {{-- SLA Stale Queue Alerts Warning Banner --}}
    @if(!empty($slaAlerts) && $slaAlerts['totalAlerts'] > 0)
        <section class="rounded-2xl border border-red-200 bg-red-50/70 p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-700 shrink-0">
                        <i class="fas fa-triangle-exclamation text-lg animate-pulse"></i>
                    </span>
                    <div>
                        <h3 class="text-xs font-extrabold text-red-950 uppercase tracking-wider">High Priority SLA Alerts</h3>
                        <p class="text-xs text-red-800">
                            You have <strong>{{ $slaAlerts['totalAlerts'] }} stale items</strong> that require immediate staff moderation or assignment.
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($slaAlerts['staleComplaints'] > 0)
                        <a href="{{ route('admin.complaints.index', ['assigned' => 'unassigned']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-red-200 text-xs font-bold text-red-700 hover:bg-red-100 transition shadow-2xs">
                            <i class="fas fa-user-clock"></i> {{ $slaAlerts['staleComplaints'] }} Unassigned Tickets (&gt;24h)
                        </a>
                    @endif
                    @if($slaAlerts['staleRooms'] > 0)
                        <a href="{{ route('admin.all-rooms', ['listing_status' => 'pending']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-red-200 text-xs font-bold text-red-700 hover:bg-red-100 transition shadow-2xs">
                            <i class="fas fa-clock"></i> {{ $slaAlerts['staleRooms'] }} Pending Rooms (&gt;24h)
                        </a>
                    @endif
                    @if($slaAlerts['overdueComplaints'] > 0)
                        <a href="{{ route('admin.complaints.index', ['sla' => 'overdue']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-red-200 text-xs font-bold text-red-700 hover:bg-red-100 transition shadow-2xs">
                            <i class="fas fa-hourglass-end"></i> {{ $slaAlerts['overdueComplaints'] }} Overdue SLA Tickets
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- Today's Operational Business Snapshot --}}
    @if(!empty($todaySnapshot))
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <i class="fas fa-bolt text-xs"></i>
                    </span>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">Today's Operational Snapshot</h2>
                        <p class="text-[11px] text-slate-400">Live performance comparing today vs yesterday</p>
                    </div>
                </div>
                <span class="text-[11px] font-bold text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100">
                    <i class="far fa-calendar-check mr-1 text-indigo-500"></i> {{ now()->format('d M Y') }}
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                {{-- New Users Today --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">New Users</span>
                        <i class="fas fa-user-plus text-slate-300 text-xs"></i>
                    </div>
                    <p class="mt-2 text-xl font-black text-slate-900">{{ $todaySnapshot['users']['today'] }}</p>
                    <p class="mt-1 text-[10px] font-bold {{ $todaySnapshot['users']['diff'] >= 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                        <i class="fas {{ $todaySnapshot['users']['diff'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ abs($todaySnapshot['users']['diff']) }} vs yesterday ({{ $todaySnapshot['users']['yesterday'] }})
                    </p>
                </div>

                {{-- Rooms Posted Today --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Rooms Posted</span>
                        <i class="fas fa-home text-slate-300 text-xs"></i>
                    </div>
                    <p class="mt-2 text-xl font-black text-slate-900">{{ $todaySnapshot['rooms']['today'] }}</p>
                    <p class="mt-1 text-[10px] font-bold {{ $todaySnapshot['rooms']['diff'] >= 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                        <i class="fas {{ $todaySnapshot['rooms']['diff'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ abs($todaySnapshot['rooms']['diff']) }} vs yesterday ({{ $todaySnapshot['rooms']['yesterday'] }})
                    </p>
                </div>

                {{-- Contact Unlocks Today --}}
                <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Contact Unlocks</span>
                        <i class="fas fa-key text-slate-300 text-xs"></i>
                    </div>
                    <p class="mt-2 text-xl font-black text-slate-900">{{ $todaySnapshot['unlocks']['today'] }}</p>
                    <p class="mt-1 text-[10px] font-bold {{ $todaySnapshot['unlocks']['diff'] >= 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                        <i class="fas {{ $todaySnapshot['unlocks']['diff'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ abs($todaySnapshot['unlocks']['diff']) }} vs yesterday ({{ $todaySnapshot['unlocks']['yesterday'] }})
                    </p>
                </div>

                {{-- Today's Revenue --}}
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/40 p-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700">Today's Revenue</span>
                        <i class="fas fa-indian-rupee-sign text-indigo-400 text-xs"></i>
                    </div>
                    <p class="mt-2 text-xl font-black text-indigo-900">₹{{ number_format($todaySnapshot['revenue']['today']) }}</p>
                    <p class="mt-1 text-[10px] font-bold {{ $todaySnapshot['revenue']['diff'] >= 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                        <i class="fas {{ $todaySnapshot['revenue']['diff'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        ₹{{ number_format(abs($todaySnapshot['revenue']['diff'])) }} vs yesterday (₹{{ number_format($todaySnapshot['revenue']['yesterday']) }})
                    </p>
                </div>
            </div>
        </section>
    @endif

    <section class="dash-grid">
        @if($access['listings'])
            <article class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Active listings</p>
                        <p class="mt-2 text-2xl font-extrabold">{{ $activeRooms }}</p>
                        <div class="mt-1 flex items-center gap-2 text-[11px] font-bold">
                            <span class="text-blue-600"><i class="fas fa-key text-[9px]"></i> Rent: {{ $activeRentRooms ?? 0 }}</span>
                            <span class="text-slate-300">|</span>
                            <span class="text-purple-600"><i class="fas fa-tag text-[9px]"></i> Sell: {{ $activeSellRooms ?? 0 }}</span>
                        </div>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-building-circle-check"></i></span>
                </div>
            </article>
            <article class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Pending review</p>
                        <p class="mt-2 text-2xl font-extrabold">{{ $pendingRooms }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fas fa-clock"></i></span>
                </div>
            </article>
            <article class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Approved listings</p>
                        <p class="mt-2 text-2xl font-extrabold">{{ $approvedRooms }}</p>
                    </div>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fas fa-circle-check"></i></span>
                </div>
            </article>
        @endif
        @if($access['people'])<article class="rounded-2xl border bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase text-slate-400">Users & owners</p><p class="mt-2 text-2xl font-extrabold">{{ $users }} <small class="text-xs text-slate-400">users</small></p><p class="text-xs text-slate-500">{{ $owners }} property owners</p></article>@endif
        @if($access['brokers'])<article class="rounded-2xl border bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase text-slate-400">Brokers</p><p class="mt-2 text-2xl font-extrabold">{{ $brokers }} <small class="text-xs text-slate-400">total</small></p><p class="text-xs text-slate-500">{{ $approvedBrokers }} approved · {{ $pendingBrokers }} pending</p></article>@endif
        @if($access['support'])<article class="rounded-2xl border bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase text-slate-400">Support desk</p><p class="mt-2 text-2xl font-extrabold">{{ $openComplaints }} <small class="text-xs text-slate-400">open</small></p><p class="text-xs text-slate-500">{{ $unreadContacts }} unread enquiries</p></article>@endif
        @if($access['finance'])<article class="rounded-2xl border bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase text-slate-400">Platform revenue</p><p class="mt-2 text-2xl font-extrabold">₹{{ number_format($totalEarnings) }}</p><p class="text-xs text-slate-500">₹{{ number_format($todayEarnings) }} collected today</p></article>@endif
        @if($access['content'])<article class="rounded-2xl border bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase text-slate-400">Website content</p><p class="mt-2 text-2xl font-extrabold">{{ $contentStats['blogs'] }} <small class="text-xs text-slate-400">blogs</small></p><p class="text-xs text-slate-500">{{ $contentStats['offers'] }} offers · {{ $contentStats['pages'] }} pages</p><p class="text-xs text-slate-500">{{ $contentStats['home_features'] }} why choose · {{ $contentStats['how_it_works'] }} steps · {{ $contentStats['testimonials'] }} reviews</p></article>@endif
        @if($access['reports'])<article class="rounded-2xl border bg-white p-4 shadow-sm"><p class="text-[10px] font-bold uppercase text-slate-400">Activity today</p><p class="mt-2 text-2xl font-extrabold">{{ $reportStats['searches_today'] }} <small class="text-xs text-slate-400">searches</small></p><p class="text-xs text-slate-500">{{ $reportStats['unlocks_today'] }} contact unlocks</p></article>@endif
    </section>

    @if(count($actionQueues))<section class="rounded-2xl border bg-white p-5 shadow-sm"><div class="flex justify-between"><div><h2 class="text-sm font-extrabold">Action required</h2><p class="text-xs text-slate-500">Queues available to your role</p></div><span class="rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-bold text-red-700">{{ collect($actionQueues)->sum('count') }} pending</span></div><div class="mt-4 dash-grid">@foreach($actionQueues as $queue)<a href="{{ $queue['route'] }}" class="flex items-center gap-3 rounded-xl border p-3 hover:bg-slate-50"><span class="flex h-9 w-9 items-center justify-center rounded-lg admin-theme-soft"><i class="fas {{ $queue['icon'] }}"></i></span><span class="min-w-0 flex-1 text-xs font-semibold text-slate-600">{{ $queue['label'] }}</span><strong>{{ $queue['count'] }}</strong></a>@endforeach</div></section>@endif

    @if(!empty($expiringSpotlights) && $expiringSpotlights->isNotEmpty())
        <section class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5 shadow-sm">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-amber-950 flex items-center gap-2">
                        <i class="fas fa-crown text-amber-600"></i>
                        Spotlights Expiring Soon (Next 7 Days)
                    </h2>
                    <p class="text-xs text-amber-800">Reach out to brokers on WhatsApp to renew their featured placement before it expires.</p>
                </div>
                <span class="rounded-full bg-amber-200/70 px-2.5 py-1 text-[10px] font-extrabold text-amber-950">{{ $expiringSpotlights->count() }} Expiring</span>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($expiringSpotlights as $exp)
                    <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-white p-3 shadow-xs">
                        <div class="min-w-0 pr-2">
                            <strong class="block truncate text-xs text-slate-900">{{ $exp->agency_name ?: $exp->name }}</strong>
                            <small class="text-[10px] text-amber-700 font-semibold">Expires: {{ $exp->featured_agency_expires_at->format('M d, Y') }} ({{ $exp->featured_agency_expires_at->diffForHumans() }})</small>
                        </div>
                        @if($exp->phone)
                            <x-admin.whatsapp-btn :phone="$exp->phone" :name="$exp->name" context="Featured Agency Spotlight Renewal on ApnaNest" size="sm" :show-text="true" />
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!empty($marketOpportunities) && $marketOpportunities->isNotEmpty())
        <section class="rounded-2xl border bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-950 flex items-center gap-2">
                        <i class="fas fa-bullseye text-indigo-600"></i>
                        Market Opportunity (High Search Volume · Low Supply)
                    </h2>
                    <p class="text-xs text-slate-500">Localities where tenants are actively searching but rooms are unavailable. Prioritize onboarding owners here.</p>
                </div>
                <a href="{{ route('admin.analytics') }}" class="text-xs font-bold admin-theme-text">View Analytics <i class="fas fa-arrow-right ml-1"></i></a>
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($marketOpportunities as $opp)
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5">
                        <div class="flex items-center justify-between">
                            <strong class="text-xs text-slate-900">{{ $opp['city'] }}</strong>
                            <span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold {{ $opp['rooms'] === 0 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $opp['status'] }}
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs">
                            <span class="text-slate-500"><i class="fas fa-magnifying-glass mr-1 text-[10px]"></i>{{ $opp['searches'] }} searches</span>
                            <span class="font-bold text-slate-700">{{ $opp['rooms'] }} rooms</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($access['finance'])<section class="rounded-2xl border bg-white p-5 shadow-sm"><div class="flex justify-between"><div><h2 class="text-sm font-extrabold">Monthly platform revenue</h2><p class="text-xs text-slate-500">Collections in {{ now()->year }}</p></div><a href="{{ route('admin.payments.index') }}" class="text-xs font-bold admin-theme-text">View payments</a></div><div class="mt-4 h-[280px]"><canvas id="revenueChart"></canvas></div></section>@endif

    <section class="dash-two">
        @if($access['people'])@foreach([['Recent users',$recentUsers,'admin.users','fa-user'],['Recent owners',$recentOwners,'admin.owners','fa-user-tie']] as [$heading,$records,$route,$icon])<div class="rounded-2xl border bg-white p-5"><div class="flex justify-between"><h2 class="text-sm font-extrabold">{{ $heading }}</h2><a href="{{ route($route) }}" class="text-xs font-bold admin-theme-text">View all</a></div><div class="mt-3 divide-y">@forelse($records as $record)<a href="{{ $route==='admin.owners'?route('admin.owners.detail',$record):route('admin.users.detail',$record) }}" class="flex items-center gap-3 py-3"><i class="fas {{ $icon }} admin-theme-text"></i><span class="min-w-0 flex-1 truncate text-xs font-bold">{{ $record->name }}</span></a>@empty<p class="py-5 text-xs text-slate-400">No records.</p>@endforelse</div></div>@endforeach @endif
        @if($access['brokers'])<div class="rounded-2xl border bg-white p-5"><div class="flex justify-between"><h2 class="text-sm font-extrabold">Recent brokers</h2><a href="{{ route('admin.brokers.index') }}" class="text-xs font-bold admin-theme-text">View all</a></div><div class="mt-3 divide-y">@forelse($recentBrokers ?? [] as $broker)<a href="{{ route('admin.brokers.show', $broker) }}" class="flex items-center gap-3 py-3"><i class="fas fa-handshake admin-theme-text"></i><span class="min-w-0 flex-1 truncate text-xs font-bold">{{ $broker->name }}</span></a>@empty<p class="py-5 text-xs text-slate-400">No brokers yet.</p>@endforelse</div></div>@endif
        @if($access['listings'])<div class="rounded-2xl border bg-white p-5"><div class="flex justify-between"><h2 class="text-sm font-extrabold">Recent listings</h2><a href="{{ route('admin.all-rooms') }}" class="text-xs font-bold admin-theme-text">View all</a></div><div class="mt-3 divide-y">@forelse($recentRooms as $room)<a href="{{ route('admin.rooms.show',$room) }}" class="flex items-center gap-3 py-3"><span class="h-7 w-1 rounded admin-theme-bg"></span><span class="min-w-0 flex-1"><strong class="block truncate text-xs">{{ $room->title??'Room #'.$room->id }}</strong><small class="text-[10px] text-slate-400">{{ $room->owner?->name??'Owner unavailable' }}</small></span></a>@empty<p class="py-5 text-xs text-slate-400">No listings.</p>@endforelse</div></div>@endif
        @if($access['support'])<div class="rounded-2xl border bg-white p-5"><div class="flex justify-between"><h2 class="text-sm font-extrabold">Recent support tickets</h2><a href="{{ route('admin.complaints.index') }}" class="text-xs font-bold admin-theme-text">View all</a></div><div class="mt-3 divide-y">@forelse($recentComplaints as $ticket)<a href="{{ route('admin.complaints.show',$ticket) }}" class="flex items-center gap-3 py-3"><span class="rounded-lg bg-red-50 px-2 py-1 text-[9px] font-bold uppercase text-red-600">{{ $ticket->priority }}</span><span class="min-w-0 flex-1"><strong class="block truncate text-xs">{{ $ticket->subject }}</strong><small class="text-[10px] text-slate-400">{{ $ticket->ticket_number }} · {{ str_replace('_',' ',$ticket->status) }}</small></span></a>@empty<p class="py-5 text-xs text-slate-400">No support tickets.</p>@endforelse</div></div>@endif
    </section>

    @if($access['finance'])<section class="overflow-hidden rounded-2xl border bg-white"><div class="flex justify-between border-b px-5 py-4"><div><h2 class="text-sm font-extrabold">Recent payments</h2><p class="text-xs text-slate-500">Latest financial activity</p></div><a href="{{ route('admin.payments.index') }}" class="text-xs font-bold admin-theme-text">View all</a></div><div class="overflow-x-auto"><table class="w-full min-w-[620px]"><thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead><tbody class="divide-y">@forelse($recentPayments as $payment)<tr><td class="px-5 text-xs font-bold">{{ $payment->user?->name??'Deleted user' }}</td><td class="px-5 text-xs capitalize">{{ $payment->type }}</td><td class="px-5 text-xs font-extrabold">₹{{ number_format($payment->amount) }}</td><td class="px-5 text-xs capitalize">{{ $payment->status }}</td></tr>@empty<tr><td colspan="4" class="p-8 text-center text-xs text-slate-400">No payments.</td></tr>@endforelse</tbody></table></div></section>@endif

    @if(!collect($access)->contains(true))<section class="rounded-2xl border border-dashed bg-white p-10 text-center"><i class="fas fa-user-lock text-3xl text-slate-300"></i><h2 class="mt-3 text-base font-extrabold">No workspace modules assigned</h2><p class="mt-1 text-xs text-slate-500">Ask the Super Admin to update your role permissions.</p></section>@endif
</div>
@endsection
@if($access['finance'])@push('scripts')<script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script>const ctx=document.getElementById('revenueChart');if(ctx&&window.Chart)new Chart(ctx,{type:'line',data:{labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],datasets:[{data:@json($revenueData),borderColor:'#4f46e5',backgroundColor:'rgba(79,70,229,.09)',fill:true,tension:.38}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}});</script>@endpush @endif
