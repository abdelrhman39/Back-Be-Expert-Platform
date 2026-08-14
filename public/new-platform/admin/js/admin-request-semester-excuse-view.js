/**
 * عرض تفاصيل طلب اعتذار الفصل الدراسي — مع توقيع الطالب
 */
(function () {
    var loading = document.getElementById('excuse-view-loading');
    var missing = document.getElementById('excuse-view-missing');
    var content = document.getElementById('excuse-view-content');
    var root = document.getElementById('excuse-detail-root');
    var foot = document.getElementById('excuse-detail-foot');
    var breadcrumb = document.getElementById('admin-breadcrumb');

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function signatureSvg(seed) {
        var s = Number(seed) || 0;
        var y1 = 78 + (s % 12);
        return (
            '<svg class="admin-signature-svg" viewBox="0 0 520 140" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="توقيع الطالب">' +
            '<path d="M35 ' +
            y1 +
            ' C95 25, 145 115, 205 68 S310 35, 385 72 S455 98, 485 58" fill="none" stroke="#222" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>' +
            '<path d="M155 ' +
            (y1 + 18) +
            ' Q215 42, 275 ' +
            (y1 + 8) +
            '" fill="none" stroke="#222" stroke-width="2" stroke-linecap="round"/>' +
            '<path d="M300 ' +
            (y1 + 5) +
            ' L335 ' +
            (y1 - 8) +
            ' L365 ' +
            (y1 + 12) +
            '" fill="none" stroke="#222" stroke-width="1.6" stroke-linecap="round"/>' +
            '</svg>'
        );
    }

    function detailRow(label, value, extraClass) {
        return (
            '<div class="admin-request-detail-row' +
            (extraClass ? ' ' + extraClass : '') +
            '">' +
            '<dt class="admin-request-detail-row__label">' +
            escapeHtml(label) +
            '</dt>' +
            '<dd class="admin-request-detail-row__value">' +
            value +
            '</dd></div>'
        );
    }

    function statusBadge(label) {
        return '<span class="admin-status-pill admin-status-pill--warning">' + escapeHtml(label) + '</span>';
    }

    function studentNameLink(req) {
        if (!req.studentProfileId) {
            return escapeHtml(req.studentName);
        }
        return (
            '<a href="student-view.html?id=' +
            encodeURIComponent(req.studentProfileId) +
            '" class="admin-student-link">' +
            escapeHtml(req.studentName) +
            '</a>'
        );
    }

    function renderBreadcrumb(req) {
        if (!breadcrumb) return;
        breadcrumb.innerHTML =
            '<a href="index.html">الرئيسية</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<a href="requests-semester-excuse.html">اعتذار عن الفصل الدراسي</a>' +
            '<span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<span class="admin-breadcrumb__current" aria-current="page">' +
            escapeHtml(String(req.id)) +
            '</span>';
        document.title = 'طلب اعتذار #' + req.id + ' | لوحة تحكم مركز التعلم المستمر';
    }

    function render(req) {
        renderBreadcrumb(req);

        root.innerHTML =
            '<dl class="admin-request-detail-list">' +
            detailRow('رقم الطلب', '<strong>' + escapeHtml(req.requestNo) + '</strong>') +
            detailRow('اسم الطالب', studentNameLink(req)) +
            detailRow('أضيف بواسطة', escapeHtml(req.addedBy)) +
            detailRow('الفصل الدراسي', escapeHtml(req.semester)) +
            detailRow('البرنامج', escapeHtml(req.programFull || req.program)) +
            detailRow('السبب', escapeHtml(req.reason)) +
            '<div class="admin-request-detail-row admin-request-detail-row--signature">' +
            '<dt class="admin-request-detail-row__label">صورة التوقيع</dt>' +
            '<dd class="admin-request-detail-row__value">' +
            '<div class="admin-signature-box">' +
            signatureSvg(req.id) +
            '<p class="admin-signature-box__hint">توقيع إلكتروني للطالب — ' +
            escapeHtml(req.studentName) +
            '</p></div></dd></div>' +
            detailRow('حالة الطلب', statusBadge(req.statusLabel)) +
            '</dl>';

        foot.innerHTML =
            '<span class="admin-request-detail-foot__label">تاريخ الإضافة</span>' +
            '<span class="admin-request-detail-foot__value">' +
            '<time datetime="' +
            escapeHtml(req.addedAt) +
            '">' +
            escapeHtml(req.addedAt) +
            '</time>' +
            ' <span class="admin-request-detail-foot__ago">— ' +
            escapeHtml(req.dateAgo) +
            '</span></span>';
    }

    function showMissing() {
        if (loading) loading.hidden = true;
        if (content) content.hidden = true;
        if (missing) missing.hidden = false;
    }

    function init() {
        var params = new URLSearchParams(window.location.search);
        var id = params.get('id');

        if (!window.domainRequests || !window.domainRequests.getSemesterExcuseById) {
            showMissing();
            return;
        }

        var req = window.domainRequests.getSemesterExcuseById(id);
        if (!req) {
            showMissing();
            return;
        }

        if (loading) loading.hidden = true;
        if (missing) missing.hidden = true;
        if (content) content.hidden = false;
        render(req);
    }

    init();
})();
