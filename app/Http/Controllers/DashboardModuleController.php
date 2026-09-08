<?php

namespace App\Http\Controllers;

use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\StudentViolation;
use App\Models\ViolationType;
use App\Http\Requests\StoreStudentRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardModuleController extends Controller
{
    public function createStudent()
    {
        return view('dashboard.students.create', [
            'classes' => SchoolClass::orderBy('grade_level')->orderBy('name')->get(),
        ]);
    }

    public function storeStudent(StoreStudentRequest $request)
    {
        $student = Student::create($request->validated() + [
            'status' => 'aktif',
            'discipline_points' => 0,
            'discipline_status' => 'aman',
        ]);

        return redirect()->route('dashboard.student', $student)->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(string $module)
    {
        $data = match ($module) {
            'students' => ['title' => 'Buku Induk Siswa', 'description' => 'Data siswa aktif dari database sekolah.', 'rows' => Student::with('schoolClass')->where('status', 'aktif')->orderBy('name')->paginate(20), 'rowLink' => 'dashboard.student'],
            'violations' => ['title' => 'Input Pelanggaran', 'description' => 'Riwayat pelanggaran dan pencatatan kedisiplinan.', 'rows' => StudentViolation::with(['student', 'violationType', 'reporter'])->latest('occurred_at')->paginate(20)],
            'agenda' => ['title' => 'Jadwal & Home Visit', 'description' => 'Agenda konseling yang tersimpan dan terjadwal.', 'rows' => CounselingSession::with(['student', 'counselor'])->orderBy('scheduled_at')->paginate(20)],
            'assessments' => ['title' => 'Asesmen & Karir', 'description' => 'Ringkasan sesi dan topik pendampingan siswa.', 'rows' => CounselingSession::with('student')->latest('updated_at')->paginate(20)],
            'reports' => ['title' => 'Rekap & Laporan', 'description' => 'Rekap data operasional BK berdasarkan data aktual.', 'rows' => StudentViolation::with(['student', 'violationType'])->latest('occurred_at')->paginate(20)],
            'settings' => ['title' => 'Pengaturan', 'description' => 'Jenis pelanggaran yang aktif di sistem.', 'rows' => ViolationType::orderBy('category')->orderBy('name')->paginate(20)],
        };

        return view('dashboard.module', $data + ['module' => $module]);
    }

    public function student(Student $student)
    {
        $student->load([
            'schoolClass',
            'violations.violationType',
            'violations.reporter',
            'counselingSessions.counselor',
        ]);

        $violations = $student->violations->sortByDesc('occurred_at')->values();
        $sessions = $student->counselingSessions->sortByDesc('scheduled_at')->values();
        $totalPoints = (int) $violations->sum('point_snapshot');
        $attendance = $student->attendance_percentage ?? null;

        return view('dashboard.student', compact('student', 'violations', 'sessions', 'totalPoints', 'attendance'));
    }

    public function exportStudent(Student $student): StreamedResponse
    {
        $student->load(['violations.violationType', 'violations.reporter', 'counselingSessions.counselor']);

        return response()->streamDownload(function () use ($student) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Buku Induk Siswa', $student->name]);
            fputcsv($handle, ['NIS', $student->nis, 'NISN', $student->nisn, 'Kelas', $student->schoolClass?->name]);
            fputcsv($handle, []);
            fputcsv($handle, ['Tanggal', 'Jenis Pelanggaran', 'Poin', 'Pelapor', 'Status']);
            foreach ($student->violations->sortByDesc('occurred_at') as $violation) {
                fputcsv($handle, [$violation->occurred_at?->format('Y-m-d H:i'), $violation->violationType?->name, $violation->point_snapshot, $violation->reporter?->name, $violation->action_status]);
            }
            fclose($handle);
        }, 'buku-induk-'.str($student->name)->slug().'.csv', ['Content-Type' => 'text/csv']);
    }

    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $students = Student::with('schoolClass')->where('status', 'aktif')
            ->when($term !== '', fn ($query) => $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('nis', 'like', "%{$term}%")->orWhere('nisn', 'like', "%{$term}%")))
            ->orderBy('name')->limit(20)->get();

        return view('dashboard.search', compact('students', 'term'));
    }

    public function exportViolations(): StreamedResponse
    {
        $violations = StudentViolation::with(['student', 'violationType'])->latest('occurred_at')->get();
        return response()->streamDownload(function () use ($violations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Siswa', 'NIS', 'Jenis', 'Poin', 'Status']);
            foreach ($violations as $violation) {
                fputcsv($handle, [$violation->occurred_at?->format('Y-m-d H:i'), $violation->student?->name, $violation->student?->nis, $violation->violationType?->name, $violation->point_snapshot, $violation->action_status]);
            }
            fclose($handle);
        }, 'rekap-pelanggaran-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
