<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Career extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'summary',
        'description',
        'skills',
        'tools',
        'roadmap',
        'sort_order',
        'is_active',
        'is_validated',
        'source_note',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'tools' => 'array',
            'roadmap' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'is_validated' => 'boolean',
        ];
    }

    public function careerProfiles(): HasMany
    {
        return $this->hasMany(CareerProfile::class);
    }

    public function criteria(): BelongsToMany
    {
        return $this->belongsToMany(Criterion::class, 'career_profiles')
            ->withPivot('score')
            ->withTimestamps();
    }
}
