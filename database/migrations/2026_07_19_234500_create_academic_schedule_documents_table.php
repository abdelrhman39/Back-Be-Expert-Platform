<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_schedule_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('academic_programs')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('academic_batches')->nullOnDelete();
            $table->string('semester_key', 32)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['program_id', 'is_published', 'is_featured'], 'acad_sched_docs_prog_pub_feat_idx');
            $table->index(['batch_id', 'is_published'], 'acad_sched_docs_batch_pub_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_schedule_documents');
    }
};
