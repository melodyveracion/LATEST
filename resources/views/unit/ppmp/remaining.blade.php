@extends('unit.layout')
@section('title', 'Remaining PPMP Items — ConsoliData')

@section('content')

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Remaining PPMP Items</h1>
        <p class="text-sm text-slate-500 mt-0.5">Original PPMP quantity minus confirmed and completed PR quantities.</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

    {{-- Filters --}}
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
        <form method="GET" action="{{ route('unit.ppmp.remaining') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-40">
                <label class="field-label">Search</label>
                <input type="text" name="search" placeholder="Fiscal year, category, item…"
                       value="{{ request('search') }}" class="field-input">
            </div>
            <div class="min-w-36">
                <label class="field-label">Category</label>
                <select name="category_id" class="field-input">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-36">
                <label class="field-label">Fund Source</label>
                <select name="fund_source_id" class="field-input">
                    <option value="">All</option>
                    @foreach($fundSources as $fs)
                        <option value="{{ $fs->id }}" {{ (string) request('fund_source_id') === (string) $fs->id ? 'selected' : '' }}>{{ $fs->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-40">
                <label class="field-label">PPMP</label>
                <select name="ppmp_id" class="field-input">
                    <option value="">All approved PPMP</option>
                    @foreach($approvedPpmps as $ppmp)
                        <option value="{{ $ppmp->id }}" {{ (string) ($selectedPpmpId ?? '') === (string) $ppmp->id ? 'selected' : '' }}>
                            {{ $ppmp->ppmp_no ?: 'Draft #'.$ppmp->id }} | FY {{ $ppmp->fiscal_year ?: '—' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm">Apply</button>
                <a href="{{ route('unit.ppmp.remaining') }}" class="btn-secondary btn-sm">Clear</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>PPMP Ref</th>
                    <th>FY</th>
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
                    @php $low = $item->remaining_quantity <= 0; @endphp
                    <tr class="{{ $low ? 'bg-red-50/40' : '' }}">
                        <td class="font-medium text-slate-900">{{ $item->ppmp_no ?: 'Draft #'.$item->ppmp_reference }}</td>
                        <td>{{ $item->fiscal_year ?: '—' }}</td>
                        <td>{{ $item->category_name ?: '—' }}</td>
                        <td class="font-medium text-slate-800">{{ $item->item_name ?: $item->description }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center text-amber-600">{{ $item->used_quantity }}</td>
                        <td class="text-center font-semibold {{ $low ? 'text-red-600' : 'text-emerald-600' }}">
                            {{ $item->remaining_quantity }}
                        </td>
                        <td>{{ $item->unit ?: '—' }}</td>
                        <td>₱{{ number_format($item->estimated_budget, 2) }}</td>
                        <td class="text-amber-600">₱{{ number_format($item->used_budget, 2) }}</td>
                        <td class="font-semibold {{ $low ? 'text-red-600' : 'text-emerald-600' }}">
                            ₱{{ number_format($item->remaining_budget, 2) }}
                        </td>
                        <td><span class="status-badge status-approved">{{ $item->ppmp_status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center text-slate-400 py-12">
                            {{ request()->hasAny(['search','category_id','fund_source_id','ppmp_id'])
                                ? 'No items matched the selected filters.'
                                : 'No approved PPMP items found.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
