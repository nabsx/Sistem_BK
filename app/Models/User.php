<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasUuid, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'nip_nik',
        'phone_number',
        'password',
        'avatar_path',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // Relasi: kelas-kelas yang diwalikan (jika role Wali Kelas)
    public function homeroomClasses()
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    // Relasi: pelanggaran yang dilaporkan user ini (Guru Piket/BK)
    public function reportedViolations()
    {
        return $this->hasMany(StudentViolation::class, 'reported_by');
    }

    // Relasi: sesi konseling yang ditangani (jika role Guru BK)
    public function counselingSessions()
    {
        return $this->hasMany(CounselingSession::class, 'counselor_id');
    }
}