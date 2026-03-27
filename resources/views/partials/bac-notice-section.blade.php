{{-- Basis for BAC notice: Upload form + Existing notice display. Include with $purchaseRequest. --}}
@php
    $noticePath = $purchaseRequest->bac_notice_path ?? $purchaseRequest->award_notice_path ?? $purchaseRequest->failure_notice_path ?? null;
    $noticeType = $purchaseRequest->bac_notice_type ?? ($purchaseRequest->award_notice_path ? 'awarded' : ($purchaseRequest->failure_notice_path ? 'failed' : null));
    $noticeLabel = match ($noticeType ?? '') {
        'awarded' => 'Award Notice',
        'canceled' => 'Canceled Notice',
        'failed' => 'Failure Notice',
        'on_going' => 'On-Going Notice',
        default => 'BAC Notice',
    };
@endphp

<div class="page-card">
    <h2>Upload Notice</h2>
    @if($purchaseRequest->status === 'Approved')
        <div class="alert-info">
            This request is already approved by admin, but it is not yet available for BAC notice upload. The unit must confirm it first so the request can move to BAC processing.
        </div>
    @else
        <form action="{{ route('bac.pr.uploadNotice', $purchaseRequest->id) }}" method="POST" enctype="multipart/form-data" data-confirm="Upload this BAC notice?">
            @csrf

            <label>Notice Type</label>
            <select name="notice_type" required>
                <option value="">Select Notice Type</option>
                <option value="awarded">Awarded</option>
                <option value="canceled">Canceled</option>
                <option value="failed">Failed</option>
                <option value="on_going">On-going</option>
            </select>

            <label>Notice File</label>
            <input type="file" name="notice_file" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required>

            <div class="action-row">
                <button type="submit" class="btn-create">Upload Notice</button>
            </div>
        </form>
    @endif
</div>

<div class="page-card">
    <h2>Existing Notice Files</h2>
    <table>
        <tr>
            <th width="220">{{ $noticeLabel }}</th>
            <td>
                @if($noticePath)
                    <a href="{{ asset('storage/' . $noticePath) }}" target="_blank">Open {{ $noticeLabel }}</a>
                @else
                    Not uploaded
                @endif
            </td>
        </tr>
    </table>
</div>
