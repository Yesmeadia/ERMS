<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum Result Candidates per PDF Part
    |--------------------------------------------------------------------------
    |
    | Limits the maximum number of student result rows compiled into a single
    | PDF file to prevent PHP memory limits and Dompdf rendering bottlenecks.
    | 250 candidates creates ~9 pages per PDF file, rendering in ~2-4 seconds.
    |
    */
    'max_per_pdf' => min(500, max(1, (int) env('MAX_RESULTS_PER_PDF', 250))),

    /*
    |--------------------------------------------------------------------------
    | Batch Expiration Duration (in hours)
    |--------------------------------------------------------------------------
    */
    'expiration_hours' => (int) env('RESULT_EXPIRATION_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Batch Retention Duration (in days)
    |--------------------------------------------------------------------------
    */
    'retention_days' => (int) env('RESULT_RETENTION_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Dedicated Queue Name
    |--------------------------------------------------------------------------
    */
    'queue' => env('RESULT_PDF_QUEUE', 'pdf'),

    /*
    |--------------------------------------------------------------------------
    | Job Timeout & Retry Configuration (in seconds)
    |--------------------------------------------------------------------------
    */
    'job_timeout' => (int) env('RESULT_PDF_TIMEOUT', 300),
    'job_tries' => (int) env('RESULT_PDF_TRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Stale Processing Threshold (in seconds)
    |--------------------------------------------------------------------------
    |
    | If a job is marked 'processing' without completion or heartbeat
    | after this duration, it can be reclaimed by retry mechanisms.
    |
    */
    'stale_after' => (int) env('RESULT_PDF_STALE_SECONDS', 600),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk for Result PDFs
    |--------------------------------------------------------------------------
    */
    'disk' => env('RESULT_PDF_DISK', 'local'),
];
