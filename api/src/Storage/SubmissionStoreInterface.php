<?php

declare(strict_types=1);

namespace App\Storage;

interface SubmissionStoreInterface
{
    /**
     * @param array<string, string> $data
     * @param array<string, mixed>|null $file
     */
    public function save(array $data, ?array $file = null): bool;
}
