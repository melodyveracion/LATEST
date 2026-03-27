@extends('unit.layout')

@section('title', 'Unit Dashboard - ConsoliData')

@section('content')
<div class="page-shell dashboard-shell">
    <section class="page-card">
        <div class="section-heading">
            <div>
                <h2>Fund Source Selection</h2>
                <p>
                    Select a fund source to filter data. PPMP and purchase request pages will follow your selection.
                </p>
            </div>
        </div>

        @if($fundSources->isEmpty())
            <p class="helper-text">
                No fund sources are available for this unit yet. Ask the administrator to complete the unit reference data first.
            </p>
        @else
            <div class="fund-source-buttons">
                <form method="POST" action="{{ route('unit.fund-source.set') }}" class="fund-source-form">
                    @csrf
                    <input type="hidden" name="fund_source_id" value="">
                    <button type="submit" class="btn fund-source-btn {{ !$activeFundSourceId ? 'fund-source-btn--active' : '' }}">
                        All Fund Sources
                    </button>
                </form>
                @foreach($fundSources as $fundSource)
                    <form method="POST" action="{{ route('unit.fund-source.set') }}" class="fund-source-form">
                        @csrf
                        <input type="hidden" name="fund_source_id" value="{{ $fundSource->id }}">
                        <button type="submit" class="btn fund-source-btn {{ (string) $activeFundSourceId === (string) $fundSource->id ? 'fund-source-btn--active' : '' }}">
                            {{ $fundSource->name }}
                        </button>
                    </form>
                @endforeach
            </div>

            <div class="info-item fund-source-scope-card">
                <span class="info-label">Current Scope</span>
                <div class="info-value">{{ $currentFundSourceScope ?: 'No fund sources available' }}</div>
                <div class="info-subtext">
                    PPMP, purchase request, and remaining-balance pages will follow this selection.
                </div>
            </div>
        @endif
    </section>

    <div class="stat-grid stat-grid--single-row-6 dashboard-kpi-grid cards--compact">
        <div class="stat-card">
            <h3>My PPMP</h3>
            <div class="value">{{ $ppmpCount }}</div>
            <div class="caption">Count for the current fund source view.</div>
        </div>

        <div class="stat-card">
            <h3>My Purchase Requests</h3>
            <div class="value">{{ $prCount }}</div>
            <div class="caption">All requests inside the current fund source view.</div>
        </div>

        <div class="stat-card pending">
            <h3>Pending Requests</h3>
            <div class="value">{{ $pendingPrCount }}</div>
            <div class="caption">Requests waiting for admin action.</div>
        </div>

        <div class="stat-card notice">
            <h3>Approved PR</h3>
            <div class="value">{{ $approvedPrCount }}</div>
            <div class="caption">Requests approved and waiting for unit confirmation.</div>
        </div>

        <div class="stat-card notice">
            <h3>Remaining PPMP Balance</h3>
            <div class="value value-compact">{{ number_format($remainingPpmpBalance, 2) }}</div>
            <div class="caption">Approved PPMP budget minus confirmed and completed requests.</div>
        </div>

        <div class="stat-card notice">
            <h3>Unread Notifications</h3>
            <div class="value">{{ $unreadNotifications }}</div>
            <div class="caption">Unread updates assigned to your unit account.</div>
        </div>
    </div>

    <section class="page-card">
        <div class="section-heading">
            <div>
                <h2>Quick Access</h2>
                <p>
                    Manage PPMP, prepare purchase requests, and track procurement activity.
                </p>
            </div>
        </div>

        <div class="quick-links">
            <a href="{{ route('unit.ppmp.index') }}" class="quick-link">
                <span class="quick-link-title">Manage PPMP</span>
                <span class="quick-link-text">Create, maintain, and submit PPMP records under the fund source you are working on.</span>
            </a>

            <a href="{{ route('unit.pr.index') }}" class="quick-link">
                <span class="quick-link-title">Manage Requests</span>
                <span class="quick-link-text">Prepare and monitor purchase requests derived from approved PPMP items.</span>
            </a>

            <a href="{{ route('unit.ppmp.remaining') }}" class="quick-link">
                <span class="quick-link-title">Remaining PPMP</span>
                <span class="quick-link-text">Check which approved PPMP items still remain available for request preparation.</span>
            </a>

            <a href="{{ route('unit.procurement-history') }}" class="quick-link">
                <span class="quick-link-title">Procurement History</span>
                <span class="quick-link-text">Review historical request movement, notices, and completed procurement activity.</span>
            </a>

            <a href="{{ route('unit.notifications') }}" class="quick-link">
                <span class="quick-link-title">Notifications</span>
                <span class="quick-link-text">Read approval updates, remarks, and BAC-related notices assigned to your account.</span>
            </a>
        </div>
    </section>
</div>
@endsection
