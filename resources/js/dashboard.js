import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Filler,
    Legend,
    Tooltip,
);

const currencyFormatter = new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const chartColors = {
    purchase: '#0f766e',
    payment: '#64748b',
    outstanding: '#115e59',
    grid: '#e2e8f0',
};

function currencyTick(value) {
    return currencyFormatter.format(value);
}

function buildMonthlyComparisonChart(canvas, data) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Purchases',
                    data: data.purchases,
                    backgroundColor: chartColors.purchase,
                    borderRadius: 8,
                },
                {
                    label: 'Payments',
                    data: data.payments,
                    backgroundColor: chartColors.payment,
                    borderRadius: 8,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: { callback: currencyTick },
                    grid: { color: chartColors.grid },
                },
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `${context.dataset.label}: ${currencyFormatter.format(context.parsed.y)}`;
                        },
                    },
                },
            },
        },
    });
}

function buildOutstandingTrendChart(canvas, data) {
    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Outstanding',
                    data: data.values,
                    borderColor: chartColors.outstanding,
                    backgroundColor: 'rgba(17, 94, 89, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: { callback: currencyTick },
                    grid: { color: chartColors.grid },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `Outstanding: ${currencyFormatter.format(context.parsed.y)}`;
                        },
                    },
                },
            },
        },
    });
}

function buildPumpOutstandingChart(canvas, data) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Outstanding',
                    data: data.values,
                    backgroundColor: chartColors.outstanding,
                    borderRadius: 8,
                },
            ],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { callback: currencyTick },
                    grid: { color: chartColors.grid },
                },
                y: {
                    grid: { display: false },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label(context) {
                            return `Outstanding: ${currencyFormatter.format(context.parsed.x)}`;
                        },
                    },
                },
            },
        },
    });
}

export function initDashboardCharts() {
    const payload = document.getElementById('dashboard-chart-data');

    if (payload) {
        const charts = JSON.parse(payload.textContent);
        const monthlyCanvas = document.getElementById('monthlyComparisonChart');
        const trendCanvas = document.getElementById('outstandingTrendChart');
        const pumpCanvas = document.getElementById('pumpOutstandingChart');

        if (monthlyCanvas) {
            buildMonthlyComparisonChart(monthlyCanvas, charts.monthlyComparison);
        }

        if (trendCanvas) {
            buildOutstandingTrendChart(trendCanvas, charts.outstandingTrend);
        }

        if (pumpCanvas) {
            buildPumpOutstandingChart(pumpCanvas, charts.pumpOutstanding);
        }
    }

    const outstandingPayload = document.getElementById('outstanding-report-chart');
    const outstandingCanvas = document.getElementById('outstandingReportChart');

    if (outstandingPayload && outstandingCanvas) {
        buildPumpOutstandingChart(outstandingCanvas, JSON.parse(outstandingPayload.textContent));
    }
}
