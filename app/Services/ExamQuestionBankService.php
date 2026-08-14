<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamPart;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionCategory;
use App\Models\User;
use App\Support\ExamOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExamQuestionBankService
{
    public function categoriesForCourse(?int $courseId): Collection
    {
        return ExamQuestionCategory::query()
            ->where('is_active', true)
            ->where(fn (Builder $query) => $query
                ->whereNull('course_id')
                ->orWhere('course_id', $courseId))
            ->withCount('questions')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function categoryIdsWithDescendants(int $categoryId, ?int $courseId): array
    {
        $categories = $this->categoriesForCourse($courseId)->keyBy('id');

        if (! $categories->has($categoryId)) {
            return [];
        }

        $ids = [$categoryId];

        do {
            $children = $categories
                ->whereIn('parent_id', $ids)
                ->pluck('id')
                ->diff($ids)
                ->values()
                ->all();
            $ids = array_values(array_unique([...$ids, ...$children]));
        } while ($children !== []);

        return $ids;
    }

    public function bankQuery(
        Exam $exam,
        string $search = '',
        ?int $categoryId = null,
        string $type = '',
        string $difficulty = '',
        bool $excludeAttached = true,
    ): Builder {
        $query = ExamQuestion::query()
            ->with(['category', 'options'])
            ->where('course_id', $exam->course_id)
            ->where('status', 'published');

        if ($excludeAttached) {
            $attached = $exam->parts()
                ->with('questionLinks:id,exam_part_id,question_id')
                ->get()
                ->pluck('questionLinks')
                ->flatten()
                ->pluck('question_id');
            $query->whereNotIn('id', $attached);
        }

        return $query
            ->when(filled($search), fn (Builder $builder) => $builder->where(
                fn (Builder $nested) => $nested
                    ->where('prompt', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%')
                    ->orWhere('tags', 'like', '%'.$search.'%')
            ))
            ->when($categoryId, function (Builder $builder) use ($categoryId, $exam): void {
                $ids = $this->categoryIdsWithDescendants($categoryId, $exam->course_id);
                $builder->whereIn('category_id', $ids ?: [-1]);
            })
            ->when(filled($type), fn (Builder $builder) => $builder->where('type', $type))
            ->when(filled($difficulty), fn (Builder $builder) => $builder->where('difficulty', $difficulty));
    }

    public function createCategory(
        Exam $exam,
        string $name,
        ?int $parentId = null,
        ?string $description = null,
    ): ExamQuestionCategory {
        $parent = $parentId
            ? ExamQuestionCategory::query()
                ->where(fn (Builder $query) => $query
                    ->whereNull('course_id')
                    ->orWhere('course_id', $exam->course_id))
                ->findOrFail($parentId)
            : null;

        return ExamQuestionCategory::query()->firstOrCreate(
            [
                'course_id' => $exam->course_id,
                'parent_id' => $parent?->id,
                'name' => trim($name),
            ],
            [
                'code' => Str::upper(Str::random(8)),
                'description' => $description,
                'sort_order' => (int) ExamQuestionCategory::query()
                    ->where('course_id', $exam->course_id)
                    ->where('parent_id', $parent?->id)
                    ->max('sort_order') + 1,
                'is_active' => true,
            ]
        );
    }

    public function configureRandomPool(
        Exam $exam,
        ExamPart $part,
        User $actor,
        int $count,
        float $points,
        ?int $categoryId = null,
        string $type = '',
        string $difficulty = '',
    ): int {
        $candidates = $this->bankQuery(
            $exam,
            categoryId: $categoryId,
            type: $type,
            difficulty: $difficulty,
            excludeAttached: true,
        )->pluck('id');

        if ($candidates->count() < $count) {
            throw ValidationException::withMessages([
                'randomCount' => "عدد الأسئلة المطابقة ({$candidates->count()}) أقل من العدد المطلوب ({$count}).",
            ]);
        }

        $part->update([
            'shuffle_questions' => true,
            'questions_to_draw' => $count,
            'pool_filters' => [
                'question_ids' => $candidates->values()->all(),
                'category_id' => $categoryId,
                'type' => $type ?: null,
                'difficulty' => $difficulty ?: null,
                'points_per_question' => $points,
                'configured_by' => $actor->id,
                'configured_at' => now()->toIso8601String(),
            ],
        ]);
        $exam->refreshTotalPoints();

        return $candidates->count();
    }

    public function disableRandomPool(Exam $exam, ExamPart $part): void
    {
        $part->update([
            'questions_to_draw' => null,
            'pool_filters' => null,
        ]);
        $exam->refreshTotalPoints();
    }

    public function importCsv(
        Exam $exam,
        User $actor,
        UploadedFile $file,
        ExamQuestionAuthoringService $authoring,
    ): int {
        $handle = fopen($file->getRealPath(), 'rb');

        if (! $handle) {
            throw ValidationException::withMessages(['importFile' => 'تعذر قراءة ملف الاستيراد.']);
        }

        try {
            $header = fgetcsv($handle);
            $header = array_map(
                fn ($value) => Str::lower(trim((string) $value, " \t\n\r\0\x0B\xEF\xBB\xBF")),
                $header ?: []
            );
            $required = ['type', 'prompt', 'difficulty', 'points'];

            if (array_diff($required, $header) !== []) {
                throw ValidationException::withMessages([
                    'importFile' => 'رؤوس الملف غير صحيحة. الحقول الإلزامية: type, prompt, difficulty, points.',
                ]);
            }

            $count = 0;
            $line = 1;

            DB::transaction(function () use ($handle, $header, $exam, $actor, $authoring, &$count, &$line): void {
                while (($values = fgetcsv($handle)) !== false) {
                    $line++;

                    if (collect($values)->filter(fn ($value) => filled($value))->isEmpty()) {
                        continue;
                    }

                    $values = array_pad($values, count($header), null);
                    $row = array_combine($header, array_slice($values, 0, count($header)));
                    $type = trim((string) ($row['type'] ?? ''));
                    $difficulty = trim((string) ($row['difficulty'] ?? 'medium'));

                    if (! array_key_exists($type, ExamOptions::questionTypes())) {
                        throw ValidationException::withMessages(['importFile' => "نوع السؤال غير صالح في الصف {$line}."]);
                    }
                    if (! array_key_exists($difficulty, ExamOptions::difficulties())) {
                        throw ValidationException::withMessages(['importFile' => "مستوى الصعوبة غير صالح في الصف {$line}."]);
                    }

                    $categoryId = $this->resolveImportedCategory($exam, trim((string) ($row['category'] ?? '')));
                    [$options, $correctScalar, $structuredAnswer] = $this->parseImportedAnswer($type, $row, $line);

                    $authoring->createForBank(
                        exam: $exam,
                        actor: $actor,
                        type: $type,
                        prompt: trim((string) ($row['prompt'] ?? '')),
                        explanation: filled($row['explanation'] ?? null) ? trim((string) $row['explanation']) : null,
                        difficulty: $difficulty,
                        points: (float) ($row['points'] ?? 1),
                        options: $options,
                        correctScalar: $correctScalar,
                        structuredAnswer: $structuredAnswer,
                        numericTolerance: (float) ($row['numeric_tolerance'] ?? 0),
                        categoryId: $categoryId,
                        tags: $this->splitValue((string) ($row['tags'] ?? '')),
                    );
                    $count++;
                }
            });

            return $count;
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'importFile' => "تعذر استيراد الصف {$line}: ".$exception->getMessage(),
            ]);
        } finally {
            fclose($handle);
        }
    }

    public function exportRows(Exam $exam): iterable
    {
        $questions = $this->bankQuery($exam, excludeAttached: false)
            ->orderBy('id')
            ->cursor();

        foreach ($questions as $question) {
            $key = $question->answer_key ?? [];
            $options = $question->options
                ->map(fn ($option) => $option->option_key.':'.$option->content)
                ->implode('||');
            $correct = match ($question->type) {
                'single_choice', 'true_false' => (string) ($key['correct'] ?? ''),
                'multiple_choice' => implode('||', $key['correct'] ?? []),
                'numeric' => (string) ($key['value'] ?? ''),
                default => '',
            };
            $structured = match ($question->type) {
                'short_text' => implode('||', $key['accepted'] ?? []),
                'fill_blank' => collect($key['blanks'] ?? [])
                    ->map(fn ($accepted) => implode('|', (array) $accepted))
                    ->implode('||'),
                'matching' => $question->options
                    ->map(fn ($option) => $option->content.' => '.($option->match_data['target'] ?? ''))
                    ->implode('||'),
                'ordering' => collect($key['order'] ?? [])
                    ->map(fn ($optionKey) => $question->options->firstWhere('option_key', $optionKey)?->content)
                    ->filter()
                    ->implode('||'),
                default => '',
            };

            yield [
                $question->type,
                $question->prompt,
                $question->explanation,
                $question->difficulty,
                $question->default_points,
                $question->category?->name,
                implode('||', $question->tags ?? []),
                $options,
                $correct,
                $structured,
                $key['tolerance'] ?? 0,
            ];
        }
    }

    private function resolveImportedCategory(Exam $exam, string $name): ?int
    {
        if ($name === '') {
            return null;
        }

        return $this->createCategory($exam, $name)->id;
    }

    private function parseImportedAnswer(string $type, array $row, int $line): array
    {
        $optionValues = $this->splitValue((string) ($row['options'] ?? ''));
        $correctValues = $this->splitValue((string) ($row['correct_answer'] ?? ''));
        $options = collect($optionValues)->map(function (string $value, int $index) use ($correctValues, $type) {
            [$key, $content] = array_pad(array_map('trim', explode(':', $value, 2)), 2, '');
            $key = $content === '' ? chr(65 + $index) : $key;
            $content = $content === '' ? $value : $content;

            return [
                'source_key' => $key,
                'content' => $content,
                'correct' => $type === 'multiple_choice' && in_array($key, $correctValues, true),
            ];
        })->values();

        $correctScalar = null;
        $structuredAnswer = null;

        if ($type === 'single_choice') {
            $correctIndex = $options->search(fn ($option) => $option['source_key'] === ($correctValues[0] ?? null));
            $correctScalar = $correctIndex === false ? null : (string) $correctIndex;
        } elseif ($type === 'true_false') {
            $correctScalar = ($correctValues[0] ?? 'true') === 'false' ? 'false' : 'true';
        } elseif ($type === 'numeric') {
            $correctScalar = $correctValues[0] ?? null;
        } elseif (! in_array($type, ['essay', 'file_upload'], true)) {
            $structuredAnswer = implode("\n", $this->splitValue((string) ($row['structured_answer'] ?? '')));
        }

        if (blank($row['prompt'] ?? null)) {
            throw ValidationException::withMessages(['importFile' => "نص السؤال مفقود في الصف {$line}."]);
        }

        return [
            $options->map(fn ($option) => [
                'content' => $option['content'],
                'correct' => $option['correct'],
            ])->all(),
            $correctScalar,
            $structuredAnswer,
        ];
    }

    private function splitValue(string $value): array
    {
        return collect(explode('||', $value))
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => $item !== '')
            ->values()
            ->all();
    }
}
