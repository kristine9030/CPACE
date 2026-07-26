{{--
    Shared Chart.js setup for the Program Chair analytics pages.

    Include once inside <head>. Charts are registered with Viz.chart(id, config)
    and are only instantiated when their container is actually on screen — the
    analytics pages keep charts inside hidden tab panels, and Chart.js sizes a
    canvas to a zero-height container when it is rendered while display:none.
--}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    :root {
        /* Categorical slots — validated for colour-vision deficiency against a
           white chart surface (worst pair ΔE 25.4 deutan). Slot order is fixed:
           a series keeps its colour when a filter removes its neighbours. */
        --viz-1:#a32b2b;  /* brand maroon  */
        --viz-2:#2a78d6;  /* blue          */

        /* Ordinal ramp — one hue, light→dark, for ordered bands (easy→difficult). */
        --viz-ord-1:#e0a3a3; --viz-ord-2:#bd5f5f; --viz-ord-3:#7b1d1d;

        /* Status palette — reserved for state, never reused as a series colour.
           Always shipped with a text label, never colour alone. */
        --viz-good:#0ca30c; --viz-warn:#fab219; --viz-crit:#d03b3b;

        /* Chrome */
        --viz-surface:#ffffff; --viz-grid:#eceae4; --viz-axis:#c3c2b7;
        --viz-muted:#898781; --viz-ink:#52514e;
    }

    .viz-grid-layout { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .viz-card { background:var(--viz-surface); border:1px solid #eee; border-radius:14px; padding:20px; min-width:0; }
    .viz-card h4 { font-size:13px; font-weight:600; color:#1a1a1a; margin-bottom:4px; display:flex; align-items:center; gap:7px; }
    .viz-card h4 i { color:var(--primary); font-size:13px; }
    .viz-card .viz-sub { font-size:10.5px; color:#999; margin-bottom:14px; line-height:1.5; }
    .viz-card.full { grid-column:1 / -1; }

    /* Explicit heights: Chart.js runs with maintainAspectRatio:false, so the
       container — not the canvas attribute — decides the size. Heights include
       the x-axis label band so no card grows an inner scrollbar. */
    .chart-canvas-wrap { position:relative; width:100%; height:260px; }
    .chart-canvas-wrap.h-sm { height:200px; }
    .chart-canvas-wrap.h-md { height:300px; }
    .chart-canvas-wrap.h-lg { height:360px; }
    .chart-canvas-wrap.h-xl { height:440px; }
    .chart-canvas-wrap canvas { width:100%!important; }

    .viz-empty { display:flex; align-items:center; justify-content:center; height:100%;
        text-align:center; color:#b3b3b3; font-size:11.5px; padding:20px; }

    /* Compact table twin so every plotted value is readable without a tooltip. */
    .viz-table { width:100%; border-collapse:collapse; margin-top:14px; }
    .viz-table th { text-align:left; font-size:9.5px; color:#aaa; font-weight:600;
        text-transform:uppercase; letter-spacing:.3px; padding:0 8px 8px; }
    .viz-table td { padding:7px 8px; font-size:11px; color:#555; border-top:1px solid #f3f3f3;
        font-variant-numeric:tabular-nums; }
    .viz-table td.num { text-align:right; }
    .viz-legend { display:flex; flex-wrap:wrap; gap:12px; margin-top:12px; }
    .viz-legend span { display:inline-flex; align-items:center; gap:6px; font-size:10.5px; color:var(--viz-ink); }
    .viz-legend i.swatch { width:10px; height:10px; border-radius:3px; display:inline-block; }

    @media(max-width:1050px) { .viz-grid-layout { grid-template-columns:1fr; } }
</style>
<script>
window.Viz = (function () {
    const css = getComputedStyle(document.documentElement);
    const token = (name, fallback) => (css.getPropertyValue(name).trim() || fallback);

    const palette = {
        s1: token('--viz-1', '#a32b2b'),
        s2: token('--viz-2', '#2a78d6'),
        ordinal: [token('--viz-ord-1', '#e0a3a3'), token('--viz-ord-2', '#bd5f5f'), token('--viz-ord-3', '#7b1d1d')],
        good: token('--viz-good', '#0ca30c'),
        warn: token('--viz-warn', '#fab219'),
        crit: token('--viz-crit', '#d03b3b'),
        surface: token('--viz-surface', '#ffffff'),
        grid: token('--viz-grid', '#eceae4'),
        axis: token('--viz-axis', '#c3c2b7'),
        muted: token('--viz-muted', '#898781'),
        ink: token('--viz-ink', '#52514e'),
    };

    if (window.Chart) {
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.font.size = 10.5;
        Chart.defaults.color = palette.muted;
        Chart.defaults.maintainAspectRatio = false;
        Chart.defaults.responsive = true;
        Chart.defaults.animation.duration = 400;
        Chart.defaults.plugins.legend.labels.boxWidth = 10;
        Chart.defaults.plugins.legend.labels.boxHeight = 10;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
        Chart.defaults.plugins.legend.labels.padding = 14;
        Chart.defaults.plugins.legend.labels.color = palette.ink;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(17,17,17,.92)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.titleFont = { family: "'Poppins', sans-serif", size: 11, weight: '600' };
        Chart.defaults.plugins.tooltip.bodyFont = { family: "'Poppins', sans-serif", size: 11 };
        Chart.defaults.plugins.tooltip.displayColors = true;
        Chart.defaults.plugins.tooltip.boxPadding = 4;
    }

    /* Recessive hairline axes; solid, never dashed. */
    const axis = (extra = {}) => Object.assign({
        grid: { color: palette.grid, drawTicks: false, drawBorder: false },
        border: { display: false },
        ticks: { color: palette.muted, padding: 6 },
    }, extra);

    const catAxis = (extra = {}) => Object.assign({
        grid: { display: false },
        border: { color: palette.axis },
        ticks: { color: palette.ink, padding: 6 },
    }, extra);

    const percentAxis = (extra = {}) => axis(Object.assign({
        beginAtZero: true, max: 100, suggestedMax: 100,
        ticks: { color: palette.muted, padding: 6, stepSize: 25, callback: (v) => v + '%' },
    }, extra));

    const countAxis = (extra = {}) => axis(Object.assign({
        beginAtZero: true,
        ticks: { color: palette.muted, padding: 6, precision: 0 },
    }, extra));

    /* Bars: thin, rounded only at the data end, anchored to the baseline. */
    const bar = (over = {}) => Object.assign({
        borderRadius: 4, borderSkipped: 'start', maxBarThickness: 34,
    }, over);

    /* Stacked segments get a 2px surface gap rather than an outline. */
    const stacked = (over = {}) => Object.assign({
        borderRadius: 3, borderSkipped: false, maxBarThickness: 34,
        borderWidth: 2, borderColor: palette.surface,
    }, over);

    const line = (over = {}) => Object.assign({
        borderWidth: 2, tension: .32, pointRadius: 4, pointHoverRadius: 6,
        pointBorderColor: palette.surface, pointBorderWidth: 2,
    }, over);

    /**
     * Direct value labels at the end of horizontal bars. Selective by design:
     * used only where the bar length is the message and the axis alone would
     * force a tooltip hunt.
     */
    const endLabels = (format) => ({
        id: 'vizEndLabels',
        afterDatasetsDraw(chart) {
            const ctx = chart.ctx;
            const meta = chart.getDatasetMeta(0);
            ctx.save();
            ctx.font = "600 10.5px 'Poppins', sans-serif";
            ctx.fillStyle = palette.ink;
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'left';
            meta.data.forEach((element, index) => {
                const raw = chart.data.datasets[0].data[index];
                if (raw === null || raw === undefined) { return; }
                ctx.fillText((format ? format(raw) : raw), element.x + 7, element.y);
            });
            ctx.restore();
        },
    });

    /**
     * Per-bar reference marks (e.g. each subject's own passing threshold) drawn
     * as a solid rule across the bar. Pass `values` as a same-order array.
     */
    const referenceMarks = (values, color) => ({
        id: 'vizReferenceMarks',
        afterDatasetsDraw(chart) {
            const ctx = chart.ctx;
            const scale = chart.scales.x;
            ctx.save();
            ctx.strokeStyle = color || palette.ink;
            ctx.lineWidth = 2;
            chart.getDatasetMeta(0).data.forEach((element, index) => {
                const value = values[index];
                if (value === null || value === undefined) { return; }
                const px = scale.getPixelForValue(value);
                const half = ((element.height || 18) / 2) + 2;
                ctx.beginPath();
                ctx.moveTo(px, element.y - half);
                ctx.lineTo(px, element.y + half);
                ctx.stroke();
            });
            ctx.restore();
        },
    });

    const pending = new Map();
    const built = new Map();

    const onScreen = (el) => el.offsetParent !== null || el.getClientRects().length > 0;

    function instantiate(id, el, config) {
        try {
            built.set(id, new Chart(el, config));
        } catch (error) {
            console.error('[Viz] chart "' + id + '" failed to render', error);
        }
        pending.delete(id);
    }

    /** Register a chart. Renders now if visible, otherwise on the first reveal. */
    function chart(id, config) {
        const el = document.getElementById(id);
        if (!el) { return; }
        if (built.has(id)) { built.get(id).destroy(); built.delete(id); }
        if (onScreen(el)) { instantiate(id, el, config); } else { pending.set(id, { el, config }); }
    }

    /** Call after a container becomes visible (tab switch, accordion, print). */
    function reveal(root) {
        pending.forEach((entry, id) => {
            if (!root || root.contains(entry.el)) {
                if (onScreen(entry.el)) { instantiate(id, entry.el, entry.config); }
            }
        });
        // Only resize what is actually on screen: resizing a hidden canvas
        // collapses it to zero and it would stay that way until the next reveal.
        built.forEach((instance) => { if (onScreen(instance.canvas)) { instance.resize(); } });
    }

    window.addEventListener('beforeprint', () => reveal(document.body));

    return {
        chart, reveal, palette, axis, catAxis, percentAxis, countAxis,
        bar, stacked, line, endLabels, referenceMarks, get: (id) => built.get(id),
    };
})();

/** Tab switching shared by the analytics pages; reveals any deferred charts. */
function switchTab(id, trigger) {
    const button = trigger || (window.event && window.event.currentTarget);
    document.querySelectorAll('.tab-panel').forEach((panel) => panel.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach((btn) => {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
    });

    const panel = document.getElementById('tab-' + id);
    if (panel) { panel.classList.add('active'); }
    if (button) { button.classList.add('active'); button.setAttribute('aria-selected', 'true'); }
    if (panel && window.Viz) { window.Viz.reveal(panel); }
}
</script>
