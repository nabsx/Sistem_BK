<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('counselor_id')->constrained('users')->restrictOnDelete(); // Guru BK

            $table->enum('type', ['individual', 'kelompok', 'home_visit', 'panggilan_ortu']);
            $table->enum('topic_category', [
                'kedisiplinan_tata_tertib',
                'masalah_belajar_akademik',
                'bimbingan_karir_studi',
                'sosial_pribadi',
            ]);
            $table->string('topic');                       // "Kontrak Belajar & Penataan Ritme Istirahat Malam"
            $table->string('location')->nullable();          // "Ruang Konseling BK 2"

            $table->dateTime('scheduled_at');
            $table->dateTime('finished_at')->nullable();

            $table->text('mediation_result')->nullable();     // hasil & kesepakatan mediasi
            $table->text('counselor_notes')->nullable();

            $table->enum('status', ['dijadwalkan', 'berlangsung', 'selesai', 'batal'])->default('dijadwalkan');
            $table->unsignedTinyInteger('session_number')->default(1); // sesi ke-N untuk siswa ybs

            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};