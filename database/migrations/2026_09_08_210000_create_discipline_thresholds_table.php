<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_thresholds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('points')->unique();
            $table->string('status', 40);
            $table->string('recommended_action');
            $table->text('sop_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_thresholds');
    }
};
