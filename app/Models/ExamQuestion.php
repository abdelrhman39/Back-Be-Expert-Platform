<?php

namespace App\Models;

use App\Support\ExamOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'course_id',
        'created_by',
        'updated_by',
        'type',
        'title',
        'title_en',
        'prompt',
        'prompt_en',
        'explanation',
        'explanation_en',
        'default_points',
        'difficulty',
        'scope',
        'status',
        'answer_key',
        'answer_key_en',
        'settings',
        'tags',
        'version',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'default_points' => 'decimal:2',
            'answer_key' => 'array',
            'answer_key_en' => 'array',
            'settings' => 'array',
            'tags' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExamQuestionCategory::class, 'category_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(AcademicCourse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ExamQuestionOption::class, 'question_id')->orderBy('sort_order');
    }

    public function isAutoGradable(): bool
    {
        return in_array($this->type, ExamOptions::autoGradableTypes(), true);
    }

    public function snapshot(float $points): array
    {
        $this->loadMissing('options');

        $snapshot = [
            'id' => $this->id,
            'version' => $this->version,
            'type' => $this->type,
            'title' => $this->title,
            'title_en' => $this->title_en,
            'prompt' => $this->prompt,
            'prompt_en' => $this->prompt_en,
            'explanation' => $this->explanation,
            'explanation_en' => $this->explanation_en,
            'points' => $points,
            'settings' => $this->settings,
            'options' => $this->options->map(fn (ExamQuestionOption $option) => [
                'key' => $option->option_key,
                'content' => $option->content,
                'content_en' => $option->content_en,
                'sort_order' => $option->sort_order,
            ])->values()->all(),
        ];

        if ($this->type === 'matching') {
            $snapshot['matching_targets'] = $this->options
                ->pluck('match_data')
                ->pluck('target')
                ->filter()
                ->shuffle()
                ->values()
                ->all();
            $snapshot['matching_targets_en'] = $this->options
                ->pluck('match_data_en')
                ->pluck('target')
                ->filter()
                ->shuffle()
                ->values()
                ->all();
        }

        return $snapshot;
    }

    public function localizedPrompt(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'en' && filled($this->prompt_en) ? $this->prompt_en : $this->prompt;
    }
}
