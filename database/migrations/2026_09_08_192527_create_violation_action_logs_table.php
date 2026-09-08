<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_action_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('student_violation_id')->nullable()
                ->constrained('student_violations')->nullOnDelete();

            $table->unsignedInteger('points_before');
            $table->unsignedInteger('points_after');
            $table->unsignedInteger('threshold_crossed'); // 25, 50, 75, 100 dst

            $table->string('recommended_action');          // "Teguran Lisan & Bimbingan Wali Kelas"
            $table->text('sop_reference')->nullable();       // ringkasan SOP pembinaan yang dipicu

            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->foreignUuid('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['student_id', 'threshold_crossed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_action_logs');
    }
};