<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status', 32)->default('pending_payment');
            $table->string('payment_method', 32)->nullable();
            $table->string('payment_ref')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('course_id');
            $table->unsignedInteger('training_id')->nullable();
            $table->string('delivery_type', 32);
            $table->decimal('price', 10, 2)->default(0);
            $table->string('course_title')->nullable();
            $table->string('course_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
