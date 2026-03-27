<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<script>
    (function () {
        const navigationEntry = window.performance && typeof window.performance.getEntriesByType === 'function'
            ? window.performance.getEntriesByType('navigation')[0]
            : null;

        if (navigationEntry && navigationEntry.type === 'back_forward') {
            window.location.reload();
            return;
        }

        window.addEventListener('pageshow', function (event) {
            const legacyBackForward = window.performance && window.performance.navigation
                ? window.performance.navigation.type === 2
                : false;

            if (event.persisted || legacyBackForward) {
                window.location.reload();
            }
        });
    })();
</script>
