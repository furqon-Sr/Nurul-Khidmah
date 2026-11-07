{{-- Chart.js + konfigurasi minimalis bersama (grid tipis, tooltip ringkas, legend sederhana, dukung mode gelap) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    window.NK = (() => {
        const mounted = [];
        const isDark = () => document.documentElement.classList.contains('dark');
        const theme = () => isDark()
            ? { text: '#CBD5E0', muted: '#A0AEC0', grid: '#2D3748', tooltip: '#F7FAFC', tooltipText: '#1A202C', surface: '#1A202C' }
            : { text: '#4A5568', muted: '#718096', grid: '#EDF2F7', tooltip: '#1A202C', tooltipText: '#FFFFFF', surface: '#FFFFFF' };

        const color = {
            primary: '#27A376', secondary: '#FFCF24', warning: '#FACC15', error: '#F75555', blue: '#0062FF',
            neutral: '#CBD5E0',
        };

        const defaults = () => {
            const t = theme();
            Chart.defaults.font.family = "'Manrope', ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = t.muted;
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 16, color: t.text, font: { size: 12, weight: 600 } },
                    },
                    tooltip: {
                        backgroundColor: t.tooltip, titleColor: t.tooltipText, bodyColor: t.tooltipText,
                        padding: 12, cornerRadius: 8, displayColors: false, titleFont: { weight: 700 },
                    },
                },
            };
        };

        const axes = () => {
            const t = theme();
            return {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: t.muted } },
                y: { beginAtZero: true, grid: { color: t.grid }, border: { display: false }, ticks: { color: t.muted, precision: 0 } },
            };
        };

        /** Pasang chart; dibangun ulang otomatis saat tema terang/gelap berganti. */
        const mount = (id, build) => {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            const entry = { canvas, build, chart: null };
            const render = () => {
                entry.chart?.destroy();
                entry.chart = new Chart(canvas, build({ defaults: defaults(), axes: axes(), theme: theme(), color }));
            };
            render();
            entry.render = render;
            mounted.push(entry);
        };

        let current = isDark();
        new MutationObserver(() => {
            if (isDark() !== current) { current = isDark(); mounted.forEach((m) => m.render()); }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        /** Plugin teks di tengah doughnut. */
        const centerText = (value, label) => ({
            id: 'centerText',
            afterDraw(chart) {
                const { ctx, chartArea: { left, right, top, bottom } } = chart;
                const t = theme();
                const x = (left + right) / 2, y = (top + bottom) / 2;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = isDark() ? '#F7FAFC' : '#1A202C';
                ctx.font = "800 32px 'Manrope', sans-serif";
                ctx.fillText(value, x, y - 8);
                ctx.fillStyle = t.muted;
                ctx.font = "600 12px 'Manrope', sans-serif";
                ctx.fillText(label, x, y + 18);
                ctx.restore();
            },
        });

        return { mount, color, theme, centerText };
    })();
</script>
