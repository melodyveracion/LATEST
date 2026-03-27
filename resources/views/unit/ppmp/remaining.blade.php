@extends('unit.layout')

@section('title', 'Remaining PPMP Items - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Remaining PPMP Items</h1>
        <p>Remaining balance is based on confirmed purchase requests only: original PPMP quantity minus confirmed/completed PR quantity.</p>
    </div>
</div>

<div class="panel-card panel-card--table">
    <form method="GET" action="{{ route('unit.ppmp.remaining') }}" class="filter-bar">
        <div class="filter-field">
            <label for="search">Search</label>
            <input type="text" id="search" name="search" placeholder="Fiscal year, fund source, category, item …" value="{{ request('search') }}">
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
        <div class="filter-field">
            <label for="ppmp_id">PPMP</label>
            <select id="ppmp_id" name="ppmp_id">
                <option value="">All approved PPMP</option>
                @foreach($approvedPpmps as $ppmp)
                    <option value="{{ $ppmp->id }}" {{ (string) $selectedPpmpId === (string) $ppmp->id ? 'selected' : '' }}>
                        {{ $ppmp->ppmp_no ?: ('Draft Ref #' . $ppmp->id) }} | FY {{ $ppmp->fiscal_year ?: '-' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">Apply</button>
            <a href="{{ route('unit.ppmp.remaining') }}" class="btn btn-secondary">Clear</a>
        </div>
    </form>

    <div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>PPMP Ref</th>
                <th>Fiscal Year</th>
                <th>Category</th>
                <th>Item Name</th>
                <th>Total Qty</th>
                <th>Used Qty</th>
                <th>Remaining Qty</th>
                <th>Unit</th>
                <th>Total Budget</th>
                <th>Used Budget</th>
                <th>Remaining Budget</th>
                <th>PPMP Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->ppmp_no ?: ('Draft Ref #' . $item->ppmp_reference) }}</td>
                    <td>{{ $item->fiscal_year ?: '-' }}</td>
                    <td>{{ $item->category_name ?: '-' }}</td>
                    <td>{{ $item->item_name ?: $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->used_quantity }}</td>
                    <td>{{ $item->remaining_quantity }}</td>
                    <td>{{ $item->unit ?: '-' }}</td>
                    <td>{{ number_format($item->estimated_budget, 2) }}</td>
                    <td>{{ number_format($item->used_budget, 2) }}</td>
                    <td>{{ number_format($item->remaining_budget, 2) }}</td>
                    <td>{{ $item->ppmp_status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;">{{ request()->hasAny(['search','category_id','fund_source_id','ppmp_id']) ? 'No items matched the selected filters.' : 'No approved PPMP items found.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
