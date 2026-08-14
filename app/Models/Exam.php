<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'section_id',
        'course_id',
        'created_by',
        'title',
        'title_en',
        'instructions',
        'instructions_en',
        'type',
        'language_policy',
        'status',
        'opens_at',
        'closes_at',
        'duration_minutes',
        'max_attempts',
        'attempt_policy',
        'grade_selection',
        'total_points',
        'passing_percent',
        'shuffle_questions',
        'shuffle_options',
        'one_question_per_page',
        'allow_back_navigation',
        'require_access_code',
        'access_code_hash',
        'result_release',
        'review_policy',
        'settings',
        'published_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'total_points' => 'decimal:2',
            'passing_percent' => 'decimal:2',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'one_question_per_page' => 'boolean',
            'allow_back_navigation' => 'boolean',
            'require_access_code' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class);
    }

    public function resolveLanguage(?string $requested = null, ?User $user = null): string
    {
        return match ($this->language_policy) {
            'en_only' => 'en',
            'student_choice' => in_array($requested, ['ar', 'en'], true)
                ? $requested
                : (($user?->locale === 'en') ? 'en' : 'ar'),
            'student_locale' => $user?->locale === 'en' ? 'en' : 'ar',
            default => 'ar',
        };
    }

    public function localizedTitle(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' && filled($this->title_en) ? $this->title_en : $this->title;
    }

    public function localizedInstructions(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' && filled($this->instructions_en)
            ? $this->instructions_en
            : $this->instructions;
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(AcademicCourse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(ExamPart::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(ExamPublication::class)->orderByDesc('version');
    }

    public function latestPublication(): ?ExamPublication
    {
        return $this->publications()->first();
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(ExamAccommodation::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(ExamCandidate::class);
    }

    public function isAvailableAt(?\DateTimeInterface $moment = null): bool
    {
        $moment = $moment ? Carbon::parse($moment) : now();

        return $this->status === 'published'
            && (! $this->opens_at || $moment->greaterThanOrEqualTo($this->opens_at))
            && (! $this->closes_at || $moment->lessThanOrEqualTo($this->closes_at));
    }

    public function isAvailableFor(AcademicStudent $student, ?\DateTimeInterface $moment = null): bool
    {
        $moment = $moment ? Carbon::parse($moment) : now();
        $accommodation = $this->accommodations()
            ->where('student_id', $student->id)
            ->first();
        $isIndividuallyReopened = (bool) $accommodation?->override_exam_availability;
        $statusAllowsAccess = $this->status === 'published'
            || ($this->status === 'closed' && $isIndividuallyReopened);

        if (! $statusAllowsAccess) {
            return false;
        }

        $opensAt = $isIndividuallyReopened
            ? $accommodation?->opens_at
            : ($accommodation?->opens_at ?? $this->opens_at);
        $closesAt = $isIndividuallyReopened
            ? $accommodation?->closes_at
            : ($accommodation?->closes_at ?? $this->closes_at);

        return (! $opensAt || $moment->greaterThanOrEqualTo($opensAt))
            && (! $closesAt || $moment->lessThanOrEqualTo($closesAt));
    }

    public function attemptLimitFor(AcademicStudent $student): ?int
    {
        $accommodation = $this->accommodations()
            ->where('student_id', $student->id)
            ->first();

        if ($accommodation?->unlimited_attempts) {
            return null;
        }

        if ($this->attempt_policy === 'unlimited') {
            return null;
        }

        $baseAttempts = $this->attempt_policy === 'single'
            ? 1
            : max(1, (int) $this->max_attempts);

        return $baseAttempts + (int) ($accommodation?->extra_attempts ?? 0);
    }

    public function selectedAttemptFor(AcademicStudent|int $student): ?ExamAttempt
    {
        $studentId = $student instanceof AcademicStudent ? $student->id : $student;

        return $this->selectAttemptFrom(
            $this->attempts()
                ->where('student_id', $studentId)
                ->where('status', 'graded')
                ->get(),
        );
    }

    public function selectAttemptFrom(iterable $attempts): ?ExamAttempt
    {
        $graded = collect($attempts)
            ->filter(fn (ExamAttempt $attempt) => $attempt->status === 'graded');

        if ($this->grade_selection === 'latest') {
            return $graded->sortByDesc('attempt_number')->first();
        }

        return $graded
            ->sort(function (ExamAttempt $left, ExamAttempt $right): int {
                $scoreOrder = (float) $right->percentage <=> (float) $left->percentage;

                return $scoreOrder !== 0
                    ? $scoreOrder
                    : $right->attempt_number <=> $left->attempt_number;
            })
            ->first();
    }

    /** @return Collection<int, ExamAttempt> */
    public function selectedAttemptsFrom(iterable $attempts): Collection
    {
        return collect($attempts)
            ->groupBy('student_id')
            ->map(fn (Collection $studentAttempts) => $this->selectAttemptFrom($studentAttempts))
            ->filter()
            ->values();
    }

    public function requiresManualGrading(): bool
    {
        if ($this->parts()
            ->whereHas('questions', fn ($query) => $query->whereIn('type', ['essay', 'file_upload']))
            ->exists()) {
            return true;
        }

        $poolQuestionIds = $this->parts()
            ->get()
            ->flatMap(fn (ExamPart $part) => $part->pool_filters['question_ids'] ?? [])
            ->unique();

        return $poolQuestionIds->isNotEmpty()
            && ExamQuestion::withTrashed()
                ->whereIn('id', $poolQuestionIds)
                ->whereIn('type', ['essay', 'file_upload'])
                ->exists();
    }

    public function resultsAreVisibleFor(ExamAttempt $attempt): bool
    {
        if ($attempt->status !== 'graded') {
            return false;
        }

        $policy = $attempt->settings_snapshot['result_release'] ?? $this->result_release;
        $publishedClosesAt = $attempt->publication?->settings_snapshot['closes_at'] ?? null;
        $closesAt = $publishedClosesAt ? Carbon::parse($publishedClosesAt) : $this->closes_at;

        return match ($policy) {
            'immediate', 'after_grading' => true,
            'after_close' => $closesAt !== null && now()->greaterThanOrEqualTo($closesAt),
            'manual' => (bool) ($this->settings['results_released'] ?? false),
            default => false,
        };
    }

    public function reviewPolicyFor(ExamAttempt $attempt): string
    {
        $policy = (string) ($attempt->settings_snapshot['review_policy'] ?? $this->review_policy ?? 'score_only');

        return $policy === 'score_and_answers' ? 'correct_answers' : $policy;
    }

    public function answersAreVisibleFor(ExamAttempt $attempt): bool
    {
        return $this->resultsAreVisibleFor($attempt)
            && in_array($this->reviewPolicyFor($attempt), ['answers', 'correct_answers'], true);
    }

    public function correctionsAreVisibleFor(ExamAttempt $attempt): bool
    {
        return $this->resultsAreVisibleFor($attempt)
            && $this->reviewPolicyFor($attempt) === 'correct_answers';
    }

    public function studentIsEligible(AcademicStudent $student): bool
    {
        return $this->candidates()
            ->where('student_id', $student->id)
            ->where('status', 'eligible')
            ->exists();
    }

    /**
     * Eligible students may open a published exam, an individually reopened
     * closed exam, or any closed/archived exam they already attempted so they
     * can still reach released results and allowed answer reviews.
     */
    public function studentCanAccess(AcademicStudent $student): bool
    {
        if (! $this->studentIsEligible($student)) {
            return false;
        }

        if ($this->status === 'published') {
            return true;
        }

        $individuallyReopened = $this->accommodations()
            ->where('student_id', $student->id)
            ->where('override_exam_availability', true)
            ->exists();

        if ($this->status === 'closed' && $individuallyReopened) {
            return true;
        }

        if (! in_array($this->status, ['closed', 'archived'], true)) {
            return false;
        }

        return $this->attempts()
            ->where('student_id', $student->id)
            ->exists();
    }

    public function snapshotCandidates(): int
    {
        $students = AcademicStudent::query()
            ->where('section_id', $this->section_id)
            ->get();

        foreach ($students as $student) {
            $this->candidates()->firstOrCreate(
                ['student_id' => $student->id],
                [
                    'user_id' => $student->user_id,
                    'section_id' => $student->section_id,
                    'batch_id' => $student->batch_id,
                    'academic_id' => $student->academic_id,
                    'student_name' => $student->name_ar,
                    'status' => 'eligible',
                    'assigned_at' => now(),
                    'snapshot' => [
                        'academic_status' => $student->academic_status,
                        'email' => $student->email,
                        'mobile' => $student->mobile,
                    ],
                ]
            );
        }

        return $this->candidates()->where('status', 'eligible')->count();
    }

    public function refreshTotalPoints(): void
    {
        $total = $this->parts()
            ->with('questionLinks')
            ->get()
            ->sum(function (ExamPart $part): float {
                $fixedPoints = (float) $part->questionLinks->sum('points');
                $pool = $part->pool_filters ?? [];

                if (! empty($pool['question_ids']) && $part->questions_to_draw) {
                    return $fixedPoints
                        + ((int) $part->questions_to_draw * (float) ($pool['points_per_question'] ?? 1));
                }

                if ($part->questions_to_draw) {
                    return (float) $part->questionLinks
                        ->sortBy('sort_order')
                        ->take($part->questions_to_draw)
                        ->sum('points');
                }

                return $fixedPoints;
            });

        $this->update([
            'total_points' => $total,
        ]);
    }
}
