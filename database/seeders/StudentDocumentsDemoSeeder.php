<?php

namespace Database\Seeders;

use App\Models\AcademicStudent;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\StatementService;
use Illuminate\Database\Seeder;

class StudentDocumentsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@domain.test')->first();
        $student = AcademicStudent::query()->where('user_id', $user?->id)->first()
            ?? AcademicStudent::query()->whereNotNull('user_id')->first();

        if (! $user || ! $student) {
            return;
        }

        app(CertificateService::class)->issueForStudent($student);

        $statements = app(StatementService::class);

        $pending = $statements->request($user, 'enrollment', 'للجهة التعليمية');
        $statements->issue($pending, User::query()->where('role', 'admin')->first() ?? $user);

        $statements->request($user, 'attendance', 'إفادة حضور للفصل الحالي');
    }
}
