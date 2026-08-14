<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin report areas
    |--------------------------------------------------------------------------
    | Each area is a tab in مركز التقارير. `permission` gates visibility;
    | users still need `reports.view` to open the hub.
    */

    'areas' => [
        'overview' => [
            'label' => 'نظرة عامة',
            'permission' => 'reports.view',
            'description' => 'ملخص شامل لكل مؤشرات المنصة في الفترة المحددة.',
        ],
        'students' => [
            'label' => 'الطلاب والالتحاق',
            'permission' => 'students.view',
            'description' => 'حالات الطلاب، التوزيع الجغرافي، والبرامج والدفعات.',
        ],
        'finance' => [
            'label' => 'المالية والطلبات',
            'permission' => 'finance.view',
            'description' => 'الإيرادات، طرق الدفع، والطلبات والاستردادات.',
        ],
        'installments' => [
            'label' => 'التقسيط والتحصيل',
            'permission' => 'installments.view',
            'description' => 'المستحق مقابل المحصّل والمتأخرات ضمن الفترة.',
        ],
        'certificates' => [
            'label' => 'الشهادات',
            'permission' => 'certificates.view',
            'description' => 'إصدار الشهادات وحالاتها ومصادرها.',
        ],
        'attendance' => [
            'label' => 'الحضور',
            'permission' => 'attendance.view',
            'description' => 'نسب الحضور والغياب ومصادر التسجيل.',
        ],
        'exams' => [
            'label' => 'الاختبارات',
            'permission' => 'exams.view',
            'description' => 'المحاولات، نسب النجاح، ومتوسط الدرجات.',
        ],
        'assignments' => [
            'label' => 'الواجبات',
            'permission' => 'assignments.view',
            'description' => 'التسليمات والتقييم ومتوسط الدرجات.',
        ],
        'support' => [
            'label' => 'الدعم الفني',
            'permission' => 'support.view',
            'description' => 'حجم التذاكر حسب الحالة والفئة.',
        ],
        'applications' => [
            'label' => 'طلبات الانضمام',
            'permission' => 'applications.view',
            'description' => 'مسار طلبات التسجيل حسب النوع والحالة.',
        ],
        'requests' => [
            'label' => 'الطلبات الأكاديمية',
            'permission' => 'requests.view',
            'description' => 'التأجيل والانسحاب وتغيير البرنامج والاعتذار.',
        ],
        'catalog' => [
            'label' => 'دورات الكatalog',
            'permission' => 'catalog.view',
            'description' => 'اشتراكات الدورات القصيرة ونسب التقدم.',
        ],
        'staff' => [
            'label' => 'الكوادر',
            'permission' => 'staff.view',
            'description' => 'المدربون، الساعات، والتعويضات.',
        ],
        'traffic' => [
            'label' => 'زيارات المنصة',
            'permission' => 'analytics.view',
            'description' => 'الزيارات، تسجيلات الدخول، والأجهزة والمناطق.',
        ],
    ],

    'presets' => [
        '7d' => ['label' => 'آخر 7 أيام', 'days' => 7],
        '30d' => ['label' => 'آخر 30 يوماً', 'days' => 30],
        '90d' => ['label' => 'آخر 90 يوماً', 'days' => 90],
        'month' => ['label' => 'هذا الشهر', 'days' => null],
        'custom' => ['label' => 'مخصص', 'days' => null],
    ],

];
