@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div class="page-header page-header--actions">
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">
                Update account details, assignment, and status.
            </p>
        </div>
        <a href="/admin/users" class="btn-edit">← Back to Users</a>
    </div>

    <div class="panel-card form-panel">
        <form method="POST" action="/admin/users/{{ $user->id }}/update">
            @csrf

            <div class="detail-grid">
                <div>
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div>
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" required>
                </div>
                <div>
                    <label>Status</label>
                    <select name="status">
                        <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                </div>
            </div>

            @if($user->role === 'unit')
                <div class="detail-grid" style="margin-top:18px;">
                    <div>
                        <label>College</label>
                        <select name="department_unit_id" id="departmentSelect" required>
                            <option value="">Select College</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (string) old('department_unit_id', $user->department_unit_id) === (string) $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="helper-text" style="margin-top:8px;">
                            This account will be able to access all fund sources under the selected unit.
                        </div>
                    </div>
                </div>
            @endif

            <div class="action-row">
                <button type="submit" class="btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>

@endsection