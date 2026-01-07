<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['entry', 'exit'])->comment('entry = Kiruvchi, exit = Chiquvchi');
            $table->integer('questions_count')->default(0);
            $table->decimal('points_per_question', 8, 2)->default(1);
            $table->decimal('total_points', 8, 2)->default(0);
            $table->integer('duration_minutes')->comment('Test davomiyligi (daqiqa)');
            $table->decimal('pass_score', 8, 2)->comment('O\'tish bali');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_retake')->default(false)->comment('Qayta topshirish');
            $table->boolean('show_results')->default(true)->comment('Natijani ko\'rsatish');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
