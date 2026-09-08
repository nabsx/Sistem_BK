<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'nis',
        'nisn',
        'name',
        'photo_path',
        'gender',
        'birth_date',
        'status',
        'school_class_id',
        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'discipline_points',
        'discipline_status',
        'dapodik_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'dapodik_synced_at' => 'datetime',
            'discipline_points' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }

    // Riwayat seluruh transaksi pelanggaran (Kronologi Rekam Kedisiplinan)
    public function violations()
    {
        return $this->hasMany(StudentViolation::class)->latest('occurred_at');
    }

    // Riwayat log tindakan/ambang batas yang pernah terpicu
    public function actionLogs()
    {
        return $this->hasMany(ViolationActionLog::class)->latest();
    }

    // Riwayat sesi konseling & home visit
    public function counselingSessions()
    {
        return $this->hasMany(CounselingSession::class)->latest('scheduled_at');
    }

    /*
    |--------------------------------------------------------------------
    | Aggregates / Computed Accessors
    |--------------------------------------------------------------------
    | Kolom `discipline_points` di tabel students bersifat denormalized
    | cache (di-maintain oleh ViolationPointService/Observer) supaya query
    | dashboard/listing tidak perlu SUM() berulang. Method di bawah adalah
    | "source of truth" yang bisa dipakai untuk audit/rekalkulasi manual.
    */

    // Total poin murni dari transaksi pelanggaran (tanpa cache)
    public function calculateTotalPointsFromLedger(): int
    {
        return (int) $this->violations()->sum('point_snapshot');
    }

    public function scopeWithHighestPoints($query)
    {
        return $query->orderByDesc('discipline_points');
    }

    public function scopeNeedsAttention($query, int $threshold = 60)
    {
        return $query->where('discipline_points', '>=', $threshold);
    }

    public function latestActionLog()
    {
        return $this->hasOne(ViolationActionLog::class)->latestOfMany();
    }
}