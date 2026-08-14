<?php

return [

    'subnav' => [
        ['id' => 'home', 'route' => 'admin.dashboard', 'label' => 'مؤشرات الأداء الرئيسية'],
        ['id' => 'reports', 'route' => 'admin.reports', 'label' => 'مركز التقارير'],
        ['id' => 'platform-analytics', 'route' => 'admin.platform-analytics', 'label' => 'زيارات المنصة'],
        ['id' => 'financial', 'route' => 'admin.financial', 'label' => 'التحليلات المالية'],
        ['id' => 'enrollment', 'route' => 'admin.enrollment', 'label' => 'التسجيل والالتحاق'],
        ['id' => 'graduates', 'route' => 'admin.graduates', 'label' => 'الخريجون'],
        ['id' => 'staff', 'route' => 'admin.staff', 'label' => 'الكوادر الأكاديمية'],
    ],

    'stats_routes' => [
        'admin.reports',
        'admin.platform-analytics',
        'admin.financial',
        'admin.enrollment',
        'admin.graduates',
        'admin.staff',
    ],

    'sidebar' => [
        ['type' => 'link', 'route' => 'admin.dashboard', 'label' => 'مركز القيادة', 'icon' => 'home'],
        ['type' => 'link', 'route' => 'admin.reports', 'label' => 'مركز التقارير', 'icon' => 'grid'],
        ['type' => 'link', 'route' => 'admin.crm', 'label' => 'CRM والمبيعات', 'icon' => 'students'],
        [
            'type' => 'group',
            'id' => 'academic',
            'label' => 'التشغيل الأكاديمي',
            'icon' => 'grid',
            'children' => [
                [
                    'type' => 'section',
                    'label' => 'بناء الخطة',
                    'items' => [
                        ['route' => 'admin.programs', 'label' => 'البرامج الدراسية'],
                        ['route' => 'admin.levels', 'label' => 'المستويات الأكاديمية'],
                        ['route' => 'admin.academic-courses', 'label' => 'المقررات الدراسية'],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'التنفيذ والجداول',
                    'items' => [
                        ['route' => 'admin.batches', 'label' => 'الدفعات الدراسية'],
                        ['route' => 'admin.sections', 'label' => 'الشعب الدراسية'],
                        ['route' => 'admin.schedules', 'label' => 'الجداول الدراسية'],
                        ['route' => 'admin.sessions', 'label' => 'الحصص الدراسية'],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'التدريس والتقييم',
                    'items' => [
                        ['route' => 'admin.staff', 'label' => 'لوحة الكوادر'],
                        ['route' => 'admin.staff.members', 'label' => 'المدربون والإسناد'],
                        ['route' => 'admin.assignments', 'label' => 'الواجبات'],
                        ['route' => 'admin.exams', 'label' => 'الاختبارات وبنوك الأسئلة'],
                    ],
                ],
            ],
        ],
        [
            'type' => 'group',
            'id' => 'students',
            'label' => 'رحلة الطالب',
            'icon' => 'students',
            'children' => [
                [
                    'type' => 'section',
                    'label' => 'السجلات والمخرجات',
                    'items' => [
                        ['route' => 'admin.students', 'label' => 'الطلاب المشتركين'],
                        ['route' => 'admin.enrollment', 'label' => 'التسجيل والالتحاق'],
                        ['route' => 'admin.graduates', 'label' => 'الخريجون'],
                        ['route' => 'admin.certificates', 'label' => 'الشهادات'],
                        ['route' => 'admin.certificate-templates', 'label' => 'منشئ قوالب الشهادات'],
                        ['route' => 'admin.statements', 'label' => 'طلبات الإفادات'],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'الطلبات الأكاديمية',
                    'items' => [
                        ['route' => 'admin.requests.deferral', 'label' => 'طلبات التأجيل'],
                        ['route' => 'admin.requests.withdrawal', 'label' => 'طلبات الانسحاب'],
                        ['route' => 'admin.requests.program-change', 'label' => 'طلبات تغيير البرنامج'],
                        ['route' => 'admin.requests.semester-excuse', 'label' => 'الاعتذار عن الفصل'],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'طلبات الانضمام',
                    'items' => [
                        ['route' => 'admin.applications.client', 'label' => 'طلبات الأفراد'],
                        ['route' => 'admin.applications.company', 'label' => 'طلبات الشركات'],
                        ['route' => 'admin.applications.instructor', 'label' => 'طلبات المدربين'],
                        ['route' => 'admin.applications.cooperative', 'label' => 'التدريب التعاوني'],
                        ['route' => 'admin.applications.employee', 'label' => 'برنامج وعد — موظف'],
                        ['route' => 'admin.applications.job-seeker', 'label' => 'برنامج وعد — باحث'],
                        ['route' => 'admin.applications.marketer', 'label' => 'طلبات المسوقين'],
                        ['route' => 'admin.applications.fellowship', 'label' => 'طلبات الزمالة'],
                        ['route' => 'admin.fellowships', 'label' => 'برامج الزمالة'],
                    ],
                ],
            ],
        ],
        [
            'type' => 'group',
            'id' => 'content',
            'label' => 'المحتوى والتواصل',
            'icon' => 'services',
            'children' => [
                [
                    'type' => 'section',
                    'label' => 'الموقع والتسويق',
                    'items' => [
                        ['route' => 'admin.cms-pages', 'label' => 'صفحات الموقع'],
                        ['route' => 'admin.cms-menus', 'label' => 'قوائم التنقل'],
                        ['route' => 'admin.media-library', 'label' => 'مكتبة الوسائط'],
                        ['route' => 'admin.articles', 'label' => 'الأخبار والفعاليات'],
                        ['route' => 'admin.article-categories', 'label' => 'تصنيفات المقالات'],
                        ['route' => 'admin.catalog-courses', 'label' => 'الدورات والدبلومات'],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'خدمة المستفيد',
                    'items' => [
                        ['route' => 'admin.support-tickets', 'label' => 'تذاكر الدعم'],
                        ['route' => 'admin.notifications', 'label' => 'صندوق الإشعارات'],
                        ['route' => 'admin.notification-rules', 'label' => 'قواعد الإشعارات'],
                    ],
                ],
            ],
        ],
        [
            'type' => 'group',
            'id' => 'finance',
            'label' => 'المالية والتجارة',
            'icon' => 'finance',
            'children' => [
                [
                    'type' => 'section',
                    'label' => 'المبيعات والمدفوعات',
                    'items' => [
                        ['route' => 'admin.financial', 'label' => 'التحليلات المالية'],
                        ['route' => 'admin.reports', 'label' => 'مركز التقارير'],
                        ['route' => 'admin.orders', 'label' => 'طلبات الشراء'],
                        ['route' => 'admin.refunds', 'label' => 'طلبات الاسترداد'],
                        ['route' => 'admin.payment-settings', 'label' => 'طرق الدفع'],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'التقسيط',
                    'items' => [
                        ['route' => 'admin.installment-plans', 'label' => 'خطط التقسيط'],
                        ['route' => 'admin.installment-contracts', 'label' => 'عقود التقسيط'],
                        ['route' => 'admin.installment-reports', 'label' => 'تقارير التقسيط'],
                        ['route' => 'admin.installment-dunning', 'label' => 'تصعيد المتأخرات'],
                        ['route' => 'admin.installment-settings', 'label' => 'إعدادات التقسيط'],
                    ],
                ],
            ],
        ],
        [
            'type' => 'group',
            'id' => 'governance',
            'label' => 'إدارة المنصة',
            'icon' => 'grid',
            'children' => [
                [
                    'type' => 'section',
                    'label' => 'الحسابات والصلاحيات',
                    'items' => [
                        ['route' => 'admin.users', 'label' => 'المستخدمون'],
                        ['route' => 'admin.users.permissions', 'label' => 'صلاحيات الأدوار'],
                    ],
                ],
                [
                    'type' => 'section',
                    'label' => 'النظام والحوكمة',
                    'items' => [
                        ['route' => 'admin.settings', 'label' => 'إعدادات المنصة'],
                        ['route' => 'admin.system-settings', 'label' => 'إعدادات النظام'],
                        ['route' => 'admin.teams-settings', 'label' => 'Microsoft Teams'],
                        ['route' => 'admin.zoom-settings', 'label' => 'Zoom'],
                        ['route' => 'admin.audit-log', 'label' => 'سجل التدقيق'],
                    ],
                ],
            ],
        ],
    ],

    // Placeholder routes only — empty while all listed modules are live Livewire pages.
    'coming_soon' => [],

];
