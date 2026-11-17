<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_session_id',
        'career_id',
        'career_code',
        'career_name',
        'rank',
        'saw_score',
        'match_percentage',
        'contribution_breakdown',
        'gap_summary',
    ];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
            'saw_score' => 'decimal:6',
            'match_percentage' => 'decimal:1',
            'contribution_breakdown' => 'array',
            'gap_summary' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }
}
