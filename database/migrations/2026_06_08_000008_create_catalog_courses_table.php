<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_courses', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('slug')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price_online', 10, 2)->nullable();
            $table->decimal('price_onsite', 10, 2)->nullable();
            $table->string('delivery_type', 32)->default('online');
            $table->string('status', 32)->default('published');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_courses');
    }
};
