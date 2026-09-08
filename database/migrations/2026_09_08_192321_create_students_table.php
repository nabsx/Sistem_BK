<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identitas dasar
            $table->string('nis', 20)->unique();
            $table->string('nisn', 20)->unique()->nullable();
            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->enum('gender', ['L', 'P']);
            $table->date('birth_date')->nullable();
            $table->enum('status', ['aktif', 'lulus', 'pindah', 'nonaktif'])->default('aktif');

            // Relasi akademik
            $table->foreignUuid('school_class_id')->constrained('school_classes')->restrictOnDelete();

            // Data wali/orang tua (disederhanakan; bisa dipecah ke tabel guardians terpisah jika perlu >1 wali)
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_relation', 30)->nullable(); // Ayah/Ibu/Wali

            // Kalkulasi poin kedisiplinan — kolom ter-denormalisasi untuk performa dashboard,
            // sumber kebenaran (source of truth) tetap tabel student_violations & reductions.
            $table->unsignedInteger('discipline_points')->default(0);
            $table->enum('discipline_status', ['aman', 'waspada', 'perlu_pendampingan', 'panggilan_ortu'])
                ->default('aman');

            // Sinkronisasi Dapodik (terlihat di UI Buku Induk)
            $table->timestamp('dapodik_synced_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_class_id', 'status']);
            $table->index('discipline_points');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};