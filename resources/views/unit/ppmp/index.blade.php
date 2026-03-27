@extends('unit.layout')

@section('title', 'Manage PPMP - ConsoliData')

@section('content')
<div class="page-shell unit-ppmp-page">
    <div class="page-header">
        <div>
            <h1>Manage PPMP</h1>
            <p>Manage draft PPMPs, submit them for review, and create purchase requests from approved plans.</p>
        </div>
    </div>

    <div class="panel-card panel-card--table">
        <div class="panel-header">
            <div class="inline-actions">
                <h2>PPMP Records</h2>
                <span class="muted-text">{{ $ppmps->count() }} record(s)</span>
            </div>
            <div class="panel-actions">
                <a href="{{ route('unit.ppmp.create') }}" class="btn btn-primary">Create PPMP</a>
                <a href="{{ route('unit.ppmp.uploadForm') }}" class="btn btn-secondary">Upload PPMP CSV</a>
            </div>
        </div>

        <form method="GET" action="{{ route('unit.ppmp.index') }}" class="filter-bar">
            <div class="filter-field">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Fiscal year, fund source, status, …" value="{{ request('search') }}">
            </div>
            <div class="filter-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['Draft', 'Submitted', 'Approved', 'Disapproved'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="fund_source_id">Fund Source</label>
                <select id="fund_source_id" name="fund_source_id">
                    <option value="">All fund sources</option>
                    @foreach($fundSources as $fs)
                        <option value="{{ $fs->id }}" {{ (string) request('fund_source_id') === (string) $fs->id ? 'selected' : '' }}>{{ $fs->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply</button>
                <a href="{{ route('unit.ppmp.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>PPMP No.</th>
                        <th>Fiscal Year</th>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Items</th>
                        <th>Total Budget</th>
                        <th>Status</th>
                        <th>Review Remarks</th>
                        <th>Created</th>
                        <th>Action</th>
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
                            <td>{{ $ppmp->department_name ?? 'Unassigned' }}</td>
                            <td>{{ $ppmp->fund_source_name ?? 'Unassigned' }}</td>
                            <td>{{ $ppmp->items_count }}</td>
                            <td>{{ number_format((float) $ppmp->total_estimated_budget, 2) }}</td>
                            <td><span class="{{ $statusClass }}">{{ $ppmp->status }}</span></td>
                            <td>{{ $ppmp->review_remarks ?: '-' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($ppmp->created_at)->format('M d, Y h:i A') }}</td>
                            <td>
                                <div class="inline-actions">
                                    <a href="{{ route('unit.ppmp.edit', $ppmp->id) }}" class="btn btn-primary">View / Edit</a>
                                    @if($ppmp->status === 'Approved')
                                        <a href="{{ route('unit.pr.create', ['ppmp_id' => $ppmp->id]) }}" class="btn btn-secondary">Create PR</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center;">{{ request()->hasAny(['search','status','category_id','fund_source_id']) ? 'No PPMP records matched the selected filters.' : 'No PPMP records found.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
