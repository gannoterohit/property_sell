@extends('layouts.admin')

@section('title', 'Admin Notifications')

@section('admin-content')
@php
    $typeConfig = [
        'payment_received' => [
            'label' => 'Payment Received',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'icon_bg' => 'bg-emerald-500/10 text-emerald-600 border border-emerald-200/60',
            'icon' => 'fa-credit-card',
        ],
        'new_user_registration' => [
            'label' => 'New User',
            'badge' => 'bg-teal-50 text-teal-700 border-teal-200',
            'icon_bg' => 'bg-teal-500/10 text-teal-600 border border-teal-200/60',
            'icon' => 'fa-user-plus',
        ],
        'new_broker_registration' => [
            'label' => 'New Broker',
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'icon_bg' => 'bg-indigo-500/10 text-indigo-600 border border-indigo-200/60',
            'icon' => 'fa-id-badge',
        ],
        'room_posted' => [
            'label' => 'Property Listing',
            'badge' => 'bg-sky-50 text-sky-700 border-sky-200',
            'icon_bg' => 'bg-sky-500/10 text-sky-600 border border-sky-200/60',
            'icon' => 'fa-home',
        ],
        'complaint_submitted' => [
            'label' => 'Complaint',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
            'icon_bg' => 'bg-rose-500/10 text-rose-600 border border-rose-200/60',
            'icon' => 'fa-exclamation-triangle',
        ],
        'complaint_reply' => [
            'label' => 'Complaint Reply',
            'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
            'icon_bg' => 'bg-amber-500/10 text-amber-600 border border-amber-200/60',
            'icon' => 'fa-reply',
        ],
        'contact_inquiry' => [
            'label' => 'Contact Inquiry',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
            'icon_bg' => 'bg-blue-500/10 text-blue-600 border border-blue-200/60',
            'icon' => 'fa-envelope',
        ],
        'lead_unlock' => [
            'label' => 'Lead Unlock',
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
            'icon_bg' => 'bg-purple-500/10 text-purple-600 border border-purple-200/60',
            'icon' => 'fa-unlock-alt',
        ],
        'broadcast' => [
            'label' => 'Broadcast',
            'badge' => 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
            'icon_bg' => 'bg-fuchsia-500/10 text-fuchsia-600 border border-fuchsia-200/60',
            'icon' => 'fa-bullhorn',
        ],
    ];

    $currentStatus = request('status', '');
    $currentType = request('type', '');
    $currentSearch = request('search', '');
@endphp

