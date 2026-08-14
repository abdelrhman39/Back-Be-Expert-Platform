<?php

namespace App\Services\Zoom;

use RuntimeException;

class ZoomApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $status = null,
        public readonly ?array $response = null,
    ) {
        parent::__construct($message, $status ?? 0);
    }
}
