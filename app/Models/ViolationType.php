<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationType extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'category',
        'name',
        'description',
        'point_weight',
        'severity',
        'requires_evidence',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'point_weight' => 'integer',
            'requires_evidence' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function violations()
    {
        return $this->hasMany(StudentViolation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}