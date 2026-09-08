<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentViolation extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'violation_type_id',
        'reported_by',
        'point_snapshot',
        'occurred_at',
        'location',
        'notes',
        'evidence_path',
        'action_status',
        'notified_homeroom_teacher',
        'notified_guardian',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'notified_at' => 'datetime',
            'point_snapshot' => 'integer',
            'notified_homeroom_teacher' => 'boolean',
            'notified_guardian' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function violationType()
    {
        return $this->belongsTo(ViolationType::class);
    }

    // Guru Piket / Guru BK yang menginput
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function actionLog()
    {
        return $this->hasOne(ViolationActionLog::class);
    }

    /*
    |--------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------
    */

    public function scopeToday($query)
    {
        return $query->whereDate('occurred_at', now()->toDateString());
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }

    public function scopeForClass($query, string $schoolClassId)
    {
        return $query->whereHas('student', fn ($q) => $q->where('school_class_id', $schoolClassId));
    }
}