<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'development_hint',
        'weight',
        'display_order',
        'step_group',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
            'display_order' => 'integer',
            'step_group' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function careerProfiles(): HasMany
    {
        return $this->hasMany(CareerProfile::class);
    }
}
