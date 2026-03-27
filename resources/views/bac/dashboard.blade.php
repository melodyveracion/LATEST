@extends('bac.layout')

@section('title', 'BAC Dashboard - ConsoliData')

@section('content')
@php
    $totalRequests = \App\Models\PurchaseRequest::count();
    $pendingRequests = \App\Models\PurchaseRequest::whereIn('status', ['Pending', 'Submitted', 'Under Review'])->count();
    $completedRequests = \App\Models\PurchaseRequest::where('status', 'Completed')->count();
    $consolidatedItems = \App\Models\ConsolidatedItem::count();
    $awardedBids = \App\Models\Bidding::where('status', 'Won')->count();
    $deliveries = \App\Models\Delivery::count();
    $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count();
@endphp

<div class="page-shell dashboard-shell">
    <section class="page-card dashboard-hero">
        <h1>BAC Dashboard</h1>
        <p class="page-subtitle">
            Process confirmed purchase requests, manage consolidation, and track procurement activity.
        </p>
    </section>

    <div class="stat-grid stat-grid--single-row dashboard-kpi-grid cards--compact">
        <div class="stat-card">
            <h3>Total Requests</h3>
            <div class="value">{{ $totalRequests }}</div>
            <div class="caption">All purchase requests in the system</div>
        </div>
        <div class="stat-card pending">
            <h3>Pending Review</h3>
            <div class="value">{{ $pendingRequests }}</div>
            <div class="caption">Requests waiting for further BAC action</div>
        </div>
        <div class="stat-card approved">
            <h3>Completed</h3>
            <div class="value">{{ $completedRequests }}</div>
            <div class="caption">Requests with completed processing</div>
        </div>
        <div class="stat-card notice">
            <h3>Consolidated Items</h3>
            <div class="value">{{ $consolidatedItems }}</div>
            <div class="caption">Grouped lines ready for bidding</div>
        </div>
        <div class="stat-card notice">
            <h3>Awarded Bids</h3>
            <div class="value">{{ $awardedBids }}</div>
            <div class="caption">Winning supplier selections recorded</div>
        </div>
        <div class="stat-card">
            <h3>Deliveries</h3>
            <div class="value">{{ $deliveries }}</div>
            <div class="caption">Delivery and receiving records saved</div>
        </div>
        <div class="stat-card notice">
            <h3>Unread Notifications</h3>
            <div class="value">{{ $unreadNotifications }}</div>
            <div class="caption">Notifications currently assigned to your BAC account</div>
        </div>
    </div>

    <section class="page-card">
        <div class="section-heading">
            <div>
                <h2>Quick Access</h2>
                <p>
                    Process requests, manage consolidation, and access account settings.
                </p>
            </div>
        </div>

        <div class="quick-links">
            <a href="{{ route('bac.pr.index') }}" class="quick-link">
                <span class="quick-link-title">Purchase Requests</span>
                <span class="quick-link-text">Review request details and upload BAC notices after unit confirmation.</span>
            </a>

            <a href="{{ route('bac.notifications') }}" class="quick-link">
                <span class="quick-link-title">Notifications</span>
                <span class="quick-link-text">Read updates related to requests, notices, and procurement movement.</span>
            </a>

            <a href="{{ route('bac.profile') }}" class="quick-link">
                <span class="quick-link-title">Account Management</span>
                <span class="quick-link-text">View your BAC account details and maintain your personal access settings.</span>
            </a>
        </div>
    </section>
</div>
@endsection
