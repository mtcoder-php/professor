<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('portfolio_files')->onDelete('cascade');
            $table->foreignId('evaluated_by')->constrained('users')->onDelete('cascade');
            $table->decimal('score', 8, 2);
            $table->text('comment')->nullable();
            $table->dateTime('evaluated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_evaluations');
    }
};
