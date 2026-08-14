<?php

use App\Models\AcademicProgram;
use App\Models\CatalogCategory;
use App\Models\CatalogCourse;
use App\Models\CatalogCourseDetail;
use App\Support\CatalogSlugResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public const DIPLOMAS_CATEGORY_ID = 14;

    public function up(): void
    {
        Schema::table('catalog_courses', function (Blueprint $table) {
            if (! Schema::hasColumn('catalog_courses', 'academic_program_id')) {
                $table->unsignedBigInteger('academic_program_id')->nullable()->after('id');
                $table->foreign('academic_program_id')
                    ->references('id')
                    ->on('academic_programs')
                    ->nullOnDelete();
                $table->unique('academic_program_id');
            }

            if (! Schema::hasColumn('catalog_courses', 'duration_hours')) {
                $table->unsignedInteger('duration_hours')->nullable()->after('delivery_type');
            }

            if (! Schema::hasColumn('catalog_courses', 'duration_days')) {
                $table->unsignedInteger('duration_days')->nullable()->after('duration_hours');
            }

            if (! Schema::hasColumn('catalog_courses', 'city')) {
                $table->string('city')->nullable()->after('duration_days');
            }

            if (! Schema::hasColumn('catalog_courses', 'duration_label')) {
                $table->string('duration_label')->nullable()->after('city');
            }
        });

        if (! CatalogCategory::query()->whereKey(self::DIPLOMAS_CATEGORY_ID)->exists()) {
            CatalogCategory::query()->create([
                'id' => self::DIPLOMAS_CATEGORY_ID,
                'title_ar' => 'الدبلومات',
                'title_en' => 'Diplomas',
                'slug' => 'aldbloat',
                'sort_order' => 20,
                'sidebar_visible' => true,
            ]);
        }

        $this->syncAcademicDiplomas();
    }

    public function down(): void
    {
        $courseIds = CatalogCourse::query()
            ->whereNotNull('academic_program_id')
            ->pluck('id');

        if ($courseIds->isNotEmpty()) {
            DB::table('catalog_category_course')->whereIn('course_id', $courseIds)->delete();
            CatalogCourseDetail::query()->whereIn('course_id', $courseIds)->delete();
            CatalogCourse::query()->whereIn('id', $courseIds)->delete();
        }

        CatalogCategory::query()->whereKey(self::DIPLOMAS_CATEGORY_ID)->delete();

        Schema::table('catalog_courses', function (Blueprint $table) {
            if (Schema::hasColumn('catalog_courses', 'academic_program_id')) {
                $table->dropUnique(['academic_program_id']);
                $table->dropForeign(['academic_program_id']);
                $table->dropColumn('academic_program_id');
            }

            foreach (['duration_hours', 'duration_days', 'city', 'duration_label'] as $column) {
                if (Schema::hasColumn('catalog_courses', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function syncAcademicDiplomas(): void
    {
        $nextId = (int) (CatalogCourse::query()->max('id') ?? 0);

        AcademicProgram::query()
            ->where('type', 'diploma')
            ->where('status', 'active')
            ->with(['batches' => function ($query) {
                $query->where('status', 'active')
                    ->where('enrollment_open', true)
                    ->where('tuition_amount', '>', 0)
                    ->orderBy('start_date');
            }])
            ->orderBy('id')
            ->each(function (AcademicProgram $program) use (&$nextId) {
                if (CatalogCourse::query()->where('academic_program_id', $program->id)->exists()) {
                    return;
                }

                $batch = $program->batches->first();
                $tuition = $batch ? (float) $batch->tuition_amount : null;
                $nextId++;

                $course = CatalogCourse::query()->create([
                    'id' => $nextId,
                    'academic_program_id' => $program->id,
                    'title_ar' => $program->name_ar,
                    'title_en' => $program->name_en,
                    'image' => $program->poster_image,
                    'price_online' => $tuition,
                    'price_onsite' => $tuition,
                    'delivery_type' => match ($batch?->study_mode) {
                        'remote' => 'online',
                        default => 'onsite',
                    },
                    'duration_label' => $program->duration_label ?: ($program->duration_months ? $program->duration_months.' شهر' : null),
                    'city' => $program->city,
                    'status' => 'published',
                    'is_featured' => true,
                    'is_self_learning' => false,
                ]);

                CatalogSlugResolver::assignSlug(
                    $course,
                    Str::slug($program->name_en ?: $program->name_ar) ?: ('diploma-'.$course->id),
                );

                $course->categories()->sync([self::DIPLOMAS_CATEGORY_ID]);

                $skillsHtml = '';
                $skills = collect($program->skills ?? [])->filter();
                if ($skills->isNotEmpty()) {
                    $items = $skills->map(function ($skill) {
                        $label = is_array($skill)
                            ? ($skill['label'] ?? $skill['name'] ?? reset($skill))
                            : $skill;

                        return '<li>'.e((string) $label).'</li>';
                    })->implode('');
                    $skillsHtml = '<ul>'.$items.'</ul>';
                }

                CatalogCourseDetail::query()->create([
                    'course_id' => $course->id,
                    'brief_ar' => $program->summary
                        ? '<p>'.nl2br(e($program->summary)).'</p>'
                        : null,
                    'features_ar' => $skillsHtml ?: null,
                ]);
            });
    }
};
