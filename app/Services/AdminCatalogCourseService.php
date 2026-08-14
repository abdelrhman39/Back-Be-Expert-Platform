<?php

namespace App\Services;

use App\Models\AcademicProgram;
use App\Models\CatalogCategory;
use App\Models\CatalogCourse;
use App\Models\CatalogCourseDetail;
use App\Support\CatalogCourseTabs;
use App\Support\CatalogSlugResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminCatalogCourseService
{
    /**
     * @param  array{
     *   title_ar: string,
     *   title_en?: ?string,
     *   slug?: ?string,
     *   image?: ?string,
     *   price_online?: ?float,
     *   price_onsite?: ?float,
     *   delivery_type?: string,
     *   status?: string,
     *   is_featured?: bool,
     *   is_self_learning?: bool,
     *   duration_hours?: ?int,
     *   duration_days?: ?int,
     *   duration_label?: ?string,
     *   city?: ?string,
     *   academic_program_id?: ?int,
     *   category_ids?: array<int, int>,
     *   details?: array<string, ?string>
     * }  $data
     */
    public function create(array $data): CatalogCourse
    {
        return DB::transaction(function () use ($data) {
            $id = ((int) CatalogCourse::query()->max('id')) + 1;

            $course = CatalogCourse::query()->create([
                'id' => $id,
                ...$this->shellAttributes($data),
            ]);

            CatalogSlugResolver::assignSlug(
                $course,
                filled($data['slug'] ?? null) ? (string) $data['slug'] : null,
            );

            $this->syncRelations($course, $data);
            $this->syncDetails($course, $data['details'] ?? []);

            return $course->fresh(['details', 'categories', 'academicProgram']);
        });
    }

    /**
     * @param  array{
     *   title_ar: string,
     *   title_en?: ?string,
     *   slug?: ?string,
     *   image?: ?string,
     *   price_online?: ?float,
     *   price_onsite?: ?float,
     *   delivery_type?: string,
     *   status?: string,
     *   is_featured?: bool,
     *   is_self_learning?: bool,
     *   duration_hours?: ?int,
     *   duration_days?: ?int,
     *   duration_label?: ?string,
     *   city?: ?string,
     *   academic_program_id?: ?int,
     *   category_ids?: array<int, int>,
     *   details?: array<string, ?string>
     * }  $data
     */
    public function update(CatalogCourse $course, array $data): CatalogCourse
    {
        return DB::transaction(function () use ($course, $data) {
            $course->update($this->shellAttributes($data));

            if (array_key_exists('slug', $data) && filled($data['slug'])) {
                CatalogSlugResolver::assignSlug($course, (string) $data['slug']);
            } elseif (! filled($course->slug)) {
                CatalogSlugResolver::assignSlug($course);
            }

            $this->syncRelations($course, $data);
            $this->syncDetails($course, $data['details'] ?? []);

            return $course->fresh(['details', 'categories', 'academicProgram']);
        });
    }

    /** @param  array<string, mixed>  $data */
    private function shellAttributes(array $data): array
    {
        return [
            'title_ar' => trim((string) $data['title_ar']),
            'title_en' => filled($data['title_en'] ?? null) ? trim((string) $data['title_en']) : null,
            'image' => filled($data['image'] ?? null) ? trim((string) $data['image']) : null,
            'price_online' => $data['price_online'] ?? null,
            'price_onsite' => $data['price_onsite'] ?? null,
            'delivery_type' => $data['delivery_type'] ?? 'online',
            'status' => $data['status'] ?? 'published',
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_self_learning' => (bool) ($data['is_self_learning'] ?? false),
            'duration_hours' => $data['duration_hours'] ?? null,
            'duration_days' => $data['duration_days'] ?? null,
            'duration_label' => filled($data['duration_label'] ?? null) ? trim((string) $data['duration_label']) : null,
            'city' => filled($data['city'] ?? null) ? trim((string) $data['city']) : null,
            'academic_program_id' => $data['academic_program_id'] ?? null,
        ];
    }

    /** @param  array<string, mixed>  $data */
    private function syncRelations(CatalogCourse $course, array $data): void
    {
        if (array_key_exists('category_ids', $data)) {
            $ids = collect($data['category_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $course->categories()->sync($ids);
        }
    }

    /**
     * @param  array{
     *   meta_description_ar?: ?string,
     *   meta_description_en?: ?string,
     *   content_blocks?: list<array{id?: string, type: string, title?: string, content?: string, enabled?: bool}>,
     *   brief_ar?: ?string,
     *   goals_ar?: ?string,
     *   audience_ar?: ?string,
     *   features_ar?: ?string,
     *   topics_ar?: ?string,
     *   outcomes_ar?: ?string,
     *   conditions_ar?: ?string,
     *   faq_ar?: ?string,
     *   article_ar?: ?string,
     * }  $details
     */
    private function syncDetails(CatalogCourse $course, array $details): void
    {
        $payload = ['course_id' => $course->id];

        if (array_key_exists('meta_description_ar', $details)) {
            $payload['meta_description_ar'] = filled($details['meta_description_ar'] ?? null)
                ? (string) $details['meta_description_ar']
                : null;
        }

        if (array_key_exists('meta_description_en', $details)) {
            $payload['meta_description_en'] = filled($details['meta_description_en'] ?? null)
                ? (string) $details['meta_description_en']
                : null;
        }

        $knownFields = [
            'brief', 'goals', 'audience', 'features', 'topics',
            'outcomes', 'conditions', 'faq', 'article',
        ];

        if (array_key_exists('content_blocks', $details) && is_array($details['content_blocks'])) {
            $blocks = [];
            $columnValues = array_fill_keys(
                array_map(fn (string $key) => $key.'_ar', $knownFields),
                null
            );

            foreach ($details['content_blocks'] as $block) {
                if (! is_array($block)) {
                    continue;
                }

                $type = (string) ($block['type'] ?? 'custom');
                $title = trim((string) ($block['title'] ?? ''));
                $content = (string) ($block['content'] ?? '');
                $content = strip_tags(
                    $content,
                    '<p><br><ul><ol><li><strong><b><em><i><u><a><h2><h3><h4><blockquote>'
                );
                $enabled = (bool) ($block['enabled'] ?? true);

                $blocks[] = [
                    'id' => (string) ($block['id'] ?? Str::uuid()),
                    'type' => $type,
                    'title' => $title !== '' ? $title : ($type === 'custom' ? 'قسم مخصص' : CatalogCourseTabs::label($type)),
                    'content' => $content,
                    'enabled' => $enabled,
                ];

                if ($enabled && in_array($type, $knownFields, true) && $columnValues[$type.'_ar'] === null) {
                    $columnValues[$type.'_ar'] = filled(strip_tags($content)) ? $content : null;
                }
            }

            $payload['content_blocks'] = $blocks;
            $payload = array_merge($payload, $columnValues);
        } else {
            foreach ([
                'brief_ar', 'goals_ar', 'audience_ar', 'features_ar', 'topics_ar',
                'outcomes_ar', 'conditions_ar', 'faq_ar', 'article_ar',
            ] as $field) {
                if (array_key_exists($field, $details)) {
                    $value = $details[$field];
                    $payload[$field] = filled($value) ? (string) $value : null;
                }
            }
        }

        CatalogCourseDetail::query()->updateOrCreate(
            ['course_id' => $course->id],
            $payload,
        );
    }

    public function storeCoverImage(mixed $file): string
    {
        if (! $file) {
            throw ValidationException::withMessages(['imageUpload' => ['لم يتم اختيار صورة.']]);
        }

        $path = $file->store('catalog-courses', 'public');

        return '/storage/'.$path;
    }

    public function suggestSlug(string $titleAr, ?string $titleEn = null): string
    {
        $base = Str::slug($titleEn ?: $titleAr);

        return CatalogSlugResolver::ensureUnique($base !== '' ? $base : 'course');
    }

    /** @return list<array{id:int,label:string}> */
    public function categoryOptions(): array
    {
        return CatalogCategory::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (CatalogCategory $category) => [
                'id' => (int) $category->id,
                'label' => $category->title_ar,
            ])
            ->all();
    }

    /** @return list<array{id:int,label:string}> */
    public function programOptions(): array
    {
        return AcademicProgram::query()
            ->orderBy('type')
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'code', 'type'])
            ->map(fn (AcademicProgram $program) => [
                'id' => (int) $program->id,
                'label' => $program->name_ar.($program->code ? ' ('.$program->code.')' : ''),
                'type' => (string) ($program->type ?: 'program'),
            ])
            ->all();
    }
}
