<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiExplanation extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'driver_used',
        'model_name',
        'content',
        'is_fallback',
        'latency_ms',
    ];

    protected function casts(): array
    {
        return [
            'is_fallback' => 'boolean',
            'latency_ms' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }
}
