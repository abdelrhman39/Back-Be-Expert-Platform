<?php

namespace Database\Seeders;

use App\Models\CatalogCourse;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrdersDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@domain.test')->first()
            ?? User::query()->where('role', 'student')->first()
            ?? User::query()->first();

        if (! $user) {
            return;
        }

        $courses = CatalogCourse::query()->limit(3)->get();

        if ($courses->isEmpty()) {
            return;
        }

        $samples = [
            [
                'status' => 'paid',
                'payment_method' => 'mada',
                'payment_ref' => 'PAY-'.Str::upper(Str::random(8)),
                'days_ago' => 5,
            ],
            [
                'status' => 'pending_payment',
                'payment_method' => 'bank_transfer',
                'payment_ref' => null,
                'days_ago' => 1,
            ],
            [
                'status' => 'paid',
                'payment_method' => 'tabby',
                'payment_ref' => 'TAB-'.Str::upper(Str::random(6)),
                'days_ago' => 12,
            ],
        ];

        foreach ($samples as $index => $sample) {
            $reference = 'BX-'.now()->format('ymd').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);

            if (Order::query()->where('reference', $reference)->exists()) {
                continue;
            }

            $course = $courses[$index % $courses->count()];
            $price = (float) ($course->price_online ?? $course->price_onsite ?? 500);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'reference' => $reference,
                'total' => $price,
                'status' => $sample['status'],
                'payment_method' => $sample['payment_method'],
                'payment_ref' => $sample['payment_ref'],
                'paid_at' => $sample['status'] === 'paid' ? now()->subDays($sample['days_ago']) : null,
                'created_at' => now()->subDays($sample['days_ago']),
                'updated_at' => now()->subDays($sample['days_ago']),
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'course_id' => $course->id,
                'delivery_type' => $course->delivery_type ?? 'online',
                'price' => $price,
                'course_title' => $course->title_ar ?? $course->title_en ?? 'دورة #'.$course->id,
                'course_image' => $course->image,
            ]);
        }
    }
}
