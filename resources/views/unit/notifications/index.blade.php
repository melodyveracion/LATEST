@extends('unit.layout')

@section('title', 'Notifications - ConsoliData')

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div>
            <h1>Notifications</h1>
            <p>Review approval, disapproval, and status notifications related to your PPMP and purchase requests.</p>
        </div>
    </div>

    <div class="panel-card">
        <div class="inline-actions" style="justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h2 style="margin:0;">Notification Feed</h2>
            <span class="muted-text">{{ $notifications->count() }} notification(s)</span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Title</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        @php
                            $statusClass = match ($notification->status) {
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
                            <td>{{ \Illuminate\Support\Carbon::parse($notification->created_at)->format('M d, Y h:i A') }}</td>
                            <td>{{ $notification->type }}</td>
                            <td><span class="{{ $statusClass }}">{{ $notification->status }}</span></td>
                            <td>{{ $notification->title }}</td>
                            <td>{{ $notification->message }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;">No notifications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
