<?php

namespace App\Support;

class OpenAiSettings
{
    public static function apiKey(): ?string
    {
        $key = config('openai.api_key');

        return filled($key) ? (string) $key : null;
    }

    public static function organization(): ?string
    {
        $org = config('openai.organization');

        return filled($org) ? (string) $org : null;
    }

    public static function baseUrl(): string
    {
        return rtrim((string) config('openai.base_url', 'https://api.openai.com/v1'), '/');
    }

    public static function model(): string
    {
        return (string) config('openai.model', 'gpt-4o-mini');
    }

    public static function embeddingModel(): string
    {
        return (string) config('openai.embedding_model', 'text-embedding-3-small');
    }

    public static function timeout(): int
    {
        return max(10, (int) config('openai.timeout', 45));
    }

    public static function maxTokens(): int
    {
        return max(256, (int) config('openai.max_tokens', 1200));
    }

    public static function temperature(): float
    {
        return max(0.0, min(1.5, (float) config('openai.temperature', 0.35)));
    }

    public static function supportEnabled(): bool
    {
        return (bool) config('openai.support.enabled', true) && filled(self::apiKey());
    }

    public static function historyLimit(): int
    {
        return max(4, (int) config('openai.support.history_limit', 12));
    }

    public static function knowledgeChunks(): int
    {
        return max(2, (int) config('openai.support.knowledge_chunks', 6));
    }

    public static function dailyLimitPerIp(): int
    {
        return max(10, (int) config('openai.support.daily_limit_per_ip', 80));
    }

    public static function assistantName(string $locale = 'ar'): string
    {
        return $locale === 'en'
            ? (string) config('openai.support.assistant_name_en', 'Be Expert Assistant')
            : (string) config('openai.support.assistant_name_ar', 'مساعد منصة كن خبيراً');
    }

    public static function isConfigured(): bool
    {
        return filled(self::apiKey());
    }
}
