@extends('unit.layout')
@section('title', 'Notifications — ConsoliData')

@section('content')

<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
        <p class="text-sm text-slate-500 mt-0.5">Approval updates, remarks, and BAC notices for your account.</p>
    </div>
    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-600">
        {{ $notifications->count() }} total
    </span>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Title</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $n)
                    @php
                        $sClass = match ($n->status ?? '') {
                            'Submitted'   => 'status-badge status-submitted',
                            'Approved'    => 'status-badge status-approved',
                            'Confirmed'   => 'status-badge status-confirmed',
                            'Completed'   => 'status-badge status-completed',
                            'Disapproved' => 'status-badge status-disapproved',
                            'Canceled'    => 'status-badge status-canceled',
                            'On-Going'    => 'status-badge status-ongoing',
                            default       => 'status-badge status-draft',
                        };
                    @endphp
                    <tr class="{{ !$n->is_read ? 'bg-blue-50/40' : '' }}">
                        <td class="text-slate-400 text-xs whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($n->created_at)->format('M d, Y h:i A') }}
                        </td>
                        <td class="text-xs text-slate-500 whitespace-nowrap">{{ $n->type }}</td>
                        <td>
                            @if($n->status)
                                <span class="{{ $sClass }}">{{ $n->status }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="font-medium text-slate-800">
                            @if(!$n->is_read)
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 align-middle"></span>
                            @endif
                            {{ $n->title }}
                        </td>
                        <td class="text-slate-600 text-sm">{{ $n->message }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-400 py-14">
                            <svg class="w-10 h-10 text-slate-200 mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 21a2 2 0 0 0 4 0"/>
                            </svg>
                            No notifications yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
