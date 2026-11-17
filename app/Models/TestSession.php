<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'visitor_name',
        'status',
        'ip_hash',
        'user_agent_hash',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(TestResult::class);
    }
}
