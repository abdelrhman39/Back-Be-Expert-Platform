<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\InstructorImpersonationService;
use App\Services\StudentImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLogoutController extends Controller
{
    public function __invoke(
        Request $request,
        StudentImpersonationService $studentImpersonation,
        InstructorImpersonationService $instructorImpersonation,
    ): RedirectResponse {
        if ($studentImpersonation->isActive()) {
            $studentImpersonation->stop();
        }
        if ($instructorImpersonation->isActive()) {
            $instructorImpersonation->stop();
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
