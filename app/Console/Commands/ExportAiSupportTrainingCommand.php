<?php

namespace App\Console\Commands;

use App\Models\AiSupportMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportAiSupportTrainingCommand extends Command
{
    protected $signature = 'ai-support:export-training
        {--path=storage/app/ai-support-training.jsonl : Output JSONL path relative to base_path or absolute}
        {--approved-only : Only export messages marked training_approved}
        {--min-feedback=0 : Minimum feedback score (0 = include unrated approved)}';

    protected $description = 'Export AI support Q&A pairs as OpenAI fine-tuning JSONL';

    public function handle(): int
    {
        $path = (string) $this->option('path');
        if (! str_starts_with($path, '/') && ! preg_match('/^[A-Za-z]:\\\\/', $path)) {
            $path = base_path($path);
        }

        $query = AiSupportMessage::query()
            ->where('role', 'assistant')
            ->with(['conversation.messages' => fn ($q) => $q->orderBy('id')]);

        if ($this->option('approved-only')) {
            $query->where('training_approved', true);
        }

        $minFeedback = (int) $this->option('min-feedback');
        if ($minFeedback !== 0) {
            $query->where('feedback', '>=', $minFeedback);
        } else {
            $query->where(function ($q) {
                $q->where('training_approved', true)
                    ->orWhere('feedback', 1);
            });
        }

        $lines = [];
        $exported = 0;

        foreach ($query->cursor() as $assistantMessage) {
            $conversation = $assistantMessage->conversation;
            if (! $conversation) {
                continue;
            }

            $prior = $conversation->messages
                ->where('id', '<', $assistantMessage->id)
                ->sortBy('id')
                ->values();

            $userMessage = $prior->last(fn ($m) => $m->role === 'user');
            if (! $userMessage) {
                continue;
            }

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'أنت المساعد الرسمي لمنصة كن خبيراً (Be Expert). أجب بدقة اعتماداً على معلومات المنصة فقط.',
                ],
            ];

            foreach ($prior as $m) {
                if (! in_array($m->role, ['user', 'assistant'], true)) {
                    continue;
                }
                // Keep a short window before the target pair
                $messages[] = [
                    'role' => $m->role,
                    'content' => $m->content,
                ];
            }

            // Ensure the assistant turn is the approved answer
            if (($messages[array_key_last($messages)]['role'] ?? null) === 'assistant') {
                array_pop($messages);
            }
            $messages[] = [
                'role' => 'assistant',
                'content' => $assistantMessage->content,
            ];

            // Cap history length for fine-tune rows
            if (count($messages) > 9) {
                $system = array_shift($messages);
                $messages = array_merge([$system], array_slice($messages, -8));
            }

            $lines[] = json_encode(['messages' => $messages], JSON_UNESCAPED_UNICODE);
            $exported++;
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, implode("\n", $lines).($lines ? "\n" : ''));

        $this->info("Exported {$exported} training examples to {$path}");

        return self::SUCCESS;
    }
}
