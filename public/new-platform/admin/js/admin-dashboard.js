/**
 * رسوم بيانية لوحة التحكم — بيانات تجريبية
 */
(function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = '"IBM Plex Sans Arabic", system-ui, sans-serif';

    var palette = (typeof AdminShell !== 'undefined' && AdminShell.colors) ? AdminShell.colors() : {};
    Chart.defaults.color = palette.muted || '#5c6b64';

    var green = palette.green || '#1b8354';
    var blue = palette.info || '#2563eb';
    var gold = palette.gold || '#b8943f';
    var neutral = palette.neutral || '#94a3b8';
    var track = palette.track || '#eef2f6';

    function bindChartResize(chart) {
        if (!chart || !chart.canvas) return;
        var parent = chart.canvas.parentElement;
        if (!parent) return;
        function fit() {
            chart.resize();
        }
        requestAnimationFrame(fit);
        if (typeof ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(fit);
            ro.observe(parent);
        } else {
            window.addEventListener('resize', fit);
        }
    }

    var enrollmentEl = document.getElementById('chart-enrollment');
    if (enrollmentEl) {
        var enrollmentChart = new Chart(enrollmentEl, {
            type: 'bar',
            data: {
                labels: ['2022/1', '2022/2', '2023/1', '2023/2', '2024/1', '2024/2'],
                datasets: [{
                    label: 'المسجلون',
                    data: [320, 410, 380, 520, 490, 610],
                    backgroundColor: green,
                    borderRadius: 6,
                    maxBarThickness: 32,
                    categoryPercentage: 0.65,
                    barPercentage: 0.85,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: { top: 4, right: 6, bottom: 4, left: 6 },
                },
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: track },
                        ticks: { maxTicksLimit: 6 },
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8,
                            font: { size: 10 },
                        },
                    },
                },
            },
        });
        bindChartResize(enrollmentChart);
    }

    var statusEl = document.getElementById('chart-status');
    if (statusEl) {
        var statusChart = new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: ['نشط', 'متخرج', 'منسحب', 'موقوف', 'أخرى'],
                datasets: [{
                    data: [2130, 89, 42, 12, 4],
                    backgroundColor: [green, blue, neutral, gold, track],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 12, font: { size: 11 } },
                    },
                },
            },
        });
        bindChartResize(statusChart);
    }
})();
