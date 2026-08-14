/**
 * الدفعات الدراسية — بحث، تصفية، ترقيم، وتصدير
 */
(function () {
    var BATCHES = window.domainBatches ? window.domainBatches.list : [];

    var pageSize = 5;
    var currentPage = 1;
    var filtered = BATCHES.slice();

    var tbody = document.getElementById('batches-tbody');
    var meta = document.getElementById('filter-result-meta');
    var pagination = document.getElementById('batches-pagination');
    var form = document.getElementById('batches-filter-form');
    var exportBtn = document.getElementById('batches-export-excel');

    if (!tbody || !form || !BATCHES.length) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function programCell(b) {
        return (
            '<div class="admin-program-cell">' +
            '<span class="admin-program-cell__name">' +
            escapeHtml(b.programName) +
            '</span>' +
            '<span class="admin-duration-pill">' +
            escapeHtml(b.programDuration) +
            '</span>' +
            '</div>'
        );
    }

    function studentCountCell(n) {
        return '<span class="admin-count-pill" aria-label="' + n + ' طالب">' + n + '</span>';
    }

    function actionsCell(b, rowNum) {
        var programUrl = 'program-view.html?code=' + encodeURIComponent(b.programCode) + '&tab=details';
        return (
            '<td class="admin-table-actions" data-label="إجراءات">' +
            '<div class="admin-actions-menu">' +
            '<button type="button" class="admin-kebab" aria-expanded="false" aria-haspopup="true" aria-label="إجراءات الدفعة ' +
            rowNum +
            '">' +
            '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>' +
            '</button>' +
            '<div class="admin-actions-dropdown" role="menu" hidden>' +
            '<a href="' +
            programUrl +
            '" class="admin-actions-item" role="menuitem">' +
            '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
            '<span>عرض البرنامج</span></a>' +
            '<button type="button" class="admin-actions-item" role="menuitem" data-batch-students="' +
            escapeHtml(b.code) +
            '">' +
            '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>' +
            '<span>قائمة الطلاب</span></button>' +
            '</div></div></td>'
        );
    }

    function bindBatchExtras() {
        tbody.querySelectorAll('[data-batch-students]').forEach(function (item) {
            if (item.dataset.batchBound === '1') return;
            item.dataset.batchBound = '1';
            item.addEventListener('click', function () {
                if (window.AdminTableActions) AdminTableActions.close();
                alert('قائمة طلاب الدفعة — قريباً');
            });
        });
    }

    function applyFilter() {
        var nameQ = (form.batchName.value || '').trim().toLowerCase();
        var codeQ = (form.batchCode.value || '').trim();
        var program = form.program.value;
        var semester = form.semester.value;

        filtered = BATCHES.filter(function (b) {
            if (nameQ && b.name.toLowerCase().indexOf(nameQ) === -1) return false;
            if (codeQ && String(b.code).indexOf(codeQ) === -1) return false;
            if (program && b.programCode !== program) return false;
            if (semester && b.semesterVal !== semester) return false;
            return true;
        });

        currentPage = 1;
        render();
    }

    function renderTable() {
        var start = (currentPage - 1) * pageSize;
        var slice = filtered.slice(start, start + pageSize);

        tbody.innerHTML = slice
            .map(function (b, i) {
                var rowNum = start + i + 1;
                return (
                    '<tr>' +
                    '<td data-label="#">' +
                    rowNum +
                    '</td>' +
                    '<td data-label="اسم الدفعة">' +
                    escapeHtml(b.name) +
                    '</td>' +
                    '<td data-label="كود الدفعة"><code class="admin-code">' +
                    escapeHtml(b.code) +
                    '</code></td>' +
                    '<td data-label="البرنامج">' +
                    programCell(b) +
                    '</td>' +
                    '<td data-label="فصل القبول">' +
                    escapeHtml(b.semester) +
                    '</td>' +
                    '<td data-label="عدد طلاب الدفعة">' +
                    studentCountCell(b.students) +
                    '</td>' +
                    actionsCell(b, rowNum) +
                    '</tr>'
                );
            })
            .join('');

        if (!slice.length) {
            tbody.innerHTML =
                '<tr><td colspan="7" class="admin-table-empty">لا توجد نتائج مطابقة لمعايير البحث</td></tr>';
        }

        if (meta) {
            meta.textContent = '— نتائج البحث: ' + filtered.length + ' دفعة';
        }

        if (window.AdminTableActions) {
            AdminTableActions.close();
            AdminTableActions.bind(tbody);
        }
        bindBatchExtras();
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

    function exportExcel() {
        var headers = ['#', 'اسم الدفعة', 'كود الدفعة', 'البرنامج', 'مدة البرنامج', 'فصل القبول', 'عدد الطلاب'];
        var rows = filtered.map(function (b, i) {
            return [i + 1, b.name, b.code, b.programName, b.programDuration, b.semester, b.students];
        });
        var csv =
            '\uFEFF' +
            [headers]
                .concat(rows)
                .map(function (row) {
                    return row
                        .map(function (cell) {
                            var s = String(cell == null ? '' : cell);
                            if (/[",\n]/.test(s)) return '"' + s.replace(/"/g, '""') + '"';
                            return s;
                        })
                        .join(',');
                })
                .join('\n');

        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'batches-export.csv';
        a.click();
        URL.revokeObjectURL(url);
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        applyFilter();
    });

    form.addEventListener('reset', function () {
        setTimeout(function () {
            filtered = BATCHES.slice();
            currentPage = 1;
            render();
        }, 0);
    });

    if (exportBtn) {
        exportBtn.addEventListener('click', exportExcel);
    }

    render();
})();
