<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User click heatmap
    |--------------------------------------------------------------------------
    |
    | Override at runtime via Admin → Settings → Session & heatmap
    | (Setting keys user_heatmap_enabled, user_heatmap_max_clicks_per_ingest,
    | user_heatmap_admin_max_points_per_response).
    |
    */
    'enabled' => env('USER_HEATMAP_ENABLED', false),

    'max_clicks_per_ingest' => (int) env('USER_HEATMAP_MAX_CLICKS_PER_INGEST', 40),

    'admin_max_points_per_response' => (int) env('USER_HEATMAP_ADMIN_MAX_POINTS', 8000),

];
