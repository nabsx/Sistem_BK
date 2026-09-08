<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentViolation;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function queueViolationNotifications(StudentViolation $violation, Student $student): void
    {
        $channels = [];

        if ($violation->notified_homeroom_teacher) {
            $channels[] = 'wali_kelas';
        }

        if ($violation->notified_guardian) {
            $channels[] = 'orang_tua';
        }

        if ($channels === []) {
            return;
        }

        Log::info('Notifikasi pelanggaran dicatat untuk diproses', [
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'channels' => $channels,
            'mode' => 'log_only',
        ]);
    }
}
