/* Renders the report charts with Chart.js from server-provided data. */
(function () {
    'use strict';
    var d = window.EFA_REPORT || {};
    if (typeof Chart === 'undefined') return;

    var sales = d.sales || [];
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: sales.map(function (r) { return r.label; }),
            datasets: [{
                label: d.labels.sales,
                data: sales.map(function (r) { return parseFloat(r.total); }),
                borderColor: '#6f42c1', backgroundColor: 'rgba(111,66,193,.15)',
                fill: true, tension: 0.3
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    var m = d.monthly || [];
    new Chart(document.getElementById('profitChart'), {
        type: 'bar',
        data: {
            labels: m.map(function (r) { return r.month; }),
            datasets: [
                { label: d.labels.profit, data: m.map(function (r) { return parseFloat(r.profit); }), backgroundColor: '#198754' },
                { label: d.labels.expenses, data: m.map(function (r) { return parseFloat(r.expenses); }), backgroundColor: '#dc3545' }
            ]
        },
        options: { responsive: true }
    });

    var inv = d.inv || [];
    new Chart(document.getElementById('invChart'), {
        type: 'doughnut',
        data: {
            labels: inv.map(function (r) { return r.label; }),
            datasets: [{
                data: inv.map(function (r) { return parseFloat(r.value); }),
                backgroundColor: ['#6f42c1', '#0d6efd', '#198754', '#fd7e14', '#dc3545', '#20c997', '#ffc107', '#6610f2']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
})();
