<?php

namespace App\Observers;

use App\Models\StudentViolation;
use App\Services\ViolationPointService;

class StudentViolationObserver
{
    public function __construct(
        protected ViolationPointService $violationPointService
    ) {
    }

    /**
     * Dipanggil otomatis oleh Eloquent setiap kali StudentViolation baru
     * berhasil disimpan (created). Di sinilah kalkulasi poin & pengecekan
     * ambang batas SOP pembinaan dijalankan.
     */
    public function created(StudentViolation $violation): void
    {
        $this->violationPointService->handleNewViolation($violation);
    }

    /**
     * Jika suatu saat admin mengoreksi/menghapus data pelanggaran (mis. salah
     * input), poin siswa perlu direkalkulasi ulang dari ledger agar konsisten.
     */
    public function deleted(StudentViolation $violation): void
    {
        $student = $violation->student;
        if ($student) {
            $this->violationPointService->recalculateFromLedger($student);
        }
    }

    public function updated(StudentViolation $violation): void
    {
        if ($violation->wasChanged('point_snapshot')) {
            $student = $violation->student;
            if ($student) {
                $this->violationPointService->recalculateFromLedger($student);
            }
        }
    }
}