<?php

declare(strict_types=1);

namespace App\Http;

final class Request
{
    /**
     * @return array<string, mixed>
     */
    public function input(): array
    {
        return $_POST;
    }

    /**
     * @return array<string, mixed>
     */
    public function files(): array
    {
        return $_FILES;
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }
}
