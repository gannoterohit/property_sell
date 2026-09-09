@extends('layouts.broker')

@section('title', 'Agent Dashboard - ' . \App\Models\Setting::get('website_name', 'RoomRental'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Welcome Banner --}}
    <div class="agent-welcome-banner">
        <div class="welcome-text">
            <h2>Welcome back, {{ explode(' ', $user->name)[0] }}! 👋</h2>
            <p>Here's an overview of your agent workspace and listings.</p>
        </div>
        <div class="welcome-actions">
            <a href="{{ route('agent.rooms.create') }}" class="welcome-btn welcome-btn-primary">
                <i class="fas fa-plus"></i> Add Property
            </a>
            <a href="{{ route('agent.properties') }}" class="welcome-btn">
                <i class="fas fa-list"></i> View All
            </a>
        </div>
    </div>

    {{-- Shareable Agency Link Card --}}
    @php
        $agencyUrl = route('agency.show', $user);
        $waShareText = urlencode("Namaste! Explore all our verified rental rooms and flats here: " . $agencyUrl);
    @endphp
    <div class="mb-6 mt-4 rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 p-5 sm:p-6 text-white shadow-md relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-bold uppercase tracking-wider border border-indigo-400/30">
                    <i class="fas fa-globe"></i> Your Agency Website Link
                </div>
                <h3 class="text-base sm:text-lg font-black text-white">Share Your Profile & Get Direct Enquiries</h3>
                <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
                    Share your official agency page on WhatsApp Status, Instagram, and with prospective tenants to showcase all your active properties.
                </p>
                <div class="mt-2 text-xs font-mono bg-black/40 text-indigo-200 px-3 py-1.5 rounded-lg inline-block break-all select-all border border-indigo-500/20">
                    {{ $agencyUrl }}
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto shrink-0">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $agencyUrl }}'); alert('Agency profile link copied to clipboard!');"
                        class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 transition shadow-sm cursor-pointer">
                    <i class="fas fa-copy text-indigo-600"></i> Copy Link
                </button>
                <a href="https://wa.me/?text={{ $waShareText }}" target="_blank" rel="noopener"
                   class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition shadow-sm">
                    <i class="fa-brands fa-whatsapp text-sm"></i> Share WhatsApp
                </a>
                <a href="{{ $agencyUrl }}" target="_blank"
                   class="inline-flex items-center justify-center p-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition border border-white/10"
                   title="View Public Profile">
                    <i class="fas fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Stat Cards Row 1 --}}
    <section class="owner-dashboard-stats" aria-label="Dashboard statistics">
        @foreach([
            ['Total Properties',   $stats['total_properties'],   'fa-building',            'bg-indigo-50 text-indigo-600',   route('agent.properties')],
            ['Active Properties',  $stats['active_properties'],  'fa-circle-check',         'bg-emerald-50 text-emerald-600', route('agent.properties')],
            ['Pending Properties', $stats['pending_properties'], 'fa-clock',               'bg-amber-50 text-amber-700',    route('agent.properties')],
            ['Expired Properties', $stats['expired_properties'], 'fa-triangle-exclamation','bg-red-50 text-red-700',        route('agent.properties')],
        ] as $stat)
            <a href="{{ $stat[4] }}" class="owner-dashboard-stat block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $stat[1] }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat[3] }} shadow-sm">
                        <i class="fas {{ $stat[2] }}"></i>
                    </span>
                </div>
            </a>
        @endforeach

        {{-- Stat Cards Row 2 --}}
        @foreach([
            ['Featured Properties', $stats['featured_properties'],             'fa-star',   'bg-amber-50 text-amber-600',    route('agent.properties')],
            ['Credits Remaining',   $credits->sum('credits_remaining'),         'fa-coins',  'bg-emerald-50 text-emerald-600', route('agent.plans')],
            ['Wallet Balance',      '₹' . number_format($wallet?->balance ?? 0),'fa-wallet', 'bg-purple-50 text-purple-600',  route('agent.payments')],
        ] as $stat)
            <a href="{{ $stat[4] }}" class="owner-dashboard-stat block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $stat[1] }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat[3] }} shadow-sm">
                        <i class="fas {{ $stat[2] }}"></i>
                    </span>
                </div>
            </a>
        @endforeach
    </section>

    {{-- Bottom Panels --}}
    <section class="owner-dashboard-body mt-4" aria-label="Recent activity">

        {{-- Recent Properties --}}
        <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="panel-header">
                <div>
                    <h2 class="font-bold text-slate-950 text-sm">Recent Properties</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Latest listings</p>
                </div>
                <a href="{{ route('agent.properties') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    View all <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            @forelse($recentProperties as $room)
                <div class="flex items-center gap-4 px-5 py-3.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/60 transition">
                    <div class="owner-recent-image flex-shrink-0">
                        <div class="owner-recent-placeholder"><i class="fas fa-house text-sm"></i></div>
                        @if($room->photo_url)
                            <img src="{{ $room->photo_url }}" alt="" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <h3 class="truncate text-sm font-bold text-slate-900">{{ $room->title }}</h3>
                            @if($room->isForSell())
                                <span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-purple-100 text-purple-700">Sell</span>
                            @endif
                        </div>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            <i class="fas fa-location-dot mr-1 text-slate-400"></i>{{ $room->city }}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-extrabold {{ $room->isForSell() ? 'text-purple-700' : 'text-slate-900' }}">
                            {{ $room->isForSell() ? $room->displayPrice() : '₹' . number_format($room->rent) }}
                        </p>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase mt-1
                            {{ $room->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($room->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ $room->status === 'booked' ? ($room->isForSell() ? 'Sold' : 'Rented') : $room->status }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <i class="fas fa-house-circle-xmark text-3xl text-slate-200 block mb-3"></i>
                    <p class="text-sm text-slate-500">No properties listed yet.</p>
                    <a href="{{ route('agent.rooms.create') }}" class="mt-2 inline-block text-indigo-600 text-sm font-bold hover:underline">Create your first listing</a>
                </div>
            @endforelse
        </div>

        {{-- Recent Transactions --}}
        <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="panel-header">
                <div>
                    <h2 class="font-bold text-slate-950 text-sm">Recent Transactions</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Latest payments & credits</p>
                </div>
                <a href="{{ route('agent.transactions') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    View all <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            @forelse($recentTransactions as $txn)
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/60 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                            <i class="fas {{ $txn->type === 'credit' ? 'fa-arrow-down-left' : 'fa-arrow-up-right' }} text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ ucfirst($txn->type) }} — {{ ucfirst($txn->category) }}</p>
                            <p class="text-xs text-slate-500">{{ $txn->description ?: $txn->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold flex-shrink-0 ml-3 {{ $txn->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $txn->type === 'credit' ? '+' : '-' }}&#8377;{{ number_format($txn->amount, 2) }}
                    </span>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <i class="fas fa-receipt text-3xl text-slate-200 block mb-3"></i>
                    <p class="text-sm text-slate-500">No transactions yet.</p>
                </div>
            @endforelse
        </div>

    </section>
</div>
@endsection