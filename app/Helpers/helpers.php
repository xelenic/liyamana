<?php

use App\Models\Setting;
use App\Models\User;
use App\Notifications\AppNotification;

if (! function_exists('allow_registration')) {
    /**
     * Check if new user registration is allowed.
     */
    function allow_registration(): bool
    {
        return filter_var(Setting::get('allow_registration', '1'), FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('site_name')) {
    /**
     * Get the site name from settings, falling back to app name.
     */
    function site_name(): string
    {
        return (string) (Setting::get('site_name') ?: config('app.name') ?: 'FlipBook');
    }
}

if (! function_exists('format_price')) {
    /**
     * Format a price using currency and pricing settings.
     */
    function format_price(?float $amount, ?string $symbol = null, ?int $decimals = null): string
    {
        $amount = $amount ?? 0;
        $symbol = $symbol ?? Setting::get('currency_symbol') ?: '$';
        $decimals = $decimals ?? (int) (Setting::get('price_decimal_places') ?: 2);

        return $symbol.number_format((float) $amount, $decimals);
    }
}

if (! function_exists('session_recording_enabled')) {
    /**
     * Whether rrweb session recording is enabled for the user-facing app.
     * Setting key session_recording_enabled overrides config when set ("1" / "0").
     */
    function session_recording_enabled(): bool
    {
        $override = Setting::get('session_recording_enabled');
        if ($override !== null && $override !== '') {
            return filter_var($override, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(config('session_recording.enabled'), FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('session_recording_max_bytes_per_session')) {
    /**
     * Max bytes stored per recording session (ingest). Setting overrides config when set.
     */
    function session_recording_max_bytes_per_session(): int
    {
        $v = Setting::get('session_recording_max_bytes_per_session');
        if ($v !== null && $v !== '') {
            return max(1, (int) $v);
        }

        return (int) config('session_recording.max_bytes_per_session', 15 * 1024 * 1024);
    }
}

if (! function_exists('session_recording_max_replay_bytes')) {
    /**
     * Max bytes loaded when replaying in admin. Setting overrides config when set.
     */
    function session_recording_max_replay_bytes(): int
    {
        $v = Setting::get('session_recording_max_replay_bytes');
        if ($v !== null && $v !== '') {
            return max(1, (int) $v);
        }

        return (int) config('session_recording.max_replay_bytes', 40 * 1024 * 1024);
    }
}

if (! function_exists('session_recording_json_max_depth')) {
    /**
     * JSON encode/decode depth for rrweb events. Setting overrides config when set.
     */
    function session_recording_json_max_depth(): int
    {
        $v = Setting::get('session_recording_json_max_depth');
        if ($v !== null && $v !== '') {
            return max(512, (int) $v);
        }

        return (int) config('session_recording.json_max_depth', 1_000_000);
    }
}

if (! function_exists('session_recording_cdn_url')) {
    /**
     * CDN URL for rrweb bundles (recorder or player). Setting keys override config.
     *
     * @param  'rrweb_js'|'player_js'|'player_css'  $key
     */
    function session_recording_cdn_url(string $key): string
    {
        $settingKeys = [
            'rrweb_js' => 'session_recording_cdn_rrweb',
            'player_js' => 'session_recording_cdn_player',
            'player_css' => 'session_recording_cdn_player_css',
        ];
        $sk = $settingKeys[$key] ?? null;
        if ($sk) {
            $override = Setting::get($sk);
            if ($override !== null && $override !== '') {
                return (string) $override;
            }
        }

        return (string) config('session_recording.cdn.'.$key);
    }
}

if (! function_exists('user_heatmap_enabled')) {
    /**
     * Whether click heatmap collection is enabled (user panel).
     * Setting key user_heatmap_enabled overrides config when set ("1" / "0");
     * configure in Admin → Settings → Session & heatmap.
     */
    function user_heatmap_enabled(): bool
    {
        $override = Setting::get('user_heatmap_enabled');
        if ($override !== null && $override !== '') {
            return filter_var($override, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(config('user_heatmap.enabled'), FILTER_VALIDATE_BOOLEAN);
    }
}

if (! function_exists('user_heatmap_max_clicks_per_ingest')) {
    /**
     * Max clicks accepted per POST to the heatmap ingest endpoint. Setting overrides config when set.
     */
    function user_heatmap_max_clicks_per_ingest(): int
    {
        $v = Setting::get('user_heatmap_max_clicks_per_ingest');
        if ($v !== null && $v !== '') {
            return max(1, (int) $v);
        }

        return (int) config('user_heatmap.max_clicks_per_ingest', 40);
    }
}

if (! function_exists('user_heatmap_admin_max_points_per_response')) {
    /**
     * Max heatmap points returned to the admin overlay API. Setting overrides config when set.
     */
    function user_heatmap_admin_max_points_per_response(): int
    {
        $v = Setting::get('user_heatmap_admin_max_points_per_response');
        if ($v !== null && $v !== '') {
            return max(1, (int) $v);
        }

        return (int) config('user_heatmap.admin_max_points_per_response', 8000);
    }
}

if (! function_exists('push_notification')) {
    /**
     * Push a notification to a user (stored in database, shown in user panel top bar).
     *
     * @param  User|int  $user  User model or user id
     * @param  string  $title  Notification title
     * @param  string|null  $message  Optional message body
     * @param  string|null  $url  Optional link URL when notification is clicked
     * @param  string  $type  One of: info, success, warning, danger
     * @return \Illuminate\Notifications\DatabaseNotification|null
     */
    function push_notification($user, string $title, ?string $message = null, ?string $url = null, string $type = 'info')
    {
        if (is_numeric($user)) {
            $user = User::find($user);
        }
        if (! $user instanceof User) {
            return null;
        }
        $user->notify(new AppNotification($title, $message, $url, $type));

        return $user->unreadNotifications->first();
    }
}
