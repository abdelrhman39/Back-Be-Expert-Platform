/**
 * المقررات الدراسية — قائمة مع بحث وترقيم
 */
(function () {
    var COURSES = window.domainPrograms ? window.domainPrograms.courses || [] : [];

    var pageSize = 8;
    var currentPage = 1;
    var filtered = COURSES.slice();

    var tbody = document.getElementById('courses-tbody');
    var meta = document.getElementById('courses-filter-meta');
    var pagination = document.getElementById('courses-pagination');
    var form = document.getElementById('courses-filter-form');
    var programSelect = document.getElementById('f-course-program');

    if (!tbody || !form || !COURSES.length) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fillProgramOptions() {
        if (!programSelect) return;
        var codes = {};
        COURSES.forEach(function (c) {
            if (!codes[c.programCode]) {
                codes[c.programCode] = c.programName || c.programCode;
            }
        });
        Object.keys(codes).forEach(function (code) {
            var opt = document.createElement('option');
            opt.value = code;
            opt.textContent = codes[code];
            programSelect.appendChild(opt);
        });
    }

    fillProgramOptions();

    function applyFilter() {
        var q = (form.search.value || '').trim().toLowerCase();
        var program = form.program.value;

        filtered = COURSES.filter(function (c) {
            if (q) {
                var hay = (c.name + ' ' + c.code + ' ' + (c.symbol || '')).toLowerCase();
                if (hay.indexOf(q) === -1) return false;
            }
            if (program && c.programCode !== program) return false;
            return true;
        });

        currentPage = 1;
        render();
    }

    function renderTable() {
        var start = (currentPage - 1) * pageSize;
        var slice = filtered.slice(start, start + pageSize);

        tbody.innerHTML = slice
            .map(function (c, i) {
                var rowNum = start + i + 1;
                var viewUrl = 'course-view.html?code=' + encodeURIComponent(c.code);
                return (
                    '<tr>' +
                    '<td data-label="#">' +
                    rowNum +
                    '</td>' +
                    '<td data-label="اسم المقرر">' +
                    escapeHtml(c.name) +
                    '</td>' +
                    '<td data-label="رمز المقرر"><code class="admin-code">' +
                    escapeHtml(c.code) +
                    '</code></td>' +
                    '<td data-label="البرنامج">' +
                    escapeHtml(c.programName || '—') +
                    '</td>' +
                    '<td data-label="الساعات">' +
                    escapeHtml(c.hours) +
                    '</td>' +
                    '<td data-label="المستوى">' +
                    escapeHtml(c.level || '—') +
                    '</td>' +
                    '<td class="admin-table-actions" data-label="إجراءات">' +
                    '<a href="' +
                    viewUrl +
                    '" class="admin-btn-primary admin-btn-primary--sm">عرض</a>' +
                    '</td>' +
                    '</tr>'
                );
            })
            .join('');

        if (!slice.length) {
            tbody.innerHTML =
                '<tr><td colspan="7" class="admin-table-empty">لا توجد نتائج مطابقة لمعايير البحث</td></tr>';
        }

        if (meta) {
            meta.textContent = '— نتائج البحث: ' + filtered.length + ' مقرر';
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
            filtered = COURSES.slice();
            currentPage = 1;
            render();
        }, 0);
    });

    render();
})();
