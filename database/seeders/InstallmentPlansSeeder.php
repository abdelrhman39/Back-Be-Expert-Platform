<?php

namespace Database\Seeders;

use App\Models\InstallmentPlanTemplate;
use Illuminate\Database\Seeder;

class InstallmentPlansSeeder extends Seeder
{
    public function run(): void
    {
        $quarterly = InstallmentPlanTemplate::query()->updateOrCreate(
            ['slug' => 'quarterly_4'],
            [
                'name_ar' => 'تقسيط ربع سنوي — 4 دفعات',
                'name_en' => 'Quarterly — 4 installments',
                'program_type' => null,
                'max_installments' => 4,
                'min_down_payment_percent' => 25,
                'is_active' => true,
                'description_ar' => 'دفعة أولى 25% ثم 3 أقساط كل 3 أشهر.',
            ]
        );

        $quarterly->items()->delete();
        foreach ([
            ['sequence' => 1, 'percent' => 25, 'due_rule' => 'at_enrollment', 'month_offset' => 0, 'label_ar' => 'الدفعة الأولى'],
            ['sequence' => 2, 'percent' => 25, 'due_rule' => 'month_offset', 'month_offset' => 3, 'label_ar' => 'القسط الثاني'],
            ['sequence' => 3, 'percent' => 25, 'due_rule' => 'month_offset', 'month_offset' => 6, 'label_ar' => 'القسط الثالث'],
            ['sequence' => 4, 'percent' => 25, 'due_rule' => 'month_offset', 'month_offset' => 9, 'label_ar' => 'القسط الرابع'],
        ] as $item) {
            $quarterly->items()->create($item);
        }

        $monthly = InstallmentPlanTemplate::query()->updateOrCreate(
            ['slug' => 'monthly_12'],
            [
                'name_ar' => 'تقسيط شهري — 12 قسط',
                'name_en' => 'Monthly — 12 installments',
                'program_type' => 'diploma_1y',
                'max_installments' => 12,
                'min_down_payment_percent' => 25,
                'is_active' => true,
                'description_ar' => 'دفعة أولى 25% ثم 11 قسطاً شهرياً.',
            ]
        );

        $monthly->items()->delete();
        $monthly->items()->create([
            'sequence' => 1,
            'percent' => 25,
            'due_rule' => 'at_enrollment',
            'month_offset' => 0,
            'label_ar' => 'الدفعة الأولى',
        ]);

        $remaining = 75.0;
        $perMonth = round($remaining / 11, 2);

        for ($i = 2; $i <= 12; $i++) {
            $percent = $i === 12 ? round($remaining - ($perMonth * 10), 2) : $perMonth;
            $monthly->items()->create([
                'sequence' => $i,
                'percent' => $percent,
                'due_rule' => 'month_offset',
                'month_offset' => $i - 1,
                'label_ar' => 'القسط الشهري '.$i,
            ]);
        }
    }
}
