@extends('bac.layout')

@section('title', 'BAC Consolidation - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Consolidation of Requests</h1>
        <p>Group confirmed purchase request items into consolidated procurement lines for bidding.</p>
    </div>
</div>

<div class="page-card">
    <h2>Generate Consolidated Items</h2>
    <p class="helper-text">
        This process collects confirmed purchase request items that have not yet been consolidated and groups matching items together.
    </p>

    <form action="{{ route('bac.consolidation.generate') }}" method="POST" data-confirm="Generate consolidated items from all eligible confirmed purchase requests?">
        @csrf
        <button type="submit" class="btn-create">Generate Consolidation</button>
    </form>
</div>

<div class="page-card">
    <h2>Eligible Confirmed Request Items</h2>
    <table>
        <thead>
            <tr>
                <th>PR Ref</th>
                <th>Department</th>
                <th>Category</th>
                <th>Item</th>
                <th>Specifications</th>
                <th>Unit</th>
                <th>Quantity</th>
                <th>Estimated Budget</th>
            </tr>
        </thead>
        <tbody>
            @forelse($eligibleItems as $item)
                <tr>
                    <td>#{{ $item->purchase_request_id }}</td>
                    <td>{{ $item->department_name ?: '-' }}</td>
                    <td>{{ $item->category_name ?: '-' }}</td>
                    <td>{{ $item->item_name ?: '-' }}</td>
                    <td>{{ $item->specifications ?: '-' }}</td>
                    <td>{{ $item->unit ?: '-' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->estimated_budget, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No confirmed request items are waiting for consolidation.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="page-card">
    <h2>Current Consolidated Items</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Item</th>
                <th>Unit</th>
                <th>Total Quantity</th>
                <th>Unit Price</th>
                <th>Estimated Budget</th>
                <th>Sources</th>
                <th>Bids</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consolidatedItems as $item)
                <tr>
                    <td>#{{ $item->id }}</td>
                    <td>{{ $item->category_name ?: '-' }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->unit ?: '-' }}</td>
                    <td>{{ $item->total_quantity }}</td>
                    <td>{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ number_format($item->estimated_budget, 2) }}</td>
                    <td>{{ $item->sources_count }}</td>
                    <td>{{ $item->bids_count }}</td>
                    <td>{{ $item->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">No consolidated items created yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
