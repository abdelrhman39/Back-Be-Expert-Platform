(function () {
    var palette = (typeof AdminShell !== 'undefined' && AdminShell.colors) ? AdminShell.colors() : {};
    var chartColors = [
        palette.green || '#1b8354',
        palette.info || '#2563eb',
        palette.gold || '#b8943f',
        palette.warning || '#f59e0b',
        palette.neutral || '#94a3b8',
    ];

    function initAgeChart() {
        if (typeof Chart === 'undefined') return;
        var ageEl = document.getElementById('chart-age');
        if (!ageEl) return;

        Chart.defaults.font.family = '"IBM Plex Sans Arabic", system-ui, sans-serif';
        Chart.defaults.color = palette.muted || '#5c6b64';

        new Chart(ageEl, {
            type: 'doughnut',
            data: {
                labels: ['أقل من 20', '20–30', '30–40', '40–50', '50+'],
                datasets: [{
                    data: [412, 986, 548, 248, 84],
                    backgroundColor: chartColors,
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 10, font: { size: 11 } },
                    },
                },
            },
        });
    }

    function colorForValue(value, min, max) {
        if (max <= min) return '#e8f4ee';
        var t = (value - min) / (max - min);
        var stops = [
            { t: 0, c: [232, 244, 238] },
            { t: 0.35, c: [168, 214, 188] },
            { t: 0.65, c: [45, 150, 100] },
            { t: 1, c: [22, 93, 49] },
        ];
        var i = 0;
        while (i < stops.length - 1 && t > stops[i + 1].t) i++;
        var a = stops[i];
        var b = stops[Math.min(i + 1, stops.length - 1)];
        var local = b.t === a.t ? 0 : (t - a.t) / (b.t - a.t);
        var r = Math.round(a.c[0] + (b.c[0] - a.c[0]) * local);
        var g = Math.round(a.c[1] + (b.c[1] - a.c[1]) * local);
        var bl = Math.round(a.c[2] + (b.c[2] - a.c[2]) * local);
        return 'rgb(' + r + ',' + g + ',' + bl + ')';
    }

    function formatNum(n) {
        return Number(n).toLocaleString('ar-SA');
    }

    function initSaudiMap() {
        var root = document.getElementById('saudi-enrollment-map');
        var viz = document.getElementById('sa-map-viz');
        var regionsList = document.getElementById('sa-map-regions-list');
        var data = typeof SaudiMapData !== 'undefined' ? SaudiMapData : null;

        if (!root || !viz) return;

        if (!data || !data.regions || !data.regions.length) {
            viz.innerHTML = '<p class="sa-map__error">تعذّر تحميل بيانات الخريطة. حدّث الصفحة.</p>';
            return;
        }

        var sortedRegions = data.regions.slice().sort(function (a, b) {
            return b.count - a.count;
        });
        var counts = data.regions.map(function (r) {
            return r.count;
        });
        var min = Math.min.apply(null, counts);
        var max = Math.max.apply(null, counts);
        var total = counts.reduce(function (a, b) {
            return a + b;
        }, 0);

        var tooltip = document.createElement('div');
        tooltip.className = 'sa-map__tooltip';
        tooltip.setAttribute('role', 'status');
        tooltip.hidden = true;
        root.appendChild(tooltip);

        var detail = document.createElement('div');
        detail.className = 'sa-map__detail';
        detail.id = 'sa-map-detail';
        detail.innerHTML = '<span class="sa-map__detail-hint">اختر منطقة من الخريطة أو القائمة</span>';
        root.appendChild(detail);

        var svgNS = 'http://www.w3.org/2000/svg';
        var svg = document.createElementNS(svgNS, 'svg');
        svg.setAttribute('class', 'sa-map__svg');
        svg.setAttribute('viewBox', data.viewBox);
        svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        svg.setAttribute('role', 'img');
        svg.setAttribute('aria-label', 'خريطة المملكة العربية السعودية — توزيع المتدربين');

        var gRegions = document.createElementNS(svgNS, 'g');
        gRegions.setAttribute('class', 'sa-map__regions-layer');
        var regionEls = {};

        function selectRegion(id) {
            Object.keys(regionEls).forEach(function (key) {
                regionEls[key].classList.toggle('is-selected', key === id);
            });
            root.querySelectorAll('.sa-map__city').forEach(function (el) {
                el.classList.toggle('is-highlighted', el.getAttribute('data-region') === id);
            });
            if (regionsList) {
                regionsList.querySelectorAll('.sa-map__region-btn').forEach(function (btn) {
                    btn.classList.toggle('is-active', btn.getAttribute('data-region') === id);
                });
            }

            var region = data.regions.find(function (r) {
                return r.id === id;
            });
            if (!region) return;

            var pct = total ? ((region.count / total) * 100).toFixed(1) : '0';
            var citiesIn = (data.cities || [])
                .filter(function (c) {
                    return c.regionId === id;
                })
                .map(function (c) {
                    return c.name + ' (' + formatNum(c.count) + ')';
                })
                .join(' · ');

            detail.innerHTML =
                '<strong>' +
                region.nameAr +
                '</strong>' +
                '<div class="sa-map__detail-stats">' +
                '<span><em>' +
                formatNum(region.count) +
                '</em> متدرب</span>' +
                '<span><em>' +
                pct +
                '%</em> من الإجمالي</span>' +
                '</div>' +
                (citiesIn ? '<p class="sa-map__detail-cities">أبرز المدن: ' + citiesIn + '</p>' : '');

            highlightCityList(region);
        }

        function highlightCityList(region) {
            var list = document.querySelector('.dash-cities-list');
            if (!list) return;
            var cityNames = (data.cities || [])
                .filter(function (c) {
                    return c.regionId === region.id;
                })
                .map(function (c) {
                    return c.name;
                });
            list.querySelectorAll('li').forEach(function (li) {
                var nameEl = li.querySelector('span');
                var name = nameEl ? nameEl.textContent.trim() : '';
                var match =
                    cityNames.indexOf(name) !== -1 ||
                    (region.id === 'mecca' && (name === 'جدة' || name === 'مكة المكرمة'));
                li.classList.toggle('is-map-active', match);
            });
        }

        function positionTooltip(ev) {
            var rect = root.getBoundingClientRect();
            var x = (ev.clientX || (ev.touches && ev.touches[0].clientX) || rect.left) - rect.left;
            var y = (ev.clientY || (ev.touches && ev.touches[0].clientY) || rect.top) - rect.top;
            tooltip.style.left = Math.min(Math.max(x, 12), rect.width - 12) + 'px';
            tooltip.style.top = Math.max(y - 8, 12) + 'px';
        }

        data.regions.forEach(function (region) {
            var path = document.createElementNS(svgNS, 'path');
            path.setAttribute('d', region.path);
            path.setAttribute('class', 'sa-map__region');
            path.setAttribute('data-id', region.id);
            path.setAttribute('fill', colorForValue(region.count, min, max));
            path.setAttribute('stroke', '#ffffff');
            path.setAttribute('stroke-width', '1.25');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('tabindex', '0');
            path.setAttribute('role', 'button');
            var pctLabel = total ? ((region.count / total) * 100).toFixed(1) : '0';
            path.setAttribute(
                'aria-label',
                region.nameAr + ' — ' + formatNum(region.count) + ' متدرب، ' + pctLabel + '%'
            );

            function showTip(ev) {
                tooltip.hidden = false;
                tooltip.innerHTML =
                    '<strong>' +
                    region.nameAr +
                    '</strong>' +
                    '<span class="sa-map__tooltip-count">' +
                    formatNum(region.count) +
                    ' متدرب</span>' +
                    '<span class="sa-map__tooltip-pct">' +
                    pctLabel +
                    '% من الإجمالي</span>';
                positionTooltip(ev);
            }

            function hideTip() {
                tooltip.hidden = true;
            }

            path.addEventListener('mouseenter', showTip);
            path.addEventListener('mousemove', positionTooltip);
            path.addEventListener('mouseleave', hideTip);
            path.addEventListener('focus', showTip);
            path.addEventListener('blur', hideTip);

            path.addEventListener('click', function () {
                selectRegion(region.id);
            });
            path.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectRegion(region.id);
                }
            });

            regionEls[region.id] = path;
            gRegions.appendChild(path);
        });

        svg.appendChild(gRegions);

        var gCities = document.createElementNS(svgNS, 'g');
        gCities.setAttribute('class', 'sa-map__cities-layer');

        (data.cities || []).forEach(function (city, idx) {
            var g = document.createElementNS(svgNS, 'g');
            g.setAttribute('class', 'sa-map__city');
            g.setAttribute('data-region', city.regionId);
            g.setAttribute('transform', 'translate(' + city.x + ',' + city.y + ')');

            var ring = document.createElementNS(svgNS, 'circle');
            ring.setAttribute('r', '12');
            ring.setAttribute('class', 'sa-map__city-pulse');
            ring.setAttribute('fill', 'rgba(27, 131, 84, 0.18)');

            var dot = document.createElementNS(svgNS, 'circle');
            dot.setAttribute('r', '5');
            dot.setAttribute('class', 'sa-map__city-dot');
            dot.setAttribute('fill', '#1b8354');
            dot.setAttribute('stroke', '#fff');
            dot.setAttribute('stroke-width', '2');

            var label = document.createElementNS(svgNS, 'text');
            label.setAttribute('class', 'sa-map__city-label');
            label.setAttribute('y', '-14');
            label.setAttribute('text-anchor', 'middle');
            label.textContent = city.name;

            var countTag = document.createElementNS(svgNS, 'text');
            countTag.setAttribute('class', 'sa-map__city-count');
            countTag.setAttribute('y', '18');
            countTag.setAttribute('text-anchor', 'middle');
            countTag.textContent = formatNum(city.count);

            g.appendChild(ring);
            g.appendChild(dot);
            g.appendChild(label);
            g.appendChild(countTag);
            g.setAttribute('tabindex', '0');
            g.setAttribute('role', 'button');
            g.setAttribute('aria-label', city.name + ' — ' + formatNum(city.count) + ' متدرب');

            g.addEventListener('click', function () {
                selectRegion(city.regionId);
            });
            g.addEventListener('mouseenter', function () {
                selectRegion(city.regionId);
            });
            g.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectRegion(city.regionId);
                }
            });

            gCities.appendChild(g);
        });

        svg.appendChild(gCities);
        viz.appendChild(svg);

        if (regionsList) {
            regionsList.innerHTML =
                '<p class="sa-map__regions-title">المناطق حسب عدد المتدربين</p><ul class="sa-map__regions-ul"></ul>';
            var ul = regionsList.querySelector('.sa-map__regions-ul');
            sortedRegions.forEach(function (region) {
                var pct = total ? ((region.count / total) * 100).toFixed(1) : '0';
                var li = document.createElement('li');
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'sa-map__region-btn';
                btn.setAttribute('data-region', region.id);
                btn.innerHTML =
                    '<span class="sa-map__region-btn-name">' +
                    region.nameAr.replace('منطقة ', '') +
                    '</span>' +
                    '<span class="sa-map__region-btn-meta">' +
                    '<strong>' +
                    formatNum(region.count) +
                    '</strong> · ' +
                    pct +
                    '%</span>' +
                    '<span class="sa-map__region-btn-bar"><i style="width:' +
                    pct +
                    '%"></i></span>';
                btn.addEventListener('click', function () {
                    selectRegion(region.id);
                });
                li.appendChild(btn);
                ul.appendChild(li);
            });
        }

        var legend = document.createElement('div');
        legend.className = 'sa-map__legend';
        legend.innerHTML =
            '<span class="sa-map__legend-title">كثافة التسجيل</span>' +
            '<div class="sa-map__legend-bar" aria-hidden="true"></div>' +
            '<div class="sa-map__legend-labels"><span>أقل</span><span>أكثر</span></div>';
        viz.appendChild(legend);

        var summary = document.createElement('div');
        summary.className = 'sa-map__summary';
        summary.innerHTML =
            '<span class="sa-map__summary-label">إجمالي المتدربين</span>' +
            '<strong class="sa-map__summary-value">' +
            formatNum(total) +
            '</strong>';
        viz.appendChild(summary);

        var cityToRegion = {};
        (data.cities || []).forEach(function (c) {
            cityToRegion[c.name] = c.regionId;
        });
        cityToRegion['مكة المكرمة'] = 'mecca';

        document.querySelectorAll('.dash-cities-list li').forEach(function (li) {
            li.setAttribute('tabindex', '0');
            li.setAttribute('role', 'button');
            li.classList.add('sa-map__city-list-item');
            var nameEl = li.querySelector('span');
            var cityName = nameEl ? nameEl.textContent.trim() : '';
            var regionId = cityToRegion[cityName];
            if (regionId) li.setAttribute('data-region', regionId);

            function activateFromList() {
                if (regionId) selectRegion(regionId);
            }
            li.addEventListener('click', activateFromList);
            li.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    activateFromList();
                }
            });
        });

        selectRegion('ryiadh');
    }

    initAgeChart();
    initSaudiMap();
})();
