<?php

namespace App\Services\Support;

use App\Models\AiSupportConversation;
use App\Models\AiSupportMessage;
use App\Models\User;
use App\Support\OpenAiSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiSupportAssistantService
{
    public function __construct(
        protected AiSupportKnowledgeBase $knowledge,
    ) {}

    public function isAvailable(): bool
    {
        return OpenAiSettings::supportEnabled();
    }

    /**
     * @param  array{locale?: string, page_url?: string, conversation_uuid?: string|null, ip?: string|null, user_agent?: string|null}  $context
     * @return array{ok: bool, conversation_uuid: string, reply: string, needs_human: bool, message_id: int|null, error?: string, sources?: list<string>}
     */
    public function chat(string $message, ?User $user = null, array $context = []): array
    {
        $message = trim($message);
        if ($message === '' || mb_strlen($message) > 4000) {
            return $this->fail('invalid_message', $context['conversation_uuid'] ?? null);
        }

        if (! $this->isAvailable()) {
            return $this->fail('unavailable', $context['conversation_uuid'] ?? null);
        }

        $ip = $context['ip'] ?? null;
        if ($ip && ! $this->withinDailyLimit($ip)) {
            return $this->fail('rate_limited', $context['conversation_uuid'] ?? null);
        }

        $locale = in_array(($context['locale'] ?? 'ar'), ['ar', 'en'], true)
            ? $context['locale']
            : 'ar';

        $conversation = $this->resolveConversation($user, $context, $locale);

        $this->storeMessage($conversation, 'user', $message);

        $chunks = $this->knowledge->retrieve($message);
        $knowledgeBlock = $this->knowledge->formatForPrompt($chunks);
        $history = $this->buildHistory($conversation);

        $system = $this->systemPrompt($locale, $user, $knowledgeBlock);

        try {
            $completion = $this->callOpenAi($system, $history);
        } catch (\Throwable $e) {
            Log::error('AI support OpenAI call failed', [
                'error' => $e->getMessage(),
                'conversation' => $conversation->uuid,
            ]);

            $fallback = $locale === 'en'
                ? 'I am temporarily unable to reach the assistant service. Please open a support ticket or contact us via WhatsApp / email.'
                : 'تعذر الوصول لخدمة المساعد مؤقتاً. يرجى فتح تذكرة دعم أو التواصل عبر واتساب / البريد.';

            $assistant = $this->storeMessage($conversation, 'assistant', $fallback, [
                'needs_human' => true,
                'meta' => ['error' => 'openai_failure'],
            ]);

            return [
                'ok' => true,
                'conversation_uuid' => $conversation->uuid,
                'reply' => $fallback,
                'needs_human' => true,
                'message_id' => $assistant->id,
                'sources' => [],
            ];
        }

        $reply = $completion['content'];
        $needsHuman = $this->detectNeedsHuman($reply, $completion['raw'] ?? '');

        $assistant = $this->storeMessage($conversation, 'assistant', $reply, [
            'knowledge_refs' => array_column($chunks, 'id'),
            'model' => OpenAiSettings::model(),
            'prompt_tokens' => $completion['prompt_tokens'] ?? null,
            'completion_tokens' => $completion['completion_tokens'] ?? null,
            'needs_human' => $needsHuman,
            'meta' => [
                'finish_reason' => $completion['finish_reason'] ?? null,
            ],
        ]);

        if ($ip) {
            $this->incrementDailyUsage($ip);
        }

        return [
            'ok' => true,
            'conversation_uuid' => $conversation->uuid,
            'reply' => $reply,
            'needs_human' => $needsHuman,
            'message_id' => $assistant->id,
            'sources' => array_values(array_unique(array_column($chunks, 'title'))),
        ];
    }

    public function recordFeedback(AiSupportMessage $message, int $feedback, ?string $note = null, bool $approveTraining = false): void
    {
        $message->update([
            'feedback' => $feedback >= 0 ? 1 : -1,
            'feedback_note' => $note,
            'training_approved' => $approveTraining || $feedback > 0,
        ]);
    }

    /**
     * @param  array{locale?: string, page_url?: string, conversation_uuid?: string|null, ip?: string|null, user_agent?: string|null}  $context
     */
    private function resolveConversation(?User $user, array $context, string $locale): AiSupportConversation
    {
        $uuid = $context['conversation_uuid'] ?? null;

        if (filled($uuid)) {
            $existing = AiSupportConversation::query()->where('uuid', $uuid)->first();
            if ($existing) {
                if ($user && ! $existing->user_id) {
                    $existing->update(['user_id' => $user->id, 'audience' => $this->audienceFor($user)]);
                }

                return $existing;
            }
        }

        return AiSupportConversation::query()->create([
            'user_id' => $user?->id,
            'locale' => $locale,
            'audience' => $this->audienceFor($user),
            'ip_hash' => filled($context['ip'] ?? null) ? hash('sha256', (string) $context['ip']) : null,
            'user_agent' => isset($context['user_agent']) ? Str::limit((string) $context['user_agent'], 250) : null,
            'page_url' => isset($context['page_url']) ? Str::limit((string) $context['page_url'], 490) : null,
            'status' => 'open',
            'message_count' => 0,
        ]);
    }

    private function audienceFor(?User $user): string
    {
        if (! $user) {
            return 'visitor';
        }

        if (method_exists($user, 'isInstructor') && $user->isInstructor()) {
            return 'instructor';
        }

        return 'student';
    }

    /** @param  array<string, mixed>  $extra */
    private function storeMessage(AiSupportConversation $conversation, string $role, string $content, array $extra = []): AiSupportMessage
    {
        $message = $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'knowledge_refs' => $extra['knowledge_refs'] ?? null,
            'model' => $extra['model'] ?? null,
            'prompt_tokens' => $extra['prompt_tokens'] ?? null,
            'completion_tokens' => $extra['completion_tokens'] ?? null,
            'needs_human' => (bool) ($extra['needs_human'] ?? false),
            'meta' => $extra['meta'] ?? null,
        ]);

        $conversation->update([
            'message_count' => $conversation->message_count + 1,
            'last_message_at' => now(),
            'status' => ($extra['needs_human'] ?? false) ? 'escalated' : $conversation->status,
        ]);

        return $message;
    }

    /** @return list<array{role: string, content: string}> */
    private function buildHistory(AiSupportConversation $conversation): array
    {
        $limit = OpenAiSettings::historyLimit();

        return $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['role', 'content'])
            ->reverse()
            ->values()
            ->map(fn (AiSupportMessage $m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->all();
    }

    private function systemPrompt(string $locale, ?User $user, string $knowledgeBlock): string
    {
        $name = OpenAiSettings::assistantName($locale);
        $userLine = $user
            ? "المستخدم مسجّل الدخول: الاسم={$user->name}، البريد={$user->email}."
            : 'المستخدم زائر غير مسجّل الدخول.';

        $replyLang = $locale === 'en'
            ? 'Reply in clear professional English unless the user writes in Arabic.'
            : 'أجب بالعربية الفصحى المبسّطة والواضحة ما لم يكتب المستخدم بالإنجليزية.';

        return <<<PROMPT
أنت «{$name}» — المساعد الرسمي لمنصة **كن خبيراً (Be Expert)** / مركز التعلم المستمر.
مهمتك مساعدة الزوار والطلاب والمدربين بإجابات دقيقة تعتمد فقط على معرفة المنصة المرفقة.

## قواعد صارمة
1. أجب فقط بناءً على «معرفة المنصة» أدناه. لا تختلق سياسات أو أسعاراً أو مواعيد غير موجودة.
2. إذا لم تجد المعلومة في المعرفة: اعترف بذلك بوضوح، واقترح فتح تذكرة دعم أو قنوات التواصل. لا تخمّن.
3. لا تطلب أو تخزّن كلمات المرور أو بيانات بطاقات الدفع أو OTP.
4. لا تقدّم استشارات طبية/قانونية خارج نطاق المنصة.
5. كن مهذباً، مختصراً عند الإمكان، ومنظّماً بنقاط عند الحاجة.
6. عند الحاجة لإجراء بشري أو مشكلة حساب/دفع معقّدة، أضف في نهاية الرد السطر التالي وحده:
NEEDS_HUMAN: true
7. يمكنك ذكر مسارات الصفحات مثل /ar/learning-list أو /ar/support/ticket/new.
8. {$replyLang}

## سياق الجلسة
{$userLine}
لغة الواجهة: {$locale}

## معرفة المنصة (مقاطع مسترجعة لهذا السؤال)
{$knowledgeBlock}

## أسلوب الإجابة الاحترافي
- ابدأ بجواب مباشر.
- ثم خطوات عملية مرقّمة إن لزم.
- اختم برابط/إجراء مقترح (تذكرة، واتساب، صفحة ذات صلة) عند الحاجة.
PROMPT;
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return array{content: string, prompt_tokens: int|null, completion_tokens: int|null, finish_reason: string|null, raw: string}
     */
    private function callOpenAi(string $system, array $history): array
    {
        $apiKey = OpenAiSettings::apiKey();
        if (! $apiKey) {
            throw new \RuntimeException('OpenAI API key missing');
        }

        $payload = [
            'model' => OpenAiSettings::model(),
            'temperature' => OpenAiSettings::temperature(),
            'max_tokens' => OpenAiSettings::maxTokens(),
            'messages' => array_merge(
                [['role' => 'system', 'content' => $system]],
                $history
            ),
        ];

        $request = Http::timeout(OpenAiSettings::timeout())
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson();

        if ($org = OpenAiSettings::organization()) {
            $request = $request->withHeaders(['OpenAI-Organization' => $org]);
        }

        $response = $request->post(OpenAiSettings::baseUrl().'/chat/completions', $payload);

        if (! $response->successful()) {
            Log::warning('OpenAI chat completions failed', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 500),
            ]);
            throw new \RuntimeException('OpenAI HTTP '.$response->status());
        }

        $json = $response->json();
        $content = trim((string) data_get($json, 'choices.0.message.content', ''));
        if ($content === '') {
            throw new \RuntimeException('Empty OpenAI content');
        }

        return [
            'content' => $this->stripNeedsHumanMarker($content),
            'prompt_tokens' => data_get($json, 'usage.prompt_tokens'),
            'completion_tokens' => data_get($json, 'usage.completion_tokens'),
            'finish_reason' => data_get($json, 'choices.0.finish_reason'),
            'raw' => $content,
        ];
    }

    private function stripNeedsHumanMarker(string $content): string
    {
        $content = preg_replace('/^\s*NEEDS_HUMAN:\s*true\s*$/mi', '', $content) ?? $content;

        return trim($content);
    }

    private function detectNeedsHuman(string $cleaned, string $raw): bool
    {
        if (preg_match('/NEEDS_HUMAN:\s*true/i', $raw)) {
            return true;
        }

        $needles = [
            'تذكرة دعم',
            'support ticket',
            'تواصل مع فريق',
            'contact our team',
            'لا أملك معلومات كافية',
            'I do not have enough',
        ];

        foreach ($needles as $needle) {
            if (Str::contains(Str::lower($cleaned), Str::lower($needle))) {
                // Soft signal only — still return false unless marker present,
                // but escalate when assistant explicitly lacks info.
                if (Str::contains(Str::lower($cleaned), ['لا أملك', 'لا تتوفر', 'do not have', "don't have", 'unable to find'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function withinDailyLimit(string $ip): bool
    {
        $key = $this->dailyKey($ip);
        $used = (int) Cache::get($key, 0);

        return $used < OpenAiSettings::dailyLimitPerIp();
    }

    private function incrementDailyUsage(string $ip): void
    {
        $key = $this->dailyKey($ip);
        if (! Cache::has($key)) {
            Cache::put($key, 1, now()->endOfDay());
        } else {
            Cache::increment($key);
        }
    }

    private function dailyKey(string $ip): string
    {
        return 'ai_support.daily.'.hash('sha256', $ip).'.'.now()->toDateString();
    }

    /** @return array{ok: bool, conversation_uuid: string, reply: string, needs_human: bool, message_id: int|null, error: string} */
    private function fail(string $code, ?string $uuid): array
    {
        $messages = [
            'invalid_message' => 'الرسالة غير صالحة. اكتب سؤالك باختصار واضح.',
            'unavailable' => 'المساعد غير مفعّل حالياً. يرجى استخدام تذاكر الدعم أو واتساب.',
            'rate_limited' => 'وصلت للحد اليومي للمحادثة. حاول لاحقاً أو افتح تذكرة دعم.',
        ];

        return [
            'ok' => false,
            'conversation_uuid' => $uuid ?? '',
            'reply' => $messages[$code] ?? 'حدث خطأ غير متوقع.',
            'needs_human' => true,
            'message_id' => null,
            'error' => $code,
        ];
    }
}
