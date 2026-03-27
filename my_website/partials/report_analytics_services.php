<?php
// Simple flag to detect direct access
$isDirectAccess = !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest';
?>

<h2>📊 Report Analytics Dashboard</h2>

<?php if ($isDirectAccess): ?>
    <div style="color: #ffb703; font-weight: bold; margin: 20px 0;">
        ⚠️ Please open this page via the InventaRise dashboard to view the charts.
        <br><br>
        <button onclick="location.href='home.php'" 
                style="padding: 10px 20px; background: #0077b6; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Go to Dashboard
        </button>
    </div>
<?php endif; ?>

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

<?php if ($isDirectAccess): ?>
    <script>
        // Hide charts to avoid empty display
        document.querySelectorAll('.chart_card canvas').forEach(c => c.style.display = 'none');
    </script>
<?php endif; ?>
