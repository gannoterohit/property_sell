@extends('layouts.broker')

@section('title', 'Account Pending Approval')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="owner-dashboard-content max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="rounded-2xl border-2 border-amber-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 shadow-inner">
            <i class="fas fa-hourglass-half text-2xl text-amber-600 animate-pulse"></i>
        </div>
        <h2 class="text-2xl font-black text-slate-900">Your Account is Under Verification</h2>
        <p class="mt-2 text-sm text-slate-600 max-w-md mx-auto leading-relaxed">
            Thank you for registering as an Agent. Our admin team is currently reviewing your profile and credentials.
            Once approved, you will be able to publish properties, purchase listing plans, and unlock tenant leads.
        </p>
        <div class="mt-4 inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-xs font-bold text-amber-800">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
            Status: <span class="uppercase tracking-wide">{{ $user->broker_verification_status ?? 'Pending' }}</span>
        </div>
        <p class="mt-2 text-xs text-slate-400">
            Registered on {{ $user->created_at?->format('M d, Y - h:i A') }}
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('agent.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-md hover:bg-indigo-700 transition">
                <i class="fas fa-chart-pie"></i> Go to Dashboard (Preview Mode)
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-6 py-3 text-sm font-bold text-slate-700 border border-slate-200 hover:bg-slate-200 transition">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>
@endsection