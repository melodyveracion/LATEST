@extends('bac.layout')

@section('title', 'BAC Profile - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Account Management</h1>
        <p>Review your BAC account information and update your password when necessary.</p>
    </div>
    <a href="{{ route('password.change') }}" class="btn btn-primary">Change Password</a>
</div>

<div class="page-card">
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Full Name</span>
            <div class="info-value">{{ auth()->user()->name }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Email Address</span>
            <div class="info-value">{{ auth()->user()->email }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Role</span>
            <div class="info-value">{{ strtoupper(auth()->user()->role ?? 'bac') }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Account Status</span>
            <div class="info-value">{{ ucfirst(auth()->user()->status ?? 'active') }}</div>
        </div>
    </div>
</div>
@endsection
