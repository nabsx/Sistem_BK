<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CounselingSession extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'type',
        'topic_category',
        'topic',
        'location',
        'scheduled_at',
        'finished_at',
        'mediation_result',
        'counselor_notes',
        'status',
        'session_number',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'finished_at' => 'datetime',
            'session_number' => 'integer',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
            ->where('status', 'dijadwalkan')
            ->orderBy('scheduled_at');
    }
}