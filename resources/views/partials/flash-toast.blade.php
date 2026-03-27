@php
    $flashSuccess = session('success');
    $flashError = session('error');
    $flashInfo = session('info');
    $flashValidation = (isset($errors) && $errors->any()) ? $errors->first() : null;
@endphp
@if($flashSuccess || $flashError || $flashInfo || $flashValidation)
<div class="portal-toast-stack" id="portal-toast-stack">
    @if($flashError)
    <div class="portal-toast portal-toast--error" role="alert" aria-live="assertive" data-dismiss-ms="9000">
        <span class="portal-toast__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        </span>
        <p class="portal-toast__text">{{ $flashError }}</p>
        <button type="button" class="portal-toast__close" aria-label="Dismiss">&times;</button>
    </div>
    @endif
    @if($flashValidation)
    <div class="portal-toast portal-toast--error" role="alert" aria-live="assertive" data-dismiss-ms="9000">
        <span class="portal-toast__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        </span>
        <p class="portal-toast__text">{{ $flashValidation }}</p>
        <button type="button" class="portal-toast__close" aria-label="Dismiss">&times;</button>
    </div>
    @endif
    @if($flashSuccess)
    <div class="portal-toast portal-toast--success" role="status" aria-live="polite" data-dismiss-ms="5500">
        <span class="portal-toast__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
        </span>
        <p class="portal-toast__text">{{ $flashSuccess }}</p>
        <button type="button" class="portal-toast__close" aria-label="Dismiss">&times;</button>
    </div>
    @endif
    @if($flashInfo)
    <div class="portal-toast portal-toast--info" role="status" aria-live="polite" data-dismiss-ms="5500">
        <span class="portal-toast__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        </span>
        <p class="portal-toast__text">{{ $flashInfo }}</p>
        <button type="button" class="portal-toast__close" aria-label="Dismiss">&times;</button>
    </div>
    @endif
</div>
<script>
(function () {
    var stack = document.getElementById('portal-toast-stack');
    if (!stack) return;

    function dismissToast(toast) {
        if (toast._dismissTimer) {
            clearTimeout(toast._dismissTimer);
            toast._dismissTimer = null;
        }
        toast.classList.remove('portal-toast--visible');
        toast.classList.add('portal-toast--leaving');
        var finished = false;
        var done = function () {
            if (finished) return;
            finished = true;
            toast.remove();
            if (stack && !stack.querySelector('.portal-toast')) stack.remove();
        };
        toast.addEventListener('transitionend', function (e) {
            if (e.propertyName === 'opacity' || e.propertyName === 'transform') done();
        });
        window.setTimeout(done, 450);
    }

    var toasts = stack.querySelectorAll('.portal-toast');
    toasts.forEach(function (toast, i) {
        var ms = parseInt(toast.getAttribute('data-dismiss-ms'), 10);
        if (isNaN(ms) || ms < 0) ms = 5500;
        toast._dismissTimer = ms > 0 ? window.setTimeout(function () { dismissToast(toast); }, ms) : null;

        var closeBtn = toast.querySelector('.portal-toast__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () { dismissToast(toast); });
        }

        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                window.setTimeout(function () {
                    toast.classList.add('portal-toast--visible');
                }, 40 + i * 100);
            });
        });
    });
})();
</script>
@endif
