@extends('admin.layout')

@section('content')
<div class="page-shell">
    <div class="inline-actions" style="justify-content:space-between; align-items:flex-start;">
        <div>
            <h1 class="page-title">Consolidated Purchase Requests</h1>
            <p class="page-subtitle">
                View request totals grouped by department, fund source, and request status, then print a clean summary when needed.
            </p>
        </div>
        <button type="button" class="btn-primary" id="open-consolidated-request-print-modal">Print Summary</button>
    </div>

    <div class="panel-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Department / Unit</th>
                        <th>Fund Source</th>
                        <th>Status</th>
                        <th>Total Requests</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $statusClass = match ($row->status) {
                                'Submitted' => 'status-badge status-submitted',
                                'Approved' => 'status-badge status-approved',
                                'Confirmed' => 'status-badge status-confirmed',
                                'Completed' => 'status-badge status-completed',
                                'Disapproved' => 'status-badge status-disapproved',
                                'Canceled' => 'status-badge status-disapproved',
                                'On-Going' => 'status-badge status-confirmed',
                                default => 'status-badge status-draft',
                            };
                        @endphp
                        <tr>
                            <td>{{ $row->department_name }}</td>
                            <td>{{ $row->fund_source_name }}</td>
                            <td><span class="{{ $statusClass }}">{{ $row->status }}</span></td>
                            <td>{{ $row->total_requests }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;">No consolidated purchase request data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="system-confirm-overlay" id="consolidated-request-print-overlay" hidden>
    <div class="system-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="consolidated-request-print-title">
        <div class="system-confirm-copy" style="width:100%;">
            <h3 id="consolidated-request-print-title">Enter Purpose Before Printing</h3>
            <p>Add the purpose that should appear in the footer of the printed consolidated purchase request form.</p>

            <form method="GET" action="{{ route('admin.consolidated-request.print') }}" target="_blank" id="consolidated-request-print-form">
                <label for="consolidated-request-purpose" style="display:block; margin-bottom:8px; font-weight:700;">Purpose</label>
                <textarea id="consolidated-request-purpose" name="purpose" rows="4" placeholder="Type the purpose for this printed purchase request..." required></textarea>
                <p class="helper-text" style="margin:10px 0 0;">This text will be placed in the printed footer.</p>

                <div class="system-confirm-actions" style="margin-top:18px;">
                    <button type="button" class="btn-edit" id="consolidated-request-print-cancel">Cancel</button>
                    <button type="submit" class="btn-primary">Continue to Print</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const openButton = document.getElementById('open-consolidated-request-print-modal');
        const overlay = document.getElementById('consolidated-request-print-overlay');
        const cancelButton = document.getElementById('consolidated-request-print-cancel');
        const form = document.getElementById('consolidated-request-print-form');
        const textarea = document.getElementById('consolidated-request-purpose');

        if (!openButton || !overlay || !cancelButton || !form || !textarea) {
            return;
        }

        let lastFocusedElement = null;

        function openModal() {
            lastFocusedElement = document.activeElement;
            overlay.hidden = false;
            document.body.classList.add('modal-open');
            textarea.value = '';
            window.setTimeout(function () {
                textarea.focus();
            }, 0);
        }

        function closeModal() {
            overlay.hidden = true;
            document.body.classList.remove('modal-open');

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }
        }

        openButton.addEventListener('click', openModal);
        cancelButton.addEventListener('click', closeModal);

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (overlay.hidden) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeModal();
            }
        });

        form.addEventListener('submit', function () {
            closeModal();
        });
    })();
</script>
@endsection
