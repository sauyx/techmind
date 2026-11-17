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
        'method_code',
        'ranking',
        'top_career_code',
        'top_career_name',
    ];

    protected function casts(): array
    {
        return [
            'ranking' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }
}
