<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use App\Models\User;
use App\Services\CertificateTemplateService;
use Illuminate\Database\Seeder;

class CertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(CertificateTemplateService::class);
        $userId = User::query()->where('role', 'admin')->value('id');

        CertificateTemplate::query()->firstOrCreate(
            ['slug' => 'default-professional-certificate'],
            [
                'name' => 'القالب الاحترافي الافتراضي',
                'description' => 'قالب جاهز يمكن تخصيصه برفع خلفية الشهادة وتحريك المتغيرات.',
                'canvas_width' => 1123,
                'canvas_height' => 794,
                'orientation' => 'landscape',
                'elements' => $service->defaultElements(),
                'settings' => $service->defaultSettings(),
                'status' => 'active',
                'is_default' => true,
                'version' => 1,
                'created_by' => $userId,
                'updated_by' => $userId,
            ],
        );
    }
}
