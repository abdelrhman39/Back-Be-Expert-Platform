/**
 * صفحة تفاصيل المقرر — مطابقة واجهة العرض المرجعية
 */
(function () {
    var loading = document.getElementById('course-view-loading');
    var missing = document.getElementById('course-view-missing');
    var content = document.getElementById('course-view-content');
    var root = document.getElementById('course-details-root');
    var breadcrumb = document.getElementById('admin-breadcrumb');
    var backLink = document.getElementById('course-back-link');
    var pageTitle = document.getElementById('course-page-title');

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function statusBadge(status) {
        if (status === 'active') {
            return (
                '<span class="admin-badge admin-badge--success admin-badge--with-icon">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>' +
                'فعال</span>'
            );
        }
        return '<span class="admin-badge admin-badge--danger">غير فعال</span>';
    }

    function infoCard(icon, label, value, extraClass) {
        return (
            '<div class="admin-info-card' + (extraClass ? ' ' + extraClass : '') + '">' +
            '<div class="admin-info-card__icon" aria-hidden="true">' + icon + '</div>' +
            '<div class="admin-info-card__body">' +
            '<span class="admin-info-card__label">' + label + '</span>' +
            '<span class="admin-info-card__value">' + value + '</span>' +
            '</div></div>'
        );
    }

    var ICONS = {
        globe: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg>',
        hash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18"/></svg>',
        clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>',
        layers: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
        users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
        image: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L8 19"/></svg>',
        user: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    };

    function renderBreadcrumb(course, programCode) {
        if (!breadcrumb) return;
        var coursesUrl = 'program-view.html?code=' + encodeURIComponent(programCode) + '&tab=courses';

        breadcrumb.innerHTML =
            '<a href="index.html">الرئيسية</a><span class="admin-breadcrumb__sep">›</span>' +
            '<a href="' + coursesUrl + '">المقررات الدراسية</a>' +
            '<span class="admin-breadcrumb__sep">›</span>' +
            '<span class="admin-breadcrumb__current" aria-current="page">' + course.id + '</span>';
    }

    function render(course, programCode) {
        if (pageTitle) pageTitle.textContent = course.nameAr || course.name;

        if (backLink) {
            backLink.href = 'program-view.html?code=' + encodeURIComponent(programCode) + '&tab=courses';
        }

        renderBreadcrumb(course, programCode);
        document.title = (course.nameAr || course.name) + ' | لوحة تحكم مركز التعلم المستمر';

        root.innerHTML =
            '<section class="admin-course-block">' +
            '<h2 class="admin-course-block__title">' +
            '<span class="admin-course-block__title-icon">' + ICONS.book + '</span>المعلومات الأساسية</h2>' +
            '<div class="admin-info-grid admin-info-grid--3">' +
            infoCard(ICONS.globe, 'اسم المقرر بالعربية', escapeHtml(course.nameAr)) +
            infoCard(ICONS.globe, 'اسم المقرر بالإنجليزية', escapeHtml(course.nameEn)) +
            infoCard(ICONS.hash, 'رمز المقرر بالعربية', escapeHtml(course.symbolAr)) +
            infoCard(ICONS.hash, 'رمز المقرر بالإنجليزية', escapeHtml(course.symbolEn)) +
            infoCard(ICONS.hash, 'كود المقرر', '<code class="admin-code">' + escapeHtml(course.code) + '</code>') +
            infoCard(ICONS.clock, 'عدد الساعات', escapeHtml(String(course.hours))) +
            '</div></section>' +

            '<section class="admin-course-block">' +
            '<h2 class="admin-course-block__title">' +
            '<span class="admin-course-block__title-icon">' + ICONS.layers + '</span>معلومات البرنامج</h2>' +
            '<div class="admin-info-grid admin-info-grid--3">' +
            infoCard(
                ICONS.book,
                'البرنامج',
                escapeHtml(course.programName) +
                    ' <span class="admin-tag admin-tag--warn">' + escapeHtml(course.programDuration) + '</span>',
                'admin-info-card--wide'
            ) +
            infoCard(ICONS.layers, 'المستوى الدراسي', escapeHtml(course.level)) +
            infoCard(ICONS.users, 'الفئة المستهدفة', escapeHtml(course.targetGroup)) +
            '</div></section>' +

            '<section class="admin-course-block">' +
            '<h2 class="admin-course-block__title">' +
            '<span class="admin-course-block__title-icon">' + ICONS.image + '</span>صورة المقرر</h2>' +
            '<div class="admin-course-image-placeholder">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L8 19"/></svg>' +
            '<p>لم يتم رفع صورة للمقرر بعد</p></div></section>' +

            '<section class="admin-course-block admin-course-block--system">' +
            '<h2 class="admin-course-block__title">معلومات النظام</h2>' +
            '<div class="admin-system-grid">' +
            '<div class="admin-system-item">' +
            '<span class="admin-system-item__label">حالة المقرر</span>' +
            '<span class="admin-system-item__value">' + statusBadge(course.status) + '</span></div>' +
            '<div class="admin-system-item">' +
            '<span class="admin-system-item__label"><span class="admin-system-item__ico">' + ICONS.user + '</span>أضيف بواسطة</span>' +
            '<span class="admin-system-item__value">' + escapeHtml(course.addedBy) + '</span></div>' +
            '<div class="admin-system-item">' +
            '<span class="admin-system-item__label"><span class="admin-system-item__ico">' + ICONS.clock + '</span>تاريخ الإضافة</span>' +
            '<span class="admin-system-item__value">' +
            '<span class="admin-system-item__datetime">' + escapeHtml(course.addedAt) + '</span>' +
            '<span class="admin-system-item__ago">' + escapeHtml(course.addedAgo) + '</span></span></div>' +
            '</div></section>';
    }

    function init() {
        var params = new URLSearchParams(window.location.search);
        var programCode = params.get('program') || 'PMP-202';

        if (!window.domainPrograms) {
            showMissing();
            return;
        }

        var course = window.domainPrograms.resolveCourse({
            code: params.get('code'),
            id: params.get('id'),
        });

        if (!course) {
            showMissing();
            return;
        }

        if (loading) loading.hidden = true;
        if (missing) missing.hidden = true;
        if (content) content.hidden = false;

        render(course, programCode);
    }

    function showMissing() {
        if (loading) loading.hidden = true;
        if (content) content.hidden = true;
        if (missing) missing.hidden = false;
    }

    init();
})();
