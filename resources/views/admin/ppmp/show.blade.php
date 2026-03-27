@extends('admin.layout')

@section('content')
@php
$statusClass = match ($ppmp->status) {
'Approved' => 'status-badge status-approved',
'Submitted' => 'status-badge status-submitted',
default => 'status-badge status-draft',
};
@endphp

<div class="page-shell">
    <div class="inline-actions" style="justify-content:space-between; align-items:flex-start;">
        <div>
            <h1 class="page-title">PPMP Details {{ $ppmp->ppmp_no ?: ('Draft Ref #' . $ppmp->id) }}</h1>
            <p class="page-subtitle">
                Review the PPMP header, assignment details, quarterly totals, and complete item breakdown before approving or returning it to draft.
            </p>
        </div>
        <a href="{{ route('admin.ppmp.validate') }}" class="btn-edit">Back to Validate PPMP</a>
    </div>

    <div class="stats-grid">
        <div class="metric-card">
            <div class="metric-label">Status</div>
            <div class="metric-value" style="font-size:22px;">
                <span class="{{ $statusClass }}">{{ $ppmp->status }}</span>
            </div>
            <div class="metric-caption">Current review state of this PPMP.</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Items</div>
            <div class="metric-value">{{ $totals['items_count'] }}</div>
            <div class="metric-caption">Item rows included in this procurement plan.</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Total Quantity</div>
            <div class="metric-value">{{ $totals['total_quantity'] }}</div>
            <div class="metric-caption">Combined quantity across all PPMP items.</div>
        </div>
        <div class="metric-card">
            <div class="metric-label">Total Budget</div>
            <div class="metric-value" style="font-size:26px;">{{ number_format((float) $totals['total_budget'], 2) }}</div>
            <div class="metric-caption">Estimated procurement budget for this PPMP.</div>
        </div>
    </div>

    <div class="detail-grid">
        <div class="panel-card">
            <h2 style="margin-top:0;">PPMP Header</h2>
            <div class="detail-grid">
                <div class="detail-card">
                    <span class="detail-label">Owner</span>
                    <div class="detail-value">{{ $ppmp->user_name ?? 'Unknown User' }}</div>
                    <div class="muted-text">{{ $ppmp->user_email ?? 'No email' }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">PPMP Number</span>
                    <div class="detail-value">{{ $ppmp->ppmp_no ?: 'Not assigned yet' }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Fiscal Year</span>
                    <div class="detail-value">{{ $ppmp->fiscal_year ?: '-' }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Department / Unit</span>
                    <div class="detail-value">{{ $ppmp->department_name ?? 'Unassigned' }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Fund Source</span>
                    <div class="detail-value">{{ $ppmp->fund_source_name ?? 'Unassigned' }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Created</span>
                    <div class="detail-value">{{ \Illuminate\Support\Carbon::parse($ppmp->created_at)->format('M d, Y h:i A') }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Submitted At</span>
                    <div class="detail-value">{{ $ppmp->submitted_at ? \Illuminate\Support\Carbon::parse($ppmp->submitted_at)->format('M d, Y h:i A') : 'Not yet submitted' }}</div>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <h2 style="margin-top:0;">Review Details</h2>
            <div class="detail-grid">
                <div class="detail-card">
                    <span class="detail-label">Reviewer</span>
                    <div class="detail-value">{{ $reviewerName ?: 'Not reviewed yet' }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Reviewed At</span>
                    <div class="detail-value">{{ $ppmp->reviewed_at ? \Illuminate\Support\Carbon::parse($ppmp->reviewed_at)->format('M d, Y h:i A') : 'Not reviewed yet' }}</div>
                </div>
                <div class="detail-card" style="grid-column: 1 / -1;">
                    <span class="detail-label">Review Remarks</span>
                    <div class="detail-value">{{ $ppmp->review_remarks ?: 'No review remarks yet.' }}</div>
                </div>
            </div>

            <div class="detail-grid" style="margin-top:16px;">
                <div class="detail-card">
                    <span class="detail-label">Q1 Total</span>
                    <div class="detail-value">{{ number_format((float) $totals['q1_total'], 2) }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Q2 Total</span>
                    <div class="detail-value">{{ number_format((float) $totals['q2_total'], 2) }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Q3 Total</span>
                    <div class="detail-value">{{ number_format((float) $totals['q3_total'], 2) }}</div>
                </div>
                <div class="detail-card">
                    <span class="detail-label">Q4 Total</span>
                    <div class="detail-value">{{ number_format((float) $totals['q4_total'], 2) }}</div>
                </div>
            </div>

            @if($ppmp->status === 'Submitted')
            <div style="margin-top:16px;">
                <a href="{{ route('admin.ppmp.review', $ppmp->id) }}" class="btn-primary">Approve / Return to Draft</a>
            </div>
            @endif
        </div>
    </div>

    <div class="panel-card">
        <h2 style="margin-top:0;">PPMP Item Breakdown</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>UACS</th>
                        <th>Item Name</th>
                        <th>Specifications</th>
                        <th>Q1</th>
                        <th>Q2</th>
                        <th>Q3</th>
                        <th>Q4</th>
                        <th>Total Qty</th>
                        <th>Unit</th>
                        <th>Unit Cost</th>
                        <th>Estimated Budget</th>
                        <th>Mode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->category_name ?: '-' }}</td>
                        <td>{{ $item->uacs_code ?: '-' }}</td>
                        <td>{{ $item->item_name ?: $item->description }}</td>
                        <td>{{ $item->specifications ?: '-' }}</td>
                        <td>{{ $item->quantity_q1 }}</td>
                        <td>{{ $item->quantity_q2 }}</td>
                        <td>{{ $item->quantity_q3 }}</td>
                        <td>{{ $item->quantity_q4 }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit ?: '-' }}</td>
                        <td>{{ number_format((float) $item->unit_cost, 2) }}</td>
                        <td>{{ number_format((float) $item->estimated_budget, 2) }}</td>
                        <td>{{ $item->mode_of_procurement ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" style="text-align:center;">This PPMP does not have any item rows yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection