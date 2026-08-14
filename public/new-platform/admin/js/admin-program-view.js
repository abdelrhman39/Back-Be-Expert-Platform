/**
 * صفحة عرض البرنامج — تبويبات التفاصيل / المستويات / المقررات
 */
(function () {
    var TAB_LABELS = { details: 'التفاصيل', levels: 'المستويات', courses: 'المقررات' };

    var program = null;
    var activeTab = 'details';
    var coursesPage = 1;
    var coursesPageSize = 10;

    var loading = document.getElementById('program-view-loading');
    var missing = document.getElementById('program-view-missing');
    var content = document.getElementById('program-view-content');
    var breadcrumb = document.getElementById('admin-breadcrumb');
    var detailsRoot = document.getElementById('program-details-root');
    var levelsTbody = document.getElementById('levels-tbody');
    var coursesTbody = document.getElementById('courses-tbody');
    var coursesPagination = document.getElementById('courses-pagination');
    var pageSizeSelect = document.getElementById('courses-page-size');

    function qs() {
        var p = new URLSearchParams(window.location.search);
        return { code: p.get('code'), id: p.get('id'), tab: p.get('tab') || 'details' };
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function statusBadge(status) {
        if (status === 'active') {
            return '<span class="admin-badge admin-badge--success">فعال</span>';
        }
        return '<span class="admin-badge admin-badge--danger">غير فعال</span>';
    }

    function formatDate(iso) {
        try {
            return new Intl.DateTimeFormat('ar-SA', { year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date(iso));
        } catch (e) {
            return iso;
        }
    }

    function renderBreadcrumb() {
        if (!breadcrumb || !program) return;
        var tabLabel = TAB_LABELS[activeTab] || activeTab;
        breadcrumb.innerHTML =
            '<a href="index.html">الرئيسية</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<a href="programs.html">البرامج الدراسية</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<a href="program-view.html?code=' + encodeURIComponent(program.code) + '">' + program.id + '</a>' +
            '<span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<span class="admin-breadcrumb__current" aria-current="page">' + tabLabel + '</span>';
        document.title = tabLabel + ' — ' + program.name + ' | لوحة تحكم مركز التعلم المستمر';
    }

    var ICONS = {
        book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>',
        user: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        hash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18"/></svg>',
        tag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
        clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        calendar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
        flag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>',
        layers: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
        cert: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>',
        mail: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16v12H4V6z"/><path d="m4 7 8 6 8-6"/></svg>',
        phone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>',
        pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        chart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 16l4-4 4 4 6-8"/></svg>',
        link: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>',
        image: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="8.5" cy="10.5" r="1.5"/><path d="M21 15l-5-5L8 19"/></svg>',
        file: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>',
        skill: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>',
        brief: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>',
    };

    function sectionTitle(iconSvg, text) {
        return (
            '<h3 class="admin-detail-section__title">' +
            '<span class="admin-detail-section__title-icon" aria-hidden="true">' + iconSvg + '</span>' +
            text +
            '</h3>'
        );
    }

    function detailField(iconSvg, label, value, tone) {
        var toneClass = tone ? ' admin-detail-field__icon--' + tone : '';
        return (
            '<div class="admin-detail-field">' +
            '<span class="admin-detail-field__icon' + toneClass + '" aria-hidden="true">' + iconSvg + '</span>' +
            '<div class="admin-detail-field__body">' +
            '<span class="admin-detail-field__label">' + label + '</span>' +
            '<span class="admin-detail-field__value">' + value + '</span></div></div>'
        );
    }

    function renderDetails() {
        if (!detailsRoot || !program) return;

        var attachmentsHtml = program.attachments.length
            ? program.attachments
                  .map(function (f) {
                      return (
                          '<li class="admin-attach-item">' +
                          '<span class="admin-attach-item__icon" aria-hidden="true">' + ICONS.file + '</span>' +
                          '<span>' + escapeHtml(f.name) + '</span></li>'
                      );
                  })
                  .join('')
            : '<li class="admin-detail-empty">لا توجد مرفقات</li>';

        var skillsHtml = program.skills
            .map(function (s) {
                return (
                    '<li class="admin-detail-list__item">' +
                    '<span class="admin-detail-list__icon" aria-hidden="true">' + ICONS.skill + '</span>' +
                    escapeHtml(s) +
                    '</li>'
                );
            })
            .join('');

        detailsRoot.innerHTML =
            '<section class="admin-detail-section">' +
            sectionTitle(ICONS.book, 'المعلومات الأساسية') +
            '<div class="admin-detail-fields admin-detail-fields--3">' +
            detailField(ICONS.book, 'اسم البرنامج', escapeHtml(program.name)) +
            detailField(ICONS.hash, 'كود البرنامج', '<code class="admin-code">' + escapeHtml(program.code) + '</code>') +
            detailField(ICONS.cert, 'اسم البرنامج في الإفادة', escapeHtml(program.cert)) +
            detailField(ICONS.tag, 'رمز البرنامج', escapeHtml(program.symbol)) +
            detailField(ICONS.clock, 'مدة البرنامج', '<span class="admin-link">' + escapeHtml(program.duration) + '</span>') +
            detailField(ICONS.calendar, 'تاريخ بدء البرنامج', formatDate(program.start)) +
            detailField(ICONS.flag, 'حالة البرنامج', statusBadge(program.status), 'success') +
            detailField(ICONS.layers, 'نوع البرنامج', escapeHtml(program.typeLabel)) +
            '</div></section>' +

            '<section class="admin-detail-section">' +
            sectionTitle(ICONS.brief, 'المؤهل العلمي والخبرات') +
            '<div class="admin-detail-fields admin-detail-fields--2">' +
            detailField(ICONS.user, 'منسق البرنامج', escapeHtml(program.coordinator)) +
            detailField(ICONS.mail, 'البريد الإلكتروني', '<a href="mailto:' + escapeHtml(program.email) + '" class="admin-link">' + escapeHtml(program.email) + '</a>') +
            detailField(ICONS.phone, 'رقم التواصل', escapeHtml(program.phone)) +
            detailField(ICONS.pin, 'المدينة', escapeHtml(program.city)) +
            '</div></section>' +

            '<section class="admin-detail-section admin-detail-section--wide">' +
            sectionTitle(ICONS.chart, 'عن البرنامج والمهارات') +
            '<p class="admin-detail-text">' + escapeHtml(program.summary) + '</p>' +
            '<ul class="admin-detail-list">' + skillsHtml + '</ul></section>' +

            '<section class="admin-detail-section">' +
            sectionTitle(ICONS.layers, 'الحالة الدراسية') +
            '<div class="admin-detail-fields admin-detail-fields--1">' +
            detailField(ICONS.chart, 'الوضع الحالي', escapeHtml(program.studyStatus), 'info') +
            '</div></section>' +

            '<section class="admin-detail-section">' +
            sectionTitle(ICONS.image, 'الوسائط والروابط') +
            '<div class="admin-media-block">' +
            '<div class="admin-media-block__thumb" aria-hidden="true">' + ICONS.image + '</div>' +
            '<div class="admin-media-block__body">' +
            detailField(ICONS.link, 'رابط البرنامج التعريفي', '<a href="' + escapeHtml(program.mediaUrl) + '" class="admin-link" target="_blank" rel="noopener">فتح الرابط</a>') +
            '</div></div></section>' +

            '<section class="admin-detail-section">' +
            sectionTitle(ICONS.file, 'المرفقات') +
            '<ul class="admin-attach-list">' + attachmentsHtml + '</ul></section>';
    }

    function renderLevels() {
        if (!levelsTbody || !window.domainPrograms) return;
        var levels = window.domainPrograms.getLevels();
        levelsTbody.innerHTML = levels
            .map(function (name, i) {
                return '<tr><td data-label="#">' + (i + 1) + '</td><td data-label="اسم المستوى">' + escapeHtml(name) + '</td></tr>';
            })
            .join('');
    }

    function renderCourses() {
        if (!coursesTbody || !window.domainPrograms) return;
        var all = window.domainPrograms.getCourses();
        var totalPages = Math.max(1, Math.ceil(all.length / coursesPageSize));
        if (coursesPage > totalPages) coursesPage = totalPages;

        var start = (coursesPage - 1) * coursesPageSize;
        var slice = all.slice(start, start + coursesPageSize);

        var programCode = program ? program.code : 'PMP-202';

        coursesTbody.innerHTML = slice
            .map(function (c, i) {
                var n = start + i + 1;
                var viewUrl =
                    'course-view.html?code=' +
                    encodeURIComponent(c.code) +
                    '&program=' +
                    encodeURIComponent(programCode);
                return (
                    '<tr>' +
                    '<td data-label="#">' + n + '</td>' +
                    '<td data-label="اسم المقرر">' + escapeHtml(c.name) + '</td>' +
                    '<td data-label="رمز المقرر">' + escapeHtml(c.symbol) + '</td>' +
                    '<td data-label="كود المقرر"><code class="admin-code">' + escapeHtml(c.code) + '</code></td>' +
                    '<td data-label="المستوى">' + escapeHtml(c.level) + '</td>' +
                    '<td data-label="الحالة">' + (c.status ? statusBadge(c.status) : '—') + '</td>' +
                    '<td class="admin-table-actions" data-label="إجراءات">' +
                    '<a href="' + viewUrl + '" class="admin-view-eye" aria-label="عرض المقرر ' + escapeHtml(c.name) + '">' +
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
                    '</a></td></tr>'
                );
            })
            .join('');

        if (!coursesPagination) return;
        var parts = [];
        parts.push('<span class="admin-pagination__info">الصفحة ' + coursesPage + ' من ' + totalPages + '</span>');
        parts.push('<div class="admin-pagination__btns">');
        parts.push('<button type="button" class="admin-pagination__btn" data-cpage="prev"' + (coursesPage <= 1 ? ' disabled' : '') + '>السابق</button>');
        for (var p = 1; p <= totalPages; p++) {
            parts.push(
                '<button type="button" class="admin-pagination__btn' + (p === coursesPage ? ' is-active' : '') + '" data-cpage="' + p + '">' + p + '</button>'
            );
        }
        parts.push('<button type="button" class="admin-pagination__btn" data-cpage="next"' + (coursesPage >= totalPages ? ' disabled' : '') + '>التالي</button>');
        parts.push('</div>');
        coursesPagination.innerHTML = parts.join('');

        coursesPagination.querySelectorAll('[data-cpage]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = btn.getAttribute('data-cpage');
                if (v === 'prev' && coursesPage > 1) coursesPage--;
                else if (v === 'next' && coursesPage < totalPages) coursesPage++;
                else if (v !== 'prev' && v !== 'next') coursesPage = parseInt(v, 10);
                renderCourses();
            });
        });
    }

    function setTab(tab) {
        activeTab = tab in TAB_LABELS ? tab : 'details';

        document.querySelectorAll('.admin-view-tab').forEach(function (btn) {
            var on = btn.getAttribute('data-tab') === activeTab;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });

        document.querySelectorAll('.admin-view-panel').forEach(function (panel) {
            var show = panel.id === 'panel-' + activeTab;
            panel.classList.toggle('is-active', show);
            panel.hidden = !show;
        });

        if (activeTab === 'levels') renderLevels();
        if (activeTab === 'courses') renderCourses();

        renderBreadcrumb();

        var url = new URL(window.location.href);
        url.searchParams.set('tab', activeTab);
        if (program) {
            url.searchParams.set('code', program.code);
            url.searchParams.delete('id');
        }
        history.replaceState(null, '', url.toString());
    }

    function bindTabs() {
        document.querySelectorAll('.admin-view-tab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setTab(btn.getAttribute('data-tab'));
            });
        });
    }

    function init() {
        var params = qs();
        activeTab = params.tab in TAB_LABELS ? params.tab : 'details';

        if (!window.domainPrograms) {
            showMissing();
            return;
        }

        program = window.domainPrograms.resolve(params);
        if (!program) {
            showMissing();
            return;
        }

        if (loading) loading.hidden = true;
        if (missing) missing.hidden = true;
        if (content) content.hidden = false;

        renderDetails();
        renderBreadcrumb();
        bindTabs();
        setTab(activeTab);

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function () {
                coursesPageSize = parseInt(pageSizeSelect.value, 10) || 10;
                coursesPage = 1;
                if (activeTab === 'courses') renderCourses();
            });
        }
    }

    function showMissing() {
        if (loading) loading.hidden = true;
        if (content) content.hidden = true;
        if (missing) missing.hidden = false;
    }

    init();
})();
