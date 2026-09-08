<?php

namespace App\Http\Controllers;

use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\StudentViolation;
use Illuminate\View\View;

class DashboardPageController extends Controller
{
    public function __invoke(): View
    {
        $todayViolations = StudentViolation::today();
        $students = Student::where('status', 'aktif');
        $attentionStudents = Student::with('schoolClass')
            ->where('status', 'aktif')
            ->needsAttention(40)
            ->withHighestPoints()
            ->limit(10)
            ->get();

        $monthViolations = StudentViolation::whereBetween('occurred_at', [now()->startOfMonth(), now()->endOfMonth()]);
        $lateThisMonth = (clone $monthViolations)->whereHas('violationType', fn ($query) => $query->where('category', 'keterlambatan'))->count();
        $alphaThisMonth = (clone $monthViolations)->whereHas('violationType', fn ($query) => $query->where('category', 'alpha'))->count();
        $monthViolationsCount = (clone $monthViolations)->count();
        $todayViolations = StudentViolation::today();
        $todayLate = (clone $todayViolations)->whereHas('violationType', fn ($query) => $query->where('category', 'keterlambatan'))->count();
        $todayViolationsCount = (clone $todayViolations)->count();

        return view('welcome', [
            'currentUser' => auth()->user(),
            'totalStudents' => (clone $students)->count(),
            'classCount' => (clone $students)->distinct('school_class_id')->count('school_class_id'),
            'activeCases' => CounselingSession::whereIn('status', ['dijadwalkan', 'berlangsung'])->count(),
            'completedThisWeek' => CounselingSession::where('status', 'selesai')->where('updated_at', '>=', now()->startOfWeek())->count(),
            'todayViolations' => $todayViolationsCount,
            'todayLate' => $todayLate,
            'scheduledActions' => CounselingSession::whereIn('type', ['home_visit', 'panggilan_ortu'])->where('status', 'dijadwalkan')->count(),
            'attentionStudents' => $attentionStudents,
            'agenda' => $agenda = CounselingSession::with('student.schoolClass')->upcoming()->limit(3)->get(),
            'topicDistribution' => StudentViolation::with('violationType')->get()->groupBy('violationType.category')->map->count(),
            'monthViolationsCount' => $monthViolationsCount,
            'lateThisMonth' => $lateThisMonth,
            'alphaThisMonth' => $alphaThisMonth,
            'agendaCount' => $agenda->count(),
        ]);
    }
}
