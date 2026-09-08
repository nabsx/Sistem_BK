<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_violations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('violation_type_id')->constrained('violation_types')->restrictOnDelete();
            $table->foreignUuid('reported_by')->constrained('users')->restrictOnDelete(); // Guru Piket/BK pelapor

            // Poin di-snapshot pada saat input, agar histori tidak berubah
            // walau bobot master violation_type diubah di kemudian hari.
            $table->unsignedInteger('point_snapshot');

            // Konteks kejadian (sesuai form "Kontekstual Kejadian")
            $table->dateTime('occurred_at');
            $table->string('location')->nullable();      // "Gerbang Depan Sekolah"
            $table->text('notes')->nullable();            // catatan guru piket & alasan siswa

            // Bukti/dokumentasi
            $table->string('evidence_path')->nullable();

            // Status tindak lanjut
            $table->enum('action_status', [
                'tercatat',                 // baru dicatat, belum ada tindakan
                'teguran_lisan',
                'surat_peringatan_1',
                'surat_peringatan_2',
                'pembinaan_wali_kelas',
                'konferensi_kasus',
                'panggilan_orang_tua',
                'selesai',
            ])->default('tercatat');

            // Status notifikasi otomatis ke wali kelas / orang tua (WhatsApp dsb)
            $table->boolean('notified_homeroom_teacher')->default(false);
            $table->boolean('notified_guardian')->default(false);
            $table->timestamp('notified_at')->nullable();

            $table->timestamps();

            $table->index(['student_id', 'occurred_at']);
            $table->index('action_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_violations');
    }
};