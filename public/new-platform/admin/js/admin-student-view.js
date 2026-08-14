/**
 * ملف الطالب — عرض كامل العرض
 */
(function () {
    var loading = document.getElementById('student-view-loading');
    var missing = document.getElementById('student-view-missing');
    var content = document.getElementById('student-view-content');
    var root = document.getElementById('student-profile-root');
    var breadcrumb = document.getElementById('admin-breadcrumb');

    var TAB_LABELS = [
        'بيانات المستخدم',
        'الإحصائيات',
        'المقررات والدبلومات',
        'المدفوعات',
        'الأقساط',
        'الاختبارات',
        'مسؤوليات التواصل',
        'حضور الطلاب',
        'الغيابات',
        'الحالات',
    ];

    var ICONS = {
        user: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        globe: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20"/></svg>',
        hash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 9h16M4 15h16M10 3L8 21M16 3l-2 18"/></svg>',
        id: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h4M7 13h6"/></svg>',
        phone: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>',
        mail: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16v12H4V6z"/><path d="m4 7 8 6 8-6"/></svg>',
        gender: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M12 14v7M9 18h6"/></svg>',
        ring: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><path d="M8 12h8"/></svg>',
        pin: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s6-5.33 6-10a6 6 0 10-12 0c0 4.67 6 10 6 10z"/><circle cx="12" cy="11" r="2"/></svg>',
        flag: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v16M4 4h12l-2 4 2 4H4"/></svg>',
        book: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6 4h12v13H6z"/></svg>',
        file: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>',
        shield: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
    };

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function initials(name) {
        var parts = String(name).trim().split(/\s+/);
        if (parts.length >= 2) return parts[0].charAt(0) + parts[1].charAt(0);
        return (parts[0] || 'ط').charAt(0);
    }

    /** صف حقل: تسمية | قيمة */
    function fieldRow(label, value, opts) {
        opts = opts || {};
        var val = opts.html ? value : escapeHtml(value);
        var full = opts.full ? ' student-field-row--full' : '';
        var ltr = opts.ltr ? ' student-field-row__value--ltr' : '';
        return (
            '<div class="student-field-row' +
            full +
            '">' +
            '<span class="student-field-row__label">' +
            escapeHtml(label) +
            '</span>' +
            '<span class="student-field-row__value' +
            ltr +
            '">' +
            val +
            '</span></div>'
        );
    }

    function fieldList(rowsHtml, cols) {
        return (
            '<div class="student-field-list student-field-list--' +
            (cols || 2) +
            '">' +
            rowsHtml +
            '</div>'
        );
    }

    /** بطاقة حقل في المعلومات الشخصية — عمودان متوازيان */
    function personalField(label, value, icon, opts) {
        opts = opts || {};
        var val = opts.html ? value : escapeHtml(value);
        var ltr = opts.ltr ? ' student-personal-field__value--ltr' : '';
        var empty = opts.empty ? ' student-personal-field--empty' : '';
        return (
            '<div class="student-personal-field' +
            empty +
            '">' +
            '<div class="student-personal-field__head">' +
            '<span class="student-personal-field__icon" aria-hidden="true">' +
            icon +
            '</span>' +
            '<span class="student-personal-field__label">' +
            escapeHtml(label) +
            '</span></div>' +
            '<div class="student-personal-field__value' +
            ltr +
            '">' +
            val +
            '</div></div>'
        );
    }

    function renderPersonalInfo(student, emailHtml, phoneHtml) {
        var marital = student.maritalStatus ? escapeHtml(student.maritalStatus) : '—';
        return (
            '<div class="student-personal-grid">' +
            personalField('الاسم', student.nameAr, ICONS.user) +
            personalField('الاسم بالإنجليزية', student.nameEn, ICONS.globe, { ltr: true }) +
            personalField('الرقم الأكاديمي', student.academicId, ICONS.hash, { ltr: true }) +
            personalField('رقم الهوية', student.nationalId, ICONS.id, { ltr: true }) +
            personalField('رقم الجوال', phoneHtml, ICONS.phone, { html: true, ltr: true }) +
            personalField('البريد الإلكتروني', emailHtml, ICONS.mail, { html: true, ltr: true }) +
            personalField('الجنس', student.gender, ICONS.gender) +
            personalField('الحالة الاجتماعية', marital, ICONS.ring, {
                empty: !student.maritalStatus,
            }) +
            personalField('المدينة', student.city, ICONS.pin) +
            personalField('الجنسية', student.nationality, ICONS.flag) +
            '</div>'
        );
    }

    function sectionCard(title, icon, body, mod) {
        return (
            '<section class="student-profile-card' +
            (mod ? ' ' + mod : '') +
            '">' +
            '<header class="student-profile-card__head">' +
            '<span class="student-profile-card__icon" aria-hidden="true">' +
            icon +
            '</span>' +
            '<h2 class="student-profile-card__title">' +
            escapeHtml(title) +
            '</h2></header>' +
            '<div class="student-profile-card__body">' +
            body +
            '</div></section>'
        );
    }

    function renderTabs() {
        return (
            '<div class="student-profile-tabs-wrap">' +
            '<div class="admin-view-tabs admin-view-tabs--scroll student-profile-tabs" role="tablist" aria-label="أقسام ملف الطالب">' +
            TAB_LABELS.map(function (label, i) {
                var active = i === 0;
                var disabled = i > 0;
                return (
                    '<button type="button" class="admin-view-tab' +
                    (active ? ' is-active' : '') +
                    '"' +
                    (disabled ? ' disabled title="قريباً"' : ' aria-selected="true"') +
                    ' role="tab">' +
                    escapeHtml(label) +
                    '</button>'
                );
            }).join('') +
            '</div></div>'
        );
    }

    function renderBreadcrumb(student) {
        if (!breadcrumb) return;
        breadcrumb.innerHTML =
            '<a href="index.html">الرئيسية</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<a href="students.html">الطلاب</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<span class="admin-breadcrumb__current" aria-current="page">' +
            escapeHtml(student.nameAr) +
            '</span>';
        document.title = student.nameAr + ' | لوحة تحكم مركز التعلم المستمر';
    }

    function render(student) {
        renderBreadcrumb(student);

        var loginBadge = student.loginAllowed
            ? '<span class="admin-badge admin-badge--success">مسموح بالدخول</span>'
            : '<span class="admin-badge admin-badge--muted">الدخول موقوف</span>';

        var docsHtml = student.documents
            .map(function (doc) {
                return (
                    '<li class="student-doc-chip">' +
                    '<span class="student-doc-chip__icon" aria-hidden="true">' +
                    ICONS.file +
                    '</span>' +
                    '<span class="student-doc-chip__label">' +
                    escapeHtml(doc.label) +
                    '</span>' +
                    '<button type="button" class="student-doc-chip__btn" data-doc-view="' +
                    escapeHtml(doc.id) +
                    '">عرض</button></li>'
                );
            })
            .join('');

        var emailHtml =
            '<a href="mailto:' +
            escapeHtml(student.email) +
            '" class="student-personal-field__link">' +
            escapeHtml(student.email) +
            '</a>';
        var phoneHtml =
            '<a href="tel:' +
            escapeHtml(student.mobile) +
            '" class="student-personal-field__link" dir="ltr">' +
            escapeHtml(student.mobile) +
            '</a>';

        root.innerHTML =
            '<header class="student-profile-hero">' +
            '<div class="student-profile-hero__bar">' +
            '<div class="student-profile-hero__start">' +
            '<div class="student-profile-avatar" aria-hidden="true">' +
            escapeHtml(initials(student.nameAr)) +
            '</div>' +
            '<div class="student-profile-hero__titles">' +
            '<p class="student-profile-hero__eyebrow">ملف الطالب · #' +
            escapeHtml(String(student.id)) +
            '</p>' +
            '<h1 class="student-profile-hero__name">' +
            escapeHtml(student.nameAr) +
            '</h1>' +
            '<p class="student-profile-hero__name-en" dir="ltr">' +
            escapeHtml(student.nameEn) +
            '</p></div></div>' +
            '<dl class="student-profile-hero__stats">' +
            '<div class="student-profile-hero__stat"><dt>الرقم الأكاديمي</dt><dd>' +
            escapeHtml(student.academicId) +
            '</dd></div>' +
            '<div class="student-profile-hero__stat"><dt>الهوية</dt><dd dir="ltr">' +
            escapeHtml(student.nationalId) +
            '</dd></div>' +
            '<div class="student-profile-hero__stat"><dt>الجوال</dt><dd dir="ltr">' +
            escapeHtml(student.mobile) +
            '</dd></div>' +
            '</dl>' +
            '<div class="student-profile-hero__end">' +
            '<div class="student-profile-hero__badges">' +
            '<span class="admin-badge admin-badge--success admin-badge--with-icon">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" aria-hidden="true"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>' +
            escapeHtml(student.studyStatus) +
            '</span>' +
            loginBadge +
            '</div>' +
            '<div class="student-profile-hero__actions">' +
            '<button type="button" class="admin-btn-secondary admin-btn-secondary--sm" disabled title="قريباً">تعديل البيانات</button>' +
            '<a href="students.html" class="admin-btn-primary admin-btn-primary--sm">كافة الطلاب</a>' +
            '</div></div></div></header>' +
            renderTabs() +
            '<div class="student-profile-board">' +
            sectionCard(
                'المعلومات الشخصية',
                ICONS.user,
                renderPersonalInfo(student, emailHtml, phoneHtml),
                'student-profile-card--personal'
            ) +
            sectionCard(
                'المعلومات الأكاديمية',
                ICONS.book,
                fieldList(
                    fieldRow('الدفعة والبرنامج', student.batch, { full: true }) +
                        fieldRow('الفرع', student.branch) +
                        fieldRow('المؤهل العلمي', student.qualification) +
                        fieldRow('نسبة الثانوية', student.highSchoolPct + '%') +
                        fieldRow('سنة التخرج', student.gradYear),
                    2
                ),
                'student-profile-card--academic'
            ) +
            sectionCard(
                'الصلاحيات والحالة',
                ICONS.shield,
                '<div class="student-status-strip">' +
                    '<div class="student-status-strip__item">' +
                    '<span class="student-status-strip__label">حالة الطالب</span>' +
                    '<span class="admin-badge admin-badge--success">' +
                    escapeHtml(student.studyStatus) +
                    '</span></div>' +
                    '<div class="student-status-strip__item">' +
                    '<span class="student-status-strip__label">تسجيل الدخول</span>' +
                    (student.loginAllowed
                        ? '<span class="admin-badge admin-badge--success">مفعّل</span>'
                        : '<span class="admin-badge admin-badge--danger">موقوف</span>') +
                    '</div></div>',
                'student-profile-card--status'
            ) +
            sectionCard(
                'الوثائق والمستندات',
                ICONS.file,
                '<ul class="student-doc-strip">' + docsHtml + '</ul>',
                'student-profile-card--docs'
            ) +
            '</div>' +
            '<footer class="student-profile-meta-bar">' +
            '<div class="student-profile-meta-bar__item">' +
            '<span class="student-profile-meta-bar__icon" aria-hidden="true">' +
            ICONS.user +
            '</span>' +
            '<div><span class="student-profile-meta-bar__label">أضيف بواسطة</span>' +
            '<span class="student-profile-meta-bar__value">' +
            escapeHtml(student.addedBy) +
            '</span></div></div>' +
            '<div class="student-profile-meta-bar__item">' +
            '<span class="student-profile-meta-bar__icon" aria-hidden="true">' +
            ICONS.clock +
            '</span>' +
            '<div><span class="student-profile-meta-bar__label">تاريخ الانضمام</span>' +
            '<span class="student-profile-meta-bar__value">' +
            '<time datetime="' +
            escapeHtml(student.joinedAt) +
            '">' +
            escapeHtml(student.joinedAt) +
            '</time>' +
            '<span class="student-profile-meta-bar__ago"> · ' +
            escapeHtml(student.joinedAgo) +
            '</span></div></div></footer>';

        root.querySelectorAll('[data-doc-view]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                alert('عرض المستند — قريباً');
            });
        });
    }

    function showMissing() {
        if (loading) loading.hidden = true;
        if (content) content.hidden = true;
        if (missing) missing.hidden = false;
    }

    function init() {
        var id = new URLSearchParams(window.location.search).get('id');
        if (!window.domainStudents) {
            showMissing();
            return;
        }
        var student = window.domainStudents.resolve({ id: id });
        if (!student) {
            showMissing();
            return;
        }
        if (loading) loading.hidden = true;
        if (missing) missing.hidden = true;
        if (content) content.hidden = false;
        render(student);
    }

    init();
})();
