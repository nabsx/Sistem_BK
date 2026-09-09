<?php

namespace Database\Seeders;

use App\Models\CounselingSession;
use App\Models\DisciplineThreshold;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $permissions = ['input pelanggaran'];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['id' => (string) Str::uuid()],
            );
        }

        $superAdmin = Role::firstOrCreate(
            ['name' => 'super admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()],
        );
        $superAdmin->givePermissionTo($permissions);

        foreach ([
            25 => ['status' => 'waspada', 'recommended_action' => 'Teguran Lisan Tercatat & Bimbingan Wali Kelas'],
            50 => ['status' => 'perlu_pendampingan', 'recommended_action' => 'SP1 & Pendampingan Intensif Guru BK'],
            75 => ['status' => 'panggilan_ortu', 'recommended_action' => 'Panggilan Resmi Orang Tua & Konferensi Kasus'],
            100 => ['status' => 'panggilan_ortu', 'recommended_action' => 'Konferensi Kasus Tingkat Sekolah'],
        ] as $points => $threshold) {
            DisciplineThreshold::updateOrCreate(
                ['points' => $points],
                $threshold + ['id' => (string) Str::uuid(), 'is_active' => true, 'sop_reference' => 'SOP pembinaan disiplin berdasarkan akumulasi poin.'],
            );
        }

        $counselor = User::updateOrCreate(
            ['email' => 'bk.mardisiswa@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Ibu Siti Rahayu, M.Pd.',
                'nip_nik' => '198304122008012014',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $counselor->assignRole('super admin');

        $teacher = User::updateOrCreate(
            ['email' => 'guru.piket@example.com'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Bapak Ahmad Fauzan, S.Pd.',
                'nip_nik' => '197906152005011008',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $classes = collect([
            ['name' => 'X-4', 'grade_level' => 'X', 'major' => null],
            ['name' => 'X-2', 'grade_level' => 'X', 'major' => null],
            ['name' => 'XI IPS 1', 'grade_level' => 'XI', 'major' => 'IPS'],
            ['name' => 'XI IPS 2', 'grade_level' => 'XI', 'major' => 'IPS'],
            ['name' => 'XI MIPA 1', 'grade_level' => 'XI', 'major' => 'MIPA'],
            ['name' => 'XII MIPA 3', 'grade_level' => 'XII', 'major' => 'MIPA'],
        ])->mapWithKeys(function (array $attributes) use ($counselor) {
            $class = SchoolClass::updateOrCreate(
                ['name' => $attributes['name'], 'academic_year' => '2024/2025', 'semester' => 'Ganjil'],
                $attributes + ['id' => (string) Str::uuid(), 'homeroom_teacher_id' => $counselor->id],
            );

            return [$class->name => $class];
        });

        $students = [
            ['nis' => '2024001', 'nisn' => '00871293', 'name' => 'Dimas Arya Pratama', 'gender' => 'L', 'class' => 'XI IPS 2', 'points' => 75, 'status' => 'panggilan_ortu'],
            ['nis' => '2024002', 'nisn' => '00654210', 'name' => 'Fajar Kurniawan', 'gender' => 'L', 'class' => 'XII MIPA 3', 'points' => 60, 'status' => 'perlu_pendampingan'],
            ['nis' => '2024003', 'nisn' => '00812904', 'name' => 'Sarah Melani', 'gender' => 'P', 'class' => 'X-4', 'points' => 50, 'status' => 'perlu_pendampingan'],
            ['nis' => '2024004', 'nisn' => '00723819', 'name' => 'Rafi Alamsyah', 'gender' => 'L', 'class' => 'XI MIPA 1', 'points' => 45, 'status' => 'waspada'],
            ['nis' => '2024005', 'nisn' => '00892011', 'name' => 'Bagus Pangestu', 'gender' => 'L', 'class' => 'X-2', 'points' => 40, 'status' => 'waspada'],
            ['nis' => '2024006', 'nisn' => '00892012', 'name' => 'Rizky Aditya', 'gender' => 'L', 'class' => 'XI IPS 1', 'points' => 10, 'status' => 'aman'],
        ];

        $studentModels = collect($students)->mapWithKeys(function (array $student) use ($classes) {
            $model = Student::updateOrCreate(
                ['nis' => $student['nis']],
                [
                    'id' => (string) Str::uuid(), 'nisn' => $student['nisn'], 'name' => $student['name'], 'gender' => $student['gender'],
                    'status' => 'aktif', 'school_class_id' => $classes[$student['class']]->id,
                    'discipline_points' => $student['points'], 'discipline_status' => $student['status'],
                    'guardian_name' => 'Orang Tua '.$student['name'], 'guardian_relation' => 'Orang Tua',
                ],
            );

            return [$student['name'] => $model];
        });

        $types = collect([
            ['category' => 'keterlambatan', 'name' => 'Terlambat Masuk Sekolah', 'point_weight' => 10, 'severity' => 'ringan'],
            ['category' => 'kedisiplinan_seragam', 'name' => 'Atribut Seragam Tidak Lengkap', 'point_weight' => 15, 'severity' => 'sedang'],
            ['category' => 'kerapian_atribut', 'name' => 'Kerapian Rambut dan Seragam', 'point_weight' => 10, 'severity' => 'ringan'],
            ['category' => 'ketertiban_khusus', 'name' => 'Tidak Hadir Tanpa Keterangan', 'point_weight' => 20, 'severity' => 'sedang'],
        ])->mapWithKeys(function (array $type) {
            $model = ViolationType::updateOrCreate(['name' => $type['name']], $type + ['id' => (string) Str::uuid(), 'is_active' => true]);
            return [$type['category'] => $model];
        });

        $violations = [
            ['student' => 'Dimas Arya Pratama', 'type' => 'keterlambatan', 'points' => 10, 'days' => 0, 'action_status' => 'panggilan_orang_tua'],
            ['student' => 'Dimas Arya Pratama', 'type' => 'keterlambatan', 'points' => 10, 'days' => 2, 'action_status' => 'panggilan_orang_tua'],
            ['student' => 'Fajar Kurniawan', 'type' => 'ketertiban_khusus', 'points' => 20, 'days' => 1, 'action_status' => 'pembinaan_wali_kelas'],
            ['student' => 'Rafi Alamsyah', 'type' => 'kerapian_atribut', 'points' => 10, 'days' => 3, 'action_status' => 'teguran_lisan'],
        ];

        foreach ($violations as $violation) {
            StudentViolation::firstOrCreate(
                ['student_id' => $studentModels[$violation['student']]->id, 'occurred_at' => now()->subDays($violation['days'])->startOfDay()],
                [
                    'id' => (string) Str::uuid(), 'violation_type_id' => $types[$violation['type']]->id, 'reported_by' => $teacher->id,
                    'point_snapshot' => $violation['points'], 'location' => 'Gerbang Depan Sekolah',
                    'notes' => 'Dicatat melalui pemantauan kedisiplinan sekolah.', 'action_status' => $violation['action_status'],
                ],
            );
        }

        $sessions = [
            ['student' => 'Rizky Aditya', 'type' => 'individual', 'topic_category' => 'bimbingan_karir_studi', 'topic' => 'Bimbingan Karir & Minat Studi Lanjut PTN', 'location' => 'Ruang BK 1', 'scheduled_at' => now()->addHours(2)],
            ['student' => 'Dimas Arya Pratama', 'type' => 'panggilan_ortu', 'topic_category' => 'kedisiplinan_tata_tertib', 'topic' => 'Konfirmasi Pemantauan Kehadiran & Surat Peringatan', 'location' => 'Ruang Rapat BK', 'scheduled_at' => now()->addHours(5)],
            ['student' => 'Sarah Melani', 'type' => 'home_visit', 'topic_category' => 'sosial_pribadi', 'topic' => 'Pendampingan Adaptasi Lingkungan', 'location' => 'Kel. Bulusan, Tembalang', 'scheduled_at' => now()->addDay()->setTime(14, 0)],
        ];

        foreach ($sessions as $session) {
            CounselingSession::firstOrCreate(
                ['student_id' => $studentModels[$session['student']]->id, 'scheduled_at' => $session['scheduled_at']],
                [
                    'id' => (string) Str::uuid(),
                    'counselor_id' => $counselor->id,
                    'type' => $session['type'],
                    'topic_category' => $session['topic_category'],
                    'topic' => $session['topic'],
                    'location' => $session['location'],
                    'scheduled_at' => $session['scheduled_at'],
                    'status' => 'dijadwalkan',
                    'session_number' => 1,
                ],
            );
        }
    }
}
