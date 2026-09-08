<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');                         // contoh: XI MIPA 3
            $table->string('grade_level', 10);               // X, XI, XII
            $table->string('major', 50)->nullable();         // MIPA, IPS, dst
            $table->foreignUuid('homeroom_teacher_id')        // Wali Kelas
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('academic_year', 20)->default('2024/2025');
            $table->string('semester', 10)->default('Ganjil');
            $table->timestamps();

            $table->unique(['name', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};