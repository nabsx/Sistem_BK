<?php

namespace App\Http\Controllers;

use App\Models\CounselingSession;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardModuleController extends Controller
{
    public function show(string $module)
    {
        $data = match ($module) {
            'students' => ['title' => 'Buku Induk Siswa', 'description' => 'Data siswa aktif dari database sekolah.', 'rows' => Student::with('schoolClass')->where('status', 'aktif')->orderBy('name')->paginate(20)],
            'violations' => ['title' => 'Input Pelanggaran', 'description' => 'Riwayat pelanggaran dan pencatatan kedisiplinan.', 'rows' => StudentViolation::with(['student', 'violationType', 'reporter'])->latest('occurred_at')->paginate(20)],
            'agenda' => ['title' => 'Jadwal & Home Visit', 'description' => 'Agenda konseling yang tersimpan dan terjadwal.', 'rows' => CounselingSession::with(['student', 'counselor'])->orderBy('scheduled_at')->paginate(20)],
            'assessments' => ['title' => 'Asesmen & Karir', 'description' => 'Ringkasan sesi dan topik pendampingan siswa.', 'rows' => CounselingSession::with('student')->latest('updated_at')->paginate(20)],
            'reports' => ['title' => 'Rekap & Laporan', 'description' => 'Rekap data operasional BK berdasarkan data aktual.', 'rows' => StudentViolation::with(['student', 'violationType'])->latest('occurred_at')->paginate(20)],
            'settings' => ['title' => 'Pengaturan', 'description' => 'Jenis pelanggaran yang aktif di sistem.', 'rows' => ViolationType::orderBy('category')->orderBy('name')->paginate(20)],
        };

        return view('dashboard.module', $data + ['module' => $module]);
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
