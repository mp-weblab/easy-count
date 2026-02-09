const charts = {};
// Fonctions définies globalement (hors DOMContentLoaded)

function createChart(canvasId, labels, data, label) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) {
        return;
    }

    if (charts[canvasId]) {
        charts[canvasId].destroy();
        delete charts[canvasId];
    }

    charts[canvasId] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                backgroundColor: '#4F46E5'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}


function createLineChart(canvasId, labels, data, label) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                fill: true,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.3,
                pointRadius: 3,
                pointBackgroundColor: 'rgba(75, 192, 192, 0.2)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                title: {
                    display: true,
                    text: label
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// DOMContentLoaded pour l’appel AJAX et l’affichage des graphiques
document.addEventListener('DOMContentLoaded', function () {
    fetch(`${easyCountData.ajax_url}?action=easy_count_get_stats&_wpnonce=${easyCountData.nonce}&t=${Date.now()}`)

        .then(response => response.json())
        .then(data => {

            if (data.success) {
                function parseStats(statsObj) {
                    return {
                        labels: Object.keys(statsObj),
                        data: Object.values(statsObj)
                    };
                }

                const daily = parseStats(data.data.daily);
                const weekly = parseStats(data.data.weekly);
                const monthly = parseStats(data.data.monthly);
                const hourly = parseStats(data.data.hourly);

                createLineChart('easy-count-daily-line', hourly.labels, hourly.data, 'Courbe des visites par heure');
                createChart('easy-count-daily', daily.labels, daily.data, 'Visites quotidiennes');
                createChart('easy-count-weekly', weekly.labels, weekly.data, 'Visites hebdomadaires');
                createChart('easy-count-monthly', monthly.labels, monthly.data, 'Visites mensuelles');
            } else {

            }
        })
        .catch(err => console.error('Erreur AJAX:', err));
});
