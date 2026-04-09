@extends('unit.layout')
@section('title', 'Account Management — ConsoliData')

@section('content')

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Account Management</h1>
        <p class="text-sm text-slate-500 mt-0.5">Review your profile information and manage your password.</p>
    </div>
    <a href="{{ route('password.change') }}" class="btn-secondary btn-sm flex-shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 0 1 2 2m4 0a6 6 0 0 1-7.743 5.743L11 17H9v2H7v2H4a1 1 0 0 1-1-1v-2.586a1 1 0 0 1 .293-.707l5.964-5.964A6 6 0 1 1 21 9z"/>
        </svg>
        Change Password
    </a>
</div>

{{-- Profile card --}}
<div class="ui-card">
    <div class="flex items-center gap-5 mb-6 pb-5 border-b border-slate-100">
        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0">
            <span class="text-white text-2xl font-bold">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </span>
        </div>
        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ auth()->user()->name }}</h2>
            <p class="text-sm text-slate-500">{{ auth()->user()->email }}</p>
            <span class="inline-flex items-center mt-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                Unit User
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Full Name</p>
            <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Email Address</p>
            <p class="text-sm font-medium text-slate-800">{{ auth()->user()->email }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Department / Unit</p>
            <p class="text-sm font-medium text-slate-800">{{ $departmentName ?: 'Not assigned yet' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Current Fund Source</p>
            <p class="text-sm font-medium text-slate-800">{{ $fundSourceName ?: 'All fund sources' }}</p>
        </div>
    </div>
</div>

{{-- Password hint card --}}
<div class="flex items-start gap-4 px-5 py-4 rounded-xl bg-slate-50 border border-slate-200">
    <div class="w-9 h-9 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/>
        </svg>
    </div>
    <div>
        <p class="text-sm font-semibold text-slate-700 mb-0.5">Update Password</p>
        <p class="text-sm text-slate-500">Keep your account secure by using a strong, unique password.</p>
        <a href="{{ route('password.change') }}" class="inline-block mt-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
            Go to password change →
        </a>
    </div>
</div>

@endsection
