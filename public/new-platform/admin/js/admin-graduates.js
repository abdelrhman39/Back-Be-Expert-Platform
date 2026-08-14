/**
 * رسوم صفحة الخريجين — بيانات تجريبية (فارغة حتى ربط API)
 */
(function () {
    if (typeof Chart === 'undefined') return;

    var fontFamily = "'IBM Plex Sans Arabic', 'Segoe UI', Tahoma, sans-serif";
    var gridColor = 'rgba(0, 0, 0, 0.06)';
    var palette = (typeof AdminShell !== 'undefined' && AdminShell.colors) ? AdminShell.colors() : {};
    var muted = palette.muted || '#5c6b64';
    var green = palette.green || '#1b8354';
    var neutral = palette.neutral || '#94a3b8';
    var warning = palette.warning || '#f59e0b';
    var info = palette.info || '#2563eb';

    var programs = [
        'دبلوم الأمن والسلامة',
        'دبلوم أمن المعلومات',
        'دبلوم إدارة الأعمال',
        'دبلوم الموارد البشرية',
        'ممارس الذكاء الاصطناعي',
        'شهادة إدارة المشاريع',
    ];

    var baseOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { font: { family: fontFamily }, color: muted } },
        },
        scales: {
            x: {
                ticks: { font: { family: fontFamily, size: 10 }, color: muted, maxRotation: 45, minRotation: 45 },
                grid: { color: gridColor },
            },
            y: {
                beginAtZero: true,
                ticks: { font: { family: fontFamily }, color: muted, stepSize: 1 },
                grid: { color: gridColor },
            },
        },
    };

    var issuedEl = document.getElementById('chart-issued');
    if (issuedEl) {
        new Chart(issuedEl, {
            type: 'bar',
            data: {
                labels: programs,
                datasets: [{
                    label: 'شهادات صادرة',
                    data: programs.map(function () { return 0; }),
                    backgroundColor: green,
                    borderRadius: 4,
                    maxBarThickness: 36,
                }],
            },
            options: baseOpts,
        });
    }

    var progEl = document.getElementById('chart-program-grads');
    if (progEl) {
        new Chart(progEl, {
            type: 'bar',
            data: {
                labels: programs,
                datasets: [
                    { label: 'مؤهل', data: programs.map(function () { return 0; }), backgroundColor: neutral, maxBarThickness: 28 },
                    { label: 'مراجعة', data: programs.map(function () { return 0; }), backgroundColor: warning, maxBarThickness: 28 },
                    { label: 'معتمد', data: programs.map(function () { return 0; }), backgroundColor: info, maxBarThickness: 28 },
                    { label: 'صادر', data: programs.map(function () { return 0; }), backgroundColor: green, maxBarThickness: 28 },
                ],
            },
            options: Object.assign({}, baseOpts, {
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: fontFamily }, color: muted, boxWidth: 12 },
                    },
                },
                scales: Object.assign({}, baseOpts.scales, {
                    x: { stacked: true, ticks: baseOpts.scales.x.ticks, grid: baseOpts.scales.x.grid },
                    y: { stacked: true, beginAtZero: true, ticks: baseOpts.scales.y.ticks, grid: baseOpts.scales.y.grid },
                }),
            }),
        });
    }
})();
