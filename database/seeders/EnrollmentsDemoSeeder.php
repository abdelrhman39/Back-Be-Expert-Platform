<?php

namespace Database\Seeders;

use App\Services\EnrollmentService;
use Illuminate\Database\Seeder;

class EnrollmentsDemoSeeder extends Seeder
{
    public function run(): void
    {
        app(EnrollmentService::class)->syncAllPaidOrders();
    }
}
