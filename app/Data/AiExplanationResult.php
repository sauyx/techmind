<?php

namespace App\Data;

readonly class AiExplanationResult
{
    public function __construct(
        public string $content,
        public string $driverUsed,
        public ?string $modelName = null,
        public bool $isFallback = false,
        public ?int $latencyMs = null,
        public string $status = 'success',
        public string $format = 'text',
        public int $attempts = 1,
        public ?string $errorMessage = null,
    ) {}
}
