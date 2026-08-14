<?php

namespace App\Http\Controllers\Integrations;

use App\Services\MicrosoftTeams\TeamsOAuthService;
use App\Support\TeamsSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MicrosoftTeamsOAuthController
{
    public function redirect(TeamsOAuthService $oauth): RedirectResponse
    {
        abort_unless(TeamsSettings::isConfigured(), 503, 'Microsoft Teams integration is not configured.');
        abort_unless(auth()->check(), 403);

        return redirect()->away($oauth->authorizationUrl(auth()->user()));
    }

    public function callback(Request $request, TeamsOAuthService $oauth): RedirectResponse
    {
        $locale = Auth::check() ? (auth()->user()->locale ?: 'ar') : 'ar';

        if ($request->filled('error')) {
            return redirect()
                ->route('settings', ['locale' => $locale])
                ->with('portal_message', 'تم إلغاء ربط Microsoft Teams.');
        }

        $code = $request->string('code')->toString();
        $state = $request->string('state')->toString();

        if (! $code || ! $state) {
            return redirect()
                ->route('settings', ['locale' => $locale])
                ->with('portal_message', 'فشل ربط Microsoft Teams — بيانات غير مكتملة.');
        }

        $result = $oauth->handleCallback($code, $state);

        if (! ($result['ok'] ?? false)) {
            return redirect()
                ->route('settings', ['locale' => $locale])
                ->with('portal_message', $result['message'] ?? 'فشل ربط Microsoft Teams.');
        }

        if (! Auth::check()) {
            return redirect()->route('login', ['locale' => $locale]);
        }

        return redirect()
            ->route('settings', ['locale' => $locale])
            ->with('portal_message', 'تم ربط حساب Microsoft Teams بنجاح.');
    }
}
