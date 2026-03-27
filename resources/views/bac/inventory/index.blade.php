@extends('bac.layout')

@section('title', 'BAC Inventory - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Inventory Update</h1>
        <p>Delivered items are added here automatically when BAC records a delivery.</p>
    </div>
</div>

<div class="page-card">
    <h2>Current Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category</th>
                <th>Item</th>
                <th>Unit</th>
                <th>Quantity On Hand</th>
                <th>Last Delivery</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventoryItems as $item)
                <tr>
                    <td>#{{ $item->id }}</td>
                    <td>{{ $item->category_name ?: '-' }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->unit ?: '-' }}</td>
                    <td>{{ $item->quantity_on_hand }}</td>
                    <td>{{ $item->delivery_date ? \Illuminate\Support\Carbon::parse($item->delivery_date)->format('M d, Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No inventory records yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
