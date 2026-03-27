@extends('bac.layout')

@section('title', 'BAC Biddings - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Bidding Process</h1>
        <p>Record supplier bids for consolidated items and award the winning supplier.</p>
    </div>
</div>

<div class="page-card">
    <h2>Record New Bid</h2>
    <form action="{{ route('bac.biddings.store') }}" method="POST" data-confirm="Save this supplier bid?">
        @csrf

        <label for="consolidated_item_id">Consolidated Item</label>
        <select id="consolidated_item_id" name="consolidated_item_id" required>
            <option value="">Select Consolidated Item</option>
            @foreach($consolidatedItems as $item)
                <option value="{{ $item->id }}">
                    #{{ $item->id }} | {{ $item->item_name }} | Qty {{ $item->total_quantity }} | {{ $item->status }}
                </option>
            @endforeach
        </select>

        <label for="supplier_name">Supplier Name</label>
        <input type="text" id="supplier_name" name="supplier_name" required>

        <label for="bid_amount">Bid Amount</label>
        <input type="number" id="bid_amount" name="bid_amount" min="0" step="0.01" required>

        <label for="remarks">Remarks</label>
        <textarea id="remarks" name="remarks" rows="3"></textarea>

        <div class="action-row">
            <button type="submit" class="btn-create">Save Bid</button>
        </div>
    </form>
</div>

<div class="page-card">
    <h2>Recorded Bids</h2>
    <table>
        <thead>
            <tr>
                <th>Bid ID</th>
                <th>Consolidated Item</th>
                <th>Supplier</th>
                <th>Bid Amount</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Award</th>
            </tr>
        </thead>
        <tbody>
            @forelse($biddings as $bid)
                <tr>
                    <td>#{{ $bid->id }}</td>
                    <td>{{ $bid->item_name ?: ('Item #' . $bid->consolidated_item_id) }}</td>
                    <td>{{ $bid->supplier_name }}</td>
                    <td>{{ number_format($bid->bid_amount, 2) }}</td>
                    <td>{{ $bid->status }}</td>
                    <td>{{ $bid->bid_submitted_at ? \Illuminate\Support\Carbon::parse($bid->bid_submitted_at)->format('M d, Y h:i A') : '-' }}</td>
                    <td>
                        @if($bid->status !== 'Won')
                            <form action="{{ route('bac.biddings.award', $bid->id) }}" method="POST" data-confirm="Award this supplier as the winning bid?">
                                @csrf
                                <button type="submit" class="btn-primary">Award Supplier</button>
                            </form>
                        @else
                            Awarded
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">No bids recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
