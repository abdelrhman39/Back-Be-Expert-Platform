<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32)->default('custom');
            $table->string('status', 16)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('show_in_footer')->default(false);
            $table->string('legacy_slug')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('cms_pages')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('body')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });

        Schema::create('cms_menus', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('label_ar');
            $table->string('label_en')->nullable();
            $table->string('locale_scope', 8)->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cms_menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('cms_menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('cms_menu_items')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label_ar');
            $table->string('label_en')->nullable();
            $table->string('link_type', 16)->default('none');
            $table->string('route_name')->nullable();
            $table->foreignId('page_id')->nullable()->constrained('cms_pages')->nullOnDelete();
            $table->string('url', 512)->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('permission')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_menu_items');
        Schema::dropIfExists('cms_menus');
        Schema::dropIfExists('cms_page_translations');
        Schema::dropIfExists('cms_pages');
    }
};
