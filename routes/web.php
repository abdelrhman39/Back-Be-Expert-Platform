<?php

use App\Http\Controllers\Admin\ArticleMediaController;
use App\Http\Controllers\Admin\CatalogModuleImageController;
use App\Http\Controllers\Admin\InstructorImpersonationController;
use App\Http\Controllers\Admin\RegistrationApplicationAttachmentController;
use App\Http\Controllers\Admin\StudentImpersonationController;
use App\Http\Controllers\Auth\AdminLogoutController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Certificates\CertificateDownloadController;
use App\Http\Controllers\Commerce\BnplCallbackController;
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Commerce\CatalogLessonFileController;
use App\Http\Controllers\Commerce\CheckoutCallbackController;
use App\Http\Controllers\Commerce\InstallmentCallbackController;
use App\Http\Controllers\Commerce\WishlistController;
use App\Http\Controllers\InstructorExamAnswerFileController;
use App\Http\Controllers\Integrations\MicrosoftTeamsOAuthController;
use App\Http\Controllers\Media\MediaPublicController;
use App\Http\Controllers\Sessions\InstructorZoomStartController;
use App\Http\Controllers\Sessions\SessionJoinController;
use App\Http\Controllers\Sessions\SessionRecordingController;
use App\Http\Controllers\Webhooks\MoyasarWebhookController;
use App\Http\Controllers\Webhooks\TabbyWebhookController;
use App\Http\Controllers\Webhooks\TamaraWebhookController;
use App\Http\Controllers\Webhooks\ZoomWebhookController;
use App\Http\Controllers\Webhooks\ZoxAgentWebhookController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/ar');
Route::redirect('/news', '/ar/news');
Route::get('/media/public/{token}', MediaPublicController::class)->name('media.public');
Route::get('/news/{slug}', function (string $slug) {
    return redirect("/ar/news/{$slug}");
})->where('slug', '[A-Za-z0-9\-]+');

Route::post('/webhooks/moyasar', MoyasarWebhookController::class)->name('webhooks.moyasar');
Route::post('/webhooks/tabby', TabbyWebhookController::class)->name('webhooks.tabby');
Route::post('/webhooks/tamara', TamaraWebhookController::class)->name('webhooks.tamara');
Route::post('/webhooks/zoom', ZoomWebhookController::class)->name('webhooks.zoom');
Route::post('/webhooks/zoxagent', ZoxAgentWebhookController::class)->name('webhooks.zoxagent');

Route::get('/integrations/microsoft/callback', [MicrosoftTeamsOAuthController::class, 'callback'])
    ->name('integrations.microsoft.callback');

Route::middleware('auth')->group(function () {
    Route::get('/integrations/microsoft/connect', [MicrosoftTeamsOAuthController::class, 'redirect'])
        ->name('integrations.microsoft.connect');
});

