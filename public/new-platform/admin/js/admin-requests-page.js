/**
 * صفحات طلبات الخدمات الأكاديمية — جدول وبحث مشترك
 */
(function () {
    var pageId = document.body.getAttribute('data-request-page');
    var data = window.domainRequests;
    if (!pageId || !data) return;

    var PAGE = {
        deferral: {
            rows: data.deferral,
            unit: 'طلب تأجيل',
            tableTitle: 'طلبات التأجيل',
            empty: 'لا توجد طلبات تأجيل',
            pageSize: 10,
            type: 'deferral',
        },
        withdrawal: {
            rows: data.withdrawal,
            unit: 'طلب انسحاب',
            tableTitle: 'عرض كافة الطلبات الانسحاب',
            empty: 'لا توجد طلبات انسحاب',
            pageSize: 10,
            type: 'withdrawal',
        },
        programChange: {
            rows: data.programChange,
            unit: 'طلب تغيير برنامج',
            tableTitle: 'عرض كافة طلبات تغيير البرنامج',
            empty: 'لا توجد طلبات تغيير برنامج',
            pageSize: 10,
            type: 'programChange',
        },
        semesterExcuse: {
            rows: data.semesterExcuse,
            unit: 'طالب اعتذار',
            tableTitle: 'طلبات اعتذار الفصل الدراسي',
            empty: 'لا توجد طلبات اعتذار',
            pageSize: 10,
            type: 'semesterExcuse',
        },
    }[pageId];

    if (!PAGE) return;

    var allRows = PAGE.rows.slice();
    var filtered = allRows.slice();
    var currentPage = 1;
    var pageSize = PAGE.pageSize;

    var form = document.getElementById('requests-filter-form');
    var tbody = document.getElementById('requests-tbody');
    var meta = document.getElementById('filter-result-meta');
    var pagination = document.getElementById('requests-pagination');
    var exportBtn = document.getElementById('requests-export-excel');

    if (!form || !tbody) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function studentCell(name, id, profileId) {
        var nameHtml = escapeHtml(name);
        if (profileId) {
            nameHtml =
                '<a href="student-view.html?id=' +
                encodeURIComponent(profileId) +
                '" class="admin-student-link">' +
                nameHtml +
                '</a>';
        }
        return (
            '<div class="admin-student-cell">' +
            '<span class="admin-student-cell__name">' +
            nameHtml +
            '</span>' +
            '<span class="admin-student-cell__id">' +
            escapeHtml(id) +
            '</span></div>'
        );
    }

    function dateCell(date, ago) {
        return (
            '<div class="admin-date-cell">' +
            '<span class="admin-date-cell__date">' +
            escapeHtml(date) +
            '</span>' +
            '<span class="admin-date-cell__ago">' +
            escapeHtml(ago) +
            '</span></div>'
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

    function statusReview(label, kind) {
        var cls = kind === 'reviewed' ? 'admin-review-pill--done' : 'admin-review-pill--pending';
        return '<span class="admin-review-pill ' + cls + '">' + escapeHtml(label) + '</span>';
    }

    function statusPending(label) {
        return '<span class="admin-status-pill admin-status-pill--warning">' + escapeHtml(label) + '</span>';
    }

    function programWithDuration(name, duration) {
        return (
            '<div class="admin-program-cell">' +
            '<span class="admin-program-cell__name">' +
            escapeHtml(name) +
            '</span>' +
            '<span class="admin-duration-pill">' +
            escapeHtml(duration) +
            '</span></div>'
        );
    }

    function requestNoCell(no, statusLabel, statusKey) {
        var pill =
            statusKey === 'approved'
                ? '<span class="admin-badge admin-badge--success">' + escapeHtml(statusLabel) + '</span>'
                : statusProcessing(statusLabel);
        return (
            '<div class="admin-request-no-cell">' +
            '<span class="admin-request-no-cell__no">' +
            escapeHtml(String(no)) +
            '</span>' +
            pill +
            '</div>'
        );
    }

    function actionsMenu(row, rowNum, withApprove) {
        var viewItem;
        if (PAGE.type === 'programChange' && row && row.id) {
            viewItem =
                '<a href="request-program-change-view.html?id=' +
                encodeURIComponent(row.id) +
                '" class="admin-actions-item" role="menuitem">' +
                '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
                '<span>عرض</span></a>';
        } else if (PAGE.type === 'semesterExcuse' && row && row.id) {
            viewItem =
                '<a href="request-semester-excuse-view.html?id=' +
                encodeURIComponent(row.id) +
                '" class="admin-actions-item" role="menuitem">' +
                '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
                '<span>عرض</span></a>';
        } else {
            viewItem =
                '<button type="button" class="admin-actions-item" role="menuitem" data-req-view="' +
                rowNum +
                '">' +
                '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
                '<span>عرض</span></button>';
        }
        var items = viewItem;
        if (withApprove) {
            items +=
                '<button type="button" class="admin-actions-item admin-actions-item--success" role="menuitem" data-req-approve="' +
                rowNum +
                '">' +
                '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>' +
                '<span>موافقة</span></button>' +
                '<button type="button" class="admin-actions-item admin-actions-item--danger" role="menuitem" data-req-reject="' +
                rowNum +
                '">' +
                '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>' +
                '<span>رفض</span></button>';
        }
        return (
            '<td class="admin-table-actions" data-label="إجراءات">' +
            '<div class="admin-actions-menu">' +
            '<button type="button" class="admin-kebab" aria-expanded="false" aria-haspopup="true" aria-label="إجراءات">' +
            '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>' +
            '</button><div class="admin-actions-dropdown" role="menu" hidden>' +
            items +
            '</div></div></td>'
        );
    }

    function rowHtml(r, rowNum) {
        var t = PAGE.type;
        if (t === 'withdrawal') {
            return (
                '<tr><td data-label="#">' +
                rowNum +
                '</td><td data-label="الطالب">' +
                studentCell(r.studentName, r.studentId, r.studentProfileId) +
                '</td><td data-label="البرنامج">' +
                escapeHtml(r.program) +
                '</td><td data-label="طريقة الدفع">' +
                escapeHtml(r.paymentMethod) +
                '</td><td data-label="الحالة">' +
                statusProcessing(r.statusLabel) +
                '</td><td data-label="التاريخ">' +
                dateCell(r.date, r.dateAgo) +
                '</td>' +
                actionsMenu(r, rowNum, true) +
                '</tr>'
            );
        }
        if (t === 'programChange') {
            return (
                '<tr><td data-label="#">' +
                rowNum +
                '</td><td data-label="رقم الطلب">' +
                requestNoCell(r.requestNo, r.requestStatusLabel, r.requestStatus) +
                '</td><td data-label="الاسم">' +
                studentCell(r.studentName, r.studentId, r.studentProfileId) +
                '</td><td data-label="البرنامج الحالي">' +
                programWithDuration(r.currentProgram, r.currentDuration) +
                '</td><td data-label="البرنامج الجديد">' +
                programWithDuration(r.newProgram, r.newDuration) +
                '</td><td data-label="تاريخ الإضافة">' +
                dateCell(r.date, r.dateAgo) +
                '</td>' +
                actionsMenu(r, rowNum, true) +
                '</tr>'
            );
        }
        if (t === 'semesterExcuse') {
            return (
                '<tr><td data-label="#">' +
                rowNum +
                '</td><td data-label="الطالب">' +
                studentCell(r.studentName, r.studentId, r.studentProfileId) +
                '</td><td data-label="البرنامج">' +
                escapeHtml(r.program) +
                '</td><td data-label="الفصل">' +
                escapeHtml(r.semester) +
                '</td><td data-label="المراجعة">' +
                statusReview(r.reviewLabel, r.reviewStatus) +
                '</td><td data-label="الحالة">' +
                statusPending(r.statusLabel) +
                '</td><td data-label="التاريخ">' +
                dateCell(r.date, r.dateAgo) +
                '</td>' +
                actionsMenu(r, rowNum, true) +
                '</tr>'
            );
        }
        /* deferral */
        return (
            '<tr><td data-label="#">' +
            rowNum +
            '</td><td data-label="الطالب">' +
            studentCell(r.studentName, r.studentId, r.studentProfileId) +
            '</td><td data-label="البرنامج">' +
            escapeHtml(r.program) +
            '</td><td data-label="المراجعة">' +
            statusReview(r.reviewLabel || 'لم يراجع', r.reviewStatus || 'pending') +
            '</td><td data-label="الحالة">' +
            statusPending(r.statusLabel || 'قيد المراجعة') +
            '</td><td data-label="التاريخ">' +
            dateCell(r.date, r.dateAgo) +
            '</td>' +
            actionsMenu(r, rowNum, true) +
            '</tr>'
        );
    }

    function emptyState() {
        return (
            '<tr><td colspan="12" class="admin-table-empty-state">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>' +
            '<p>' +
            escapeHtml(PAGE.empty) +
            '</p></td></tr>'
        );
    }

    function applyFilter() {
        var q = (form.search && form.search.value ? form.search.value : '').trim().toLowerCase();
        var status = form.status ? form.status.value : '';
        var semesterEl = form.semester;
        var semesterLabel = '';
        if (semesterEl && semesterEl.value && semesterEl.selectedIndex >= 0) {
            semesterLabel = semesterEl.options[semesterEl.selectedIndex].textContent;
        }
        var program = form.program ? form.program.value : '';
        var payment = form.paymentType ? form.paymentType.value : '';

        filtered = allRows.filter(function (r) {
            if (q) {
                var hay = (
                    (r.studentName || '') +
                    ' ' +
                    (r.studentId || '') +
                    ' ' +
                    (r.program || '')
                ).toLowerCase();
                if (hay.indexOf(q) === -1) return false;
            }
            if (status && r.status !== status && r.requestStatus !== status) return false;
            if (semesterLabel && r.semester && r.semester.indexOf(semesterLabel) === -1) return false;
            if (program && r.program && r.program !== program) return false;
            if (payment && r.paymentMethod !== payment) return false;
            return true;
        });
        currentPage = 1;
        render();
    }

    function bindExtras() {
        tbody.querySelectorAll('[data-req-view],[data-req-approve],[data-req-reject]').forEach(function (btn) {
            if (btn.dataset.reqBound === '1') return;
            btn.dataset.reqBound = '1';
            btn.addEventListener('click', function () {
                if (window.AdminTableActions) AdminTableActions.close();
                var msg = 'عرض الطلب — قريباً';
                if (btn.hasAttribute('data-req-approve')) msg = 'تم تسجيل الموافقة (تجريبي)';
                if (btn.hasAttribute('data-req-reject')) msg = 'تم تسجيل الرفض (تجريبي)';
                alert(msg);
            });
        });
    }

    function renderTable() {
        if (!filtered.length) {
            tbody.innerHTML = emptyState();
            if (meta) meta.textContent = '(نتائج البحث 0 ' + PAGE.unit + ')';
            return;
        }
        var start = (currentPage - 1) * pageSize;
        var slice = filtered.slice(start, start + pageSize);
        tbody.innerHTML = slice
            .map(function (r, i) {
                return rowHtml(r, start + i + 1);
            })
            .join('');
        if (meta) meta.textContent = '(نتائج البحث ' + filtered.length + ' ' + PAGE.unit + ')';
        if (window.AdminTableActions) {
            AdminTableActions.close();
            AdminTableActions.bind(tbody);
        }
        bindExtras();
    }

    function renderPagination() {
        if (!pagination) return;
        var totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        if (!filtered.length) {
            pagination.innerHTML = '';
            return;
        }
        if (currentPage > totalPages) currentPage = totalPages;
        var parts = [
            '<span class="admin-pagination__info">صفحة ' + currentPage + ' من ' + totalPages + '</span>',
            '<div class="admin-pagination__btns">',
            '<button type="button" class="admin-pagination__btn" data-page="prev"' +
                (currentPage <= 1 ? ' disabled' : '') +
                '>السابق</button>',
        ];
        var maxBtns = totalPages > 10 ? 10 : totalPages;
        var p;
        for (p = 1; p <= maxBtns; p++) {
            parts.push(
                '<button type="button" class="admin-pagination__btn' +
                    (p === currentPage ? ' is-active' : '') +
                    '" data-page="' +
                    p +
                    '">' +
                    p +
                    '</button>'
            );
        }
        if (totalPages > 10) {
            parts.push('<span class="admin-pagination__ellipsis">…</span>');
            parts.push(
                '<button type="button" class="admin-pagination__btn" data-page="' +
                    totalPages +
                    '">' +
                    totalPages +
                    '</button>'
            );
        }
        parts.push(
            '<button type="button" class="admin-pagination__btn" data-page="next"' +
                (currentPage >= totalPages ? ' disabled' : '') +
                '>التالي</button></div>'
        );
        pagination.innerHTML = parts.join('');
        pagination.querySelectorAll('[data-page]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = btn.getAttribute('data-page');
                if (v === 'prev' && currentPage > 1) currentPage--;
                else if (v === 'next' && currentPage < totalPages) currentPage++;
                else if (v !== 'prev' && v !== 'next') currentPage = parseInt(v, 10);
                renderTable();
                renderPagination();
            });
        });
    }

    function render() {
        renderTable();
        renderPagination();
    }

    function exportCsv() {
        alert('تصدير Excel — قريباً');
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        applyFilter();
    });
    form.addEventListener('reset', function () {
        setTimeout(function () {
            filtered = allRows.slice();
            currentPage = 1;
            render();
        }, 0);
    });
    if (exportBtn) exportBtn.addEventListener('click', exportCsv);

    render();
})();
