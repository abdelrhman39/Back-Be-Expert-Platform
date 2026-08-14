/**
 * بيانات الشعب الدراسية — تجريبية حتى ربط API
 */
(function (global) {
    var PROGRAMS = [
        { code: 'OFF-201', name: 'دبلوم الإدارة المكتبية' },
        { code: 'ACC-301', name: 'دبلوم المحاسبة العامة' },
        { code: 'PMP-202', name: 'دبلوم إدارة المشاريع الاحترافية' },
        { code: 'BBA-103', name: 'دبلوم إدارة الأعمال' },
        { code: 'OSH-101', name: 'دبلوم الأمن والسلامة المهنية' },
    ];

    var COURSES = [
        { code: 'ENG-101', name: 'مهارات ومعلومات اللغة الانجليزية' },
        { code: 'ACC-110', name: 'مبادئ المحاسبة' },
        { code: 'MGT-120', name: 'مبادئ الإدارة' },
        { code: 'IT-130', name: 'مهارات الحاسب الآلي' },
        { code: 'COM-140', name: 'الاتصال الإداري' },
    ];

    var BATCHES = [
        { code: '251010', name: 'دفعة دبلوم الإدارة المكتبية عام 2026' },
        { code: '251009', name: 'دفعة دبلوم المحاسبة 2025' },
        { code: '251008', name: 'دفعة الأمن والسلامة 2025' },
    ];

    var SEMESTERS = [
        { value: '2026-f1', label: 'الفصل الأول للعام الدراسي 2026/2027' },
        { value: '2025-f2', label: 'الفصل الثاني للعام الدراسي 2025/2026' },
        { value: '2025-f1', label: 'الفصل الأول للعام الدراسي 2025/2026' },
        { value: '2024-f1', label: 'الفصل الأول للعام الدراسي 2024/2025' },
    ];

    var LEVELS = [
        { value: '1', label: 'المستوى الأول' },
        { value: '2', label: 'المستوى الثاني' },
        { value: '3', label: 'المستوى الثالث' },
    ];

    var PERIODS = [
        { value: 'morning', label: 'صباحي' },
        { value: 'evening', label: 'مسائي' },
    ];

    var FACULTY = [
        { value: 'f1', label: 'د. سعد العتيبي' },
        { value: 'f2', label: 'أ. سارة الدوسري' },
        { value: 'f3', label: 'م. فهد الشمري' },
        { value: 'f4', label: 'د. نورة القحطاني' },
    ];

    var SECTIONS = [];

    (function buildSections() {
        var i;
        for (i = 1; i <= 148; i++) {
            var prog = PROGRAMS[i % PROGRAMS.length];
            var course = COURSES[i % COURSES.length];
            var batch = BATCHES[i % BATCHES.length];
            var sem = SEMESTERS[i % SEMESTERS.length];
            var level = LEVELS[i % LEVELS.length];
            var period = PERIODS[i % PERIODS.length];
            var faculty = FACULTY[i % FACULTY.length];
            var num = String(i).padStart(3, '0');
            var status = i % 11 === 0 ? 'inactive' : 'active';
            var sectionName = 'شعبة ' + num + ' — ' + prog.name;
            var courseName = course.name;
            var semesterLabel = sem.label;
            var batchFull =
                batch.name + ' — ' + prog.name + ' (دبلوم متوسط مهني)';
            var supervisor = FACULTY[(i + 1) % FACULTY.length].label;

            if (i === 1) {
                sectionName = 'تسويق - 001 - دبلوم الإدارة المكتبية';
                courseName = 'مهارات ومفردات اللغة الإنجليزية';
                batchFull =
                    'دفعة دبلوم الإدارة المكتبية عامين — دبلوم الإدارة المكتبية (دبلوم متوسط مهني)';
                semesterLabel =
                    'فصل القبول الأول للعام الأكاديمي (2026-2027) — 2026/2027';
            }

            SECTIONS.push({
                id: 5000 + i,
                name: sectionName,
                subtitle: courseName,
                code: '24101' + String(22500 + i),
                maxCapacity: i === 1 ? 300 : 200 + (i % 50),
                batchFullLabel: batchFull,
                supervisor: supervisor,
                addedBy: 'مدير النظام',
                addedAt: '2023-04-07 12:50:35',
                addedAgo: 'منذ سنة',
                period: period.value,
                periodLabel: period.label,
                semester: sem.value,
                semesterLabel: semesterLabel,
                programCode: prog.code,
                programName: prog.name,
                courseCode: course.code,
                courseName: courseName,
                batchCode: batch.code,
                batchName: batch.name,
                level: level.value,
                levelLabel: level.label,
                faculty: faculty.value,
                facultyName: faculty.label,
                students: 12 + (i % 28),
                status: status,
            });
        }
    })();

    function getByCode(code) {
        return SECTIONS.find(function (s) {
            return s.code === code;
        });
    }

    function getById(id) {
        return SECTIONS.find(function (s) {
            return String(s.id) === String(id);
        });
    }

    function resolve(opts) {
        opts = opts || {};
        if (opts.code) return getByCode(opts.code);
        if (opts.id != null && opts.id !== '') return getById(opts.id);
        return null;
    }

    global.domainSections = {
        list: SECTIONS,
        programs: PROGRAMS,
        courses: COURSES,
        batches: BATCHES,
        semesters: SEMESTERS,
        levels: LEVELS,
        periods: PERIODS,
        faculty: FACULTY,
        getByCode: getByCode,
        getById: getById,
        resolve: resolve,
    };
})(typeof window !== 'undefined' ? window : this);
