<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\AiSupportMessage;
use App\Services\Support\AiSupportAssistantService;
use App\Support\OpenAiSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function bootstrap(Request $request): JsonResponse
    {
        $locale = app()->getLocale();

        return response()->json([
            'enabled' => OpenAiSettings::supportEnabled(),
            'assistant_name' => OpenAiSettings::assistantName($locale),
            'locale' => $locale,
            'welcome' => $locale === 'en'
                ? 'Hello! I am the Be Expert assistant. Ask me about courses, payments, learning, certificates, or support tickets.'
                : 'مرحباً! أنا مساعد منصة كن خبيراً. اسألني عن الدورات، الدفع، التعلم، الشهادات، أو تذاكر الدعم.',
            'suggestions' => $locale === 'en'
                ? [
                    'How do I reset my password?',
                    'What payment methods are available?',
                    'How do I open a support ticket?',
                    'Where do I find my courses after purchase?',
                ]
                : [
                    'كيف أسترجع كلمة المرور؟',
                    'ما طرق الدفع المتاحة؟',
                    'كيف أفتح تذكرة دعم؟',
                    'أين أجد دوراتي بعد الشراء؟',
                ],
            'links' => [
                'faq' => route('support.faq', ['locale' => $locale]),
                'ticket_new' => route('support.ticket.new', ['locale' => $locale]),
                'ticket_search' => route('support.ticket.search', ['locale' => $locale]),
                'contact' => route('support.contact', ['locale' => $locale]),
            ],
        ]);
    }

    public function chat(Request $request, AiSupportAssistantService $assistant): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
            'conversation_uuid' => ['nullable', 'uuid'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $assistant->chat($data['message'], $request->user(), [
            'locale' => app()->getLocale(),
            'conversation_uuid' => $data['conversation_uuid'] ?? null,
            'page_url' => $data['page_url'] ?? $request->headers->get('referer'),
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        $status = ($result['ok'] ?? false) ? 200 : (
            ($result['error'] ?? '') === 'rate_limited' ? 429 : 503
        );

        return response()->json($result, $status);
    }

    public function feedback(Request $request, AiSupportAssistantService $assistant): JsonResponse
    {
        $data = $request->validate([
            'message_id' => ['required', 'integer', 'exists:ai_support_messages,id'],
            'feedback' => ['required', 'integer', 'in:-1,1'],
            'note' => ['nullable', 'string', 'max:1000'],
            'approve_training' => ['sometimes', 'boolean'],
        ]);

        $message = AiSupportMessage::query()->findOrFail($data['message_id']);

        if ($message->role !== 'assistant') {
            return response()->json(['ok' => false, 'error' => 'invalid_message'], 422);
        }

        $assistant->recordFeedback(
            $message,
            (int) $data['feedback'],
            $data['note'] ?? null,
            (bool) ($data['approve_training'] ?? $data['feedback'] === 1),
        );

        return response()->json(['ok' => true]);
    }
}
