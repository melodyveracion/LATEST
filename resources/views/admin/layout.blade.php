<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - ConsoliData')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/portal.css') }}">
    @include('auth.partials.no-cache-guard')
</head>
<body>
    <header class="app-header">
        <div class="app-header-inner">
            <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                <span class="app-header-title">Purchase Request Consolidating Management System</span>
                <span class="portal-badge" style="background:rgba(255,255,255,0.2);color:#e2e8f0;">Admin</span>
            </div>
            <div class="app-header-right">
                <div class="sidebar-toggle-wrap sidebar-toggle-mobile">@include('partials.portal-sidebar-toggle')</div>
                <span class="user-text">Logged in as <strong>{{ auth()->user()->name ?? 'Admin' }}</strong></span>
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
                            <small>Admin Portal</small>
                        </div>
                    </div>
                    <div class="sidebar-toggle-wrap sidebar-toggle-desktop">@include('partials.portal-sidebar-toggle')</div>
                </div>

                @php
                $ppmpOpen = request()->is('admin/ppmp*');
                $purchaseRequestOpen = request()->is('admin/validate-request*') || request()->is('admin/consolidated-request*');
                @endphp

                <nav class="sidebar-nav" aria-label="Admin navigation">
                    <ul class="sidebar-menu">
                        <li class="sidebar-menu-item">
                            <a href="/admin/dashboard" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
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
                                    <li><a href="/admin/ppmp/validate" class="{{ request()->is('admin/ppmp/validate*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="m9 12 2 2 4-4"/><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"/><path d="M14 2v5h5"/></svg></span><span class="nav-label">Validate PPMP</span></a></li>
                                    <li><a href="/admin/ppmp/consolidated" class="{{ request()->is('admin/ppmp/consolidated*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="m12 3 9 4.5-9 4.5-9-4.5L12 3z"/><path d="m3 12 9 4.5 9-4.5"/><path d="m3 16.5 9 4.5 9-4.5"/></svg></span><span class="nav-label">View Consolidated PPMP</span></a></li>
                                    <li><a href="/admin/ppmp/unit" class="{{ request()->is('admin/ppmp/unit*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M4 21V7l8-4 8 4v14"/><path d="M3 21h18"/><path d="M9 21v-4h6v4"/><path d="M8 10h2"/><path d="M14 10h2"/><path d="M8 14h2"/><path d="M14 14h2"/></svg></span><span class="nav-label">View Unit PPMP</span></a></li>
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
                                    <li><a href="/admin/validate-request" class="{{ request()->is('admin/validate-request*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M9 5h6"/><path d="M9 9h6"/><path d="M9 13h3"/><path d="M9 3a2 2 0 0 0 6 0"/><path d="M7 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1"/><path d="m15 16 2 2 4-4"/></svg></span><span class="nav-label">Validate Request</span></a></li>
                                    <li><a href="/admin/consolidated-request" class="{{ request()->is('admin/consolidated-request*') ? 'active' : '' }}"><span class="nav-icon"><svg viewBox="0 0 24 24"><path d="M9 5h6"/><path d="M9 9h6"/><path d="M9 13h6"/><path d="M9 17h4"/><path d="M9 3a2 2 0 0 0 6 0"/><path d="M7 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-1"/></svg></span><span class="nav-label">Consolidated Request</span></a></li>
                                </ul>
                            </details>
                        </li>
                        <li class="sidebar-menu-item">
                            <a href="/admin/reports" class="{{ request()->is('admin/reports*') ? 'active' : '' }}">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 20h16"/><path d="M7 16v-5"/><path d="M12 16V8"/><path d="M17 16v-8"/></svg></span>
                                <span class="nav-label">Manage Report</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item">
                            <a href="/admin/users" class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                                <span class="nav-label">Manage Users</span>
                            </a>
                        </li>
                        <li class="sidebar-menu-item">
                            <a href="{{ route('admin.profile') }}" class="{{ request()->is('admin/profile*') || request()->is('change-password') ? 'active' : '' }}">
                                <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M6 20a6 6 0 0 1 12 0"/></svg></span>
                                <span class="nav-label">Account Management</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <div class="sidebar-account">
                    <span class="sidebar-account-label">ACCOUNT</span>
                    <span class="sidebar-account-name"><strong>{{ auth()->user()->name ?? 'Admin' }}</strong></span>
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
