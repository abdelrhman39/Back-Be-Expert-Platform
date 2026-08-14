/**
 * صفحة عرض تفاصيل الشعبة الدراسية
 */
(function () {
    var loading = document.getElementById('section-view-loading');
    var missing = document.getElementById('section-view-missing');
    var content = document.getElementById('section-view-content');
    var detailsRoot = document.getElementById('section-details-root');
    var systemRoot = document.getElementById('section-system-root');
    var breadcrumb = document.getElementById('admin-breadcrumb');

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
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>' +
                'مفعل</span>'
            );
        }
        return '<span class="admin-badge admin-badge--muted">غير مفعل</span>';
    }

    var ICONS = {
        bookmark:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>',
        barcode:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M7 5v14M11 5v6M15 5v14M19 5v10"/></svg>',
        users:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
        book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>',
        package:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><path d="M3.27 6.96L12 12.01l8.73-5.05M12 22.08V12"/></svg>',
        calendar:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
        userRed:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        userPink:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        clock:
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        user: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    };

    function sectionTitle(iconSvg, text) {
        return (
            '<h3 class="admin-detail-section__title">' +
            '<span class="admin-detail-section__title-icon" aria-hidden="true">' +
            iconSvg +
            '</span>' +
            text +
            '</h3>'
        );
    }

    function detailField(iconSvg, label, value, tone) {
        var toneClass = tone ? ' admin-detail-field__icon--' + tone : '';
        return (
            '<div class="admin-detail-field">' +
            '<span class="admin-detail-field__icon' +
            toneClass +
            '" aria-hidden="true">' +
            iconSvg +
            '</span>' +
            '<div class="admin-detail-field__body">' +
            '<span class="admin-detail-field__label">' +
            label +
            '</span>' +
            '<span class="admin-detail-field__value">' +
            value +
            '</span></div></div>'
        );
    }

    function renderBreadcrumb(section) {
        if (!breadcrumb) return;
        breadcrumb.innerHTML =
            '<a href="index.html">الرئيسية</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<a href="sections.html">الشعب الدراسية</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<span class="admin-breadcrumb__current" aria-current="page">' +
            escapeHtml(String(section.id)) +
            '</span>';
        document.title = section.name + ' | لوحة تحكم مركز التعلم المستمر';
    }

    function render(section) {
        renderBreadcrumb(section);

        detailsRoot.innerHTML =
            '<section class="admin-detail-section">' +
            sectionTitle(ICONS.bookmark, 'المعلومات الأساسية') +
            '<div class="admin-detail-fields admin-detail-fields--2">' +
            detailField(ICONS.bookmark, 'اسم الشعبة', escapeHtml(section.name)) +
            detailField(
                ICONS.barcode,
                'كود الشعبة',
                '<code class="admin-code">' + escapeHtml(section.code) + '</code>'
            ) +
            detailField(
                ICONS.users,
                'الحد الأقصى للشعبة',
                escapeHtml(section.maxCapacity + ' طالب')
            ) +
            '</div></section>' +

            '<section class="admin-detail-section admin-detail-section--wide">' +
            sectionTitle(ICONS.book, 'المعلومات الأكاديمية') +
            '<div class="admin-detail-fields admin-detail-fields--2">' +
            detailField(ICONS.book, 'المقرر', escapeHtml(section.courseName), 'warn') +
            detailField(
                ICONS.package,
                'الدفعة / البرنامج',
                '<span class="admin-detail-field__multiline">' + escapeHtml(section.batchFullLabel) + '</span>',
                'success'
            ) +
            detailField(ICONS.calendar, 'الفصل الدراسي', escapeHtml(section.semesterLabel), 'info') +
            detailField(ICONS.calendar, 'فترة التدريس', escapeHtml(section.periodLabel)) +
            detailField(ICONS.book, 'المستوى', escapeHtml(section.levelLabel)) +
            '</div></section>' +

            '<section class="admin-detail-section">' +
            sectionTitle(ICONS.userRed, 'طاقم التدريس') +
            '<div class="admin-detail-fields admin-detail-fields--2">' +
            detailField(ICONS.userRed, 'مشرف الشعبة', escapeHtml(section.supervisor || '—')) +
            detailField(ICONS.userPink, 'عضو هيئة التدريس', escapeHtml(section.facultyName || '—')) +
            '</div></section>';

        systemRoot.innerHTML =
            '<section class="admin-course-block admin-course-block--system">' +
            '<h2 class="admin-course-block__title">معلومات النظام</h2>' +
            '<div class="admin-system-grid">' +
            '<div class="admin-system-item">' +
            '<span class="admin-system-item__label">حالة الشعبة</span>' +
            '<span class="admin-system-item__value">' +
            statusBadge(section.status) +
            '</span></div>' +
            '<div class="admin-system-item">' +
            '<span class="admin-system-item__label"><span class="admin-system-item__ico" aria-hidden="true">' +
            ICONS.user +
            '</span>أضيف بواسطة</span>' +
            '<span class="admin-system-item__value">' +
            escapeHtml(section.addedBy) +
            '</span></div>' +
            '<div class="admin-system-item">' +
            '<span class="admin-system-item__label"><span class="admin-system-item__ico" aria-hidden="true">' +
            ICONS.clock +
            '</span>تاريخ الإضافة</span>' +
            '<span class="admin-system-item__value">' +
            '<span class="admin-system-item__datetime">' +
            escapeHtml(section.addedAt) +
            '</span>' +
            '<span class="admin-system-item__ago">' +
            escapeHtml(section.addedAgo) +
            '</span></span></div>' +
            '</div></section>';
    }

    function showMissing() {
        if (loading) loading.hidden = true;
        if (content) content.hidden = true;
        if (missing) missing.hidden = false;
    }

    function init() {
        var params = new URLSearchParams(window.location.search);

        if (!window.domainSections) {
            showMissing();
            return;
        }

        var section = window.domainSections.resolve({
            code: params.get('code'),
            id: params.get('id'),
        });

        if (!section) {
            showMissing();
            return;
        }

        if (loading) loading.hidden = true;
        if (missing) missing.hidden = true;
        if (content) content.hidden = false;

        render(section);
    }

    init();
})();
