@extends('unit.layout')

@section('title', 'Create PPMP - ConsoliData')

@section('content')
<div class="page-header">
    <div>
        <h1>Create PPMP</h1>
        <p>Start a new PPMP under your assigned unit and choose which fund source it belongs to.</p>
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
    <h2>Create Draft PPMP</h2>
    <p class="helper-text">
        Create the PPMP header first. After that, you can add quarterly item entries and submit the plan for review.
    </p>

    <form action="{{ route('unit.ppmp.store') }}" method="POST" data-confirm="Create a new draft PPMP?">
        @csrf
        <input type="hidden" name="status" value="Draft">

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
            Choose which fund source this PPMP should belong to.
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

        <button type="submit" class="btn-create" {{ $fundSources->isEmpty() ? 'disabled' : '' }}>Create Draft PPMP</button>
    </form>
</div>
@endsection