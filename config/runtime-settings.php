<?php

/**
 * سجل إعدادات النظام القابلة للإدارة من لوحة التحكم.
 * القيم المحفوظة في platform_settings تتجاوز .env عند التشغيل.
 */
return [

    'sections' => [
        'app' => [
            'label' => 'التطبيق والبيئة',
            'description' => 'اسم التطبيق، الرابط، وضع التصحيح، اللغة، السجلات',
            'permission' => 'system-settings.manage',
            'icon' => 'fa-server',
        ],
        'mail' => [
            'label' => 'البريد الإلكتروني',
            'description' => 'SMTP وعنوان المرسل — يُستخدم للإشعارات وإعادة تعيين كلمة المرور',
            'permission' => 'mail-settings.manage',
            'icon' => 'fa-envelope',
        ],
        'infrastructure' => [
            'label' => 'البنية التحتية',
            'description' => 'الجلسات، التخزين المؤقت، الطوابير، البث',
            'permission' => 'infrastructure-settings.manage',
            'icon' => 'fa-gears',
        ],
        'database' => [
            'label' => 'قاعدة البيانات',
            'description' => 'اتصال قاعدة البيانات — يتطلب إعادة تشغيل التطبيق',
            'permission' => 'infrastructure-settings.manage',
            'icon' => 'fa-database',
        ],
        'redis' => [
            'label' => 'Redis',
            'description' => 'إعدادات Redis للتخزين المؤقت والطوابير',
            'permission' => 'infrastructure-settings.manage',
            'icon' => 'fa-memory',
        ],
        'storage' => [
            'label' => 'التخزين والسحابة',
            'description' => 'قرص الملفات و AWS S3',
            'permission' => 'storage-settings.manage',
            'icon' => 'fa-cloud',
        ],
        'analytics' => [
            'label' => 'تحليلات الزيارات',
            'description' => 'مصدر الموقع الجغرافي، الخصوصية، وفترة الاحتفاظ بالأحداث',
            'permission' => 'analytics.manage',
            'icon' => 'fa-chart-line',
        ],
    ],

    'fields' => [

        // --- تحليلات الزيارات ---
        'ANALYTICS_GEO_PROVIDER' => [
            'section' => 'analytics',
            'label_ar' => 'مزوّد بيانات الموقع الجغرافي',
            'type' => 'select',
            'options' => [
                'none' => 'معطّل',
                'cloudflare' => 'Cloudflare',
                'vercel' => 'Vercel',
                'cloudfront' => 'Amazon CloudFront',
                'appengine' => 'Google App Engine',
                'custom' => 'ترويسات مخصّصة X-Geo-*',
            ],
            'config' => ['analytics.geo_provider'],
            'hint_ar' => 'فعّله فقط عندما تمر كل الطلبات عبر المزوّد المختار حتى لا يمكن تزوير بيانات الموقع.',
        ],
        'ANALYTICS_RETENTION_DAYS' => [
            'section' => 'analytics',
            'label_ar' => 'مدة الاحتفاظ بالأحداث الخام (أيام)',
            'type' => 'integer',
            'config' => ['analytics.retention_days'],
            'hint_ar' => 'الافتراضي 180 يوماً لدعم مقارنة آخر 90 يوماً بالفترة السابقة؛ تُحذف البيانات الأقدم تلقائياً.',
        ],
        'ANALYTICS_HASH_SALT' => [
            'section' => 'analytics',
            'label_ar' => 'مفتاح تشفير بصمة الزائر',
            'type' => 'password',
            'secret' => true,
            'config' => ['analytics.hash_salt'],
            'hint_ar' => 'استخدم قيمة عشوائية مستقلة. لا يتم تخزين عنوان IP الخام، وتتغير البصمة شهرياً.',
        ],

        // --- التطبيق ---
        'APP_NAME' => [
            'section' => 'app',
            'label_ar' => 'اسم التطبيق',
            'type' => 'string',
            'config' => ['app.name'],
            'hint_ar' => 'يظهر في عناوين البريد وواجهة النظام',
        ],
        'APP_ENV' => [
            'section' => 'app',
            'label_ar' => 'بيئة التشغيل',
            'type' => 'select',
            'options' => ['local' => 'local', 'staging' => 'staging', 'production' => 'production'],
            'config' => ['app.env'],
            'requires_restart' => true,
        ],
        'APP_DEBUG' => [
            'section' => 'app',
            'label_ar' => 'وضع التصحيح',
            'type' => 'boolean',
            'config' => ['app.debug'],
            'hint_ar' => 'عطّله في الإنتاج',
        ],
        'APP_URL' => [
            'section' => 'app',
            'label_ar' => 'رابط المنصة',
            'type' => 'url',
            'config' => ['app.url'],
            'hint_ar' => 'يجب أن يطابق الرابط العام للموقع (Teams OAuth، البريد)',
        ],
        'APP_LOCALE' => [
            'section' => 'app',
            'label_ar' => 'اللغة الافتراضية',
            'type' => 'select',
            'options' => ['ar' => 'العربية', 'en' => 'English'],
            'config' => ['app.locale'],
        ],
        'APP_FALLBACK_LOCALE' => [
            'section' => 'app',
            'label_ar' => 'لغة الاحتياط',
            'type' => 'select',
            'options' => ['ar' => 'العربية', 'en' => 'English'],
            'config' => ['app.fallback_locale'],
        ],
        'LOG_CHANNEL' => [
            'section' => 'app',
            'label_ar' => 'قناة السجلات',
            'type' => 'select',
            'options' => ['stack' => 'stack', 'single' => 'single', 'daily' => 'daily', 'stderr' => 'stderr'],
            'config' => ['logging.default'],
        ],
        'LOG_LEVEL' => [
            'section' => 'app',
            'label_ar' => 'مستوى السجلات',
            'type' => 'select',
            'options' => ['debug' => 'debug', 'info' => 'info', 'warning' => 'warning', 'error' => 'error'],
            'config' => ['logging.channels.single.level'],
        ],

        // --- البريد ---
        'MAIL_MAILER' => [
            'section' => 'mail',
            'label_ar' => 'نوع المرسل',
            'type' => 'select',
            'options' => ['log' => 'log (تطوير)', 'smtp' => 'SMTP', 'sendmail' => 'sendmail', 'array' => 'array (اختبار)'],
            'config' => ['mail.default'],
        ],
        'MAIL_HOST' => [
            'section' => 'mail',
            'label_ar' => 'خادم SMTP',
            'type' => 'string',
            'config' => ['mail.mailers.smtp.host'],
        ],
        'MAIL_PORT' => [
            'section' => 'mail',
            'label_ar' => 'منفذ SMTP',
            'type' => 'integer',
            'config' => ['mail.mailers.smtp.port'],
        ],
        'MAIL_USERNAME' => [
            'section' => 'mail',
            'label_ar' => 'اسم مستخدم SMTP',
            'type' => 'string',
            'config' => ['mail.mailers.smtp.username'],
        ],
        'MAIL_PASSWORD' => [
            'section' => 'mail',
            'label_ar' => 'كلمة مرور SMTP',
            'type' => 'password',
            'secret' => true,
            'config' => ['mail.mailers.smtp.password'],
        ],
        'MAIL_FROM_ADDRESS' => [
            'section' => 'mail',
            'label_ar' => 'بريد المرسل',
            'type' => 'email',
            'config' => ['mail.from.address'],
        ],
        'MAIL_FROM_NAME' => [
            'section' => 'mail',
            'label_ar' => 'اسم المرسل',
            'type' => 'string',
            'config' => ['mail.from.name'],
        ],

        // --- البنية ---
        'SESSION_DRIVER' => [
            'section' => 'infrastructure',
            'label_ar' => 'محرك الجلسات',
            'type' => 'select',
            'options' => ['file' => 'file', 'database' => 'database', 'redis' => 'redis'],
            'config' => ['session.driver'],
            'requires_restart' => true,
        ],
        'SESSION_LIFETIME' => [
            'section' => 'infrastructure',
            'label_ar' => 'مدة الجلسة (دقيقة)',
            'type' => 'integer',
            'config' => ['session.lifetime'],
        ],
        'CACHE_STORE' => [
            'section' => 'infrastructure',
            'label_ar' => 'مخزن التخزين المؤقت',
            'type' => 'select',
            'options' => ['file' => 'file', 'database' => 'database', 'redis' => 'redis', 'array' => 'array'],
            'config' => ['cache.default'],
            'requires_restart' => true,
        ],
        'QUEUE_CONNECTION' => [
            'section' => 'infrastructure',
            'label_ar' => 'اتصال الطوابير',
            'type' => 'select',
            'options' => ['sync' => 'sync', 'database' => 'database', 'redis' => 'redis'],
            'config' => ['queue.default'],
            'requires_restart' => true,
        ],
        'BROADCAST_CONNECTION' => [
            'section' => 'infrastructure',
            'label_ar' => 'اتصال البث',
            'type' => 'select',
            'options' => ['log' => 'log', 'null' => 'null', 'redis' => 'redis'],
            'config' => ['broadcasting.default'],
        ],
        'FILESYSTEM_DISK' => [
            'section' => 'infrastructure',
            'label_ar' => 'قرص الملفات الافتراضي',
            'type' => 'select',
            'options' => ['local' => 'local', 'public' => 'public', 's3' => 's3'],
            'config' => ['filesystems.default'],
        ],

        // --- قاعدة البيانات ---
        'DB_CONNECTION' => [
            'section' => 'database',
            'label_ar' => 'نوع الاتصال',
            'type' => 'select',
            'options' => ['sqlite' => 'sqlite', 'mysql' => 'mysql', 'pgsql' => 'pgsql'],
            'config' => ['database.default'],
            'requires_restart' => true,
        ],
        'DB_HOST' => [
            'section' => 'database',
            'label_ar' => 'المضيف',
            'type' => 'string',
            'config' => ['database.connections.mysql.host', 'database.connections.pgsql.host'],
            'requires_restart' => true,
        ],
        'DB_PORT' => [
            'section' => 'database',
            'label_ar' => 'المنفذ',
            'type' => 'integer',
            'config' => ['database.connections.mysql.port', 'database.connections.pgsql.port'],
            'requires_restart' => true,
        ],
        'DB_DATABASE' => [
            'section' => 'database',
            'label_ar' => 'اسم القاعدة',
            'type' => 'string',
            'config' => ['database.connections.mysql.database', 'database.connections.pgsql.database', 'database.connections.sqlite.database'],
            'requires_restart' => true,
        ],
        'DB_USERNAME' => [
            'section' => 'database',
            'label_ar' => 'اسم المستخدم',
            'type' => 'string',
            'config' => ['database.connections.mysql.username', 'database.connections.pgsql.username'],
            'requires_restart' => true,
        ],
        'DB_PASSWORD' => [
            'section' => 'database',
            'label_ar' => 'كلمة المرور',
            'type' => 'password',
            'secret' => true,
            'config' => ['database.connections.mysql.password', 'database.connections.pgsql.password'],
            'requires_restart' => true,
        ],

        // --- Redis ---
        'REDIS_HOST' => [
            'section' => 'redis',
            'label_ar' => 'مضيف Redis',
            'type' => 'string',
            'config' => ['database.redis.default.host', 'database.redis.cache.host'],
            'requires_restart' => true,
        ],
        'REDIS_PORT' => [
            'section' => 'redis',
            'label_ar' => 'منفذ Redis',
            'type' => 'integer',
            'config' => ['database.redis.default.port', 'database.redis.cache.port'],
            'requires_restart' => true,
        ],
        'REDIS_PASSWORD' => [
            'section' => 'redis',
            'label_ar' => 'كلمة مرور Redis',
            'type' => 'password',
            'secret' => true,
            'config' => ['database.redis.default.password', 'database.redis.cache.password'],
            'requires_restart' => true,
        ],

        // --- التخزين / AWS ---
        'AWS_ACCESS_KEY_ID' => [
            'section' => 'storage',
            'label_ar' => 'AWS Access Key',
            'type' => 'string',
            'config' => ['filesystems.disks.s3.key', 'services.ses.key'],
        ],
        'AWS_SECRET_ACCESS_KEY' => [
            'section' => 'storage',
            'label_ar' => 'AWS Secret Key',
            'type' => 'password',
            'secret' => true,
            'config' => ['filesystems.disks.s3.secret', 'services.ses.secret'],
        ],
        'AWS_DEFAULT_REGION' => [
            'section' => 'storage',
            'label_ar' => 'منطقة AWS',
            'type' => 'string',
            'config' => ['filesystems.disks.s3.region', 'services.ses.region'],
        ],
        'AWS_BUCKET' => [
            'section' => 'storage',
            'label_ar' => 'اسم الحاوية S3',
            'type' => 'string',
            'config' => ['filesystems.disks.s3.bucket'],
        ],
    ],

    'readonly_keys' => [
        'APP_KEY' => [
            'section' => 'app',
            'label_ar' => 'مفتاح التطبيق (APP_KEY)',
            'hint_ar' => 'يُولَّد عبر artisan — لا يُعدَّل من اللوحة لأسباب أمنية',
        ],
    ],

    'integration_pages' => [
        [
            'route' => 'admin.payment-settings',
            'label' => 'طرق الدفع (Moyasar)',
            'permission' => 'payment-settings.manage',
            'description' => 'مفاتيح Moyasar والتحويل البنكي',
        ],
        [
            'route' => 'admin.teams-settings',
            'label' => 'Microsoft Teams',
            'permission' => 'teams-settings.manage',
            'description' => 'Azure AD، الحضور، التسجيلات',
        ],
        [
            'route' => 'admin.zoom-settings',
            'label' => 'Zoom',
            'permission' => 'zoom-settings.manage',
            'description' => 'ربط Zoom، المضيفون، الحضور والتسجيلات',
        ],
        [
            'route' => 'admin.zoxagent-settings',
            'label' => 'ZoxAgent Meet',
            'permission' => 'zoxagent-settings.manage',
            'description' => 'ربط ZoxAgent، القاعات، الحضور والتسجيل التلقائي',
        ],
        [
            'route' => 'admin.notification-rules',
            'label' => 'قواعد الإشعارات',
            'permission' => 'notifications.manage',
            'description' => 'تذكيرات المحاضرات والقنوات',
        ],
        [
            'route' => 'admin.audit-log',
            'label' => 'سجل التدقيق',
            'permission' => 'audit-log.view',
            'description' => 'تتبع تغييرات الإعدادات والإشعارات',
        ],
        [
            'route' => 'admin.settings',
            'label' => 'إعدادات المنصة العامة',
            'permission' => 'settings.manage',
            'description' => 'الاسم، الدعم، وضع الصيانة',
        ],
        [
            'route' => 'admin.identity-themes',
            'label' => 'قوالب الهوية',
            'permission' => 'settings.manage',
            'description' => 'ثيمات الصفحة الرئيسية حسب الجهة',
        ],
    ],

];
