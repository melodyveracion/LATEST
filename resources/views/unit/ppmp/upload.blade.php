@extends('unit.layout')

@section('title', 'Upload PPMP - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Upload PPMP</h1>
        <p>Import PPMP items from a CSV file and automatically create a submitted PPMP record for the chosen fund source.</p>
    </div>
    <a href="{{ route('unit.ppmp.index') }}" class="btn btn-primary">Back to PPMP List</a>
</div>

<div class="page-card">
    <h2>Unit Account Details</h2>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">Department / Unit</span>
            <div class="info-value">{{ $departmentName ?: 'Not assigned yet' }}</div>
        </div>
        <div class="info-item">
            <span class="info-label">Current Dashboard View</span>
            <div class="info-value">{{ $fundSourceName ?: 'No fund sources available' }}</div>
        </div>
    </div>
</div>

<div class="page-card">
    <h2>CSV Format</h2>
    <p class="helper-text">
        Supported columns:
        <code>uacs_code,item_name,specifications,category_id,q1,q2,q3,q4,unit_cost,unit,estimated_budget,mode_of_procurement</code>
    </p>
    <p class="helper-text">
        Header row is optional. If quarter columns are blank but <code>quantity</code> exists, the value will be placed in Q1.
        If <code>estimated_budget</code> is blank, the system will compute it from the quarter quantities and unit cost.
    </p>

    <form action="{{ route('unit.ppmp.upload') }}" method="POST" enctype="multipart/form-data" data-confirm="Upload this PPMP CSV and submit it for validation?">
        @csrf

        <label for="fund_source_id">Fund Source</label>
        <select id="fund_source_id" name="fund_source_id" required>
            <option value="">Select Fund Source</option>
            @foreach($fundSources as $fundSource)
                <option value="{{ $fundSource->id }}" {{ (string) old('fund_source_id', $activeFundSourceId) === (string) $fundSource->id ? 'selected' : '' }}>
                    {{ $fundSource->name }}
                </option>
            @endforeach
        </select>
        <p class="helper-text">
            The uploaded PPMP will be saved under the selected fund source.
        </p>

        <label for="fiscal_year">Fiscal Year</label>
        <input
            type="number"
            id="fiscal_year"
            name="fiscal_year"
            min="2000"
            max="2100"
            value="{{ old('fiscal_year', $currentYear) }}"
            required
        >

        <label for="csv_file">CSV File</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required>

        <div style="margin-top:16px;">
            <button type="submit" class="btn-create" {{ $fundSources->isEmpty() ? 'disabled' : '' }}>Upload and Submit</button>
        </div>
    </form>
</div>
@endsection
