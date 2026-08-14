<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Database\Seeder;

class RefundsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'demo@domain.test')->first();
        $order = Order::query()->where('user_id', $user?->id)->where('status', 'paid')->first();

        if (! $user || ! $order) {
            return;
        }

        if (app(RefundService::class)->openForOrder($order)) {
            return;
        }

        app(RefundService::class)->request($user, $order, 'طلب تجريبي — لم أتمكن من حضور البرنامج.');
    }
}