<div class="space-y-6">
    <!-- Header with Breadcrumb & Quick Actions -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="fas fa-bell text-sm"></i>
                </span>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Notification Center</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">Real-time alert activity across new users, property submissions, broker signups, and complaints.</p>
        </div>

        <!-- Global Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            @if(($stats['unread'] ?? 0) > 0)
                <form action="{{ route('admin.notifications.markAllRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm shadow-indigo-200 transition active:scale-95">
                        <i class="fas fa-check-double text-[11px]"></i>
                        <span>Mark All as Read</span>
                    </button>
                </form>
            @endif

            @if(($stats['read'] ?? 0) > 0)
                <form action="{{ route('admin.notifications.clearAll') }}" method="POST" class="admin-confirm"
                      data-confirm-title="Clear Read Notifications?"
                      data-confirm-text="All read notifications will be deleted permanently."
                      data-confirm-icon="warning"
                      data-confirm-button="Yes, delete read"
                      data-confirm-color="#4f46e5">
                    @csrf
                    <input type="hidden" name="mode" value="read">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl border border-slate-200 transition active:scale-95">
                        <i class="fas fa-broom text-[11px] text-slate-500"></i>
                        <span>Clear Read</span>
                    </button>
                </form>
            @endif

            @if(($stats['total'] ?? 0) > 0)
                <form action="{{ route('admin.notifications.clearAll') }}" method="POST" class="admin-confirm"
                      data-confirm-title="Clear All Notifications?"
                      data-confirm-text="ALL notifications will be deleted permanently. This action cannot be undone."
                      data-confirm-icon="error"
                      data-confirm-button="Yes, delete everything"
                      data-confirm-color="#dc2626">
                    @csrf
                    <input type="hidden" name="mode" value="all">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-xl transition active:scale-95">
                        <i class="fas fa-trash-alt text-[11px]"></i>
                        <span>Clear All</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Stats KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total -->
        <a href="{{ route('admin.notifications.index') }}" 
           class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ empty($currentStatus) && empty($currentType) ? 'border-indigo-500 shadow-sm' : 'border-slate-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Total Alerts</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs">
                    <i class="fas fa-layer-group"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-800">{{ number_format($stats['total'] ?? 0) }}</span>
                <span class="text-[10px] text-slate-400 font-medium">Recorded</span>
            </div>
        </a>

        <!-- Unread -->
        <a href="{{ route('admin.notifications.index', array_merge(request()->except(['status', 'page']), ['status' => 'unread'])) }}" 
           class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ $currentStatus === 'unread' ? 'border-rose-500 ring-2 ring-rose-100' : 'border-slate-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Unread</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs relative">
                    <i class="fas fa-bell"></i>
                    @if(($stats['unread'] ?? 0) > 0)
                        <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                        </span>
                    @endif
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-rose-600">{{ number_format($stats['unread'] ?? 0) }}</span>
                <span class="text-[10px] text-rose-500 font-bold">Action needed</span>
            </div>
        </a>

        <!-- Read -->
        <a href="{{ route('admin.notifications.index', array_merge(request()->except(['status', 'page']), ['status' => 'read'])) }}" 
           class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ $currentStatus === 'read' ? 'border-emerald-500 ring-2 ring-emerald-100' : 'border-slate-200' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Read</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fas fa-check"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-600">{{ number_format($stats['read'] ?? 0) }}</span>
                <span class="text-[10px] text-slate-400 font-medium">Archived</span>
            </div>
        </a>

        <!-- Today -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Today</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                    <i class="fas fa-calendar-day"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-purple-600">{{ number_format($stats['today'] ?? 0) }}</span>
                <span class="text-[10px] text-slate-400 font-medium">Last 24 hrs</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <!-- Left: Status Tabs & Type Select -->
            <div class="flex flex-wrap items-center gap-2 flex-1">
                <!-- Status Pills -->
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/60 text-xs font-semibold">
                    <a href="{{ route('admin.notifications.index', array_merge(request()->except(['status', 'page']))) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ empty($currentStatus) ? 'bg-white text-indigo-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        All
                    </a>
                    <a href="{{ route('admin.notifications.index', array_merge(request()->except(['status', 'page']), ['status' => 'unread'])) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'unread' ? 'bg-white text-rose-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        Unread ({{ $stats['unread'] ?? 0 }})
                    </a>
                    <a href="{{ route('admin.notifications.index', array_merge(request()->except(['status', 'page']), ['status' => 'read'])) }}" 
                       class="px-3 py-1.5 rounded-lg transition {{ $currentStatus === 'read' ? 'bg-white text-emerald-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                        Read
                    </a>
                </div>

                @if(!empty($currentStatus))
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif

                <!-- Type Dropdown Filter -->
                <div class="relative min-w-[170px]">
                    <select name="type" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        <option value="">All Alert Types</option>
                        @foreach($availableTypes as $typeKey)
                            @php
                                $cfg = $typeConfig[$typeKey] ?? null;
                                $label = $cfg['label'] ?? ucwords(str_replace('_', ' ', $typeKey));
                            @endphp
                            <option value="{{ $typeKey }}" {{ $currentType === $typeKey ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(!empty($currentStatus) || !empty($currentType) || !empty($currentSearch))
                    <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                        <i class="fas fa-times-circle"></i> Clear Filters
                    </a>
                @endif
            </div>

            <!-- Right: Search Input -->
            <div class="relative w-full md:w-72">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $currentSearch }}" placeholder="Search alert title or body..."
                       class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                @if(!empty($currentSearch))
                    <a href="{{ route('admin.notifications.index', array_merge(request()->except(['search', 'page']))) }}" 
                       class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($notifications as $notification)
            @php
                $cfg = $typeConfig[$notification->type] ?? null;
                $badgeStyle = $cfg['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                $iconBg = $cfg['icon_bg'] ?? 'bg-slate-100 text-slate-500 border border-slate-200';
                $displayIcon = $notification->icon ?: ($cfg['icon'] ?? 'fa-bell');
                $typeLabel = $cfg['label'] ?? ucwords(str_replace('_', ' ', $notification->type ?? 'Notification'));
            @endphp

            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between p-4 sm:p-5 gap-4 transition hover:bg-slate-50/80 {{ !$notification->is_read ? 'bg-indigo-50/25 border-l-4 border-indigo-500' : 'bg-white' }}">
                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                    <!-- Icon -->
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $notification->is_read ? 'bg-slate-100 text-slate-400' : $iconBg }} shadow-sm">
                        <i class="fas {{ $displayIcon }} text-sm"></i>
                    </div>

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Type Badge -->
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $badgeStyle }}">
                                {{ $typeLabel }}
                            </span>

                            @if(!$notification->is_read)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[9px] font-extrabold tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> NEW
                                </span>
                            @endif

                            <span class="text-[11px] font-medium text-slate-400 ml-auto sm:ml-0">
                                <i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="text-sm font-bold text-slate-800 mt-1.5">
                            @if($notification->link)
                                <a href="{{ route('admin.notifications.markRead', $notification->id) }}" class="hover:text-indigo-600 transition inline-flex items-center gap-1">
                                    <span>{{ $notification->title }}</span>
                                    <i class="fas fa-external-link-alt text-[10px] text-slate-400"></i>
                                </a>
                            @else
                                {{ $notification->title }}
                            @endif
                        </h3>

                        <!-- Message -->
                        @if($notification->message)
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed line-clamp-2 sm:line-clamp-none">
                                {{ $notification->message }}
                            </p>
                        @endif

                        <!-- Exact Timestamp -->
                        <div class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-2">
                            <span><i class="far fa-calendar-alt mr-1"></i>{{ $notification->created_at->format('d M Y, h:i A') }}</span>
                            @if($notification->is_read && $notification->read_at)
                                <span class="text-emerald-600 font-medium">
                                    <i class="fas fa-check-double mr-1"></i>Read {{ $notification->read_at->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                    @if($notification->link)
                        <a href="{{ route('admin.notifications.markRead', $notification->id) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-700 text-xs font-semibold transition active:scale-95 shadow-sm">
                            <span>View</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif

                    @if(!$notification->is_read)
                        <form action="{{ route('admin.notifications.markRead', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" title="Mark as read"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-600 hover:text-white text-xs font-semibold transition active:scale-95 shadow-sm">
                                <i class="fas fa-check text-[11px]"></i>
                                <span class="hidden sm:inline">Mark Read</span>
                            </button>
                        </form>
                    @endif

                    <!-- Delete Button -->
                    <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" class="admin-confirm"
                          data-confirm-title="Delete Notification?"
                          data-confirm-text="This notification will be permanently removed."
                          data-confirm-icon="warning"
                          data-confirm-button="Yes, delete"
                          data-confirm-color="#dc2626">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Delete notification"
                                class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition active:scale-95">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 mb-3">
                    <i class="fas fa-bell-slash text-2xl"></i>
                </div>
                <h3 class="text-base font-bold text-slate-700">No Notifications Found</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    @if(!empty($currentStatus) || !empty($currentType) || !empty($currentSearch))
                        No notifications match your current filter criteria. Try adjusting or resetting your filters.
                    @else
                        You're all caught up! When system events occur, alerts will appear right here.
                    @endif
                </p>
                @if(!empty($currentStatus) || !empty($currentType) || !empty($currentSearch))
                    <div class="mt-4">
                        <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-600 hover:bg-indigo-600 hover:text-white text-xs font-semibold rounded-xl transition">
                            <i class="fas fa-undo"></i> Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
