@extends('unit.layout')
@section('title', 'Upload PPMP — ConsoliData')

@section('content')

<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
            <a href="{{ route('unit.ppmp.index') }}" class="hover:text-slate-600 transition-colors">PPMP</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            <span class="text-slate-600">Upload CSV</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Upload PPMP</h1>
        <p class="text-sm text-slate-500 mt-0.5">Import PPMP items from a CSV file. The record is auto-submitted on success.</p>
    </div>
    <a href="{{ route('unit.ppmp.index') }}" class="btn-secondary btn-sm flex-shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        Back
    </a>
</div>

{{-- Unit details --}}
<div class="ui-card">
    <h2 class="ui-card-title">Unit Details</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Department / Unit</p>
            <p class="text-sm font-medium text-slate-800">{{ $departmentName ?: 'Not assigned' }}</p>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Active Fund Source</p>
            <p class="text-sm font-medium text-slate-800">{{ $fundSourceName ?: 'None selected' }}</p>
        </div>
    </div>
</div>

{{-- CSV format guide --}}
<div class="ui-card">
    <h2 class="ui-card-title flex items-center gap-2">
        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
        </svg>
        CSV Format Guide
    </h2>
    <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 mb-3">
        <p class="text-xs font-mono text-slate-700 break-all">
            uacs_code, item_name, specifications, category_id, q1, q2, q3, q4, unit_cost, unit, estimated_budget, mode_of_procurement
        </p>
    </div>
    <ul class="text-sm text-slate-500 space-y-1.5 list-disc list-inside">
        <li>Header row is optional.</li>
        <li>If <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">estimated_budget</code> is blank, the system computes it from quantities × unit cost.</li>
        <li>If quarterly columns are blank but a <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">quantity</code> column exists, it is placed in Q1.</li>
    </ul>
</div>

{{-- Upload form --}}
<div class="ui-card" x-data="{ fileName: '' }">
    <h2 class="ui-card-title">Upload File</h2>

    <form action="{{ route('unit.ppmp.upload') }}" method="POST"
          enctype="multipart/form-data"
          data-confirm="Upload this CSV and auto-submit the PPMP for validation?">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="fund_source_id" class="field-label">Fund Source <span class="text-red-500">*</span></label>
                <select id="fund_source_id" name="fund_source_id" required class="field-input">
                    <option value="">Select Fund Source</option>
                    @foreach($fundSources as $fs)
                        <option value="{{ $fs->id }}" {{ (string) old('fund_source_id', $activeFundSourceId) === (string) $fs->id ? 'selected' : '' }}>
                            {{ $fs->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="fiscal_year" class="field-label">Fiscal Year <span class="text-red-500">*</span></label>
                <input type="number" id="fiscal_year" name="fiscal_year"
                       min="2000" max="2100"
                       value="{{ old('fiscal_year', $currentYear) }}"
                       required class="field-input">
            </div>
        </div>

        {{-- File drop zone --}}
        <div class="mb-6">
            <label class="field-label">CSV File <span class="text-red-500">*</span></label>
            <label for="csv_file"
                   class="flex flex-col items-center justify-center gap-3 w-full h-36 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 cursor-pointer hover:border-blue-400 hover:bg-blue-50/40 transition-colors">
                <svg class="w-8 h-8 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-8-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span class="text-sm text-slate-500" x-text="fileName || 'Click to select a .csv file'"></span>
                <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" required class="sr-only"
                       @change="fileName = $event.target.files[0]?.name || ''">
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary" {{ $fundSources->isEmpty() ? 'disabled' : '' }}>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-8-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload and Submit
            </button>
        </div>
    </form>
</div>

@endsection
