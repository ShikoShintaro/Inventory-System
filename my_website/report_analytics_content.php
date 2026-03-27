<h2>📊 Report Analytics Dashboard</h2>

<div class="report_grid fade-in">
    <div class="card chart_card">
        <h3>📦 Monthly Deliveries</h3>
        <canvas id="analyticsDeliveryChart"></canvas>
    </div>
    <div class="card chart_card">
        <h3>⚠️ Low Stock Items</h3>
        <canvas id="analyticsStockChart"></canvas>
    </div>
    <div class="card chart_card">
        <h3>💰 Revenue Overview</h3>
        <canvas id="analyticsRevenueChart"></canvas>
    </div>
</div>

<!-- ✅ Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
async function initReportAnalytics() {
    try {
        const res = await fetch("helpers/report_analytics_data.php?rand=" + Date.now());
        const json = await res.json();
        if (json.status !== "success") throw new Error(json.message);

        const { low_stock, monthly_deliveries, revenue_overview } = json.data;

        // 🩵 Monthly Deliveries
        const deliveryLabels = monthly_deliveries.map(m => m.month);
        const deliveryData = monthly_deliveries.map(m => m.deliveries);
        new Chart(document.getElementById('analyticsDeliveryChart'), {
            type: 'line',
            data: { 
                labels: deliveryLabels, 
                datasets: [{
                    label: 'Deliveries', 
                    data: deliveryData,
                    borderColor: '#00d4ff',
                    backgroundColor: 'rgba(0,212,255,0.3)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { 
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { labels: { color: '#fff' } } }
            }
        });

        // 🟡 Low Stock
        const stockLabels = low_stock.map(i => i.item);
        const stockQty = low_stock.map(i => i.total_qty);
        new Chart(document.getElementById('analyticsStockChart'), {
            type: 'bar',
            data: { 
                labels: stockLabels,
                datasets: [{
                    label: 'Quantity',
                    data: stockQty,
                    backgroundColor: stockQty.map(q => q <= 2 ? '#d00000' : '#ffb703')
                }]
            },
            options: { 
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { labels: { color: '#fff' } } }
            }
        });

        // 💰 Revenue Overview
        const revenueLabels = revenue_overview?.map(r => r.month) ?? [];
        const revenueData = revenue_overview?.map(r => r.revenue) ?? [];
        const ctxRevenue = document.getElementById('analyticsRevenueChart');

        new Chart(ctxRevenue, {
            type: 'doughnut',
            data: {
                labels: revenueLabels.length ? revenueLabels : ['Deliveries'],
                datasets: [{
                    data: revenueData.length ? revenueData : [1],
                    backgroundColor: ['#00b4d8', '#0077b6', '#90e0ef', '#48cae4', '#0096c7']
                }]
            },
            options: {
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#fff' }
                    }
                },
                maintainAspectRatio: false
            }
        });

    } catch (err) {
        console.error("Analytics load failed:", err);
        document.querySelector(".report_grid").innerHTML =
            "<p style='color: #ffb703;'>⚠️ Failed to load analytics data.</p>";
    }
}

initReportAnalytics();
</script>

<style>
body {
    background-color: #0b0c10;
    color: white;
    font-family: "Segoe UI", sans-serif;
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    margin-top: 80px; /* space below navbar */
    color: #00d4ff;
}

.report_grid {
    display: flex;
    flex-direction: row; /* horizontally aligned */
    justify-content: center;
    align-items: flex-start;
    gap: 2rem;
    flex-wrap: wrap; /* makes it responsive */
    padding: 2rem;
    min-height: calc(100vh - 100px);
    box-sizing: border-box;
}

.chart_card {
    flex: 1 1 30%;
    max-width: 400px;
    min-width: 280px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    text-align: center;
}

.chart_card canvas {
    width: 100% !important;
    height: 250px !important;
}
</style>
