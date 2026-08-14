/**
 * عرض تفاصيل طلب تغيير البرنامج — مع توقيع الطالب
 */
(function () {
    var loading = document.getElementById('change-view-loading');
    var missing = document.getElementById('change-view-missing');
    var content = document.getElementById('change-view-content');
    var root = document.getElementById('change-detail-root');
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
        var y1 = 72 + (s % 15);
        return (
            '<svg class="admin-signature-svg" viewBox="0 0 520 140" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="توقيع الطالب">' +
            '<path d="M42 ' +
            (y1 + 5) +
            ' C110 20, 150 120, 220 65 S320 30, 400 70 S470 100, 500 55" fill="none" stroke="#222" stroke-width="2.5" stroke-linecap="round"/>' +
            '<path d="M170 ' +
            (y1 + 20) +
            ' Q240 35, 310 ' +
            (y1 + 10) +
            '" fill="none" stroke="#222" stroke-width="2" stroke-linecap="round"/>' +
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

    function statusProcessing(label) {
        return (
            '<span class="admin-status-pill admin-status-pill--processing">' +
            '<span class="admin-status-pill__dot" aria-hidden="true"></span>' +
            escapeHtml(label) +
            '</span>'
        );
    }

    function statusApproved(label) {
        return '<span class="admin-badge admin-badge--success">' + escapeHtml(label) + '</span>';
    }

    function renderBreadcrumb(req) {
        if (!breadcrumb) return;
        breadcrumb.innerHTML =
            '<a href="index.html">الرئيسية</a><span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<a href="requests-program-change.html">طلبات تغيير البرنامج</a>' +
            '<span class="admin-breadcrumb__sep" aria-hidden="true">›</span>' +
            '<span class="admin-breadcrumb__current" aria-current="page">' +
            escapeHtml(String(req.id)) +
            '</span>';
        document.title = 'طلب تغيير برنامج #' + req.id + ' | لوحة تحكم مركز التعلم المستمر';
    }

    function render(req) {
        renderBreadcrumb(req);

        var statusHtml =
            req.requestStatus === 'approved'
                ? statusApproved(req.requestStatusLabel)
                : statusProcessing(req.requestStatusLabel);

        var dateHtml =
            '<time datetime="' +
            escapeHtml(req.addedAt) +
            '">' +
            escapeHtml(req.addedAt) +
            '</time>' +
            ' <span class="admin-request-detail-foot__ago">— ' +
            escapeHtml(req.dateAgo) +
            '</span>';

        root.innerHTML =
            '<dl class="admin-request-detail-list">' +
            detailRow('تاريخ الإضافة', dateHtml) +
            detailRow('رقم الطلب', '<strong>' + escapeHtml(req.requestNo) + '</strong>') +
            detailRow(
                'اسم الطالب',
                req.studentProfileId
                    ? '<a href="student-view.html?id=' +
                      encodeURIComponent(req.studentProfileId) +
                      '" class="admin-student-link">' +
                      escapeHtml(req.studentName) +
                      '</a>'
                    : escapeHtml(req.studentName)
            ) +
            detailRow('البرنامج الحالي', escapeHtml(req.currentProgramFull || req.currentProgram)) +
            detailRow('البرنامج الجديد', escapeHtml(req.newProgramFull || req.newProgram)) +
            detailRow('السبب', '<p class="admin-request-detail-text">' + escapeHtml(req.reason) + '</p>') +
            detailRow('حالة الطلب', statusHtml) +
            '<div class="admin-request-detail-row admin-request-detail-row--signature">' +
            '<dt class="admin-request-detail-row__label">صورة التوقيع</dt>' +
            '<dd class="admin-request-detail-row__value">' +
            '<div class="admin-signature-box">' +
            signatureSvg(req.id) +
            '<p class="admin-signature-box__hint">توقيع إلكتروني للطالب — ' +
            escapeHtml(req.studentName) +
            '</p></div></dd></div>' +
            '</dl>';
    }

    function showMissing() {
        if (loading) loading.hidden = true;
        if (content) content.hidden = true;
        if (missing) missing.hidden = false;
    }

    function init() {
        var id = new URLSearchParams(window.location.search).get('id');

        if (!window.domainRequests || !window.domainRequests.getProgramChangeById) {
            showMissing();
            return;
        }

        var req = window.domainRequests.getProgramChangeById(id);
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
