import Chart from 'chart.js/auto';

const dataElement = document.getElementById('catalog-statistics-data');

if (dataElement) {
    const data = JSON.parse(dataElement.textContent);
    const palette = ['#023E73', '#024873', '#0A89A6', '#15AEBF', '#BF6B04', '#a35a03', '#4b6072', '#7fb8c4'];
    const yearCanvas = document.getElementById('chart-coins-by-year');

    if (yearCanvas && data.yearLabels.length) {
        new Chart(yearCanvas, {
            type: 'bar',
            data: {
                labels: data.yearLabels,
                datasets: [{
                    label: data.totalCoinsLabel,
                    data: data.yearData,
                    backgroundColor: '#0A89A6',
                    borderRadius: 3,
                    maxBarThickness: 22,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    const metalCanvas = document.getElementById('chart-metals');

    if (metalCanvas && data.metalLabels.length) {
        new Chart(metalCanvas, {
            type: 'doughnut',
            data: {
                labels: data.metalLabels,
                datasets: [{
                    data: data.metalData,
                    backgroundColor: palette,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } },
                },
            },
        });
    }
}
