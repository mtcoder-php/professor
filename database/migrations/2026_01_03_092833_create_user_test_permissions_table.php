<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_test_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->boolean('is_allowed')->default(false);
            $table->foreignId('allowed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('allowed_at')->nullable();
            $table->timestamps();

            // Unique constraint
            $table->unique(['user_id', 'test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_test_permissions');
    }
};
