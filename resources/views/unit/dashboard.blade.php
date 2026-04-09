@extends('unit.layout')
@section('title', 'Dashboard — ConsoliData')

@section('content')

{{-- Page heading --}}
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-sm text-slate-500 mt-0.5">Overview of your PPMP plans, purchase requests, and budget balances.</p>
</div>

{{-- ── Fund Source Selector ────────────────────────────────── --}}
<div class="ui-card">
    <h2 class="ui-card-title flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z"/>
        </svg>
        Fund Source
    </h2>

    @if($fundSources->isEmpty())
        <p class="text-sm text-slate-500 py-2">
            No fund sources are assigned to your unit yet. Contact the administrator.
        </p>
    @else
        <p class="text-sm text-slate-500 mb-4">
            Select a fund source to filter data across all pages. Your selection is saved for this session.
        </p>
        <div class="flex flex-wrap gap-2 mb-4">
            <form method="POST" action="{{ route('unit.fund-source.set') }}">
                @csrf
                <input type="hidden" name="fund_source_id" value="">
                <button type="submit"
                        class="px-4 py-2 rounded-lg text-sm font-semibold border transition-colors
                               {{ !$activeFundSourceId
                                   ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                   : 'bg-white text-slate-600 border-slate-300 hover:border-blue-400 hover:text-blue-600' }}">
                    All Fund Sources
                </button>
            </form>
            @foreach($fundSources as $fs)
                <form method="POST" action="{{ route('unit.fund-source.set') }}">
                    @csrf
                    <input type="hidden" name="fund_source_id" value="{{ $fs->id }}">
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold border transition-colors
                                   {{ (string) $activeFundSourceId === (string) $fs->id
                                       ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                                       : 'bg-white text-slate-600 border-slate-300 hover:border-blue-400 hover:text-blue-600' }}">
                        {{ $fs->name }}
                    </button>
                </form>
            @endforeach
        </div>

        <div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-slate-50 border border-slate-200 text-sm">
            <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
            </svg>
            <div>
                <span class="font-semibold text-slate-700">Current scope:</span>
                <span class="text-slate-600 ml-1">{{ $currentFundSourceScope ?: 'All fund sources' }}</span>
            </div>
        </div>
    @endif
</div>

{{-- ── KPI Cards ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">My PPMP</p>
        <p class="text-3xl font-bold text-slate-900">{{ $ppmpCount }}</p>
        <p class="text-xs text-slate-400 mt-1">Plans in current scope</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Requests</p>
        <p class="text-3xl font-bold text-slate-900">{{ $prCount }}</p>
        <p class="text-xs text-slate-400 mt-1">All purchase requests</p>
    </div>

    <div class="bg-white rounded-xl border border-amber-200 bg-amber-50 p-4">
        <p class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-2">Pending</p>
        <p class="text-3xl font-bold text-amber-700">{{ $pendingPrCount }}</p>
        <p class="text-xs text-amber-500 mt-1">Waiting for admin action</p>
    </div>

    <div class="bg-white rounded-xl border border-blue-200 bg-blue-50 p-4">
        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-2">Approved PR</p>
        <p class="text-3xl font-bold text-blue-700">{{ $approvedPrCount }}</p>
        <p class="text-xs text-blue-500 mt-1">Awaiting your confirmation</p>
    </div>

    <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50 p-4 col-span-2 sm:col-span-1">
        <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-2">PPMP Balance</p>
        <p class="text-xl font-bold text-emerald-700 leading-tight mt-1">
            ₱{{ number_format($remainingPpmpBalance, 2) }}
        </p>
        <p class="text-xs text-emerald-500 mt-1">Budget remaining</p>
    </div>

    <div class="bg-white rounded-xl border border-violet-200 bg-violet-50 p-4">
        <p class="text-xs font-semibold text-violet-600 uppercase tracking-wider mb-2">Unread</p>
        <p class="text-3xl font-bold text-violet-700">{{ $unreadNotifications }}</p>
        <p class="text-xs text-violet-500 mt-1">Notifications</p>
    </div>
</div>

{{-- ── Quick Access ────────────────────────────────────────── --}}
<div class="ui-card">
    <h2 class="ui-card-title flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Quick Access
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">

        <a href="{{ route('unit.ppmp.index') }}"
           class="group flex flex-col gap-1.5 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                    <svg class="w-4 h-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v5h5"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">Manage PPMP</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">Create, maintain, and submit PPMP records for review.</p>
        </a>

        <a href="{{ route('unit.pr.index') }}"
           class="group flex flex-col gap-1.5 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                    <svg class="w-4 h-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3a2 2 0 0 0 6 0M7 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">Manage Requests</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">Prepare and monitor purchase requests from approved PPMP items.</p>
        </a>

        <a href="{{ route('unit.ppmp.remaining') }}"
           class="group flex flex-col gap-1.5 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 0-2 2h-2a2 2 0 0 1-2-2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">Remaining PPMP</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">Check which approved items still have available quantities.</p>
        </a>

        <a href="{{ route('unit.procurement-history') }}"
           class="group flex flex-col gap-1.5 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">Procurement History</span>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">Review completed requests, notices, and procurement records.</p>
        </a>

        <a href="{{ route('unit.notifications') }}"
           class="group flex flex-col gap-1.5 p-4 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                    <svg class="w-4 h-4 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 21a2 2 0 0 0 4 0"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-800 group-hover:text-blue-700">Notifications</span>
                @if($unreadNotifications > 0)
                    <span class="ml-auto inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold">
                        {{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">Read approval updates, remarks, and BAC notices.</p>
        </a>

        <a href="{{ route('unit.ppmp.create') }}"
           class="group flex flex-col gap-1.5 p-4 rounded-xl border border-dashed border-slate-300 hover:border-blue-400 hover:bg-blue-50/50 transition-colors">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                    <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-700 group-hover:text-blue-700">Create New PPMP</span>
            </div>
            <p class="text-xs text-slate-400 leading-relaxed">Start a new procurement plan for the current fiscal year.</p>
        </a>

    </div>
</div>

@endsection
