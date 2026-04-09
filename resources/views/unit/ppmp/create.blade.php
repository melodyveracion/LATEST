@extends('unit.layout')
@section('title', 'Create PPMP — ConsoliData')

@section('content')

{{-- Page header --}}
<div class="flex items-center justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
            <a href="{{ route('unit.ppmp.index') }}" class="hover:text-slate-600 transition-colors">PPMP</a>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            <span class="text-slate-600">Create</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Create PPMP</h1>
        <p class="text-sm text-slate-500 mt-0.5">Start a new procurement plan. After creating, add quarterly item entries then submit.</p>
    </div>
    <a href="{{ route('unit.ppmp.index') }}" class="btn-secondary btn-sm flex-shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
        Back
    </a>
</div>

{{-- Account info --}}
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

{{-- Create form --}}
<div class="ui-card">
    <h2 class="ui-card-title">New Draft PPMP</h2>
    <p class="text-sm text-slate-500 mb-5">
        Choose a fund source and fiscal year. Items are added after the plan is created.
    </p>

    <form action="{{ route('unit.ppmp.store') }}" method="POST" data-confirm="Create a new draft PPMP?">
        @csrf
        <input type="hidden" name="status" value="Draft">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
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
                <p class="field-hint">Which fund source this PPMP belongs to.</p>
            </div>
            <div>
                <label for="fiscal_year" class="field-label">Fiscal Year <span class="text-red-500">*</span></label>
                <input type="number" id="fiscal_year" name="fiscal_year"
                       min="2000" max="2100"
                       value="{{ old('fiscal_year', $currentYear) }}"
                       required class="field-input">
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary" {{ $fundSources->isEmpty() ? 'disabled' : '' }}>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Create Draft PPMP
            </button>
        </div>
    </form>
</div>

@endsection
