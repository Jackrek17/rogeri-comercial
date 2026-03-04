<?php

declare(strict_types=1);

return [
    'form' => [
        // File that stores one submission per line (JSONL format)
        'storage_file' => getenv('FORM_STORAGE_FILE') ?: __DIR__ . '/../storage/submissions.ndjson',
        // Directory used to store uploaded attachments
        'uploads_dir' => getenv('FORM_UPLOADS_DIR') ?: __DIR__ . '/../storage/uploads',

        // Max attachment size: 5 MB
        'max_attachment_size' => 5 * 1024 * 1024,
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
        ],
    ],
];
