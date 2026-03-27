@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div class="page-header page-header--actions">
        <div>
            <h1 class="page-title">Manage Users</h1>
            <p class="page-subtitle">
                Unit and BAC accounts. Each unit has one user with access to all fund sources under that unit.
            </p>
        </div>
        <a href="/admin/users/create" class="btn-primary">+ Create User</a>
    </div>

    <div class="panel-card panel-card--table">
        <div class="table-responsive">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department / Unit</th>
                        <th>Fund Source Access</th>
                        <th>Status</th>
                        <th width="200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="stacked-meta">
                                <strong>{{ $user->name }}</strong><br>
                                <small>Contact: {{ $user->contact_number ?: 'N/A' }}</small>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ strtoupper($user->role) }}</td>
                            <td>{{ $user->department_name ?: 'Not assigned' }}</td>
                            <td>
                                @if($user->role === 'unit')
                                    {{ $user->department_name ? 'All fund sources under assigned unit' : 'Unit not assigned yet' }}
                                @else
                                    {{ $user->fund_source_name ?: 'Not applicable' }}
                                @endif
                            </td>
                            <td>
                                <span class="{{ $user->status == 'active' ? 'status-active' : 'status-inactive' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="inline-actions">
                                    <a href="/admin/users/{{ $user->id }}/edit" class="btn-edit">Edit</a>

                                    @if($user->status === 'active' && $user->id !== auth()->id())
                                        <form action="/admin/users/{{ $user->id }}/disable" method="POST" data-confirm="Are you sure you want to disable this user?">
                                            @csrf
                                            <button type="submit" class="btn-danger">Disable</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection