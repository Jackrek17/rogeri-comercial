<?php

declare(strict_types=1);

namespace App\Storage;

final class FileSubmissionStore implements SubmissionStoreInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private array $config)
    {
    }

    /**
     * @param array<string, string> $data
     * @param array<string, mixed>|null $file
     */
    public function save(array $data, ?array $file = null): bool
    {
        $storageFile = (string) ($this->config['storage_file'] ?? __DIR__ . '/../../storage/submissions.ndjson');
        $uploadDir = (string) ($this->config['uploads_dir'] ?? __DIR__ . '/../../storage/uploads');

        $attachmentInfo = null;

        if ($file !== null && !empty($file['tmp_name']) && is_string($file['tmp_name'])) {
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                return false;
            }

            $originalName = (string) ($file['name'] ?? 'arquivo');
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeFileName = date('Ymd_His') . '_' . bin2hex(random_bytes(6));
            if ($extension !== '') {
                $safeFileName .= '.' . preg_replace('/[^a-zA-Z0-9]/', '', $extension);
            }

            $destination = rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeFileName;
            if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
                return false;
            }

            $attachmentInfo = [
                'original_name' => $originalName,
                'stored_name' => $safeFileName,
                'stored_path' => $destination,
                'mime_type' => (string) ($file['type'] ?? ''),
                'size' => (int) ($file['size'] ?? 0),
            ];
        }

        $record = [
            'submitted_at' => date('c'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data,
            'attachment' => $attachmentInfo,
        ];

        $dir = dirname($storageFile);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return false;
        }

        $json = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            return false;
        }

        return file_put_contents($storageFile, $json . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
    }
}
