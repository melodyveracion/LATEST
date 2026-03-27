@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div>
            <h1 class="page-title">Account Management</h1>
            <p class="page-subtitle">
                Review your administrator details and move to password management when you need to secure the account.
            </p>
        </div>
        <a href="{{ route('password.change') }}" class="btn-primary">Change Password</a>
    </div>

    <div class="panel-card">
        <div class="detail-grid">
            <div class="detail-card">
                <span class="detail-label">Full Name</span>
                <div class="detail-value">{{ auth()->user()->name }}</div>
            </div>
            <div class="detail-card">
                <span class="detail-label">Email Address</span>
                <div class="detail-value">{{ auth()->user()->email }}</div>
            </div>
            <div class="detail-card">
                <span class="detail-label">Role</span>
                <div class="detail-value">{{ strtoupper(auth()->user()->role ?? 'admin') }}</div>
            </div>
            <div class="detail-card">
                <span class="detail-label">Account Status</span>
                <div class="detail-value">
                    <span class="{{ (auth()->user()->status ?? 'active') === 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ ucfirst(auth()->user()->status ?? 'active') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
