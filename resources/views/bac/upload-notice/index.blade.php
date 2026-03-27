@extends('bac.layout')

@section('title', 'Upload Notice - ConsoliData')

@section('content')
<div class="page-shell">
    <div class="page-header">
        <div>
            <h1>Upload Notice</h1>
            <p>Select a purchase request and upload the corresponding BAC notice (Awarded, Canceled, Failed, or On-going).</p>
        </div>
    </div>

    <div class="panel-card">
        <div class="inline-actions" style="justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h2 style="margin:0;">Purchase Requests Ready for Notice</h2>
            <span class="muted-text">{{ $purchaseRequests->count() }} record(s)</span>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>PR #</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Fund Source</th>
                        <th>Budget</th>
                        <th>Status</th>
                        <th>Notice</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseRequests as $pr)
                        @php
                            $statusClass = match ($pr->status) {
                                'Confirmed' => 'status-badge status-confirmed',
                                'Completed' => 'status-badge status-completed',
                                'On-Going' => 'status-badge status-confirmed',
                                default => 'status-badge status-submitted',
                            };
                            $noticePath = $pr->bac_notice_path ?? $pr->award_notice_path ?? $pr->failure_notice_path ?? null;
                            $noticeType = $pr->bac_notice_type ?? ($pr->award_notice_path ? 'awarded' : ($pr->failure_notice_path ? 'failed' : null));
                            $noticeLabel = match ($noticeType ?? '') {
                                'awarded' => 'Award Notice',
                                'canceled' => 'Canceled Notice',
                                'failed' => 'Failure Notice',
                                'on_going' => 'On-Going Notice',
                                default => 'BAC Notice',
                            };
                            $canUpload = !$noticePath;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('bac.pr.show', $pr->id) }}">#{{ $pr->id }}</a>
                                <br><a href="{{ route('bac.pr.print', $pr->id) }}" target="_blank" rel="noopener" class="link-muted" style="font-size:12px;">Print</a>
                            </td>
                            <td class="stacked-meta">
                                <strong>{{ $pr->user_name ?? 'Unknown' }}</strong><br>
                                <small>{{ $pr->user_email ?? '—' }}</small>
                            </td>
                            <td>{{ $pr->department_name ?? '—' }}</td>
                            <td>{{ $pr->fund_source_name ?? '—' }}</td>
                            <td>{{ number_format((float) ($pr->total_estimated_budget ?? 0), 2) }}</td>
                            <td><span class="{{ $statusClass }}">{{ $pr->status }}</span></td>
                            <td>
                                @if($noticePath)
                                    <a href="{{ asset('storage/' . $noticePath) }}" target="_blank" rel="noopener">Open {{ $noticeLabel }}</a>
                                @else
                                    <form action="{{ route('bac.pr.uploadNotice', $pr->id) }}" method="POST" enctype="multipart/form-data" class="upload-notice-form" data-confirm="Upload this BAC notice?">
                                        @csrf
                                        <div class="upload-notice-row">
                                            <select name="notice_type" required style="min-width:110px;">
                                                <option value="">Type</option>
                                                @foreach($noticeTypes as $type)
                                                    <option value="{{ $type }}">{{ $noticeLabels[$type] ?? ucfirst(str_replace('_',' ',$type)) }}</option>
                                                @endforeach
                                            </select>
                                            <input type="file" name="notice_file" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required style="max-width:140px;">
                                            <button type="submit" class="btn btn-primary" style="white-space:nowrap;">Upload</button>
                                        </div>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">No purchase requests ready for notice upload. Requests must be Confirmed, Completed, or On-Going.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.upload-notice-form { margin: 0; }
.upload-notice-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.upload-notice-row select,
.upload-notice-row input[type="file"] { font-size: 13px; }
.upload-notice-row input[type="file"]::-webkit-file-upload-button { cursor: pointer; }
.link-muted { color: var(--muted-color, #64748b); text-decoration: none; }
.link-muted:hover { text-decoration: underline; }
</style>
@endsection
