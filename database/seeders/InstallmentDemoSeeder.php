<?php

namespace Database\Seeders;

use App\Models\AcademicStudent;
use App\Models\InstallmentPlanTemplate;
use App\Models\User;
use App\Services\InstallmentContractService;
use App\Services\InstallmentPaymentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class InstallmentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(InstallmentPlansSeeder::class);

        $user = User::query()->where('email', 'demo@domain.test')->first();
        $student = AcademicStudent::query()->where('user_id', $user?->id)->first()
            ?? AcademicStudent::query()->whereNotNull('user_id')->first();

        if (! $user || ! $student) {
            return;
        }

        $template = InstallmentPlanTemplate::query()->where('slug', 'quarterly_4')->first();

        if (! $template) {
            return;
        }

        $existing = $user->installmentContracts()->where('template_id', $template->id)->exists();

        if ($existing) {
            return;
        }

        $contracts = app(InstallmentContractService::class);
        $payments = app(InstallmentPaymentService::class);

        $contract = $contracts->createFromTemplate(
            studentUser: $user,
            template: $template,
            totalAmount: 12000,
            academicStudent: $student,
            startsAt: Carbon::now()->subMonths(2)->startOfMonth(),
            creator: User::query()->where('role', 'admin')->first(),
            title: 'دبلوم المحاسبة العامة — دفعة 2026/2027',
            adminNotes: 'عقد تجريبي للاختبار',
        );

        if ($contract->needsStudentSignature()) {
            $path = "contracts/signatures/{$contract->id}/demo.png";
            Storage::disk('public')->put($path, base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
                true,
            ));
            $contract->update([
                'student_signature_path' => $path,
                'student_signature_name' => $user->displayName(),
                'student_signed_at' => now(),
                'status' => 'active',
            ]);
        }

        $first = $contract->schedules()->where('sequence', 1)->first();

        if ($first) {
            $payments->recordManualPayment($first, User::query()->where('role', 'admin')->first(), 'سداد تجريبي للدفعة الأولى');
        }
    }
}
