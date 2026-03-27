@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div>
        <h1 class="page-title">Purchase Request Reports</h1>
        <p class="page-subtitle">
            Track request volume by status and department, then review the latest activity without leaving the reports page.
        </p>
    </div>

    <div class="panel-card">
        <h2>Status Summary</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statusCounts as $row)
                        @php
                            $statusClass = match ($row->status) {
                                'Submitted' => 'status-badge status-submitted',
                                'Approved' => 'status-badge status-approved',
                                'Confirmed' => 'status-badge status-confirmed',
                                'Completed' => 'status-badge status-completed',
                                'Disapproved' => 'status-badge status-disapproved',
                                'Canceled' => 'status-badge status-disapproved',
                                'On-Going' => 'status-badge status-confirmed',
                                default => 'status-badge status-draft',
                            };
                        @endphp
                        <tr>
                            <td><span class="{{ $statusClass }}">{{ $row->status }}</span></td>
                            <td>{{ $row->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;">No status data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h2>Department Summary</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Department / Unit</th>
                        <th>Total Requests</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departmentCounts as $row)
                        <tr>
                            <td>{{ $row->department_name }}</td>
                            <td>{{ $row->total_requests }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" style="text-align:center;">No department data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h2>Recent Requests</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Requester</th>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $requestItem)
                        @php
                            $statusClass = match ($requestItem->status) {
                                'Submitted' => 'status-badge status-submitted',
                                'Approved' => 'status-badge status-approved',
                                'Confirmed' => 'status-badge status-confirmed',
                                'Completed' => 'status-badge status-completed',
                                'Disapproved' => 'status-badge status-disapproved',
                                'Canceled' => 'status-badge status-disapproved',
                                'On-Going' => 'status-badge status-confirmed',
                                default => 'status-badge status-draft',
                            };
                        @endphp
                        <tr>
                            <td>#{{ $requestItem->id }}</td>
                            <td>{{ $requestItem->user_name ?? 'Unknown User' }}</td>
                            <td>{{ $requestItem->department_name ?? 'Unassigned' }}</td>
                            <td>{{ $requestItem->fund_source_name ?? 'Unassigned' }}</td>
                            <td><span class="{{ $statusClass }}">{{ $requestItem->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;">No recent requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
