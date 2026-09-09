<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentViolationRequest;
use App\Http\Resources\StudentViolationResource;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentViolationController extends Controller
{
    /**
     * GET /api/violations
     * Menyuplai tabel "Pencatatan Kedisiplinan Terakhir Hari Ini" & Rekap & Laporan.
     * Query params opsional: date, school_class_id, category, per_page.
     */
    public function index(Request $request)
    {
        $violations = StudentViolation::query()
            ->with(['student.schoolClass', 'violationType', 'reporter'])
            ->when($request->filled('date'), fn ($q) => $q->whereDate('occurred_at', $request->date('date')))
            ->when(!$request->filled('date') && $request->boolean('today_only'), fn ($q) => $q->today())
            ->when($request->filled('school_class_id'), fn ($q) => $q->forClass($request->string('school_class_id')))
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->whereHas('violationType', fn ($vt) => $vt->where('category', $request->string('category')));
            })
            ->latest('occurred_at')
            ->paginate($request->integer('per_page', 15));

        return StudentViolationResource::collection($violations);
    }

    /**
     * POST /api/violations
     * Menyimpan input pelanggaran baru dari form "Input & Pencatatan Pelanggaran Siswa".
     * Kalkulasi poin & pengecekan ambang batas ditangani otomatis lewat
     * StudentViolationObserver (event `created`) -> ViolationPointService.
     */
    public function store(StoreStudentViolationRequest $request)
    {
        abort_unless($request->user()->can('input pelanggaran'), 403, 'Anda tidak memiliki izin untuk mencatat pelanggaran.');

        $violationType = ViolationType::query()->active()->findOrFail($request->validated('violation_type_id'));

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')->store('violation-evidence', 'public');
        }

        $violation = StudentViolation::create([
            'student_id' => $request->validated('student_id'),
            'violation_type_id' => $violationType->id,
            'reported_by' => $request->user()->id,
            'point_snapshot' => $violationType->point_weight, // snapshot bobot poin saat ini
            'occurred_at' => $request->validated('occurred_at'),
            'location' => $request->validated('location'),
            'notes' => $request->validated('notes'),
            'evidence_path' => $evidencePath,
            'notified_homeroom_teacher' => $request->boolean('notify_homeroom_teacher'),
            'notified_guardian' => $request->boolean('notify_guardian'),
            'notified_at' => now(),
        ]);

        $violation->load(['student.schoolClass', 'violationType', 'reporter']);

        return (new StudentViolationResource($violation))
            ->additional(['message' => 'Pelanggaran berhasil dicatat & notifikasi terkirim.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/violations/{studentViolation}
     */
    public function show(StudentViolation $studentViolation)
    {
        $studentViolation->load(['student.schoolClass', 'violationType', 'reporter', 'actionLog']);

        return new StudentViolationResource($studentViolation);
    }

    /**
     * DELETE /api/violations/{studentViolation}
     * Hanya Admin. Poin siswa otomatis direkalkulasi via Observer::deleted().
     */
    public function destroy(StudentViolation $studentViolation)
    {
        if ($studentViolation->evidence_path) {
            Storage::disk('public')->delete($studentViolation->evidence_path);
        }

        $studentViolation->delete();

        return response()->json(['message' => 'Data pelanggaran dihapus & poin siswa direkalkulasi.']);
    }

    /**
     * GET /api/violations/lookup-student?q=...
     * Endpoint pencarian otomatis identitas siswa terlapor (search box di form).
     */
    public function lookupStudent(Request $request)
    {
        $request->validate(['q' => ['required', 'string', 'min:2']]);

        $students = Student::query()
            ->with('schoolClass')
            ->where('status', 'aktif')
            ->where(function ($q) use ($request) {
                $term = $request->string('q');
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('nis', 'like', "%{$term}%")
                    ->orWhere('nisn', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'nis', 'photo_path', 'discipline_points', 'discipline_status', 'school_class_id']);

        return response()->json($students);
    }
}
