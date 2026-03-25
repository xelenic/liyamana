{{-- rrweb recorder: no npm/Vite — loads from CDN (config session_recording.cdn_*) --}}
@php
    $rrwebJs = session_recording_cdn_url('rrweb_js');
@endphp
<script src="{{ $rrwebJs }}" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function () {
    if (typeof rrweb === 'undefined' || !rrweb.record) {
        return;
    }
    var cfg = window.__SESSION_RECORDING__;
    if (!cfg || !cfg.startUrl || !cfg.appendUrl || !cfg.finishUrl || !cfg.csrfToken) {
        return;
    }

    function postJson(url, body, keepalive) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': cfg.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(body),
            credentials: 'same-origin',
            keepalive: !!keepalive
        }).then(function (res) {
            return { ok: res.ok, status: res.status, res: res };
        }).catch(function () {
            return { ok: false, status: 0 };
        });
    }

    var sessionId = null;
    var queue = [];
    var flushing = false;
    var stopRecord = null;
    var timer = null;

    function start() {
        var path = (window.location.pathname + window.location.search).slice(0, 2000);
        return postJson(cfg.startUrl, {
            landing_path: path,
            viewport_w: window.innerWidth,
            viewport_h: window.innerHeight,
            user_agent: (navigator.userAgent || '').slice(0, 500)
        }, false).then(function (_a) {
            var ok = _a.ok, res = _a.res;
            if (!ok || !res) {
                return null;
            }
            return res.json().then(function (data) {
                return data && data.id ? data.id : null;
            }).catch(function () {
                return null;
            });
        });
    }

    function flush(useKeepalive) {
        if (!sessionId || queue.length === 0 || flushing) {
            return Promise.resolve();
        }
        flushing = true;
        var batch = queue.splice(0, queue.length);
        return postJson(cfg.appendUrl, { id: sessionId, events: batch }, !!useKeepalive).then(function (_a) {
            var ok = _a.ok, status = _a.status;
            if (!ok && status !== 410) {
                queue.unshift.apply(queue, batch);
            }
            flushing = false;
        });
    }

    function finish(useKeepalive) {
        if (!sessionId) {
            return Promise.resolve();
        }
        return flush(!!useKeepalive).then(function () {
            return postJson(cfg.finishUrl, { id: sessionId }, !!useKeepalive);
        });
    }

    function run() {
        start().then(function (id) {
            sessionId = id;
            if (!sessionId) {
                return;
            }

            timer = setInterval(function () {
                flush(false);
            }, 4000);

            stopRecord = rrweb.record({
                emit: function (event) {
                    queue.push(event);
                    if (queue.length >= 45) {
                        flush(false);
                    }
                },
                /* Inline CSS into the snapshot so the replay iframe matches (external sheets often look wrong). */
                inlineStylesheet: true,
                maskAllInputs: true,
                maskTextClass: 'rr-mask',
                blockClass: 'rr-block',
                ignoreClass: 'rr-ignore'
            });

            window.addEventListener('pagehide', function () {
                if (timer) {
                    clearInterval(timer);
                }
                try {
                    if (typeof stopRecord === 'function') {
                        stopRecord();
                    }
                } catch (e) { /* ignore */ }
                finish(true);
            });

            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'hidden') {
                    flush(false);
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
</script>
