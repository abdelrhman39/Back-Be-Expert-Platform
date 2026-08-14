<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fellowships', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('status', 24)->default('open')->index();
            $table->boolean('application_open')->default(true);
            $table->string('legacy_slug')->nullable()->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('registration_applications', function (Blueprint $table) {
            $table->foreignId('fellowship_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registration_applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fellowship_id');
        });

        Schema::dropIfExists('fellowships');
    }
};
