<div class="system-confirm-overlay" id="system-confirm-overlay" hidden>
    <div
        class="system-confirm-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="system-confirm-title"
        aria-describedby="system-confirm-message"
    >
        <div class="system-confirm-copy">
            <h3 id="system-confirm-title">Confirm Action</h3>
            <p id="system-confirm-message">Are you sure you want to continue?</p>
        </div>

        <div class="system-confirm-actions">
            <button type="button" class="btn-edit" id="system-confirm-cancel">Cancel</button>
            <button type="button" class="btn-primary" id="system-confirm-accept">Continue</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const overlay = document.getElementById('system-confirm-overlay');
        const titleElement = document.getElementById('system-confirm-title');
        const messageElement = document.getElementById('system-confirm-message');
        const cancelButton = document.getElementById('system-confirm-cancel');
        const acceptButton = document.getElementById('system-confirm-accept');

        if (!overlay || !titleElement || !messageElement || !cancelButton || !acceptButton) {
            return;
        }

        let pendingForm = null;
        let pendingSubmitter = null;
        let lastFocusedElement = null;

        function closeConfirmModal() {
            overlay.hidden = true;
            document.body.classList.remove('modal-open');
            pendingForm = null;
            pendingSubmitter = null;

            if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
                lastFocusedElement.focus();
            }
        }

        function openConfirmModal(form, submitter) {
            pendingForm = form;
            pendingSubmitter = submitter || null;
            lastFocusedElement = document.activeElement;

            titleElement.textContent = form.getAttribute('data-confirm-title') || 'Confirm Action';
            messageElement.textContent = form.getAttribute('data-confirm') || 'Are you sure you want to continue?';
            acceptButton.textContent = form.getAttribute('data-confirm-action-label')
                || (submitter && submitter.textContent ? submitter.textContent.trim() : 'Continue');

            overlay.hidden = false;
            document.body.classList.add('modal-open');
            acceptButton.focus();
        }

        function submitPendingForm() {
            if (!pendingForm) {
                return;
            }

            const formToSubmit = pendingForm;
            const submitterToUse = pendingSubmitter || formToSubmit.__lastSubmitter || null;
            delete formToSubmit.__lastSubmitter;

            closeConfirmModal();

            if (submitterToUse && submitterToUse.name) {
                const submitterInput = document.createElement('input');
                submitterInput.type = 'hidden';
                submitterInput.name = submitterToUse.name;
                submitterInput.value = submitterToUse.value;
                formToSubmit.appendChild(submitterInput);
            }

            HTMLFormElement.prototype.submit.call(formToSubmit);
        }

        document.addEventListener('click', function (event) {
            const submitter = event.target.closest('button[type="submit"], input[type="submit"]');

            if (!submitter || !submitter.form || !submitter.form.matches('form[data-confirm]')) {
                return;
            }

            submitter.form.__lastSubmitter = submitter;
        }, true);

        document.addEventListener('submit', function (event) {
            const form = event.target;

            if (!(form instanceof HTMLFormElement) || !form.matches('form[data-confirm]')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }

            openConfirmModal(form, event.submitter || form.__lastSubmitter || null);
        }, true);

        cancelButton.addEventListener('click', closeConfirmModal);
        acceptButton.addEventListener('click', submitPendingForm);

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeConfirmModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (overlay.hidden) {
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeConfirmModal();
            }
        });
    })();
</script>
