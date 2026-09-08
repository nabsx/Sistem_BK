<?php

namespace App\Services;

use App\Models\DisciplineThreshold;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\ViolationActionLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * ViolationPointService
 *
 * Menangani seluruh business logic kalkulasi poin kedisiplinan siswa:
 * 1. Menambah poin siswa saat ada StudentViolation baru.
 * 2. Mengecek apakah akumulasi poin melewati salah satu ambang batas
 *    (threshold) SOP pembinaan sekolah.
 * 3. Jika ambang batas terlewati, membuat entri ViolationActionLog
 *    berisi rekomendasi tindakan (mis. "Perlu Panggilan Ortu").
 * 4. Meng-update kolom denormalized `discipline_points` &
 *    `discipline_status` pada tabel students agar dashboard cepat dibaca.
 *
 * Dipanggil dari StudentViolationObserver (event `created`), bisa juga
 * dipanggil manual dari Controller/Console command untuk rekalkulasi.
 */
class ViolationPointService
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * Peta ambang batas poin -> [status siswa, rekomendasi tindakan, ringkasan SOP].
     * Urutan dari terkecil ke terbesar penting untuk menentukan threshold_crossed
     * yang paling relevan (tertinggi yang baru saja dilewati).
     */
    protected const THRESHOLDS = [
        25 => [
            'status' => 'waspada',
            'action' => 'Teguran Lisan Tercatat & Bimbingan Wali Kelas',
            'sop' => 'Kategori Pembinaan Ringan. Belum memerlukan Surat Peringatan (SP) tertulis. '
                . 'Tindakan restoratif diutamakan agar siswa tidak tertinggal materi ajar.',
        ],
        50 => [
            'status' => 'perlu_pendampingan',
            'action' => 'Surat Peringatan 1 (SP1) & Pendampingan Intensif Guru BK',
            'sop' => 'Kategori Pembinaan Sedang. Wajib melibatkan Wali Kelas dan dokumentasi tertulis. '
                . 'Direkomendasikan sesi konseling individu.',
        ],
        75 => [
            'status' => 'panggilan_ortu',
            'action' => 'Surat Panggilan Resmi ke Orang Tua & Konferensi Kasus',
            'sop' => 'Kategori Pembinaan Berat. Wajib menghadirkan orang tua/wali dan menyusun '
                . 'kontrak perilaku bersama BK, Wali Kelas, dan Kesiswaan.',
        ],
        100 => [
            'status' => 'panggilan_ortu',
            'action' => 'Konferensi Kasus Tingkat Sekolah & Evaluasi Skorsing',
            'sop' => 'Ambang batas maksimal. Ditangani langsung oleh tim Kesiswaan bersama Kepala Sekolah.',
        ],
    ];

    /**
     * Proses satu transaksi StudentViolation baru: update total poin siswa
     * dan buat log tindakan jika ambang batas baru terlewati.
     */
    public function handleNewViolation(StudentViolation $violation): void
    {
        DB::transaction(function () use ($violation) {
            /** @var Student $student */
            $student = Student::query()->lockForUpdate()->findOrFail($violation->student_id);

            $pointsBefore = $student->discipline_points;
            $pointsAfter = $pointsBefore + $violation->point_snapshot;

            $crossedThreshold = $this->findHighestCrossedThreshold($pointsBefore, $pointsAfter);

            $student->discipline_points = $pointsAfter;
            $student->discipline_status = $crossedThreshold
                ? self::THRESHOLDS[$crossedThreshold]['status']
                : $this->statusForPoints($pointsAfter);
            $student->save();

            if ($crossedThreshold !== null) {
                $this->logThresholdCrossing($student, $violation, $pointsBefore, $pointsAfter, $crossedThreshold);
            }

            $this->notificationService->queueViolationNotifications($violation, $student);
        });
    }

    /**
     * Rekalkulasi ulang total poin siswa langsung dari ledger transaksi
     * (berguna untuk perbaikan data / audit, tidak bergantung pada cache).
     */
    public function recalculateFromLedger(Student $student): int
    {
        $total = (int) $student->violations()->sum('point_snapshot');

        $student->discipline_points = $total;
        $student->discipline_status = $this->statusForPoints($total);
        $student->save();

        return $total;
    }

    /**
     * Cari ambang batas TERTINGGI yang baru saja dilewati antara poin lama
     * dan poin baru. Return null jika tidak ada ambang batas yang dilewati.
     */
    protected function findHighestCrossedThreshold(int $before, int $after): ?int
    {
        $crossed = null;

        foreach ($this->thresholds() as $threshold) {
            if ($before < $threshold->points && $after >= $threshold->points) {
                $crossed = $threshold->points; // ambil yang terbesar jika melompati >1 sekaligus
            }
        }

        return $crossed;
    }

    protected function statusForPoints(int $points): string
    {
        $thresholds = $this->thresholds();
        $status = 'aman';

        foreach ($thresholds as $threshold) {
            if ($points >= $threshold->points) {
                $status = $threshold->status;
            }
        }

        return $status;
    }

    protected function thresholds(): Collection
    {
        $thresholds = DisciplineThreshold::query()->active()->get();

        return $thresholds->isNotEmpty()
            ? $thresholds
            : collect(collect(self::THRESHOLDS)->map(fn (array $meta, int $points) => new DisciplineThreshold([
                'points' => $points,
                'status' => $meta['status'],
'recommended_action' => $meta->recommended_action,
                'sop_reference' => $meta['sop'],
            ]))->values());
    }

    protected function logThresholdCrossing(
        Student $student,
        StudentViolation $violation,
        int $pointsBefore,
        int $pointsAfter,
        int $threshold
    ): void {
        $meta = $this->thresholds()->firstWhere('points', $threshold);

        ViolationActionLog::create([
            'student_id' => $student->id,
            'student_violation_id' => $violation->id,
            'points_before' => $pointsBefore,
            'points_after' => $pointsAfter,
            'threshold_crossed' => $threshold,
            'recommended_action' => $meta->recommended_action,
            'sop_reference' => $meta->sop_reference,
            'status' => 'open',
        ]);

        Log::info("Threshold {$threshold} poin terlewati untuk siswa {$student->id}", [
            'points_after' => $pointsAfter,
            'recommended_action' => $meta['action'],
        ]);

        // TODO: dispatch Notification/Job di sini, misal:
        // NotifyGuardianOfThreshold::dispatch($student, $meta);
        // NotifyHomeroomTeacher::dispatch($student, $meta);
    }
}
