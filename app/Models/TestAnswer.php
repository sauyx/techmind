<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'criterion_id',
        'criterion_code',
        'criterion_name',
        'rating',
        'multiplier_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'multiplier_snapshot' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }
}
