<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API
    |--------------------------------------------------------------------------
    |
    | Used by the Be Expert AI support assistant. The secret key must never
    | be exposed to the browser — only server-side services may read it.
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    'organization' => env('OPENAI_ORGANIZATION'),

    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

    'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),

    'timeout' => (int) env('OPENAI_TIMEOUT', 45),

    'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 1200),

    'temperature' => (float) env('OPENAI_TEMPERATURE', 0.35),

    /*
    |--------------------------------------------------------------------------
    | Support assistant behaviour
    |--------------------------------------------------------------------------
    */

    'support' => [
        'enabled' => filter_var(env('OPENAI_SUPPORT_ENABLED', true), FILTER_VALIDATE_BOOLEAN),

        /** Max user messages kept in the OpenAI context window */
        'history_limit' => (int) env('OPENAI_SUPPORT_HISTORY_LIMIT', 12),

        /** How many knowledge chunks to inject per turn */
        'knowledge_chunks' => (int) env('OPENAI_SUPPORT_KNOWLEDGE_CHUNKS', 6),

        /** Soft daily cap per IP (in addition to route throttle) */
        'daily_limit_per_ip' => (int) env('OPENAI_SUPPORT_DAILY_LIMIT', 80),

        'assistant_name_ar' => env('OPENAI_SUPPORT_NAME_AR', 'مساعد منصة كن خبيراً'),

        'assistant_name_en' => env('OPENAI_SUPPORT_NAME_EN', 'Be Expert Assistant'),
    ],

];
