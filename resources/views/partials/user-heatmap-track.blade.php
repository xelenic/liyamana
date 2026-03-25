{{-- Click heatmap ingest: no npm; coordinates as % of viewport --}}
<script>
(function () {
    var cfg = window.__USER_HEATMAP__;
    if (!cfg || !cfg.url || !cfg.csrfToken) {
        return;
    }
    var buffer = [];
    var flushing = false;

    function postClicks(clicks, keepalive) {
        return fetch(cfg.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': cfg.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ clicks: clicks }),
            credentials: 'same-origin',
            keepalive: !!keepalive
        }).catch(function () {});
    }

    function flush(useKeepalive) {
        if (!buffer.length || flushing) {
            return;
        }
        flushing = true;
        var batch = buffer.splice(0, buffer.length);
        postClicks(batch, useKeepalive).finally(function () {
            flushing = false;
        });
    }

    document.addEventListener('click', function (e) {
        try {
            if (e.target && e.target.closest && e.target.closest('[data-no-heatmap]')) {
                return;
            }
            var vw = window.innerWidth;
            var vh = window.innerHeight;
            if (vw < 1 || vh < 1) {
                return;
            }
            buffer.push({
                path: (window.location.pathname + window.location.search).slice(0, 1000),
                x_pct: (e.clientX / vw) * 100,
                y_pct: (e.clientY / vh) * 100,
                viewport_w: vw,
                viewport_h: vh
            });
            if (buffer.length >= 25) {
                flush(false);
            }
        } catch (err) { /* ignore */ }
    }, true);

    setInterval(function () {
        flush(false);
    }, 5000);

    window.addEventListener('pagehide', function () {
        flush(true);
    });
})();
</script>
