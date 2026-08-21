<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum Hall Tickets per Combined PDF
    |--------------------------------------------------------------------------
    |
    | Limits the maximum number of student hall tickets compiled into a single
    | PDF file to prevent PHP memory limits and Dompdf rendering bottlenecks
    | on shared hosting.
    |
    | Default is 50 for conservative Hostinger memory safety; hard-capped at 100.
    |
    */
    'max_per_pdf' => min(100, max(1, (int) env('MAX_HALL_TICKETS_PER_PDF', 50))),

    /*
    |--------------------------------------------------------------------------
    | Batch Expiration Duration (in hours)
    |--------------------------------------------------------------------------
    |
    | Generated PDF files and batch records will expire after this duration.
    | The scheduled cleanup command deletes expired PDF files from storage.
    |
    */
    'expiration_hours' => (int) env('HALL_TICKET_EXPIRATION_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Batch Retention Duration (in days)
    |--------------------------------------------------------------------------
    |
    | Expired batch records older than this duration will be pruned by the cleanup command.
    |
    */
    'retention_days' => (int) env('HALL_TICKET_RETENTION_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Dedicated Queue Name
    |--------------------------------------------------------------------------
    */
    'queue' => env('HALL_TICKET_QUEUE', 'pdf'),

    /*
    |--------------------------------------------------------------------------
    | Job Timeout & Retry Configuration (in seconds)
    |--------------------------------------------------------------------------
    |
    | Production Timing Relationship:
    | job_timeout (300s) < DB_QUEUE_RETRY_AFTER (420s) < stale_after (600s)
    |
    */
    'job_timeout' => (int) env('HALL_TICKET_JOB_TIMEOUT', 300),
    'job_tries' => (int) env('HALL_TICKET_JOB_TRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Stale Job Recovery Threshold (in seconds)
    |--------------------------------------------------------------------------
    |
    | If a job remains in 'processing' status beyond this threshold (due to a worker crash),
    | subsequent worker instances are allowed to reclaim ownership and process it.
    |
    */
    'stale_after' => (int) env('HALL_TICKET_STALE_AFTER', 600),

    /*
    |--------------------------------------------------------------------------
    | Storage Disk & Base Directory
    |--------------------------------------------------------------------------
    */
    'disk' => env('HALL_TICKET_DISK', 'local'),
    'storage_directory' => 'hall-tickets',
];
