<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViolationActionLog extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'student_violation_id',
        'points_before',
        'points_after',
        'threshold_crossed',
        'recommended_action',
        'sop_reference',
        'status',
        'handled_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'points_before' => 'integer',
            'points_after' => 'integer',
            'threshold_crossed' => 'integer',
            'resolved_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function triggeringViolation()
    {
        return $this->belongsTo(StudentViolation::class, 'student_violation_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}