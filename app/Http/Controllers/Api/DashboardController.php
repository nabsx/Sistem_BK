<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\StudentViolation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/summary
     * Menyuplai 4 kartu ringkasan di Dashboard:
     * Total Siswa Binaan, Kasus Konseling Aktif, Pelanggaran Hari Ini, Home Visit.
     */
    public function summary()
    {
        return response()->json([
            'total_students' => Student::where('status', 'aktif')->count(),

            'active_counseling_cases' => CounselingSession::whereIn('status', ['dijadwalkan', 'berlangsung'])->count(),

            'violations_today' => StudentViolation::today()->count(),
            'violations_today_by_category' => StudentViolation::today()
                ->join('violation_types', 'violation_types.id', '=', 'student_violations.violation_type_id')
                ->select('violation_types.category', DB::raw('count(*) as total'))
                ->groupBy('violation_types.category')
                ->pluck('total', 'category'),

            'home_visits_scheduled' => CounselingSession::where('type', 'home_visit')
                ->where('status', 'dijadwalkan')
                ->count(),
        ]);
    }

    /**
     * GET /api/dashboard/attendance-trend?range=month|semester
     * Tren kehadiran & kedisiplinan mingguan (chart "Tren Kehadiran & Kedisiplinan").
     * Catatan: data kehadiran murni idealnya dari tabel presensi terpisah;
     * di sini dicontohkan agregasi keterlambatan per pekan dari violation ledger.
     */
    public function attendanceTrend()
    {
        $weeklyLateCounts = StudentViolation::query()
            ->join('violation_types', 'violation_types.id', '=', 'student_violations.violation_type_id')
            ->where('violation_types.category', 'keterlambatan')
            ->selectRaw('YEARWEEK(occurred_at, 1) as year_week, COUNT(*) as total')
            ->where('occurred_at', '>=', now()->subWeeks(8))
            ->groupBy('year_week')
            ->orderBy('year_week')
            ->get();

        return response()->json(['weekly_lateness' => $weeklyLateCounts]);
    }

    /**
     * GET /api/dashboard/students-needing-attention
     * Tabel "Perhatian Khusus Siswa" — siswa dengan poin mendekati/melewati ambang batas.
     */
    public function studentsNeedingAttention()
    {
        $students = Student::query()
            ->with('schoolClass', 'latestActionLog')
            ->needsAttention(40)
            ->withHighestPoints()
            ->limit(10)
            ->get(['id', 'name', 'nis', 'school_class_id', 'discipline_points', 'discipline_status']);

        return response()->json($students);
    }
}