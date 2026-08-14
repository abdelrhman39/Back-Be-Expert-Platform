<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id', 120)->nullable()->index();
            $table->timestamps();

            $table->unique('user_id');
            $table->unique('session_id');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('training_id')->nullable();
            $table->string('delivery_type', 32);
            $table->decimal('price_snapshot', 10, 2)->default(0);
            $table->string('course_title')->nullable();
            $table->timestamps();

            $table->unique(['cart_id', 'course_id', 'delivery_type']);
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id', 120)->nullable()->index();
            $table->unsignedInteger('course_id');
            $table->string('course_title')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'course_id']);
            $table->unique(['session_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
