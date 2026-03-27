@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div>
        <h1 class="page-title">View Unit PPMP</h1>
        <p class="page-subtitle">
            Browse all unit-submitted PPMP records with owner, assignment, review remarks, and fiscal year details in one table.
        </p>
    </div>

    <div class="panel-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>PPMP No.</th>
                        <th>Fiscal Year</th>
                        <th>Owner</th>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Review Remarks</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ppmps as $ppmp)
                        @php
                            $statusClass = match ($ppmp->status) {
                                'Submitted' => 'status-badge status-submitted',
                                'Approved' => 'status-badge status-approved',
                                default => 'status-badge status-draft',
                            };
                        @endphp
                        <tr>
                            <td>{{ $ppmp->ppmp_no ?: ('Draft Ref #' . $ppmp->id) }}</td>
                            <td>{{ $ppmp->fiscal_year ?: '-' }}</td>
                            <td>{{ $ppmp->user_name ?? 'Unknown User' }}</td>
                            <td>{{ $ppmp->department_name ?? 'Unassigned' }}</td>
                            <td>{{ $ppmp->fund_source_name ?? 'Unassigned' }}</td>
                            <td>{{ $ppmp->items_count }}</td>
                            <td><span class="{{ $statusClass }}">{{ $ppmp->status }}</span></td>
                            <td>{{ $ppmp->review_remarks ?: '-' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($ppmp->created_at)->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;">No unit PPMP records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
