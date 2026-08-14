/**
 * الجداول الدراسية — اختيار الفلاتر وجدول الشعب
 */
(function () {
    var data = window.domainSchedules;
    if (!data) return;

    var filterForm = document.getElementById('schedules-filter-form');
    var tbody = document.getElementById('schedules-tbody');
    var tableTitle = document.getElementById('schedules-table-title');
    var saveBtn = document.getElementById('schedules-save-btn');
    var tableCard = document.getElementById('schedules-table-card');

    if (!filterForm || !tbody) return;

    var rows = [];

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function optionsHtml(list, selected) {
        return list
            .map(function (o) {
                return (
                    '<option value="' +
                    escapeHtml(o.value) +
                    '"' +
                    (o.value === selected ? ' selected' : '') +
                    '>' +
                    escapeHtml(o.label) +
                    '</option>'
                );
            })
            .join('');
    }

    function sectionCell(row) {
        return (
            '<div class="admin-section-cell">' +
            '<code class="admin-code admin-code--block">' +
            escapeHtml(row.sectionCode) +
            '</code>' +
            '<span class="admin-section-cell__title">' +
            escapeHtml(row.sectionTitle) +
            '</span>' +
            '</div>'
        );
    }

    function timeCell(row, index) {
        return (
            '<div class="admin-time-range">' +
            '<label class="visually-hidden" for="time-start-' +
            index +
            '">من</label>' +
            '<input type="time" class="admin-control admin-control--time" id="time-start-' +
            index +
            '" data-field="timeStart" data-row="' +
            index +
            '" value="' +
            escapeHtml(row.timeStart || '') +
            '">' +
            '<span class="admin-time-range__sep" aria-hidden="true">–</span>' +
            '<label class="visually-hidden" for="time-end-' +
            index +
            '">إلى</label>' +
            '<input type="time" class="admin-control admin-control--time" id="time-end-' +
            index +
            '" data-field="timeEnd" data-row="' +
            index +
            '" value="' +
            escapeHtml(row.timeEnd || '') +
            '">' +
            '</div>'
        );
    }

    function renderTable() {
        if (!rows.length) {
            tbody.innerHTML =
                '<tr><td colspan="6" class="admin-table-empty">لا توجد شعب لهذا الاختيار — غيّر الفلاتر أو اختر مجموعة أخرى</td></tr>';
            if (tableTitle) tableTitle.textContent = 'جداول الشعب (0)';
            if (tableCard) tableCard.hidden = false;
            return;
        }

        tbody.innerHTML = rows
            .map(function (row, i) {
                return (
                    '<tr data-schedule-row="' +
                    i +
                    '">' +
                    '<td data-label="#">' +
                    (i + 1) +
                    '</td>' +
                    '<td data-label="الشعبة">' +
                    sectionCell(row) +
                    '</td>' +
                    '<td data-label="المقرر">' +
                    escapeHtml(row.courseName) +
                    '</td>' +
                    '<td data-label="المدرب">' +
                    '<select class="admin-control admin-control--table" data-field="trainer" data-row="' +
                    i +
                    '">' +
                    optionsHtml(data.trainers, row.trainer) +
                    '</select></td>' +
                    '<td data-label="اليوم">' +
                    '<select class="admin-control admin-control--table" data-field="day" data-row="' +
                    i +
                    '">' +
                    optionsHtml(data.days, row.day) +
                    '</select></td>' +
                    '<td data-label="الوقت">' +
                    timeCell(row, i) +
                    '</td>' +
                    '</tr>'
                );
            })
            .join('');

        if (tableTitle) tableTitle.textContent = 'جداول الشعب (' + rows.length + ')';
        if (tableCard) tableCard.hidden = false;

        bindRowInputs();
    }

    function bindRowInputs() {
        tbody.querySelectorAll('[data-field]').forEach(function (el) {
            el.addEventListener('change', function () {
                var idx = parseInt(el.getAttribute('data-row'), 10);
                var field = el.getAttribute('data-field');
                if (rows[idx]) rows[idx][field] = el.value;
            });
        });
    }

    function loadRows() {
        var semester = filterForm.semester.value;
        var batch = filterForm.batch.value;
        var level = filterForm.level.value;
        var period = filterForm.period.value;

        if (!semester || !batch || !level || !period) {
            rows = [];
            if (tableCard) tableCard.hidden = true;
            return;
        }

        rows = data.getRows(semester, batch, level, period);
        renderTable();
    }

    function filterBatches() {
        var semester = filterForm.semester.value;
        var batchSel = filterForm.batch;
        var current = batchSel.value;
        batchSel.innerHTML = '<option value="">-- اختر الدفعة --</option>';
        data.filterOptions.batches
            .filter(function (b) {
                return !semester || b.semester === semester;
            })
            .forEach(function (b) {
                var opt = document.createElement('option');
                opt.value = b.value;
                opt.textContent = b.label;
                batchSel.appendChild(opt);
            });
        if ([].some.call(batchSel.options, function (o) { return o.value === current; })) {
            batchSel.value = current;
        }
    }

    filterForm.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'semester') filterBatches();
        loadRows();
    });

    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadRows();
    });

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            if (!rows.length) {
                alert('اختر الفصل والدفعة والمستوى والفترة أولاً');
                return;
            }
            alert('تم حفظ جدول الشعب — ' + rows.length + ' شعبة (تجريبي)');
        });
    }

    filterBatches();
    loadRows();
})();
