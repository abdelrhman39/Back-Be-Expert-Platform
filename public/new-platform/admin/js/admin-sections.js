/**
 * الشعب الدراسية — بحث، تصفية، وترقيم
 */
(function () {
    var SECTIONS = window.domainSections ? window.domainSections.list : [];

    var pageSize = 10;
    var currentPage = 1;
    var filtered = SECTIONS.slice();

    var tbody = document.getElementById('sections-tbody');
    var meta = document.getElementById('filter-result-meta');
    var pagination = document.getElementById('sections-pagination');
    var form = document.getElementById('sections-filter-form');
    var exportBtn = document.getElementById('sections-export-excel');

    if (!tbody || !form || !SECTIONS.length) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function sectionNameCell(s) {
        return (
            '<div class="admin-section-cell">' +
            '<span class="admin-section-cell__title">' +
            escapeHtml(s.name) +
            '</span>' +
            '<span class="admin-section-cell__sub">' +
            escapeHtml(s.subtitle) +
            '</span>' +
            '</div>'
        );
    }

    function periodPill(label) {
        return '<span class="admin-period-pill">' + escapeHtml(label) + '</span>';
    }

    function statusBadge(status) {
        if (status === 'active') {
            return '<span class="admin-badge admin-badge--success">مفعل</span>';
        }
        return '<span class="admin-badge admin-badge--muted">غير مفعل</span>';
    }

    function actionsCell(s, rowNum) {
        return (
            '<td class="admin-table-actions" data-label="إجراءات">' +
            '<div class="admin-actions-menu">' +
            '<button type="button" class="admin-kebab" aria-expanded="false" aria-haspopup="true" aria-label="إجراءات الشعبة ' +
            rowNum +
            '">' +
            '<svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>' +
            '</button>' +
            '<div class="admin-actions-dropdown" role="menu" hidden>' +
            '<a href="section-view.html?code=' +
            encodeURIComponent(s.code) +
            '" class="admin-actions-item" role="menuitem">' +
            '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>' +
            '<span>عرض التفاصيل</span></a>' +
            '<a href="schedules.html" class="admin-actions-item" role="menuitem">' +
            '<svg class="admin-actions-item__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>' +
            '<span>الجدول الدراسي</span></a>' +
            '</div></div></td>'
        );
    }

    function applyFilter() {
        var nameQ = (form.sectionName.value || '').trim().toLowerCase();
        var codeQ = (form.sectionCode.value || '').trim();
        var semester = form.semester.value;
        var program = form.program.value;
        var level = form.level.value;
        var course = form.course.value;
        var batch = form.batch.value;
        var faculty = form.faculty.value;
        var status = form.status.value;

        filtered = SECTIONS.filter(function (s) {
            if (nameQ) {
                var hay = (s.name + ' ' + s.subtitle).toLowerCase();
                if (hay.indexOf(nameQ) === -1) return false;
            }
            if (codeQ && String(s.code).indexOf(codeQ) === -1) return false;
            if (semester && s.semester !== semester) return false;
            if (program && s.programCode !== program) return false;
            if (level && s.level !== level) return false;
            if (course && s.courseCode !== course) return false;
            if (batch && s.batchCode !== batch) return false;
            if (faculty && s.faculty !== faculty) return false;
            if (status && s.status !== status) return false;
            return true;
        });

        currentPage = 1;
        render();
    }

    function renderTable() {
        var start = (currentPage - 1) * pageSize;
        var slice = filtered.slice(start, start + pageSize);

        tbody.innerHTML = slice
            .map(function (s, i) {
                var rowNum = start + i + 1;
                return (
                    '<tr>' +
                    '<td data-label="#">' +
                    rowNum +
                    '</td>' +
                    '<td data-label="اسم الشعبة">' +
                    sectionNameCell(s) +
                    '</td>' +
                    '<td data-label="كود الشعبة"><code class="admin-code">' +
                    escapeHtml(s.code) +
                    '</code></td>' +
                    '<td data-label="فترة التدريس">' +
                    periodPill(s.periodLabel) +
                    '</td>' +
                    '<td data-label="الفصل الدراسي">' +
                    escapeHtml(s.semesterLabel) +
                    '</td>' +
                    '<td data-label="المستوى">' +
                    escapeHtml(s.levelLabel) +
                    '</td>' +
                    '<td data-label="عدد طلاب الشعبة"><span class="admin-count-pill">' +
                    s.students +
                    '</span></td>' +
                    '<td data-label="الحالة">' +
                    statusBadge(s.status) +
                    '</td>' +
                    actionsCell(s, rowNum) +
                    '</tr>'
                );
            })
            .join('');

        if (!slice.length) {
            tbody.innerHTML =
                '<tr><td colspan="9" class="admin-table-empty">لا توجد نتائج مطابقة لمعايير البحث</td></tr>';
        }

        if (meta) {
            meta.textContent = '(تم العثور على ' + filtered.length + ' شعبة)';
        }

        if (window.AdminTableActions) {
            AdminTableActions.close();
            AdminTableActions.bind(tbody);
        }
    }

    function pageNumbers(totalPages) {
        if (totalPages <= 12) {
            var all = [];
            for (var i = 1; i <= totalPages; i++) all.push(i);
            return all;
        }
        var pages = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        if (totalPages > 11) pages.push('…');
        pages.push(totalPages - 1, totalPages);
        return pages;
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

        pageNumbers(totalPages).forEach(function (p) {
            if (p === '…') {
                parts.push('<span class="admin-pagination__ellipsis" aria-hidden="true">…</span>');
                return;
            }
            parts.push(
                '<button type="button" class="admin-pagination__btn' +
                    (p === currentPage ? ' is-active' : '') +
                    '" data-page="' +
                    p +
                    '">' +
                    p +
                    '</button>'
            );
        });

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
        var headers = [
            '#',
            'اسم الشعبة',
            'المقرر',
            'كود الشعبة',
            'فترة التدريس',
            'الفصل',
            'المستوى',
            'عدد الطلاب',
            'الحالة',
        ];
        var rows = filtered.map(function (s, i) {
            return [
                i + 1,
                s.name,
                s.subtitle,
                s.code,
                s.periodLabel,
                s.semesterLabel,
                s.levelLabel,
                s.students,
                s.status === 'active' ? 'مفعل' : 'غير مفعل',
            ];
        });
        var csv =
            '\uFEFF' +
            [headers]
                .concat(rows)
                .map(function (row) {
                    return row
                        .map(function (cell) {
                            var t = String(cell == null ? '' : cell);
                            if (/[",\n]/.test(t)) return '"' + t.replace(/"/g, '""') + '"';
                            return t;
                        })
                        .join(',');
                })
                .join('\n');
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'sections-export.csv';
        a.click();
        URL.revokeObjectURL(url);
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        applyFilter();
    });

    form.addEventListener('reset', function () {
        setTimeout(function () {
            filtered = SECTIONS.slice();
            currentPage = 1;
            render();
        }, 0);
    });

    if (exportBtn) exportBtn.addEventListener('click', exportExcel);

    render();
})();
