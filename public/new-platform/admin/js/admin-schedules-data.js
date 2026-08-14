/**
 * بيانات الجداول الدراسية — تجريبية حتى ربط API
 */
(function (global) {
    var FILTER_OPTIONS = {
        semesters: [
            {
                value: '2024-f1',
                label: 'الفصل الدراسي الأول للعام الأكاديمي (2024-2025)',
            },
            {
                value: '2025-f1',
                label: 'الفصل الدراسي الأول للعام الأكاديمي (2025-2026)',
            },
        ],
        batches: [
            { value: 'off-2024', label: 'دفعة دبلوم الإدارة المكتبية عام', semester: '2024-f1' },
            { value: 'acc-2025', label: 'دفعة دبلوم المحاسبة العامة', semester: '2025-f1' },
        ],
        levels: [
            { value: '1', label: 'المستوى الأول' },
            { value: '2', label: 'المستوى الثاني' },
        ],
        periods: [
            { value: 'morning', label: 'صباحي' },
            { value: 'evening', label: 'مسائي' },
        ],
    };

    var TRAINERS = [
        { value: '', label: '-- اختر --' },
        { value: 't1', label: 'د. سعد العتيبي' },
        { value: 't2', label: 'أ. سارة الدوسري' },
        { value: 't3', label: 'م. فهد الشمري' },
        { value: 't4', label: 'د. نورة القحطاني' },
    ];

    var DAYS = [
        { value: '', label: '-- اليوم --' },
        { value: 'sun', label: 'الأحد' },
        { value: 'mon', label: 'الإثنين' },
        { value: 'tue', label: 'الثلاثاء' },
        { value: 'wed', label: 'الأربعاء' },
        { value: 'thu', label: 'الخميس' },
    ];

    var SCHEDULE_ROWS = {
        '2024-f1|off-2024|1|morning': [
            {
                sectionCode: '2410122501',
                sectionTitle: 'شعبة 001 — دبلوم الإدارة المكتبية',
                courseName: 'مهارات ومعلومات اللغة الانجليزية',
                trainer: '',
                day: '',
                timeStart: '',
                timeEnd: '',
            },
            {
                sectionCode: '2410122502',
                sectionTitle: 'شعبة 002 — دبلوم الإدارة المكتبية',
                courseName: 'مبادئ المحاسبة',
                trainer: 't2',
                day: 'sun',
                timeStart: '08:00',
                timeEnd: '10:00',
            },
            {
                sectionCode: '2410122503',
                sectionTitle: 'شعبة 003 — دبلوم الإدارة المكتبية',
                courseName: 'مهارات الحاسب الآلي',
                trainer: '',
                day: '',
                timeStart: '',
                timeEnd: '',
            },
            {
                sectionCode: '2410122504',
                sectionTitle: 'شعبة 004 — دبلوم الإدارة المكتبية',
                courseName: 'مبادئ الإدارة',
                trainer: 't1',
                day: 'tue',
                timeStart: '10:00',
                timeEnd: '12:00',
            },
            {
                sectionCode: '2410122505',
                sectionTitle: 'شعبة 005 — دبلوم الإدارة المكتبية',
                courseName: 'الاتصال الإداري',
                trainer: '',
                day: '',
                timeStart: '',
                timeEnd: '',
            },
        ],
        '2025-f1|acc-2025|1|morning': [
            {
                sectionCode: '2510122601',
                sectionTitle: 'شعبة 101 — دبلوم المحاسبة العامة',
                courseName: 'مبادئ المحاسبة المالية',
                trainer: 't3',
                day: 'mon',
                timeStart: '09:00',
                timeEnd: '11:00',
            },
            {
                sectionCode: '2510122602',
                sectionTitle: 'شعبة 102 — دبلوم المحاسبة العامة',
                courseName: 'محاسبة التكاليف',
                trainer: '',
                day: '',
                timeStart: '',
                timeEnd: '',
            },
        ],
    };

    function filterKey(semester, batch, level, period) {
        return [semester, batch, level, period].join('|');
    }

    global.domainSchedules = {
        filterOptions: FILTER_OPTIONS,
        trainers: TRAINERS,
        days: DAYS,
        getRows: function (semester, batch, level, period) {
            var key = filterKey(semester, batch, level, period);
            return (SCHEDULE_ROWS[key] || []).slice();
        },
        filterKey: filterKey,
    };
})(typeof window !== 'undefined' ? window : this);
