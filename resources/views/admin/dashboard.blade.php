@extends('admin.layout')

@section('title', 'Admin Dashboard - ConsoliData')

@section('content')
@php
    $totalPpmp = \App\Models\Ppmp::count() ?? 0;
    $totalPr = \App\Models\PurchaseRequest::count() ?? 0;
    $pendingRequests = \App\Models\PurchaseRequest::where('status', 'Pending')->count() ?? 0;
    $approvedRequests = \App\Models\PurchaseRequest::where('status', 'Approved')->count() ?? 0;
@endphp

<div class="page-shell dashboard-shell">
    <section class="page-card dashboard-hero">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">
            Monitor PPMP and purchase request activity across colleges and units. Review submissions and keep the institutional workflow moving.
        </p>
    </section>

    <div class="cards dashboard-kpi-grid cards--compact">
        <div class="card">
            <h3>Total PPMP</h3>
            <p>{{ $totalPpmp }}</p>
            <div class="metric-caption">All procurement plans currently saved in the system.</div>
        </div>

        <div class="card review">
            <h3>Total PR</h3>
            <p>{{ $totalPr }}</p>
            <div class="metric-caption">All purchase requests across units and colleges.</div>
        </div>

        <div class="card pending">
            <h3>Pending Requests</h3>
            <p>{{ $pendingRequests }}</p>
            <div class="metric-caption">Requests still waiting for review or movement.</div>
        </div>

        <div class="card approved">
            <h3>Approved Requests</h3>
            <p>{{ $approvedRequests }}</p>
            <div class="metric-caption">Requests already cleared by admin review.</div>
        </div>
    </div>

    <section class="page-card">
        <div class="section-heading">
            <div>
                <h2>Quick Access</h2>
                <p>
                    Review PPMP and purchase requests, manage reports, and maintain user accounts.
                </p>
            </div>
        </div>

        <div class="quick-links">
            <a href="/admin/validate-request" class="quick-link">
                <span class="quick-link-title">Validate Requests</span>
                <span class="quick-link-text">Review submitted purchase requests that require administrative approval or return remarks.</span>
            </a>

            <a href="/admin/ppmp/validate" class="quick-link">
                <span class="quick-link-title">Validate PPMP</span>
                <span class="quick-link-text">Check annual procurement plans before they move forward in the workflow.</span>
            </a>

            <a href="/admin/consolidated-request" class="quick-link">
                <span class="quick-link-title">Consolidated Request</span>
                <span class="quick-link-text">View the consolidated purchase request output prepared from approved records.</span>
            </a>

            <a href="/admin/reports" class="quick-link">
                <span class="quick-link-title">Manage Report</span>
                <span class="quick-link-text">Review the reporting area for monitoring, summaries, and administrative tracking.</span>
            </a>

            <a href="/admin/users" class="quick-link">
                <span class="quick-link-title">Manage Users</span>
                <span class="quick-link-text">Maintain unit and BAC accounts, assignments, and account status records.</span>
            </a>
        </div>
    </section>
</div>

@endsection