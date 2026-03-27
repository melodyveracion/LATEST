<script>
document.addEventListener('DOMContentLoaded', function() {
    var btns = document.querySelectorAll('.sidebar-toggle.hamburger-btn');
    var backdrop = document.getElementById('sidebar-backdrop');
    var mq = window.matchMedia('(max-width: 1100px)');

    function syncAria() {
        var visible = mq.matches ? document.body.classList.contains('sidebar-open') : !document.body.classList.contains('sidebar-collapsed');
        btns.forEach(function(b) { b.setAttribute('aria-expanded', visible); });
    }

    function handleClick() {
        if (mq.matches) {
            document.body.classList.toggle('sidebar-open');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
        syncAria();
    }

    function closeSidebar() {
        if (mq.matches) {
            document.body.classList.remove('sidebar-open');
        } else {
            document.body.classList.add('sidebar-collapsed');
        }
        syncAria();
    }

    btns.forEach(function(b) { b.addEventListener('click', handleClick); });
    if (backdrop) backdrop.addEventListener('click', closeSidebar);

    mq.addEventListener && mq.addEventListener('change', function() {
        if (mq.matches) {
            document.body.classList.remove('sidebar-collapsed');
        } else {
            document.body.classList.remove('sidebar-open');
        }
        syncAria();
    });

    syncAria();
});
</script>
