<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineThreshold extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'points',
        'status',
        'recommended_action',
        'sop_reference',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['points' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('points');
    }
}
