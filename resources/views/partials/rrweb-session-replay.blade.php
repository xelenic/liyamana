{{-- rrweb-player replay scripts (load player CSS via @push styles in the page) --}}
@php
    $rrwebJs = session_recording_cdn_url('rrweb_js');
    $playerJs = session_recording_cdn_url('player_js');
@endphp
<script src="{{ $rrwebJs }}" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{ $playerJs }}" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
(function () {
    /**
     * rrweb replayer does createTextNode(n.textContent) / textContent = mutation.value without
     * guarding null. JSON null becomes the literal string "null" in the DOM.
     * Do not coerce null inside `attributes` objects (null means removeAttribute).
     */
    function sanitizeRrwebEvents(events) {
        function walk(node, inAttributes) {
            if (node === null || node === undefined) {
                return node;
            }
            if (Array.isArray(node)) {
                for (var i = 0; i < node.length; i++) {
                    node[i] = walk(node[i], false);
                }
                return node;
            }
            if (typeof node !== 'object') {
                return node;
            }
            if (inAttributes) {
                for (var ak in node) {
                    if (!Object.prototype.hasOwnProperty.call(node, ak)) {
                        continue;
                    }
                    var av = node[ak];
                    if (av !== null && typeof av === 'object') {
                        node[ak] = walk(av, false);
                    }
                }
                return node;
            }
            for (var k in node) {
                if (!Object.prototype.hasOwnProperty.call(node, k)) {
                    continue;
                }
                var v = node[k];
                if (v === null || v === undefined) {
                    if (k === 'textContent' || k === 'text' || k === 'value') {
                        node[k] = '';
                    }
                } else if (typeof v === 'object') {
                    node[k] = walk(v, k === 'attributes');
                }
            }
            return node;
        }
        try {
            var clone = JSON.parse(JSON.stringify(events));
            walk(clone, false);
            return clone;
        } catch (e) {
            return events;
        }
    }

    var url = window.__RRWEB_EVENTS_URL__;
    var target = document.getElementById('rrweb-replayer');
    var statusEl = document.getElementById('rrweb-replay-status');
    if (!url || !target) {
        return;
    }

    if (typeof rrwebPlayer === 'undefined') {
        if (statusEl) {
            statusEl.textContent = 'Replay library failed to load (rrwebPlayer). Check CDN or network.';
            statusEl.classList.remove('d-none');
        }
        return;
    }

    fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
        .then(function (res) {
            return res.json().then(function (data) {
                return { res: res, data: data };
            });
        })
        .then(function (_a) {
            var res = _a.res, data = _a.data;
            if (!res.ok) {
                var msg = (data && data.error) ? data.error : ('Failed to load events (' + res.status + ')');
                if (statusEl) {
                    statusEl.textContent = msg;
                    statusEl.classList.remove('d-none');
                }
                return;
            }
            if (!Array.isArray(data) || data.length === 0) {
                if (statusEl) {
                    statusEl.textContent = 'No events in this recording.';
                    statusEl.classList.remove('d-none');
                }
                return;
            }
            if (statusEl) {
                statusEl.classList.add('d-none');
            }
            var safeEvents = sanitizeRrwebEvents(data);
            new rrwebPlayer({
                target: target,
                props: {
                    events: safeEvents,
                    showController: true,
                    autoPlay: false
                }
            });
        })
        .catch(function (e) {
            if (statusEl) {
                statusEl.textContent = (e && e.message) ? e.message : 'Could not load replay.';
                statusEl.classList.remove('d-none');
            }
        });
})();
</script>
