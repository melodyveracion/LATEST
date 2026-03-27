@extends('admin.layout')

@section('content')
<div class="page-shell validate-ppmp-page">
    <div>
        <h1 class="page-title">Validate PPMP</h1>
        <p class="page-subtitle">
            Review submitted procurement plans, inspect the full PPMP details, and return plans to the unit with remarks when corrections are needed.
        </p>
    </div>

    <div class="stats-grid">
        <div class="metric-card">
            <div class="metric-label">Total PPMP</div>
            <div class="metric-value">{{ $summary['total'] }}</div>
            <div class="metric-caption">All PPMP records currently in the system.</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Submitted</div>
            <div class="metric-value">{{ $summary['submitted'] }}</div>
            <div class="metric-caption">Ready for admin review and action.</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Approved</div>
            <div class="metric-value">{{ $summary['approved'] }}</div>
            <div class="metric-caption">Already accepted and usable for PR creation.</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Draft / Returned</div>
            <div class="metric-value">{{ $summary['draft'] }}</div>
            <div class="metric-caption">Waiting for the unit to complete or revise details.</div>
        </div>
    </div>

    <div class="panel-card panel-card--table">
        <div class="panel-header">
            <div class="inline-actions">
                <h2>PPMP Review Queue</h2>
                <span class="muted-text">{{ $ppmps->count() }}record(s)</span>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.ppmp.validate') }}" class="filter-bar">
            <div class="filter-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['Draft', 'Submitted', 'Approved'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="department_unit_id">Unit</label>
                <select id="department_unit_id" name="department_unit_id">
                    <option value="">All units</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ (string) request('department_unit_id') === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('admin.ppmp.validate') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>PPMP</th>
                        <th>Owner</th>
                        <th>Assignment</th>
                        <th>Items / Budget</th>
                        <th>Status</th>
                        <th>Review Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ppmps as $ppmp)
                    @php
                    $statusClass = match ($ppmp->status) {
                    'Approved' => 'status-badge status-approved',
                    'Submitted' => 'status-badge status-submitted',
                    default => 'status-badge status-draft',
                    };
                    @endphp
                    <tr>
                        <td class="stacked-meta stacked-meta--compact">
                            <strong>{{ $ppmp->ppmp_no ?: ('Draft Ref #' . $ppmp->id) }}</strong>
                            <small>FY {{ $ppmp->fiscal_year ?: '-' }} · {{ \Illuminate\Support\Carbon::parse($ppmp->created_at)->format('M d, Y') }}</small>
                        </td>
                        <td class="stacked-meta stacked-meta--compact">
                            <strong>{{ $ppmp->user_name ?? 'Unknown User' }}</strong>
                            <small>{{ $ppmp->user_email ?? 'No email' }}</small>
                        </td>
                        <td class="stacked-meta stacked-meta--compact">
                            <strong>{{ $ppmp->department_name ?? 'Unassigned' }}</strong>
                            <small>{{ $ppmp->fund_source_name ?? 'Unassigned' }}</small>
                        </td>
                        <td class="stacked-meta stacked-meta--compact">
                            <strong>{{ $ppmp->items_count }} item(s)</strong>
                            <small>{{ number_format((float) $ppmp->total_estimated_budget, 2) }}</small>
                        </td>
                        <td>
                            <span class="{{ $statusClass }}">{{ $ppmp->status }}</span>
                        </td>
                        <td>{{ $ppmp->review_remarks ?: 'No remarks yet.' }}</td>
                        <td>
                            <div class="action-cell">
                                <div class="action-cell__row">
                                    <a href="{{ route('admin.ppmp.show', $ppmp->id) }}" class="btn-edit">View Details</a>
                                    @if($ppmp->status === 'Submitted')
                                        <a href="{{ route('admin.ppmp.review', $ppmp->id) }}" class="btn-primary">Approve/Return</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;">No PPMP records matched the selected filters.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection