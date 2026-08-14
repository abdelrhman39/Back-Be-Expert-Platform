/**
 * البرامج الدراسية — بحث، تصفية، وترقيم
 */
(function () {
    var PROGRAMS = window.domainPrograms ? window.domainPrograms.list : [];

    var pageSize = 5;
    var currentPage = 1;
    var filtered = PROGRAMS.slice();

    var tbody = document.getElementById('programs-tbody');
    var meta = document.getElementById('filter-result-meta');
    var pagination = document.getElementById('programs-pagination');
    var form = document.getElementById('programs-filter-form');

    if (!tbody || !form || !PROGRAMS.length) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function actionsCell(p, rowNum) {
        var viewUrl = 'program-view.html?code=' + encodeURIComponent(p.code) + '&tab=details';
        return (
            '<td class="admin-table-actions" data-label="إجراءات">' +
            '<div class="admin-actions-menu">' +
            '<button type="button" class="admin-kebab" aria-expanded="false" aria-haspopup="true" aria-label="إجراءات البرنامج ' + rowNum + '">' +
            '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>' +
            '</button>' +
            '<div class="admin-actions-dropdown" role="menu" hidden>' +
            '<a href="' + viewUrl + '" class="admin-actions-item" role="menuitem">' +
            '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
            '<span>عرض</span></a>' +
            '</div></div></td>'
        );
    }

    function statusBadge(status) {
        if (status === 'active') {
            return '<span class="admin-badge admin-badge--success">فعال</span>';
        }
        return '<span class="admin-badge admin-badge--danger">غير فعال</span>';
    }

    function formatDate(iso) {
        try {
            return new Intl.DateTimeFormat('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(iso));
        } catch (e) {
            return iso;
        }
    }

    function applyFilter() {
        var q = (form.search.value || '').trim().toLowerCase();
        var type = form.type.value;
        var status = form.status.value;
        var duration = form.duration.value;

        filtered = PROGRAMS.filter(function (p) {
            if (q && p.name.toLowerCase().indexOf(q) === -1 && p.code.toLowerCase().indexOf(q) === -1) {
                return false;
            }
            if (type && p.type !== type) return false;
            if (status && p.status !== status) return false;
            if (duration && p.durationVal !== duration) return false;
            return true;
        });

        currentPage = 1;
        render();
    }

    function renderTable() {
        var start = (currentPage - 1) * pageSize;
        var slice = filtered.slice(start, start + pageSize);

        tbody.innerHTML = slice
            .map(function (p, i) {
                var rowNum = start + i + 1;
                return (
                    '<tr>' +
                    '<td data-label="#">' + rowNum + '</td>' +
                    '<td data-label="اسم البرنامج">' + escapeHtml(p.name) + '</td>' +
                    '<td data-label="الشهادة">' + escapeHtml(p.cert) + '</td>' +
                    '<td data-label="الرمز"><code class="admin-code">' + escapeHtml(p.code) + '</code></td>' +
                    '<td data-label="المدة">' + escapeHtml(p.duration) + '</td>' +
                    '<td data-label="البدء">' + formatDate(p.start) + '</td>' +
                    '<td data-label="الحالة">' + statusBadge(p.status) + '</td>' +
                    actionsCell(p, rowNum) +
                    '</tr>'
                );
            })
            .join('');

        if (!slice.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="admin-table-empty">لا توجد نتائج مطابقة لمعايير البحث</td></tr>';
        }

        if (meta) {
            meta.textContent = '— نتائج البحث: ' + filtered.length + ' برنامج';
        }

        if (window.AdminTableActions) {
            AdminTableActions.close();
            AdminTableActions.bind(tbody);
        }
    }

    function renderPagination() {
        var totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;

        var parts = [];
        parts.push('<span class="admin-pagination__info">صفحة ' + currentPage + ' من ' + totalPages + '</span>');
        parts.push('<div class="admin-pagination__btns">');
        parts.push(
            '<button type="button" class="admin-pagination__btn" data-page="prev"' +
                (currentPage <= 1 ? ' disabled' : '') +
                '>السابق</button>'
        );

        for (var p = 1; p <= totalPages; p++) {
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

        parts.push(
            '<button type="button" class="admin-pagination__btn" data-page="next"' +
                (currentPage >= totalPages ? ' disabled' : '') +
                '>التالي</button>'
        );
        parts.push('</div>');

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

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        applyFilter();
    });

    form.addEventListener('reset', function () {
        setTimeout(function () {
            filtered = PROGRAMS.slice();
            currentPage = 1;
            render();
        }, 0);
    });

    render();
})();
