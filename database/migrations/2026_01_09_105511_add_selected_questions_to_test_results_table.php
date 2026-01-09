<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            // Store which questions were selected for this attempt
            if (!Schema::hasColumn('test_results', 'selected_questions')) {
                $table->json('selected_questions')->nullable()->after('answers');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn('selected_questions');
        });
    }
};