Route::prefix('{locale}')
    ->where(['locale' => 'ar|en'])
    ->middleware('set.locale')
    ->group(function () {
        Route::livewire('/', 'pages.home-page')->name('home');
        Route::livewire('/about', 'pages.about-page')->name('about');
        Route::livewire('/contact', 'pages.contact-page')->name('contact');
        Route::livewire('/page/{slug}', 'pages.cms-page')->name('cms.page');
        Route::livewire('/news', 'pages.articles-index-page')->name('articles.index');
        Route::livewire('/news/{slug}', 'pages.article-show-page')->name('articles.show');

        Route::livewire('/apply/{type}', 'apps.registration-application-form-page')
            ->where('type', 'client|company|marketer|instructor|employee|job_seeker|cooperative')
            ->name('apply.form');
        Route::livewire('/apply/track/{application?}', 'apps.registration-application-track-page')->name('apply.track');

        Route::livewire('/request/{fellowship:slug}', 'apps.fellowship-application-page')->name('fellowship.apply');

        foreach ([
            'client-request' => 'client',
            'company-request' => 'company',
            'marketer-request' => 'marketer',
            'instructor-request' => 'instructor',
            'employee-request' => 'employee',
            'job-seeker-request' => 'job_seeker',
            'cooperative-training' => 'cooperative',
        ] as $legacy => $applyType) {
            Route::redirect($legacy, 'apply/'.$applyType);
        }

        Route::livewire('/courses', 'catalog.courses-index')->name('courses.index');
        Route::livewire('/courses/{course}/preview/{lesson}', 'catalog.course-preview-page')->name('courses.preview');
        Route::get('/courses/{course}/enroll', function (string $locale, string $course) {
            return redirect()->to(route('courses.show', [
                'locale' => $locale,
                'course' => $course,
            ]).'#course-enroll');
        })->name('courses.enroll');
        Route::livewire('/courses/{course}', 'catalog.course-show-page')->name('courses.show');
        Route::livewire('/cart', 'commerce.cart-page')->name('cart');
        Route::livewire('/wishlist', 'commerce.wishlist-page')->name('wishlist');
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

        Route::middleware('portal.auth')->group(function () {
            Route::livewire('/checkout', 'commerce.checkout-page')->name('checkout');
            Route::get('/checkout/callback', CheckoutCallbackController::class)->name('checkout.callback');
            Route::get('/checkout/bnpl/{provider}/callback', BnplCallbackController::class)
                ->whereIn('provider', ['tabby', 'tamara'])
                ->name('checkout.bnpl.callback');
        });

        Route::middleware('portal.guest')->group(function () {
            Route::livewire('/login', 'auth.login-page')->name('login');
            Route::livewire('/register', 'auth.register-page')->name('register');
            Route::livewire('/password/reset', 'auth.forgot-password-page')->name('password.request');
            Route::livewire('/password/reset/{token}', 'auth.reset-password-page')->name('password.reset');
        });

        Route::livewire('/certificate-verify/{code?}', 'pages.certificate-verify-page')->name('certificate-verify');

        Route::prefix('support')->name('support.')->group(function () {
            Route::livewire('/faq', 'pages.faq-page')->name('faq');
            Route::livewire('/contact', 'support.contact-channels-page')->name('contact');
            Route::livewire('/ticket/new', 'support.ticket-new-page')->name('ticket.new');
            Route::livewire('/ticket/search', 'support.ticket-search-page')->name('ticket.search');
            Route::livewire('/ticket/{ticket}', 'support.ticket-view-page')->name('ticket.view');

            Route::get('/chat/bootstrap', [\App\Http\Controllers\Support\SupportChatController::class, 'bootstrap'])
                ->name('chat.bootstrap');
            Route::post('/chat', [\App\Http\Controllers\Support\SupportChatController::class, 'chat'])
                ->middleware('throttle:20,1')
                ->name('chat');
            Route::post('/chat/feedback', [\App\Http\Controllers\Support\SupportChatController::class, 'feedback'])
                ->middleware('throttle:30,1')
                ->name('chat.feedback');
        });

        Route::middleware('portal.auth')->group(function () {
            Route::post('/logout', LogoutController::class)->name('logout');
            Route::livewire('/profile', 'student.profile-page')->name('profile');
            Route::livewire('/academic/curriculum-courses', 'student.academic-curriculum-courses-page')->name('academic.curriculum-courses');
            Route::middleware('installment.active')->group(function () {
                Route::livewire('/learning-list', 'student.learning-list-page')->name('learning-list');
                Route::livewire('/academic-curriculum', 'student.academic-curriculum-page')->name('academic-curriculum');
                Route::livewire('/academic-curriculum/courses', 'student.academic-curriculum-courses-page')->name('academic-curriculum.courses');
                Route::livewire('/academic-curriculum/{course}', 'student.academic-course-page')->name('academic-curriculum.course');
                Route::livewire('/learning/{enrollment}', 'student.learning-player-page')->name('learning.player');
                Route::get('/learning/{enrollment}/lessons/{lesson}/file', CatalogLessonFileController::class)
                    ->name('catalog.lesson-file');
                Route::livewire('/sessions', 'student.sessions-page')->name('sessions');
                Route::livewire('/sessions/{session}', 'student.session-view-page')->name('sessions.show');
                Route::livewire('/assignments', 'student.assignments-page')->name('assignments');
                Route::livewire('/assignments/{assignment}', 'student.assignment-submit-page')->name('assignments.show');
                Route::livewire('/exams', 'student.exams-page')->name('exams');
                Route::livewire('/exams/{exam}', 'student.exam-start-page')->name('exams.show');
                Route::livewire('/exam-attempts/{attempt}', 'student.exam-attempt-page')->name('exam-attempts.show');
                Route::livewire('/exam-attempts/{attempt}/review', 'student.exam-review-page')->name('exam-attempts.review');
            });
            Route::livewire('/settings', 'student.settings-page')->name('settings');
            Route::livewire('/notifications', 'student.notifications-page')->name('notifications');
            Route::livewire('/certificates', 'student.certificates-page')->name('certificates');
            Route::livewire('/certificates/{certificate}', 'student.certificate-view-page')->name('certificates.show');
            Route::get('/certificates/{certificate}/download', CertificateDownloadController::class)->name('certificates.download');
            Route::livewire('/statements', 'student.statements-page')->name('statements');
            Route::livewire('/statements/{statement}', 'student.statement-view-page')->name('statements.show');
            Route::livewire('/my-orders', 'student.orders-page')->name('my-orders');
            Route::livewire('/user-requests', 'student.user-requests-hub-page')->name('user-requests');
            Route::livewire('/user-requests/new/{type}', 'student.user-request-form-page')->name('user-requests.create');
            Route::livewire('/user-requests/academic/{academicRequest}', 'student.user-request-view-page')->name('user-requests.show');
            Route::livewire('/refunds', 'student.refunds-page')->name('refunds');
            Route::livewire('/academic-registration', 'auth.register-page')->name('academic-registration');
            Route::livewire('/installments', 'student.installments-page')->name('installments');
            Route::livewire('/installments/{contract}', 'student.installment-view-page')->name('installments.show');
            Route::livewire('/installments/{contract}/pay/{schedule}', 'student.installment-pay-page')->name('installments.pay');
            Route::get('/installments/{contract}/pay/{schedule}/callback', InstallmentCallbackController::class)->name('installments.pay.callback');
            Route::livewire('/my-orders/{order:reference}', 'student.order-view-page')->name('my-orders.show');
            Route::get('/sessions/{session}/join', SessionJoinController::class)->name('sessions.join');
            Route::get('/session-recordings/{recording}', SessionRecordingController::class)->name('sessions.recording');

            Route::prefix('instructor')->name('instructor.')->middleware('instructor')->group(function () {
                Route::livewire('/', 'instructor.dashboard-page')->name('dashboard');
                Route::livewire('/sections', 'instructor.sections-page')->name('sections');
                Route::livewire('/assignments', 'instructor.assignments-page')->name('assignments');
                Route::livewire('/attendance', 'instructor.attendance-page')->name('attendance');
                Route::livewire('/settings', 'instructor.settings-page')->name('settings');
                Route::livewire('/exams', 'instructor.exams-page')->name('exams');
                Route::livewire('/notifications', 'instructor.notifications-page')->name('notifications');
                Route::livewire('/sections/{section}/roster', 'instructor.roster-page')->name('sections.roster');
                Route::livewire('/sections/{section}/exams/create', 'instructor.exam-form-page')->name('exams.create');
                Route::livewire('/sections/{section}/exams/{exam}/edit', 'instructor.exam-form-page')->name('exams.edit');
                Route::livewire('/sections/{section}/exams/{exam}/builder', 'instructor.exam-builder-page')->name('exams.builder');
                Route::livewire('/sections/{section}/exams/{exam}/grading', 'instructor.exam-grading-page')->name('exams.grading');
                Route::get('/exam-answers/{answer}/file', InstructorExamAnswerFileController::class)->name('exam-answers.file');
                Route::livewire('/sections/{section}', 'instructor.section-page')->name('sections.show');
                Route::livewire('/sections/{section}/sessions/{session}', 'instructor.session-hub-page')->name('sessions.show');
                Route::get('/sessions/{session}/zoom/start', InstructorZoomStartController::class)->name('zoom.start');
            });
        });
    });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/login', 'admin.login-page')->name('login');

    Route::middleware(['auth', 'admin', 'admin.permission'])->group(function () {
        Route::post('/logout', AdminLogoutController::class)->name('logout');

        Route::livewire('/', 'admin.dashboard-page')->name('dashboard');
        Route::livewire('/reports', 'admin.reports-page')->name('reports');
        Route::livewire('/platform-analytics', 'admin.platform-analytics-page')->name('platform-analytics');
        Route::livewire('/crm', 'admin.crm-page')->name('crm');
        Route::livewire('/crm/import', 'admin.crm-import-page')->name('crm.import');
        Route::livewire('/crm/rules', 'admin.crm-rules-page')->name('crm.rules');
        Route::livewire('/crm/settings', 'admin.crm-settings-page')->name('crm.settings');
        Route::livewire('/crm/audit', 'admin.crm-audit-page')->name('crm.audit');
        Route::livewire('/crm/contacts/{contact}', 'admin.crm-contact-page')->name('crm.contacts.show');
        Route::livewire('/financial', 'admin.financial-page')->name('financial');
        Route::livewire('/enrollment', 'admin.enrollment-page')->name('enrollment');
        Route::livewire('/graduates', 'admin.graduates-page')->name('graduates');
        Route::livewire('/staff', 'admin.staff-page')->name('staff');
        Route::livewire('/staff/members', 'admin.staff-members-page')->name('staff.members');
        Route::livewire('/staff/members/create', 'admin.staff-form-page')->name('staff.create');
        Route::livewire('/staff/members/{staff}/edit', 'admin.staff-form-page')->name('staff.edit');
        Route::livewire('/staff/members/{staff}', 'admin.staff-view-page')->name('staff.show');
        Route::post('/staff/members/{staff}/impersonate', [InstructorImpersonationController::class, 'start'])
            ->name('staff.impersonate');
        Route::post('/impersonate/instructor/stop', [InstructorImpersonationController::class, 'stop'])
            ->name('staff.impersonate.stop');
        Route::livewire('/orders', 'admin.orders-page')->name('orders');
        Route::livewire('/orders/{order}', 'admin.order-view-page')->name('orders.show');
        Route::livewire('/settings', 'admin.settings-page')->name('settings');
        Route::livewire('/system-settings', 'admin.system-settings-hub-page')->name('system-settings');
        Route::livewire('/system-settings/{section}', 'admin.system-settings-section-page')->name('system-settings.section');
        Route::livewire('/payment-settings', 'admin.payment-settings-page')->name('payment-settings');
        Route::livewire('/teams-settings', 'admin.teams-settings-page')->name('teams-settings');
        Route::livewire('/zoom-settings', 'admin.zoom-settings-page')->name('zoom-settings');
        Route::livewire('/zoxagent-settings', 'admin.zoxagent-settings-page')->name('zoxagent-settings');
        Route::livewire('/notifications', 'admin.notifications-page')->name('notifications');
        Route::livewire('/notification-rules', 'admin.notification-rules-page')->name('notification-rules');
        Route::livewire('/audit-log', 'admin.audit-log-page')->name('audit-log');
        Route::livewire('/refunds', 'admin.refunds-page')->name('refunds');
        Route::livewire('/certificates', 'admin.certificates-page')->name('certificates');
        Route::get('/certificates/{certificate}/download', [CertificateDownloadController::class, 'admin'])->name('certificates.download');
        Route::livewire('/certificate-templates', 'admin.certificate-templates-page')->name('certificate-templates');
        Route::livewire('/certificate-templates/{template}/builder', 'admin.certificate-template-builder-page')->name('certificate-templates.builder');
        Route::livewire('/statements', 'admin.statements-page')->name('statements');
        Route::livewire('/sessions', 'admin.sessions-page')->name('sessions');
        Route::get('/sessions/{session}/zoxagent/join', SessionJoinController::class)->name('sessions.zoxagent.join');
        Route::livewire('/assignments', 'admin.assignments-page')->name('assignments');
        Route::livewire('/assignments/create', 'admin.assignment-form-page')->name('assignments.create');
        Route::livewire('/assignments/{assignment}/edit', 'admin.assignment-form-page')->name('assignments.edit');
        Route::livewire('/assignments/{assignment}', 'admin.assignment-view-page')->name('assignments.show');
        Route::livewire('/exams', 'admin.exams-page')->name('exams');
        Route::livewire('/exams/create', 'admin.exam-form-page')->name('exams.create');
        Route::livewire('/exams/{exam}/edit', 'admin.exam-form-page')->name('exams.edit');
        Route::livewire('/exams/{exam}/builder', 'admin.exam-builder-page')->name('exams.builder');
        Route::livewire('/exams/{exam}/preview', 'admin.exam-preview-page')->name('exams.preview');
        Route::livewire('/exams/{exam}/integrity', 'admin.exam-integrity-page')->name('exams.integrity');
        Route::livewire('/users', 'admin.users-page')->name('users');
        Route::livewire('/users/permissions', 'admin.role-permissions-page')->name('users.permissions');
        Route::livewire('/users/create', 'admin.user-form-page')->name('users.create');
        Route::livewire('/users/{user}/access', 'admin.user-access-page')->name('users.access');
        Route::livewire('/users/{user}/edit', 'admin.user-form-page')->name('users.edit');
        Route::livewire('/users/{user}', 'admin.user-view-page')->name('users.show');
        Route::livewire('/catalog-courses', 'admin.catalog-courses-page')->name('catalog-courses');
        Route::livewire('/catalog-courses/create', 'admin.catalog-course-form-page')->name('catalog-courses.create');
        Route::livewire('/catalog-courses/{course:id}/edit', 'admin.catalog-course-form-page')->name('catalog-courses.edit');
        Route::livewire('/catalog-courses/{course:id}/content', 'admin.catalog-course-content-page')->name('catalog-courses.content');
        Route::get('/catalog-courses/{course:id}/lessons/{lesson}/file', App\Http\Controllers\Admin\CatalogLessonFileController::class)
            ->name('catalog-courses.lesson-file');
        Route::get('/catalog-courses/{course:id}/modules/{module}/image', CatalogModuleImageController::class)
            ->name('catalog-courses.module-image');
        Route::livewire('/support-tickets', 'admin.support-tickets-page')->name('support-tickets');
        Route::livewire('/support-tickets/{ticket}', 'admin.support-ticket-view-page')->name('support-tickets.show');

        Route::livewire('/programs', 'admin.programs-page')->name('programs');
        Route::livewire('/programs/create', 'admin.program-form-page')->name('programs.create');
        Route::livewire('/programs/{program}/edit', 'admin.program-form-page')->name('programs.edit');
        Route::livewire('/programs/{program}', 'admin.program-view-page')->name('programs.show');
        Route::livewire('/batches', 'admin.batches-page')->name('batches');
        Route::livewire('/batches/create', 'admin.batch-form-page')->name('batches.create');
        Route::livewire('/batches/{batch}/edit', 'admin.batch-form-page')->name('batches.edit');
        Route::livewire('/batches/{batch}', 'admin.batch-view-page')->name('batches.show');
        Route::livewire('/students', 'admin.students-page')->name('students');
        Route::livewire('/students/create', 'admin.student-form-page')->name('students.create');
        Route::livewire('/students/{student}/edit', 'admin.student-form-page')->name('students.edit');
        Route::livewire('/students/{student}', 'admin.student-view-page')->name('students.show');
        Route::post('/students/{student}/impersonate', [StudentImpersonationController::class, 'start'])
            ->name('students.impersonate');
        Route::post('/impersonate/stop', [StudentImpersonationController::class, 'stop'])
            ->name('students.impersonate.stop');

        Route::livewire('/sections', 'admin.sections-page')->name('sections');
        Route::livewire('/sections/create', 'admin.section-form-page')->name('sections.create');
        Route::livewire('/sections/{section}/edit', 'admin.section-form-page')->name('sections.edit');
        Route::livewire('/sections/{section}', 'admin.section-view-page')->name('sections.show');
        Route::livewire('/schedules', 'admin.schedules-page')->name('schedules');
        Route::livewire('/levels', 'admin.levels-page')->name('levels');
        Route::livewire('/levels/create', 'admin.level-form-page')->name('levels.create');
        Route::livewire('/levels/{level}/edit', 'admin.level-form-page')->name('levels.edit');
        Route::livewire('/academic-courses', 'admin.academic-courses-page')->name('academic-courses');
        Route::livewire('/academic-courses/create', 'admin.academic-course-form-page')->name('academic-courses.create');
        Route::livewire('/academic-courses/{course}/edit', 'admin.academic-course-form-page')->name('academic-courses.edit');
        Route::livewire('/academic-courses/{course}', 'admin.academic-course-view-page')->name('academic-courses.show');

        Route::livewire('/requests/deferral', 'admin.academic-requests-page')->name('requests.deferral');
        Route::livewire('/requests/withdrawal', 'admin.academic-requests-page')->name('requests.withdrawal');
        Route::livewire('/requests/program-change', 'admin.academic-requests-page')->name('requests.program-change');
        Route::livewire('/requests/semester-excuse', 'admin.academic-requests-page')->name('requests.semester-excuse');
        Route::livewire('/requests/view/{academicRequest}', 'admin.academic-request-view-page')->name('requests.show');

        Route::livewire('/installment-plans', 'admin.installment-plans-page')->name('installment-plans');
        Route::livewire('/installment-plans/create', 'admin.installment-plan-form-page')->name('installment-plans.create');
        Route::livewire('/installment-plans/{plan}/edit', 'admin.installment-plan-form-page')->name('installment-plans.edit');
        Route::livewire('/installment-contracts', 'admin.installment-contracts-page')->name('installment-contracts');
        Route::livewire('/installment-contracts/{contract}', 'admin.installment-contract-view-page')->name('installment-contracts.show');
        Route::livewire('/installment-reports', 'admin.installment-reports-page')->name('installment-reports');
        Route::livewire('/installment-settings', 'admin.installment-settings-page')->name('installment-settings');
        Route::livewire('/installment-dunning', 'admin.installment-dunning-page')->name('installment-dunning');
        Route::livewire('/cms-pages', 'admin.cms-pages-page')->name('cms-pages');
        Route::livewire('/cms-pages/create', 'admin.cms-page-form-page')->name('cms-pages.create');
        Route::livewire('/cms-pages/{page}/edit', 'admin.cms-page-form-page')->name('cms-pages.edit');
        Route::livewire('/cms-pages/{page}/preview', 'admin.cms-page-preview-page')->name('cms-pages.preview');
        Route::livewire('/cms-menus', 'admin.cms-menus-page')->name('cms-menus');
        Route::livewire('/media-library', 'admin.media-library-page')->name('media-library');
        Route::livewire('/articles', 'admin.articles-page')->name('articles');
        Route::livewire('/articles/create', 'admin.article-form-page')->name('articles.create');
        Route::livewire('/articles/{article}/edit', 'admin.article-form-page')->name('articles.edit');
        Route::post('/articles/media', ArticleMediaController::class)->name('articles.media');
        Route::livewire('/article-categories', 'admin.article-categories-page')->name('article-categories');

        Route::livewire('/applications/client', 'admin.registration-applications-page')->name('applications.client');
        Route::livewire('/applications/company', 'admin.registration-applications-page')->name('applications.company');
        Route::livewire('/applications/marketer', 'admin.registration-applications-page')->name('applications.marketer');
        Route::livewire('/applications/instructor', 'admin.registration-applications-page')->name('applications.instructor');
        Route::livewire('/applications/employee', 'admin.registration-applications-page')->name('applications.employee');
        Route::livewire('/applications/job-seeker', 'admin.registration-applications-page')->name('applications.job-seeker');
        Route::livewire('/applications/cooperative', 'admin.registration-applications-page')->name('applications.cooperative');
        Route::livewire('/applications/fellowship', 'admin.registration-applications-page')->name('applications.fellowship');
        Route::livewire('/fellowships', 'admin.fellowships-page')->name('fellowships');
        Route::livewire('/fellowships/{fellowship}/edit', 'admin.fellowship-form-page')->name('fellowships.edit');
        Route::livewire('/fellowships/{fellowship}/form-fields', 'admin.fellowship-form-fields-page')->name('fellowships.form-fields');
        Route::livewire('/fellowships/{fellowship}/settings', 'admin.fellowship-settings-page')->name('fellowships.settings');
        Route::livewire('/applications/view/{application}', 'admin.registration-application-view-page')->name('applications.show');
        Route::get('/applications/view/{application}/attachment/{key}', RegistrationApplicationAttachmentController::class)
            ->name('applications.attachment');
    });
});
