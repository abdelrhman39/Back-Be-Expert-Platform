/**
 * المستويات الأكاديمية — بحث وترقيم
 */
(function () {
    var LEVELS = window.domainLevels ? window.domainLevels.list : [];
    var pageSize = 10;
    var currentPage = 1;
    var filtered = LEVELS.slice();

    var tbody = document.getElementById('levels-tbody');
    var meta = document.getElementById('filter-result-meta');
    var pagination = document.getElementById('levels-pagination');
    var form = document.getElementById('levels-filter-form');

    if (!tbody || !form) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function statusBadge(status) {
        if (status === 'active') {
            return '<span class="admin-badge admin-badge--success">مفعل</span>';
        }
        return '<span class="admin-badge admin-badge--muted">غير مفعل</span>';
    }

    function actionsCell(level, rowNum) {
        return (
            '<td class="admin-table-actions" data-label="إجراءات">' +
            '<div class="admin-actions-menu">' +
            '<button type="button" class="admin-kebab" aria-expanded="false" aria-haspopup="true" aria-label="إجراءات المستوى ' +
            rowNum +
            '">' +
            '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>' +
            '</button>' +
            '<div class="admin-actions-dropdown" role="menu" hidden>' +
            '<button type="button" class="admin-actions-item" role="menuitem" data-level-view="' +
            escapeHtml(level.id) +
            '">' +
            '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
            '<span>عرض</span></button>' +
            '</div></div></td>'
        );
    }

    function bindExtras() {
        tbody.querySelectorAll('[data-level-view]').forEach(function (btn) {
            if (btn.dataset.levelBound === '1') return;
            btn.dataset.levelBound = '1';
            btn.addEventListener('click', function () {
                if (window.AdminTableActions) AdminTableActions.close();
                alert('تفاصيل المستوى — قريباً');
            });
        });
    }

    function applyFilter() {
        var q = (form.title.value || '').trim().toLowerCase();
        var status = form.status.value;
        filtered = LEVELS.filter(function (l) {
            if (q && l.name.toLowerCase().indexOf(q) === -1) return false;
            if (status && l.status !== status) return false;
            return true;
        });
        currentPage = 1;
        render();
    }

    function renderTable() {
        var start = (currentPage - 1) * pageSize;
        var slice = filtered.slice(start, start + pageSize);
        tbody.innerHTML = slice
            .map(function (l, i) {
                var rowNum = start + i + 1;
                return (
                    '<tr><td data-label="#">' +
                    rowNum +
                    '</td><td data-label="اسم المستوى الدراسي">' +
                    escapeHtml(l.name) +
                    '</td><td data-label="الحالة">' +
                    statusBadge(l.status) +
                    '</td>' +
                    actionsCell(l, rowNum) +
                    '</tr>'
                );
            })
            .join('');
        if (!slice.length) {
            tbody.innerHTML =
                '<tr><td colspan="4" class="admin-table-empty">لا توجد مستويات مطابقة</td></tr>';
        }
        if (meta) meta.textContent = '(نتائج البحث ' + filtered.length + ' مستوى دراسي)';
        if (window.AdminTableActions) {
            AdminTableActions.close();
            AdminTableActions.bind(tbody);
        }
        bindExtras();
    }

    function renderPagination() {
        var totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        if (currentPage > totalPages) currentPage = totalPages;
        var parts = [
            '<span class="admin-pagination__info">صفحة ' + currentPage + ' من ' + totalPages + '</span>',
            '<div class="admin-pagination__btns">',
            '<button type="button" class="admin-pagination__btn" data-page="prev"' +
                (currentPage <= 1 ? ' disabled' : '') +
                '>السابق</button>',
        ];
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

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        applyFilter();
    });
    form.addEventListener('reset', function () {
        setTimeout(function () {
            filtered = LEVELS.slice();
            currentPage = 1;
            render();
        }, 0);
    });
    render();
})();
