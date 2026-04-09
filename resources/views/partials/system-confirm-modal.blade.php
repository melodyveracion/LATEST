<div id="system-confirm-overlay"
     hidden
     style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(2px);">
    <div role="dialog"
         aria-modal="true"
         aria-labelledby="system-confirm-title"
         aria-describedby="system-confirm-message"
         style="background:#fff;border-radius:1rem;box-shadow:0 20px 60px rgba(0,0,0,0.2);padding:1.75rem 2rem;max-width:420px;width:calc(100% - 2rem);margin:1rem;">
        <div style="margin-bottom:1.25rem;">
            <h3 id="system-confirm-title"
                style="font-size:1.0625rem;font-weight:700;color:#111827;margin:0 0 0.5rem;">
                Confirm Action
            </h3>
            <p id="system-confirm-message"
               style="font-size:0.875rem;color:#6b7280;margin:0;line-height:1.5;">
                Are you sure you want to continue?
            </p>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:0.625rem;">
            <button type="button"
                    id="system-confirm-cancel"
                    style="padding:0.5rem 1.125rem;border-radius:0.5rem;font-size:0.8125rem;font-weight:600;background:#fff;color:#374151;border:1px solid #d1d5db;cursor:pointer;transition:background 0.15s;">
                Cancel
            </button>
            <button type="button"
                    id="system-confirm-accept"
                    style="padding:0.5rem 1.125rem;border-radius:0.5rem;font-size:0.8125rem;font-weight:600;background:#2563eb;color:#fff;border:none;cursor:pointer;transition:background 0.15s;">
                Continue
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const overlay       = document.getElementById('system-confirm-overlay');
        const titleEl       = document.getElementById('system-confirm-title');
        const messageEl     = document.getElementById('system-confirm-message');
        const cancelBtn     = document.getElementById('system-confirm-cancel');
        const acceptBtn     = document.getElementById('system-confirm-accept');

        if (!overlay) return;

        let pendingForm = null;
        let lastFocus   = null;

        function close() {
            overlay.hidden = true;
            document.body.style.overflow = '';
            pendingForm = null;
            if (lastFocus && lastFocus.focus) lastFocus.focus();
        }

        function open(form, submitter) {
            pendingForm = form;
            lastFocus   = document.activeElement;
            titleEl.textContent   = form.dataset.confirmTitle   || 'Confirm Action';
            messageEl.textContent = form.dataset.confirm        || 'Are you sure you want to continue?';
            acceptBtn.textContent = form.dataset.confirmActionLabel
                || (submitter && submitter.textContent ? submitter.textContent.trim() : 'Continue');
            overlay.hidden = false;
            document.body.style.overflow = 'hidden';
            acceptBtn.focus();
        }

        function submit() {
            if (!pendingForm) return;
            const f = pendingForm;
            const s = f.__lastSubmitter || null;
            delete f.__lastSubmitter;
            close();
            if (s && s.name) {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = s.name;
                inp.value = s.value;
                f.appendChild(inp);
            }
            HTMLFormElement.prototype.submit.call(f);
        }

        document.addEventListener('click', e => {
            const s = e.target.closest('button[type="submit"],input[type="submit"]');
            if (s && s.form && s.form.matches('form[data-confirm]')) {
                s.form.__lastSubmitter = s;
            }
        }, true);

        document.addEventListener('submit', e => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement) || !form.matches('form[data-confirm]')) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            open(form, e.submitter || form.__lastSubmitter || null);
        }, true);

        cancelBtn.addEventListener('click', close);
        acceptBtn.addEventListener('click', submit);
        overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
        document.addEventListener('keydown', e => {
            if (!overlay.hidden && e.key === 'Escape') { e.preventDefault(); close(); }
        });

        /* Hover styles */
        cancelBtn.addEventListener('mouseenter', () => cancelBtn.style.background = '#f9fafb');
        cancelBtn.addEventListener('mouseleave', () => cancelBtn.style.background = '#fff');
        acceptBtn.addEventListener('mouseenter', () => acceptBtn.style.background = '#1d4ed8');
        acceptBtn.addEventListener('mouseleave', () => acceptBtn.style.background = '#2563eb');
    })();
</script>
