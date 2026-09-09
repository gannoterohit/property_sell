@extends('layouts.owner')

@section('title', 'Owner Dashboard - ' . \App\Models\Setting::get('website_name', 'RoomRental'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('owner-content')
@php $user = Auth::user(); @endphp
<div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <section class="owner-dashboard-stats" aria-label="Dashboard statistics">
                @foreach([
                    ['Total properties', $rooms ?? 0, 'fa-building', 'bg-indigo-50 text-indigo-600', route('owner.rooms')],
                    ['Featured', $featuredRooms ?? 0, 'fa-star', 'bg-amber-50 text-amber-600', route('owner.rooms')],
                    ['Wallet points', number_format($user->wallet ?? 0), 'fa-wallet', 'bg-sky-50 text-sky-600', route('wallet')],
                    ['Active listings', $activeRooms ?? 0, 'fa-check-circle', 'bg-emerald-50 text-emerald-600', route('owner.rooms')],
                ] as $stat)
                    <a href="{{ $stat[4] }}" class="owner-dashboard-stat block rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div><p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p><p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $stat[1] }}</p></div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $stat[3] }}"><i class="fas {{ $stat[2] }}"></i></span>
                        </div>
                    </a>
                @endforeach
            </section>

            <section class="owner-dashboard-body">
                <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
                        <div><h2 class="font-bold text-slate-950">Recent listings</h2><p class="mt-0.5 text-xs text-slate-500">Your latest properties and their current status</p></div>
                        <a href="{{ route('owner.rooms') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View all</a>
                    </div>
                    @forelse($recentRooms as $room)
                        <div class="flex items-center gap-4 px-5 py-4 border-b border-slate-100 last:border-0">
                            <div class="owner-recent-image">
                                <div class="owner-recent-placeholder"><i class="fas fa-house"></i></div>
                                 @if($room->photo_url)<img src="{{ $room->photo_url }}" alt="" width="400" height="300" loading="lazy" onerror="this.style.display='none'">@endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="truncate text-sm font-bold text-slate-900">{{ $room->title }}</h3>
                                    @if($room->isForSell())
                                        <span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-purple-100 text-purple-700">Sell</span>
                                    @endif
                                </div>
                                <p class="mt-1 truncate text-xs text-slate-500"><i class="fas fa-location-dot mr-1 text-slate-400"></i>{{ $room->city }}</p>
                                <p class="mt-1 text-sm font-extrabold {{ $room->isForSell() ? 'text-purple-700' : 'text-slate-900' }}">
                                    @if($room->isForSell())
                                        {{ $room->displayPrice() }}
                                    @else
                                        &#8377;{{ number_format($room->rent) }}<span class="text-xs font-normal text-slate-400">/month</span>
                                    @endif
                                </p>
                            </div>
                            <span class="hidden sm:inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $room->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($room->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $room->status === 'booked' ? ($room->isForSell() ? 'Sold' : 'Rented') : $room->status }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center"><i class="fas fa-house-circle-xmark text-3xl text-slate-300"></i><h3 class="mt-3 font-bold text-slate-900">No properties listed yet</h3><p class="mt-1 text-sm text-slate-500">Create your first property listing to get started.</p></div>
                    @endforelse
                </div>

                <aside class="owner-dashboard-side">
                    {{-- Active Subscription Card --}}
                    @if(isset($activePlan) && $activePlan)
                        <div class="rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50/50 to-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Active Plan</span>
                                <span class="text-xs font-semibold text-slate-500">Quota-based access</span>
                            </div>
                            <h3 class="mt-3 text-base font-extrabold text-slate-900">{{ $activePlan->plan->name ?? 'Subscription' }}</h3>
                            <p class="mt-1 text-xs text-slate-500">
                                <span class="font-bold text-emerald-600"><i class="fas fa-layer-group mr-1"></i>Use included listing credits until they run out</span>
                            </p>
                            <a href="{{ route('owner.plans') }}" class="mt-4 flex w-full items-center justify-center rounded-xl bg-indigo-600 py-2.5 text-xs font-bold text-white transition hover:bg-indigo-700">
                                Upgrade / Renew Plan
                            </a>
                        </div>
                    @else
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5 shadow-sm">
                            <div class="flex items-center gap-2 text-amber-800">
                                <i class="fas fa-crown text-amber-600"></i>
                                <span class="text-xs font-bold uppercase tracking-wider">No Active Plan</span>
                            </div>
                            <p class="mt-2 text-xs text-slate-600">Subscribe to a plan to unlock more listings and featured property promotions.</p>
                            <a href="{{ route('owner.plans') }}" class="mt-3 flex w-full items-center justify-center rounded-xl bg-amber-600 py-2 text-xs font-bold text-white transition hover:bg-amber-700">
                                View Plans
                            </a>
                        </div>
                    @endif

                    <div class="owner-quick-actions shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10"><i class="fas fa-bolt text-amber-300"></i></span>
                        <h2 class="mt-4 font-bold">Quick actions</h2>
                        <div class="mt-4 space-y-2">
                            <a href="{{ route('owner.rooms.create') }}" class="owner-quick-primary flex items-center justify-between rounded-xl px-4 py-3 text-sm font-bold"><span><i class="fas fa-plus mr-2 text-indigo-600"></i>Add a property</span><i class="fas fa-arrow-right text-xs"></i></a>
                            <a href="{{ route('owner.plans') }}" class="owner-quick-secondary flex items-center justify-between rounded-xl px-4 py-3 text-sm font-semibold"><span><i class="fas fa-tags mr-2"></i>Listing plans</span><i class="fas fa-arrow-right text-xs"></i></a>
                        </div>
                    </div>
                    <div class="owner-account-card rounded-2xl border border-slate-200 bg-white shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-400">Account</p><div class="mt-3 flex items-center gap-3"><span class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-50 font-bold text-indigo-600">{{ strtoupper(substr($user->name, 0, 1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-bold text-slate-900">{{ $user->name }}</p><p class="truncate text-xs text-slate-500">{{ $user->email }}</p></div></div><a href="{{ route('profile.edit') }}" class="mt-4 flex w-full items-center justify-center rounded-xl border border-slate-200 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">Manage profile</a></div>
                </aside>
            </section>
        </div>
    </div>
@endsection
