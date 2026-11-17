<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'visitor_name',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class)->orderBy('rank');
    }
}
