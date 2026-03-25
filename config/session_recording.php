<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Session recording (rrweb)
    |--------------------------------------------------------------------------
    |
    | When true, authenticated users (non-admin) on the main app layout will
    | send anonymized DOM replay events to your server. Admins can replay
    | sessions in the admin panel. See https://www.rrweb.io/
    |
    | You may override this at runtime via Admin → Settings → Session recording
    | (Setting key session_recording_enabled, values "1" / "0"), or other keys
    | documented on that page for limits and CDN URLs.
    |
    */
    'enabled' => env('SESSION_RECORDING_ENABLED', false),

    'max_bytes_per_session' => (int) env('SESSION_RECORDING_MAX_BYTES', 15 * 1024 * 1024),

    'max_replay_bytes' => (int) env('SESSION_RECORDING_MAX_REPLAY_BYTES', 40 * 1024 * 1024),

    /*
    | rrweb FullSnapshot events nest the DOM far deeper than PHP's default JSON depth (512).
    | Too low a value causes lines to be skipped on load → broken replay ("null", missing layout).
    */
    'json_max_depth' => max(512, (int) env('SESSION_RECORDING_JSON_MAX_DEPTH', 1_000_000)),

    /*
    | CDN URLs for browser bundles (no npm/Vite required). Pin versions for stability.
    */
    'cdn' => [
        'rrweb_js' => env('SESSION_RECORDING_CDN_RRWEB', 'https://cdn.jsdelivr.net/npm/rrweb@1.1.3/dist/rrweb.js'),
        'player_js' => env('SESSION_RECORDING_CDN_PLAYER', 'https://cdn.jsdelivr.net/npm/rrweb-player@0.7.14/dist/index.js'),
        'player_css' => env('SESSION_RECORDING_CDN_PLAYER_CSS', 'https://cdn.jsdelivr.net/npm/rrweb-player@0.7.14/dist/style.css'),
    ],

];
