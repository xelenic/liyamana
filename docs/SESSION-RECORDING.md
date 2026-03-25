# Session recording (rrweb)

Pixel-perfect session replay for the **user-facing app** (`layouts.app`) using [rrweb](https://www.rrweb.io/).

## Enable

1. **Env:** `SESSION_RECORDING_ENABLED=true` in `.env`, **or**
2. **Admin → Settings → Features:** turn on **Record user sessions (rrweb…)**.

If the setting exists in the database, it overrides the env flag.

Recording runs only for **logged-in users who are not admins**. Inputs are **masked** in the recorder config (`maskAllInputs`); extend `resources/js/session-record.js` for more privacy rules (`blockClass`, `ignoreClass`, etc.).

## Admin

**Admin → Settings → Session recordings** (sidebar): list, replay, delete.

Events are stored as JSON Lines under `storage/app/private/session-recordings/{uuid}.jsonl`.

## Deploy

Session recording uses **rrweb from a CDN** (jsDelivr by default). You do **not** need npm or a frontend build for this feature.

```bash
php artisan migrate
```

Optional `.env` overrides:

- `SESSION_RECORDING_CDN_RRWEB` — e.g. `https://cdn.jsdelivr.net/npm/rrweb@1.1.3/dist/rrweb.js`
- `SESSION_RECORDING_CDN_PLAYER` — rrweb-player `dist/index.js`
- `SESSION_RECORDING_CDN_PLAYER_CSS` — rrweb-player `dist/style.css`

If your site uses a strict **Content-Security-Policy**, allow these script/style origins (or self-host the same files under `public/` and point the env URLs there).

## Limits

- `SESSION_RECORDING_MAX_BYTES` — max size per session file (default ~15 MB).
- `SESSION_RECORDING_MAX_REPLAY_BYTES` — max file size loaded for replay (default ~40 MB).

## Replay looks wrong (“null”, broken layout)

1. **Literal “null” text everywhere:** rrweb’s replayer calls `document.createTextNode(textContent)` and assigns `textContent = mutation.value` without handling JSON `null`. The browser turns that into visible **"null"** strings. Events are **sanitized** server-side (`RrwebEventSanitizer`) and again in the replay page script before `rrwebPlayer` runs (`null` → `""` for `textContent`, `text`, and `value`, except inside `attributes` where `null` means remove attribute).
2. **JSON depth (fixed in code):** PHP’s default `json_decode` / `json_encode` max depth is **512**. rrweb’s FullSnapshot nests the DOM much deeper, so events were skipped when loading → broken replay. The app now uses `session_recording.json_max_depth` (default **1,000,000**). **Re-record** sessions captured before this fix.
3. **CSS:** Recording uses `inlineStylesheet: true` so styles are embedded; external stylesheets alone often look wrong inside the replay iframe.
4. **CSP / ad blockers:** CDNs for rrweb must load; check the browser console on the replay page.

## Legal / privacy

Obtain appropriate consent and disclose recording in your privacy policy before enabling in production.
