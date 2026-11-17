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
    ) {}
}
