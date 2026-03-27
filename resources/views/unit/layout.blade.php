<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Unit - ConsoliData')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>
    @php
        $sidebarFundSources = \App\Support\UnitFundDepartmentResolver::fundSourcesForDepartment(auth()->user()?->department_unit_id);
        $sidebarActiveFundSource = $sidebarFundSources->firstWhere('id', (int) session('unit_active_fund_source_id'));
    @endphp

    <header class="app-header">
        <div class="app-header-inner">
            <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                <span class="app-header-title">Purchase Request Consolidating Management System</span>
                <span class="portal-badge" style="background:rgba(255,255,255,0.2);color:#e2e8f0;">Unit</span>
            </div>
            <div class="app-header-right">
                <div class="sidebar-toggle-wrap sidebar-toggle-mobile">@include('partials.portal-sidebar-toggle')</div>
                <span class="user-text">Logged in as <strong>{{ auth()->user()->name ?? 'Unit User' }}</strong></span>
                <form method="POST" action="/logout" style="margin:0" data-confirm="Are you sure you want to log out?">
                    @csrf
                    <button type="submit" class="btn-header-logout">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <div class="app-body">
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
        <div class="sidebar-expand-tab sidebar-toggle-wrap sidebar-toggle-desktop">@include('partials.portal-sidebar-toggle')</div>
        <div class="container">
            <aside class="sidebar">
                <div class="brand-row">
                    <div class="brand-mark">
                        <div class="brand-copy">
                            <h2>ConsoliData</h2>
                        </div>
                    </div>
                    <div class="sidebar-toggle-wrap sidebar-toggle-desktop">@include('partials.portal-sidebar-toggle')</div>
                </div>

                @php
                $ppmpOpen = request()->is('unit/ppmp*') || request()->is('unit/ppmp-remaining');
                $purchaseRequestOpen = request()->is('unit/purchase-requests*') || request()->is('unit/procurement-history');
                @endphp

                <nav class="sidebar-nav" aria-label="Unit navigation">
                    <ul class="sidebar-menu">
                        <li class="sidebar-menu-item">
                            <a href="{{ route('unit.dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="4" rx="1.5"/><rect x="14" y="10" width="7" height="11" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg></span>
                                <span class="nav-label">Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item">
                            <details class="sidebar-group" @if($ppmpOpen) open @endif>
                                <summary>
                                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/><path d="M14 2v5h5"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/></svg></span>
                                    <span class="nav-label">PPMP</span>
                                    <span class="group-caret" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></span>
                                </summary>
                                <ul class="sidebar-submenu">
                                    <li><a href="{{ route('unit.ppmp.index') }}" class="{{ request()->is('unit/ppmp*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/><path d="M14 2v5h5"/><path d="M9 9h2"/><path d="M9 13h6"/><path d="M9 17h6"/></svg></span><span class="nav-label">Manage PPMP</span></a></li>
                                    <li><a href="{{ route('unit.ppmp.remaining') }}" class="{{ request()->is('unit/ppmp-remaining') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg></span><span class="nav-label">Remaining PPMP Items</span></a></li>
                                </ul>
                            </details>
                        </li>
                        <li class="sidebar-menu-item">
                            <details class="sidebar-group" @if($purchaseRequestOpen) open @endif>
                                <summary>
                                    <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 5h6"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/><path d="M9 3a2 2 0 0 0 6 0"/><path d="M7 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1"/></svg></span>
                                    <span class="nav-label">Purchase Request</span>
                                    <span class="group-caret" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></span>
                                </summary>
                                <ul class="sidebar-submenu">
                                    <li><a href="{{ route('unit.pr.index') }}" class="{{ request()->is('unit/purchase-requests*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M9 5h6"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/><path d="M9 3a2 2 0 0 0 6 0"/><path d="M7 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1"/></svg></span><span class="nav-label">Manage Requests</span></a></li>
                                    <li><a href="{{ route('unit.procurement-history') }}" class="{{ request()->is('unit/procurement-history') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 3"/></svg></span><span class="nav-label">Procurement History</span></a></li>
                                </ul>
                            </details>
                        </li>
                        <li class="sidebar-menu-item">
                            <a href="{{ route('unit.notifications') }}" class="{{ request()->is('unit/notifications') ? 'active' : '' }}">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/><path d="M10 21a2 2 0 0 0 4 0"/></svg></span>
                                <span class="nav-label">Notifications</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item">
                            <a href="{{ route('unit.profile') }}" class="{{ request()->is('unit/profile*') || request()->is('change-password') ? 'active' : '' }}">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/></svg></span>
                                <span class="nav-label">Account Management</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                @if($sidebarFundSources->isNotEmpty())
                    <span class="nav-muted">{{ $sidebarFundSources->count() }} fund source(s)</span>
                @endif

                <div class="sidebar-account">
                    <span class="sidebar-account-label">ACCOUNT</span>
                    <span class="sidebar-account-name"><strong>{{ auth()->user()->name ?? 'Unit User' }}</strong></span>
                </div>
            </aside>

            <main class="main-content">
                <div class="content-inner">
                    @if (session('success'))<div class="alert-success">{{ session('success') }}</div>@endif
                    @if (session('error'))<div class="alert-danger">{{ session('error') }}</div>@endif
                    @if (session('info'))<div class="alert-info">{{ session('info') }}</div>@endif
                    @if (isset($errors) && $errors->any())<div class="alert-danger">{{ $errors->first() }}</div>@endif
                    @yield('content')
                    <footer class="app-footer">© {{ date('Y') }} Purchase Request Consolidating Management System — ConsoliData</footer>
                </div>
            </main>
        </div>
    </div>

    @include('partials.system-confirm-modal')
    @include('partials.portal-sidebar-toggle-script')
    <script>
        document.querySelectorAll(".sidebar a").forEach(function(link) {
            link.addEventListener("click", function() { document.body.classList.remove("sidebar-open"); });
        });
    </script>
</body>
</html>
