@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div class="page-header page-header--actions">
        <div>
            <h1 class="page-title">Create User Account</h1>
            <p class="page-subtitle">
                Add a unit or BAC account. One user per unit; that user accesses all fund sources under the assigned unit.
            </p>
        </div>
        <a href="/admin/users" class="btn-edit">← Back to Users</a>
    </div>

    <div class="panel-card form-panel">
        <form method="POST" action="/admin/users/store">
            @csrf

            <div class="detail-grid">
                <div>
                    <label for="roleSelect">Select Role</label>
                    <select name="role" id="roleSelect" required>
                        <option value="">-- Select Role --</option>
                        <option value="unit" {{ old('role') === 'unit' ? 'selected' : '' }}>Unit / College</option>
                        <option value="bac" {{ old('role') === 'bac' ? 'selected' : '' }}>BAC</option>
                    </select>
                </div>
            </div>

            <div id="commonFields" style="display:none; margin-top:18px;">
                <div class="detail-grid">
                    <div>
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label>Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}">
                    </div>
                    <div>
                        <label>Account Status</label>
                        <select name="status">
                            <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="unitFields" style="display:none; margin-top:18px;">
                <div class="detail-grid">
                    <div>
                        <label>College / Unit</label>
                        <select name="department_unit_id" id="departmentSelect">
                            <option value="">Select College</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ (string) old('department_unit_id') === (string) $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="helper-text" style="margin-top:8px;">
                            Only one unit user can be created for each college or unit.
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-row">
                <button type="submit" class="btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const unitFields = document.getElementById('unitFields');
    const commonFields = document.getElementById('commonFields');

    function syncRoleFields() {

        if (roleSelect.value === 'unit') {
            commonFields.style.display = 'block';
            unitFields.style.display = 'block';
        }
        else if (roleSelect.value === 'bac') {
            commonFields.style.display = 'block';
            unitFields.style.display = 'none';
        }
        else {
            commonFields.style.display = 'none';
            unitFields.style.display = 'none';
        }
    }

    roleSelect.addEventListener('change', syncRoleFields);
    syncRoleFields();
</script>

@endsection