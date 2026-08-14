/**
 * بيانات الدفعات الدراسية — تجريبية حتى ربط API
 */
(function (global) {
    var BATCHES = [
        {
            id: 401,
            name: 'دفعة دبلوم المحاسبة العامة — مسار التحصيلي 2026/2027',
            code: '251010',
            programCode: 'BBA-103',
            programName: 'دبلوم المحاسبة العامة',
            programDuration: 'عامان دراسيان',
            semester: 'الفصل الأول للعام الدراسي 2026/2027',
            semesterVal: '2026-f1',
            students: 172,
        },
        {
            id: 400,
            name: 'دفعة دبلوم إدارة المشاريع الاحترافية — خريف 2025',
            code: '251009',
            programCode: 'PMP-202',
            programName: 'دبلوم إدارة المشاريع الاحترافية',
            programDuration: 'عام دراسي',
            semester: 'الفصل الأول للعام الدراسي 2025/2026',
            semesterVal: '2025-f1',
            students: 103,
        },
        {
            id: 399,
            name: 'دفعة دبلوم الأمن والسلامة المهنية — 2025',
            code: '251008',
            programCode: 'OSH-101',
            programName: 'دبلوم الأمن والسلامة المهنية',
            programDuration: 'عام دراسي',
            semester: 'الفصل الأول للعام الدراسي 2025/2026',
            semesterVal: '2025-f1',
            students: 36,
        },
        {
            id: 398,
            name: 'دفعة دبلوم أمن المعلومات — ربيع 2025',
            code: '251007',
            programCode: 'CYB-102',
            programName: 'دبلوم أمن المعلومات',
            programDuration: '6 أشهر',
            semester: 'الفصل الثاني للعام الدراسي 2024/2025',
            semesterVal: '2025-s2',
            students: 58,
        },
        {
            id: 397,
            name: 'دفعة دبلوم إدارة الأعمال — مسائي 2024',
            code: '251006',
            programCode: 'BBA-103',
            programName: 'دبلوم إدارة الأعمال',
            programDuration: 'عامان دراسيان',
            semester: 'الفصل الأول للعام الدراسي 2024/2025',
            semesterVal: '2024-f1',
            students: 124,
        },
        {
            id: 396,
            name: 'دفعة دبلوم المحاسبة العامة — صباحي 2024',
            code: '251005',
            programCode: 'BBA-103',
            programName: 'دبلوم المحاسبة العامة',
            programDuration: 'عامان دراسيان',
            semester: 'الفصل الأول للعام الدراسي 2024/2025',
            semesterVal: '2024-f1',
            students: 89,
        },
        {
            id: 395,
            name: 'دفعة دبلوم إدارة المشاريع — دفعة تجريبية',
            code: '251004',
            programCode: 'PMP-202',
            programName: 'دبلوم إدارة المشاريع الاحترافية',
            programDuration: 'عام دراسي',
            semester: 'الفصل الثاني للعام الدراسي 2023/2024',
            semesterVal: '2024-s2',
            students: 0,
        },
        {
            id: 394,
            name: 'دفعة دبلوم الأمن والسلامة — خريف 2023',
            code: '251003',
            programCode: 'OSH-101',
            programName: 'دبلوم الأمن والسلامة المهنية',
            programDuration: 'عام دراسي',
            semester: 'الفصل الأول للعام الدراسي 2023/2024',
            semesterVal: '2023-f1',
            students: 67,
        },
        {
            id: 393,
            name: 'دفعة دبلوم أمن المعلومات — مسار مكثف',
            code: '251002',
            programCode: 'CYB-102',
            programName: 'دبلوم أمن المعلومات',
            programDuration: '6 أشهر',
            semester: 'الفصل الأول للعام الدراسي 2023/2024',
            semesterVal: '2023-f1',
            students: 41,
        },
        {
            id: 392,
            name: 'دفعة دبلوم إدارة الأعمال — عن بُعد',
            code: '251001',
            programCode: 'BBA-103',
            programName: 'دبلوم إدارة الأعمال',
            programDuration: 'عامان دراسيان',
            semester: 'الفصل الثاني للعام الدراسي 2022/2023',
            semesterVal: '2023-s2',
            students: 15,
        },
    ];

    function uniquePrograms() {
        var map = {};
        BATCHES.forEach(function (b) {
            if (!map[b.programCode]) {
                map[b.programCode] = { code: b.programCode, name: b.programName };
            }
        });
        return Object.keys(map).map(function (k) {
            return map[k];
        });
    }

    function uniqueSemesters() {
        var map = {};
        BATCHES.forEach(function (b) {
            if (!map[b.semesterVal]) {
                map[b.semesterVal] = b.semester;
            }
        });
        return Object.keys(map).map(function (k) {
            return { value: k, label: map[k] };
        });
    }

    global.domainBatches = {
        list: BATCHES,
        programs: uniquePrograms,
        semesters: uniqueSemesters,
    };
})(typeof window !== 'undefined' ? window : this);
