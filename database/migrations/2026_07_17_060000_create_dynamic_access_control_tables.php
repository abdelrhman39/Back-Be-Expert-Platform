<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_roles', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->string('scope', 24)->default('all');
            $table->boolean('is_system')->default(false);
            $table->boolean('is_super')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('access_permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 160)->unique();
            $table->string('name_ar');
            $table->text('description')->nullable();
            $table->string('group_key', 64)->index();
            $table->string('scope', 24)->default('admin')->index();
            $table->boolean('is_system')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('access_permission_role', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('access_roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('access_permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('access_role_user', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('access_roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->primary(['role_id', 'user_id']);
        });

        Schema::create('access_user_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('access_permissions')->cascadeOnDelete();
            $table->string('effect', 8);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'permission_id']);
            $table->index(['user_id', 'effect']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_user_permissions');
        Schema::dropIfExists('access_role_user');
        Schema::dropIfExists('access_permission_role');
        Schema::dropIfExists('access_permissions');
        Schema::dropIfExists('access_roles');
    }
};
