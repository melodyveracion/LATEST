@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div class="inline-actions" style="justify-content:space-between; align-items:flex-start;">
        <div>
            <h1 class="page-title">Consolidated PPMP</h1>
            <p class="page-subtitle">
                Review procurement plan totals grouped by unit, fund source, and status, then print a clean consolidated view.
            </p>
        </div>
        <a href="{{ route('admin.ppmp.consolidated.print') }}" target="_blank" rel="noopener" class="btn-primary">Print Summary</a>
    </div>

    <div class="panel-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Status</th>
                        <th>Total PPMP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $statusClass = match ($row->status) {
                                'Submitted' => 'status-badge status-submitted',
                                'Approved' => 'status-badge status-approved',
                                default => 'status-badge status-draft',
                            };
                        @endphp
                        <tr>
                            <td>{{ $row->department_name }}</td>
                            <td>{{ $row->fund_source_name }}</td>
                            <td><span class="{{ $statusClass }}">{{ $row->status }}</span></td>
                            <td>{{ $row->total_ppmps }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;">No consolidated PPMP data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
