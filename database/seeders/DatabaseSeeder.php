<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoUserSeeder::class,
            AdminUserSeeder::class,
            PaymentSettingsSeeder::class,
            PlatformSettingsSeeder::class,
            AcademicDemoSeeder::class,
            AcademicStaffSeeder::class,
            NotificationRulesSeeder::class,
            CertificateTemplateSeeder::class,
            CertificateAccessSettingsSeeder::class,
            CertificatesDemoSeeder::class,
            StudentDocumentsDemoSeeder::class,
            RefundsDemoSeeder::class,
            AcademicRequestsSeeder::class,
            UserRequestsDemoSeeder::class,
            InstructorDemoSeeder::class,
            CompleteDiplomaDemoSeeder::class,
            AccessControlSeeder::class,
            InstallmentSettingsSeeder::class,
            InstallmentDunningSeeder::class,
            InstallmentDemoSeeder::class,
            CmsSeeder::class,
            ArticleSeeder::class,
            RegistrationApplicationsDemoSeeder::class,
            FellowshipSeeder::class,
        ]);
    }
}
