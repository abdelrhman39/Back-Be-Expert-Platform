/**
 * طلبات الخدمات الأكاديمية — بيانات تجريبية
 */
(function (global) {
    var PROGRAMS = [
        'دبلوم الأمن والسلامة المهنية',
        'دبلوم أمن المعلومات والتحول الرقمي',
        'دبلوم إدارة المشاريع الاحترافية',
        'دبلوم المحاسبة العامة',
        'دبلوم الإدارة المكتبية',
        'دبلوم إدارة الأعمال',
    ];

    var SEMESTERS = [
        { value: '2027-f1', label: 'فصل القبول الأول للعام الأكاديمي 2027-2028' },
        { value: '2026-f1', label: 'الفصل الأول للعام الدراسي 2026/2027' },
        { value: '2025-f1', label: 'الفصل الأول للعام الدراسي 2025/2026' },
        { value: '2024-f1', label: 'الفصل الأول للعام الدراسي 2024/2025' },
    ];

    var FIRST_NAMES = [
        'أحمد',
        'محمد',
        'فهد',
        'سعد',
        'نورة',
        'سارة',
        'ريم',
        'عبدالله',
        'خالد',
        'مريم',
    ];
    var LAST_NAMES = [
        'العتيبي',
        'القحطاني',
        'الشمري',
        'الدوسري',
        'الحربي',
        'الزهراني',
        'العنزي',
        'المطيري',
    ];

    function studentName(i) {
        return (
            FIRST_NAMES[i % FIRST_NAMES.length] +
            ' ' +
            LAST_NAMES[(i + 3) % LAST_NAMES.length] +
            ' ' +
            LAST_NAMES[i % LAST_NAMES.length]
        );
    }

    function studentId(i) {
        return String(1000000000 + (i % 900000000));
    }

    function dateAgo(i) {
        var opts = ['منذ 4 ساعات', 'منذ يوم', 'منذ يومين', 'منذ 3 أيام', 'منذ 23 ساعة', 'منذ 5 أيام'];
        return opts[i % opts.length];
    }

    function buildWithdrawal(count) {
        var rows = [];
        var i;
        for (i = 0; i < count; i++) {
            rows.push({
                id: 8000 + i,
                studentName: studentName(i),
                studentId: studentId(i),
                program: PROGRAMS[i % PROGRAMS.length],
                paymentMethod: 'دفع إلكتروني',
                status: 'processing',
                statusLabel: 'جاري العمل عليه',
                date: '2024-05-' + String(10 + (i % 18)).padStart(2, '0'),
                dateAgo: dateAgo(i),
            });
        }
        return rows;
    }

    var CHANGE_REASONS = [
        'رغبة في التخصص الأنسب لسوق العمل',
        'تعارض الجدول الدراسي مع العمل',
        'تغيير مسار مهني بعد استشارة أكاديمية',
    ];

    function buildProgramChange(count) {
        var rows = [];
        var i;
        for (i = 0; i < count; i++) {
            var approved = i % 7 === 0;
            var current = PROGRAMS[i % PROGRAMS.length];
            var next = PROGRAMS[(i + 2) % PROGRAMS.length];
            var row = {
                id: 9000 + i,
                requestNo: String(1779589387102000 + i),
                studentName: studentName(i + 2),
                studentId: studentId(i + 2),
                requestStatus: approved ? 'approved' : 'processing',
                requestStatusLabel: approved ? 'موافقة' : 'جاري العمل عليه',
                currentProgram: current,
                currentProgramFull: current + ' (دبلوم متوسط مهني)',
                currentDuration: i % 2 === 0 ? 'عامان دراسيان' : 'عام دراسي',
                newProgram: next,
                newProgramFull: next + ' (دبلوم متوسط مهني)',
                newDuration: 'عامان دراسيان',
                reason: CHANGE_REASONS[i % CHANGE_REASONS.length],
                date: '2024-06-' + String(1 + (i % 28)).padStart(2, '0'),
                addedAt: '2024-06-' + String(1 + (i % 28)).padStart(2, '0') + ' 10:15:00',
                dateAgo: dateAgo(i + 1),
            };
            if (i === 0) {
                row.id = 322;
                row.requestNo = '1779589387102157';
                row.studentProfileId = 102157;
                row.studentName = 'عبدالله علي صالح القيسي';
                row.currentProgram = 'دبلوم المحاسبة والإدارة المالية';
                row.currentProgramFull = 'دبلوم المحاسبة والإدارة المالية (دبلوم متوسط مهني)';
                row.newProgram = 'دبلوم إدارة الأعمال';
                row.newProgramFull = 'دبلوم إدارة الأعمال (دبلوم متوسط مهني)';
                row.reason =
                    'لم أتمكن من بدء الدراسة منذ تاريخ التسجيل في 12 مايو، وأطلب تغيير البرنامج مع استرداد الرسوم في حال عدم إمكانية الحل.';
                row.date = '2024-05-24';
                row.addedAt = '2024-05-24 05:23:07';
                row.dateAgo = 'منذ 12 ساعة';
            }
            rows.push(row);
        }
        return rows;
    }

    var programChangeList = buildProgramChange(162);

    function getProgramChangeById(id) {
        return programChangeList.find(function (r) {
            return String(r.id) === String(id);
        });
    }

    var EXCUSE_REASONS = [
        'مشاكل في تأمين دخول المحاضرات',
        'ظروف صحية تمنع الاستمرار في الفصل',
        'التزامات عمل لا تتفق مع الجدول الدراسي',
        'ظروف عائلية طارئة',
    ];

    function buildSemesterExcuse(count) {
        var rows = [];
        var i;
        for (i = 0; i < count; i++) {
            var reviewed = i % 3 === 0;
            var prog = PROGRAMS[(i + 1) % PROGRAMS.length];
            var row = {
                id: 7000 + i,
                studentName: studentName(i + 5),
                studentId: studentId(i + 5),
                requestNo: '1779' + String(10 + (i % 90)) + '/177989' + String(90 + i),
                addedBy: studentName(i + 5),
                program: prog,
                programFull: prog + ' (دبلوم متوسط مهني)',
                semester: SEMESTERS[i % SEMESTERS.length].label,
                reason: EXCUSE_REASONS[i % EXCUSE_REASONS.length],
                reviewStatus: reviewed ? 'reviewed' : 'pending',
                reviewLabel: reviewed ? 'تم المراجعة' : 'لم يراجع',
                status: 'pending',
                statusLabel: 'قيد المراجعة',
                date: '2026-05-' + String(10 + (i % 15)).padStart(2, '0'),
                addedAt: '2026-05-' + String(10 + (i % 15)).padStart(2, '0') + ' 14:30:00',
                dateAgo: dateAgo(i + 2),
                studentProfileId: i % 2 === 0 ? 102157 : 102288,
            };
            if (i === 0) {
                row.id = 288;
                row.requestNo = '177913/17798993';
                row.addedBy = 'بدر عيد سعود المطيري';
                row.studentName = 'بدر عيد سعود المطيري';
                row.studentProfileId = 102288;
                row.program = 'دبلوم أمن المعلومات';
                row.programFull = 'دبلوم أمن المعلومات (دبلوم متوسط مهني)';
                row.semester =
                    'فصل القبول الأول للعام الأكاديمي (2026 - 2027) - 2026/2027';
                row.reason = 'مشاكل في تأمين دخول المحاضرات';
                row.date = '2024-05-18';
                row.addedAt = '2024-05-18 23:40:17';
                row.dateAgo = 'منذ 5 أيام';
            }
            rows.push(row);
        }
        return rows;
    }

    var semesterExcuseList = buildSemesterExcuse(25);

    function getSemesterExcuseById(id) {
        return semesterExcuseList.find(function (r) {
            return String(r.id) === String(id);
        });
    }

    global.domainRequests = {
        semesters: SEMESTERS,
        programs: PROGRAMS.map(function (name) {
            return { value: name, label: name };
        }),
        deferral: [],
        withdrawal: buildWithdrawal(93),
        programChange: programChangeList,
        getProgramChangeById: getProgramChangeById,
        semesterExcuse: semesterExcuseList,
        getSemesterExcuseById: getSemesterExcuseById,
    };
})(typeof window !== 'undefined' ? window : this);
