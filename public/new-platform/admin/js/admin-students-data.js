/**
 * بيانات الطلاب — تجريبية حتى ربط API
 */
(function (global) {
    var STUDENTS = [
        {
            id: 102157,
            nameAr: 'عبدالله علي صالح القيسي',
            nameEn: 'ABDULLAH ALI SALEH ALQUBAISI',
            academicId: '26102234',
            nationalId: '1088540792',
            mobile: '966546472008',
            email: 'abdullah2025@gmail.com',
            gender: 'ذكر',
            city: 'الرياض',
            nationality: 'سعودي',
            batch:
                'دبلوم المحاسبة والإدارة المالية — تأمين (فصل القبول الأول للعام الأكاديمي 2023-2024)',
            branch: 'النسائية',
            qualification: 'ثانوي',
            highSchoolPct: '75.08',
            gradYear: '1427',
            documents: [
                { id: 'qual', label: 'صورة المؤهل العلمي' },
                { id: 'nid', label: 'صورة الهوية الوطنية' },
            ],
            loginAllowed: true,
            studyStatus: 'مستمر دراسياً',
            addedBy: 'مدير النظام',
            joinedAt: '2024-05-16 20:22:01',
            joinedAgo: 'منذ أسبوع',
        },
        {
            id: 102288,
            nameAr: 'بدر عيد سعود المطيري',
            nameEn: 'BADR EID SAUD ALMUTAIRI',
            academicId: '26101880',
            nationalId: '1091234567',
            mobile: '966501234567',
            email: 'badr.mutairi@example.com',
            gender: 'ذكر',
            city: 'الامير مقرن',
            nationality: 'سعودي',
            batch: 'دبلوم أمن المعلومات (فصل القبول الأول للعام الأكاديمي 2026-2027)',
            branch: 'الامير مقرن — عن بُعد',
            qualification: 'ثانوي',
            highSchoolPct: '82.50',
            gradYear: '1445',
            documents: [
                { id: 'qual', label: 'صورة المؤهل العلمي' },
                { id: 'nid', label: 'صورة الهوية الوطنية' },
            ],
            loginAllowed: true,
            studyStatus: 'مستمر دراسياً',
            addedBy: 'مدير النظام',
            joinedAt: '2024-05-10 18:05:22',
            joinedAgo: 'منذ أسبوعين',
        },
    ];

    function getById(id) {
        return STUDENTS.find(function (s) {
            return String(s.id) === String(id);
        });
    }

    function getByNationalId(nid) {
        return STUDENTS.find(function (s) {
            return String(s.nationalId) === String(nid);
        });
    }

    function resolve(opts) {
        opts = opts || {};
        if (opts.id != null && opts.id !== '') return getById(opts.id);
        if (opts.nationalId) return getByNationalId(opts.nationalId);
        if (opts.academicId) {
            return STUDENTS.find(function (s) {
                return s.academicId === opts.academicId;
            });
        }
        return null;
    }

    global.domainStudents = {
        list: STUDENTS,
        getById: getById,
        resolve: resolve,
    };
})(typeof window !== 'undefined' ? window : this);
