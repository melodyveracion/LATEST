<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Unit Portal — ConsoliData')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @include('auth.partials.no-cache-guard')
</head>
<body class="h-full bg-slate-100 antialiased font-sans" x-data="unitLayout()" x-init="init()">

@php
    $sidebarFundSources = \App\Support\UnitFundDepartmentResolver::fundSourcesForDepartment(auth()->user()?->department_unit_id);
    $__ppmpOpen        = request()->is('unit/ppmp*') || request()->is('unit/ppmp-remaining');
    $__prOpen          = request()->is('unit/purchase-requests*') || request()->is('unit/procurement-history');
@endphp

{{-- Mobile backdrop --}}
<div class="fixed inset-0 bg-black/60 z-20 lg:hidden"
     x-show="sidebarOpen"
     x-transition:enter="transition-opacity duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     style="display:none;"></div>

<div class="flex h-full">

    {{-- ─── SIDEBAR ──────────────────────────────────────────── --}}
    <aside class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-[#000e2c] transition-transform duration-200 ease-in-out lg:relative lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        {{-- Brand --}}
        <div class="flex items-center justify-between h-16 px-5 border-b border-white/10 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm leading-none">CD</span>
                </div>
                <span class="text-white font-semibold text-base tracking-tight">ConsoliData</span>
            </div>
            <button @click="sidebarOpen = false"
                    class="lg:hidden p-1 rounded text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-0.5">

            {{-- Dashboard --}}
            <a href="{{ route('unit.dashboard') }}"
               class="nav-link {{ request()->is('dashboard') ? 'nav-link--active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                    <rect x="14" y="3" width="7" height="4" rx="1.5"/>
                    <rect x="14" y="10" width="7" height="11" rx="1.5"/>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- PPMP group --}}
            <div x-data="{ open: {{ $__ppmpOpen ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="nav-link w-full {{ $__ppmpOpen ? 'nav-link--active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v5h5M9 9h6M9 13h6M9 17h4"/>
                    </svg>
                    <span class="flex-1 text-left">PPMP</span>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-0.5 ml-3 pl-4 border-l border-white/10 space-y-0.5" style="display:none;">
                    <a href="{{ route('unit.ppmp.index') }}"
                       class="nav-sub-link {{ request()->is('unit/ppmp') || request()->is('unit/ppmp/create') || request()->is('unit/ppmp/*/edit') ? 'nav-sub-link--active' : '' }}">
                        Manage PPMP
                    </a>
                    <a href="{{ route('unit.ppmp.remaining') }}"
                       class="nav-sub-link {{ request()->is('unit/ppmp-remaining') ? 'nav-sub-link--active' : '' }}">
                        Remaining Items
                    </a>
                </div>
            </div>

            {{-- Purchase Request group --}}
            <div x-data="{ open: {{ $__prOpen ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="nav-link w-full {{ $__prOpen ? 'nav-link--active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 9h6M9 13h6M9 17h4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3a2 2 0 0 0 6 0"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1"/>
                    </svg>
                    <span class="flex-1 text-left">Purchase Request</span>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-150" :class="open ? 'rotate-180' : ''"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-0.5 ml-3 pl-4 border-l border-white/10 space-y-0.5" style="display:none;">
                    <a href="{{ route('unit.pr.index') }}"
                       class="nav-sub-link {{ request()->is('unit/purchase-requests*') ? 'nav-sub-link--active' : '' }}">
                        Manage Requests
                    </a>
                    <a href="{{ route('unit.procurement-history') }}"
                       class="nav-sub-link {{ request()->is('unit/procurement-history') ? 'nav-sub-link--active' : '' }}">
                        Procurement History
                    </a>
                </div>
            </div>

            {{-- Notifications --}}
            <a href="{{ route('unit.notifications') }}"
               class="nav-link {{ request()->is('unit/notifications') ? 'nav-link--active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 21a2 2 0 0 0 4 0"/>
                </svg>
                <span>Notifications</span>
            </a>

            {{-- Account --}}
            <a href="{{ route('unit.profile') }}"
               class="nav-link {{ request()->is('unit/profile*') || request()->is('change-password') ? 'nav-link--active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <circle cx="12" cy="8" r="4"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 20a6 6 0 0 1 12 0"/>
                </svg>
                <span>Account</span>
            </a>
        </nav>

        {{-- User footer --}}
        <div class="flex-shrink-0 px-4 py-4 border-t border-white/10">
            @if($sidebarFundSources->isNotEmpty())
                <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-2">
                    {{ $sidebarFundSources->count() }} fund source(s)
                </p>
            @endif
            <div class="text-xs text-slate-500 uppercase tracking-wider mb-1">Account</div>
            <div class="text-sm text-white font-medium truncate">{{ auth()->user()->name ?? 'Unit User' }}</div>
            <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</div>
        </div>
    </aside>

    {{-- ─── MAIN AREA ────────────────────────────────────────── --}}
    <div class="flex flex-col flex-1 min-w-0">

        {{-- Top header --}}
        <header class="flex-shrink-0 flex items-center h-14 px-4 gap-3 bg-[#000e2c] border-b border-white/10">

            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = true"
                    class="lg:hidden p-1.5 rounded text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- System name + badge --}}
            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                <span class="text-white text-sm font-medium truncate hidden sm:block">
                    Purchase Request Consolidating Management System
                </span>
                <span class="text-sm font-medium truncate sm:hidden text-white">ConsoliData</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/20 text-amber-400 ring-1 ring-amber-500/30 flex-shrink-0">
                    Unit
                </span>
            </div>

            {{-- User + logout --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <span class="text-slate-400 text-sm hidden md:block">{{ auth()->user()->name ?? '' }}</span>
                <form method="POST" action="/logout" data-confirm="Are you sure you want to log out?">
                    @csrf
                    <button type="submit"
                            class="text-sm text-slate-300 hover:text-white px-3 py-1.5 rounded-md hover:bg-white/10 transition-colors border border-white/10 hover:border-white/20">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto">
            <div class="max-w-screen-xl mx-auto px-4 sm:px-6 py-6">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
                @if(session('info'))
                    <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        <p>{{ session('info') }}</p>
                    </div>
                @endif
                @if(isset($errors) && $errors->any())
                    <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10 14l2-2m0 0l2-2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                        </svg>
                        <p>{{ $errors->first() }}</p>
                    </div>
                @endif

                @yield('content')

                <footer class="mt-10 pt-5 border-t border-slate-200 text-center text-xs text-slate-400">
                    © {{ date('Y') }} Purchase Request Consolidating Management System — ConsoliData
                </footer>
            </div>
        </main>
    </div>
</div>

{{-- Confirm modal --}}
@include('partials.system-confirm-modal')

<style>
    /* Sidebar nav link styles */
    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #94a3b8;
        transition: background-color 0.15s, color 0.15s;
        text-decoration: none;
    }
    .nav-link:hover {
        background-color: rgba(255,255,255,0.06);
        color: #fff;
    }
    .nav-link--active {
        background-color: rgba(245,158,11,0.15);
        color: #fbbf24;
    }
    .nav-link--active:hover {
        background-color: rgba(245,158,11,0.2);
        color: #fcd34d;
    }
    .nav-sub-link {
        display: block;
        padding: 0.4rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #94a3b8;
        transition: background-color 0.15s, color 0.15s;
        text-decoration: none;
    }
    .nav-sub-link:hover {
        background-color: rgba(255,255,255,0.06);
        color: #fff;
    }
    .nav-sub-link--active {
        color: #fbbf24;
        background-color: rgba(245,158,11,0.12);
    }

    /* Shared form field styles */
    .field-label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.375rem;
    }
    .field-input {
        display: block;
        width: 100%;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        background-color: #fff;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        color: #111827;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .field-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
    }
    .field-input:disabled {
        background-color: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
    }
    .field-hint {
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: #6b7280;
    }

    /* Shared card style */
    .ui-card {
        background: #fff;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        margin-bottom: 1.25rem;
    }
    .ui-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 1rem;
    }

    /* Status badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .status-draft     { background:#f1f5f9; color:#475569; outline: 1px solid #cbd5e1; }
    .status-submitted { background:#eff6ff; color:#1d4ed8; outline: 1px solid #bfdbfe; }
    .status-approved  { background:#f0fdf4; color:#166534; outline: 1px solid #bbf7d0; }
    .status-confirmed { background:#eef2ff; color:#3730a3; outline: 1px solid #c7d2fe; }
    .status-completed { background:#f0fdf4; color:#14532d; outline: 1px solid #86efac; }
    .status-disapproved,
    .status-canceled  { background:#fef2f2; color:#991b1b; outline: 1px solid #fecaca; }
    .status-ongoing   { background:#f5f3ff; color:#5b21b6; outline: 1px solid #ddd6fe; }

    /* Data table */
    .data-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
    .data-table th {
        padding: 0.625rem 0.875rem;
        text-align: left;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .data-table td {
        padding: 0.75rem 0.875rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .data-table tbody tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background-color: #f9fafb; }

    /* Buttons */
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        background-color: #2563eb;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: background-color 0.15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        background-color: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
        cursor: pointer;
        transition: background-color 0.15s, border-color 0.15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-secondary:hover { background-color: #f9fafb; border-color: #9ca3af; }
    .btn-success {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        background-color: #059669;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: background-color 0.15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-success:hover { background-color: #047857; }
    .btn-danger {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        background-color: #dc2626;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: background-color 0.15s;
        text-decoration: none;
        white-space: nowrap;
    }
    .btn-danger:hover { background-color: #b91c1c; }
    .btn-sm {
        padding: 0.3rem 0.625rem;
        font-size: 0.75rem;
    }
</style>

<script>
    function unitLayout() {
        return {
            sidebarOpen: false,
            init() {
                document.querySelectorAll('aside nav a').forEach(link => {
                    link.addEventListener('click', () => { this.sidebarOpen = false; });
                });
            }
        };
    }
</script>

</body>
</html>
