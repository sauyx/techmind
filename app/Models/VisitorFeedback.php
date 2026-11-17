<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorFeedback extends Model
{
    use HasFactory;

    protected $table = 'visitor_feedbacks';

    protected $fillable = [
        'test_session_id',
        'score',
        'comment',
        'suggestion',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(TestSession::class, 'test_session_id');
    }
}
