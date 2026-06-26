// Grafik dashboard — ApexCharts (dimuat dinamis hanya bila ada grafik)
const TEAL = '#009177';        // primary brand (#009177)
const FONT = "'Inter', ui-sans-serif, system-ui, sans-serif";

const base = {
    chart: { fontFamily: FONT, toolbar: { show: false }, animations: { speed: 500 } },
    grid: { borderColor: '#eef2f7', strokeDashArray: 4 },
    tooltip: { theme: 'light' },
    dataLabels: { enabled: false },
};

function buatStatus(el, cfg) {
    return {
        ...base,
        chart: { ...base.chart, type: 'donut', height: 260 },
        series: [cfg.draf || 0, cfg.berlaku || 0, cfg.kadaluarsa || 0, cfg.dicabut || 0],
        labels: ['Draf', 'Berlaku', 'Kadaluarsa', 'Dicabut'],
        colors: ['#E6B450', TEAL, '#E57373', '#A8B2C0'],
        legend: { position: 'bottom', fontWeight: 600 },
        stroke: { width: 2 },
        plotOptions: {
            pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', fontWeight: 600 } } } },
        },
    };
}

function buatKategori(el, cfg) {
    const jumlah = (cfg.labels || []).length || 1;

    return {
        ...base,
        chart: { ...base.chart, type: 'bar', height: Math.max(200, jumlah * 44) },
        series: [{ name: 'Dokumen', data: cfg.data }],
        xaxis: {
            categories: cfg.labels,
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { show: false },        // nilai sudah tampil di ujung batang
        },
        yaxis: { labels: { style: { fontWeight: 600, colors: '#475569', fontSize: '12px' } } },
        grid: { ...base.grid, borderColor: '#f1f5f9', xaxis: { lines: { show: false } }, padding: { right: 28, left: 4 } },
        colors: [TEAL],
        plotOptions: {
            bar: { horizontal: true, borderRadius: 6, borderRadiusApplication: 'end', barHeight: '58%', distributed: false },
        },
        states: { hover: { filter: { type: 'darken', value: 0.9 } } },
        dataLabels: {
            enabled: true,
            textAnchor: 'start',
            offsetX: 8,
            style: { fontSize: '12px', fontWeight: 700, colors: ['#334155'] },
            formatter: (val) => val,
        },
        tooltip: { theme: 'light', y: { formatter: (v) => `${v} dokumen` } },
        noData: { text: 'Belum ada data kategori', style: { color: '#94a3b8' } },
    };
}

const builder = { status: buatStatus, kategori: buatKategori };

function render(el, ApexCharts) {
    const tipe = el.dataset.chart;
    const make = builder[tipe];
    if (!make) return;

    let cfg;
    try {
        cfg = JSON.parse(el.dataset.config);
    } catch {
        return;
    }

    new ApexCharts(el, make(el, cfg)).render();
}

export async function initCharts(root = document) {
    const els = [...root.querySelectorAll('[data-chart]')].filter((el) => !el._chartReady);
    if (!els.length) return;

    const { default: ApexCharts } = await import('apexcharts'); // chunk terpisah
    els.forEach((el) => {
        el._chartReady = true;
        render(el, ApexCharts);
    });
}

if (document.readyState !== 'loading') {
    initCharts();
} else {
    document.addEventListener('DOMContentLoaded', () => initCharts());
}

window.initCharts = initCharts;
