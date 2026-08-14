<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('exams')
            ->where('review_policy', 'score_and_answers')
            ->update(['review_policy' => 'correct_answers']);
    }

    public function down(): void
    {
        // The legacy value was invalid and is intentionally not restored.
    }
};
