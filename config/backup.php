<?php

return [

    'format_version' => 1,

    /*
    |--------------------------------------------------------------------------
    | Paths under storage/app/private to skip when backing up (relative to private root)
    |--------------------------------------------------------------------------
    */
    'exclude_private_subpaths' => [
        'backups',
        'framework/cache',
        'framework/sessions',
        'framework/views',
    ],

    /*
    |--------------------------------------------------------------------------
    | Safety limits for restore (zip bomb / oversized archives)
    |--------------------------------------------------------------------------
    */
    'max_upload_bytes' => (int) env('BACKUP_MAX_UPLOAD_BYTES', 512 * 1024 * 1024),

    'max_uncompressed_bytes' => (int) env('BACKUP_MAX_UNCOMPRESSED_BYTES', 2 * 1024 * 1024 * 1024),

    'max_zip_entries' => (int) env('BACKUP_MAX_ZIP_ENTRIES', 100_000),

];
