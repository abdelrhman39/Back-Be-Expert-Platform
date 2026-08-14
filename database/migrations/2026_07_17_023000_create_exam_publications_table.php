<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->decimal('total_points', 10, 2);
            $table->unsignedInteger('question_count');
            $table->longText('question_blueprint');
            $table->json('settings_snapshot');
            $table->string('checksum', 64);
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at');
            $table->timestamps();

            $table->unique(['exam_id', 'version']);
            $table->index(['exam_id', 'published_at']);
        });

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->foreignId('publication_id')
                ->nullable()
                ->after('exam_id')
                ->constrained('exam_publications')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('publication_id');
        });

        Schema::dropIfExists('exam_publications');
    }
};
