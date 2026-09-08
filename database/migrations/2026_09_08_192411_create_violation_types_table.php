<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('category', [
                'keterlambatan',
                'kedisiplinan_seragam',
                'kerapian_atribut',
                'ketertiban_khusus',
            ]);
            $table->string('name');                     // "Terlambat Masuk Sekolah (>15 Menit)"
            $table->text('description')->nullable();
            $table->unsignedInteger('point_weight');     // bobot poin, misal 5, 10, 15, 25
            $table->enum('severity', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->boolean('requires_evidence')->default(false); // wajib upload bukti foto?
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_types');
    }
};