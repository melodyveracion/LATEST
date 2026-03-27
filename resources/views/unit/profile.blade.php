@extends('unit.layout')

@section('title', 'Account Management - ConsoliData')

@section('content')
<div class="page-shell">
    <div class="page-header" style="align-items: flex-start;">
        <div>
            <h1>Account Management</h1>
            <p>Review your profile or log out.</p>
        </div>
        <a href="{{ route('password.change') }}" class="btn btn-primary">Update Password</a>
    </div>

    <div class="page-card">
        <h2>Profile</h2>
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
                <span class="info-label">Department / Unit</span>
                <div class="info-value">{{ $departmentName ?: 'Not assigned yet' }}</div>
            </div>
            <div class="info-item">
                <span class="info-label">Current Fund Source View</span>
                <div class="info-value">{{ $fundSourceName ?: 'No fund sources available' }}</div>
            </div>
        </div>
    </div>

    <div class="page-card" style="display: none;">
        <h2>Change Password</h2>
        <p class="helper-text">Use a strong password and confirm your current password before saving.</p>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
            <label for="password">New Password</label>
            <input type="password" id="password" name="password" required>
            <label for="password_confirmation">Confirm New Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>
@endsection
