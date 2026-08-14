<?php

namespace App\Services;

use App\Models\AcademicProgram;
use App\Models\AcademicStudent;
use App\Models\Article;
use App\Models\CatalogCourse;
use App\Models\Certificate;
use App\Models\CrmContact;
use App\Models\Order;
use App\Models\RegistrationApplication;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\AdminPermissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminGlobalSearchService
{
    /**
     * @return list<array{key: string, label: string, items: list<array<string, mixed>>}>
     */
    public function search(User $user, string $query, int $perGroup = 5): array
    {
        $query = trim(preg_replace('/\s+/u', ' ', $query) ?? '');

        if ($query === '') {
            return [
                $this->group('quick', 'اختصارات سريعة', $this->quickLinks($user)->take(8)->all()),
            ];
        }

        if (mb_strlen($query) < 2) {
            return [
                $this->group('hint', 'اكتب حرفين على الأقل', []),
            ];
        }

        $groups = [];

        $pages = $this->searchPages($user, $query, $perGroup);
        if ($pages->isNotEmpty()) {
            $groups[] = $this->group('pages', 'صفحات اللوحة', $pages->all());
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.students') && Schema::hasTable('academic_students')) {
            $students = $this->searchStudents($query, $perGroup);
            if ($students->isNotEmpty()) {
                $groups[] = $this->group('students', 'الطلاب', $students->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.crm') && Schema::hasTable('crm_contacts')) {
            $crm = $this->searchCrm($user, $query, $perGroup);
            if ($crm->isNotEmpty()) {
                $groups[] = $this->group('crm', 'CRM والمبيعات', $crm->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.users') && Schema::hasTable('users')) {
            $users = $this->searchUsers($query, $perGroup);
            if ($users->isNotEmpty()) {
                $groups[] = $this->group('users', 'المستخدمون', $users->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.orders') && Schema::hasTable('orders')) {
            $orders = $this->searchOrders($query, $perGroup);
            if ($orders->isNotEmpty()) {
                $groups[] = $this->group('orders', 'الطلبات', $orders->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.catalog-courses') && Schema::hasTable('catalog_courses')) {
            $courses = $this->searchCatalogCourses($query, $perGroup);
            if ($courses->isNotEmpty()) {
                $groups[] = $this->group('catalog', 'الدورات والدبلومات', $courses->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.programs') && Schema::hasTable('academic_programs')) {
            $programs = $this->searchPrograms($query, $perGroup);
            if ($programs->isNotEmpty()) {
                $groups[] = $this->group('programs', 'البرامج الأكاديمية', $programs->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.certificates') && Schema::hasTable('certificates')) {
            $certs = $this->searchCertificates($query, $perGroup);
            if ($certs->isNotEmpty()) {
                $groups[] = $this->group('certificates', 'الشهادات', $certs->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.articles') && Schema::hasTable('articles')) {
            $articles = $this->searchArticles($query, $perGroup);
            if ($articles->isNotEmpty()) {
                $groups[] = $this->group('articles', 'الأخبار والمقالات', $articles->all());
            }
        }

        if (
            AdminPermissions::canAccessRoute($user, 'admin.applications.client')
            && Schema::hasTable('registration_applications')
        ) {
            $apps = $this->searchApplications($query, $perGroup);
            if ($apps->isNotEmpty()) {
                $groups[] = $this->group('applications', 'طلبات الانضمام', $apps->all());
            }
        }

        if (AdminPermissions::canAccessRoute($user, 'admin.support-tickets') && Schema::hasTable('support_tickets')) {
            $tickets = $this->searchTickets($query, $perGroup);
            if ($tickets->isNotEmpty()) {
                $groups[] = $this->group('tickets', 'تذاكر الدعم', $tickets->all());
            }
        }

        if ($groups === []) {
            return [
                $this->group('empty', 'لا توجد نتائج', [
                    $this->item(
                        id: 'empty',
                        title: 'لم نعثر على مطابقة لـ «'.$query.'»',
                        subtitle: 'جرّب الاسم، رقم الهوية، رقم الطلب، أو اسم الصفحة',
                        url: '#',
                        icon: 'search',
                        type: 'empty',
                    ),
                ]),
            ];
        }

        return $groups;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{key: string, label: string, items: list<array<string, mixed>>}
     */
    protected function group(string $key, string $label, array $items): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function item(
        string $id,
        string $title,
        string $subtitle,
        string $url,
        string $icon,
        string $type,
        ?string $badge = null,
    ): array {
        return [
            'id' => $id,
            'title' => $title,
            'subtitle' => $subtitle,
            'url' => $url,
            'icon' => $icon,
            'type' => $type,
            'badge' => $badge,
        ];
    }

    protected function like(string $query): string
    {
        return '%'.str_replace(['%', '_'], ['\\%', '\\_'], $query).'%';
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function quickLinks(User $user): Collection
    {
        $candidates = [
            ['route' => 'admin.dashboard', 'title' => 'مركز القيادة', 'subtitle' => 'لوحة المؤشرات', 'icon' => 'home'],
            ['route' => 'admin.crm', 'title' => 'CRM والمبيعات', 'subtitle' => 'العملاء والمتابعة', 'icon' => 'users'],
            ['route' => 'admin.students', 'title' => 'الطلاب', 'subtitle' => 'شؤون الطلاب', 'icon' => 'student'],
            ['route' => 'admin.enrollment', 'title' => 'التسجيل والالتحاق', 'subtitle' => 'المسجلون الجدد', 'icon' => 'student'],
            ['route' => 'admin.orders', 'title' => 'الطلبات والمدفوعات', 'subtitle' => 'المالية', 'icon' => 'order'],
            ['route' => 'admin.catalog-courses', 'title' => 'الدورات والدبلومات', 'subtitle' => 'الكتالوج', 'icon' => 'course'],
            ['route' => 'admin.certificates', 'title' => 'الشهادات', 'subtitle' => 'الإصدار والتحقق', 'icon' => 'certificate'],
            ['route' => 'admin.articles', 'title' => 'الأخبار والفعاليات', 'subtitle' => 'المحتوى', 'icon' => 'article'],
            ['route' => 'admin.settings', 'title' => 'إعدادات المنصة', 'subtitle' => 'الشعارات والهوية', 'icon' => 'settings'],
            ['route' => 'admin.notifications', 'title' => 'صندوق الإشعارات', 'subtitle' => 'التنبيهات', 'icon' => 'bell'],
        ];

        return collect($candidates)
            ->filter(fn (array $item): bool => AdminPermissions::canAccessRoute($user, $item['route']))
            ->map(fn (array $item) => $this->item(
                id: 'quick-'.$item['route'],
                title: $item['title'],
                subtitle: $item['subtitle'],
                url: route($item['route']),
                icon: $item['icon'],
                type: 'page',
                badge: 'صفحة',
            ))
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchablePages(User $user): Collection
    {
        $pages = collect();

        foreach (config('admin.sidebar', []) as $item) {
            $type = $item['type'] ?? '';

            if ($type === 'link') {
                $route = $item['route'] ?? null;
                if (! $route || ! AdminPermissions::canAccessRoute($user, $route)) {
                    continue;
                }

                $pages->push([
                    'label' => (string) ($item['label'] ?? ''),
                    'route' => $route,
                    'group' => 'صفحات اللوحة',
                    'keywords' => $item['keywords'] ?? [],
                ]);

                continue;
            }

            if ($type !== 'group') {
                continue;
            }

            $groupLabel = (string) ($item['label'] ?? 'القائمة');

            foreach ($item['children'] ?? [] as $section) {
                $sectionLabel = (string) ($section['label'] ?? '');

                foreach ($section['items'] ?? [] as $link) {
                    $route = $link['route'] ?? null;
                    if (! $route || ! AdminPermissions::canAccessRoute($user, $route)) {
                        continue;
                    }

                    $pages->push([
                        'label' => (string) ($link['label'] ?? ''),
                        'route' => $route,
                        'group' => $groupLabel,
                        'section' => $sectionLabel,
                        'keywords' => $link['keywords'] ?? [],
                    ]);
                }
            }
        }

        foreach ([
            ['route' => 'admin.settings', 'label' => 'إعدادات المنصة', 'group' => 'النظام'],
            ['route' => 'admin.system-settings', 'label' => 'إعدادات النظام', 'group' => 'النظام'],
            ['route' => 'admin.notifications', 'label' => 'صندوق الإشعارات', 'group' => 'النظام'],
            ['route' => 'admin.notification-rules', 'label' => 'قواعد الإشعارات', 'group' => 'النظام'],
        ] as $extra) {
            if (! AdminPermissions::canAccessRoute($user, $extra['route'])) {
                continue;
            }
            if ($pages->contains(fn (array $p): bool => $p['route'] === $extra['route'])) {
                continue;
            }
            $pages->push($extra + ['keywords' => []]);
        }

        return $pages->unique('route')->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchPages(User $user, string $query, int $limit): Collection
    {
        $needle = Str::lower($query);

        return $this->searchablePages($user)
            ->map(function (array $page) use ($needle) {
                $haystack = Str::lower(implode(' ', array_filter([
                    $page['label'] ?? '',
                    $page['group'] ?? '',
                    $page['section'] ?? '',
                    implode(' ', $page['keywords'] ?? []),
                    $this->pageAliases($page['route'] ?? ''),
                ])));

                $score = 0;
                if (str_contains($haystack, $needle)) {
                    $score += 10;
                }
                if (str_starts_with(Str::lower($page['label'] ?? ''), $needle)) {
                    $score += 20;
                }
                foreach (preg_split('/\s+/u', $needle) ?: [] as $token) {
                    if ($token !== '' && str_contains($haystack, $token)) {
                        $score += 4;
                    }
                }

                return $score > 0 ? ($page + ['_score' => $score]) : null;
            })
            ->filter()
            ->sortByDesc('_score')
            ->take($limit)
            ->map(fn (array $page) => $this->item(
                id: 'page-'.$page['route'],
                title: $page['label'],
                subtitle: trim(($page['group'] ?? '').(filled($page['section'] ?? null) ? ' · '.$page['section'] : '')),
                url: route($page['route']),
                icon: 'page',
                type: 'page',
                badge: 'صفحة',
            ))
            ->values();
    }

    protected function pageAliases(string $route): string
    {
        return match (true) {
            str_contains($route, 'crm') => 'crm مبيعات عملاء leads sales',
            str_contains($route, 'student') || str_contains($route, 'enrollment') => 'طلاب متدربين تسجيل الحاق students',
            str_contains($route, 'order') || str_contains($route, 'financial') || str_contains($route, 'installment') => 'طلبات مدفوعات مالية اقساط orders finance',
            str_contains($route, 'catalog') => 'دورات دبلومات شهادات احترافية courses',
            str_contains($route, 'certificate') => 'شهادات certificates',
            str_contains($route, 'article') => 'اخبار فعاليات مقالات news',
            str_contains($route, 'application') => 'طلبات انضمام applications',
            str_contains($route, 'support') => 'دعم تذاكر tickets',
            str_contains($route, 'setting') => 'اعدادات settings',
            str_contains($route, 'notification') => 'اشعارات notifications',
            str_contains($route, 'user') => 'مستخدمين صلاحيات users',
            str_contains($route, 'program') => 'برامج اكاديمية programs',
            default => '',
        };
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchStudents(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return AcademicStudent::query()
            ->where(function ($q) use ($like) {
                $q->where('name_ar', 'like', $like)
                    ->orWhere('academic_id', 'like', $like)
                    ->orWhere('national_id', 'like', $like)
                    ->orWhere('mobile', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'name_ar', 'academic_id', 'national_id', 'mobile'])
            ->map(fn (AcademicStudent $student) => $this->item(
                id: 'student-'.$student->id,
                title: $student->name_ar ?: 'طالب #'.$student->id,
                subtitle: collect([
                    $student->academic_id ? 'أكاديمي: '.$student->academic_id : null,
                    $student->national_id ? 'هوية: '.$student->national_id : null,
                    $student->mobile ? 'جوال: '.$student->mobile : null,
                ])->filter()->implode(' · '),
                url: route('admin.students.show', $student),
                icon: 'student',
                type: 'student',
                badge: 'طالب',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchCrm(User $user, string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return CrmContact::query()
            ->visibleTo($user)
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('company', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'phone', 'company'])
            ->map(fn (CrmContact $contact) => $this->item(
                id: 'crm-'.$contact->id,
                title: $contact->name ?: 'عميل #'.$contact->id,
                subtitle: collect([$contact->company, $contact->phone, $contact->email])->filter()->implode(' · '),
                url: route('admin.crm.contacts.show', $contact),
                icon: 'users',
                type: 'crm',
                badge: 'CRM',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchUsers(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return User::query()
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('name_ar', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('national_id', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'name', 'name_ar', 'email', 'phone', 'role'])
            ->map(fn (User $user) => $this->item(
                id: 'user-'.$user->id,
                title: $user->displayName(),
                subtitle: collect([$user->email, $user->phone, $user->role])->filter()->implode(' · '),
                url: route('admin.users.show', $user),
                icon: 'user',
                type: 'user',
                badge: 'مستخدم',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchOrders(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return Order::query()
            ->with('user:id,name,name_ar,email')
            ->where(function ($q) use ($like, $query) {
                $q->where('reference', 'like', $like)
                    ->orWhere('payment_ref', 'like', $like)
                    ->orWhere('gateway_payment_id', 'like', $like)
                    ->orWhereHas('user', function ($uq) use ($like) {
                        $uq->where('name', 'like', $like)
                            ->orWhere('name_ar', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });

                if (is_numeric($query)) {
                    $q->orWhere('id', (int) $query);
                }
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Order $order) => $this->item(
                id: 'order-'.$order->id,
                title: $order->reference ?: 'طلب #'.$order->id,
                subtitle: collect([
                    $order->user?->displayName(),
                    $order->status,
                    $order->total !== null ? number_format((float) $order->total, 2).' ر.س' : null,
                ])->filter()->implode(' · '),
                url: route('admin.orders.show', $order),
                icon: 'order',
                type: 'order',
                badge: 'طلب',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchCatalogCourses(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return CatalogCourse::query()
            ->where(function ($q) use ($like) {
                $q->where('title_ar', 'like', $like)
                    ->orWhere('title_en', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title_ar', 'title_en', 'slug'])
            ->map(fn (CatalogCourse $course) => $this->item(
                id: 'catalog-'.$course->id,
                title: $course->displayTitle(),
                subtitle: $course->slug ?: 'دورة / دبلوم',
                url: route('admin.catalog-courses.edit', $course),
                icon: 'course',
                type: 'course',
                badge: 'دورة',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchPrograms(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return AcademicProgram::query()
            ->where(function ($q) use ($like) {
                $q->where('name_ar', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('symbol', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'name_ar', 'code', 'symbol'])
            ->map(fn (AcademicProgram $program) => $this->item(
                id: 'program-'.$program->id,
                title: $program->name_ar ?: 'برنامج #'.$program->id,
                subtitle: collect([$program->code, $program->symbol])->filter()->implode(' · '),
                url: route('admin.programs.show', $program),
                icon: 'program',
                type: 'program',
                badge: 'برنامج',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchCertificates(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return Certificate::query()
            ->where(function ($q) use ($like) {
                $q->where('holder_name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('program_name', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'holder_name', 'code', 'program_name'])
            ->map(fn (Certificate $certificate) => $this->item(
                id: 'certificate-'.$certificate->id,
                title: $certificate->holder_name ?: 'شهادة #'.$certificate->id,
                subtitle: collect([$certificate->code, $certificate->program_name])->filter()->implode(' · '),
                url: route('admin.certificates', ['q' => $certificate->code ?: $certificate->holder_name]),
                icon: 'certificate',
                type: 'certificate',
                badge: 'شهادة',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchArticles(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return Article::query()
            ->whereHas('translations', function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            })
            ->with(['translations' => fn ($q) => $q->where('locale', 'ar')->orWhere('locale', 'en')])
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (Article $article) {
                $t = $article->translations->firstWhere('locale', 'ar')
                    ?? $article->translations->first();

                return $this->item(
                    id: 'article-'.$article->id,
                    title: $t?->title ?: 'مقال #'.$article->id,
                    subtitle: $t?->slug ?: 'خبر / فعالية',
                    url: route('admin.articles.edit', $article),
                    icon: 'article',
                    type: 'article',
                    badge: 'مقال',
                );
            });
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchApplications(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return RegistrationApplication::query()
            ->where(function ($q) use ($like) {
                $q->where('application_no', 'like', $like)
                    ->orWhere('applicant_name', 'like', $like)
                    ->orWhere('applicant_email', 'like', $like)
                    ->orWhere('applicant_phone', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'application_no', 'applicant_name', 'applicant_email', 'type'])
            ->map(fn (RegistrationApplication $app) => $this->item(
                id: 'application-'.$app->id,
                title: $app->applicant_name ?: ($app->application_no ?: 'طلب #'.$app->id),
                subtitle: collect([$app->application_no, $app->type, $app->applicant_email])->filter()->implode(' · '),
                url: route('admin.applications.show', $app),
                icon: 'application',
                type: 'application',
                badge: 'انضمام',
            ));
    }

    /** @return Collection<int, array<string, mixed>> */
    protected function searchTickets(string $query, int $limit): Collection
    {
        $like = $this->like($query);

        return SupportTicket::query()
            ->where(function ($q) use ($like) {
                $q->where('reference_code', 'like', $like)
                    ->orWhere('subject', 'like', $like)
                    ->orWhere('contact_email', 'like', $like)
                    ->orWhere('contact_name', 'like', $like);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'reference_code', 'subject', 'contact_name', 'status'])
            ->map(fn (SupportTicket $ticket) => $this->item(
                id: 'ticket-'.$ticket->id,
                title: $ticket->reference_code ?: 'تذكرة #'.$ticket->id,
                subtitle: collect([$ticket->subject, $ticket->contact_name, $ticket->status])->filter()->implode(' · '),
                url: route('admin.support-tickets.show', $ticket),
                icon: 'ticket',
                type: 'ticket',
                badge: 'دعم',
            ));
    }
}
