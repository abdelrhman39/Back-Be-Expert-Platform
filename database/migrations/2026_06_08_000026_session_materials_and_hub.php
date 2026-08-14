<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->unsignedSmallInteger('session_number')->nullable()->after('title');
            $table->text('description')->nullable()->after('session_number');
            $table->timestamp('published_at')->nullable()->after('notes');
        });

        Schema::create('session_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32)->default('file');
            $table->string('title');
            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('external_url', 500)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('visibility', 16)->default('published');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['attendance_session_id', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_materials');

        Schema::table('attendance_sessions', function (Blueprint $table) {
            $table->dropColumn(['session_number', 'description', 'published_at']);
        });
    }
};
