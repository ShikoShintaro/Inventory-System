<h2>Welcome back, <span class="username_highlight">User!</span></h2>
<p>Access your management tools using the menu on the left.</p>

<div class="dashboard_cards">
    <div class="card">
        <h3>Total Items</h3>
        <p id="stat-total-items">0</p>
    </div>
    <div class="card">
        <h3>Low Stock</h3>
        <p id="stat-low-stock">0</p>
    </div>
    <div class="card">
        <h3>Pending Orders</h3>
        <p id="stat-pending-orders">0</p>
    </div>
</div>

<div class="report_grid fade-in">
    <div class="card chart_card">
        <h3>Monthly Deliveries</h3>
        <canvas id="analyticsDeliveryChart"></canvas>
    </div>
    <div class="card chart_card">
        <h3>Low Stock Alerts</h3>
        <canvas id="analyticsStockChart"></canvas>
    </div>
    <div class="card chart_card">
        <h3>Revenue Overview</h3>
        <canvas id="analyticsRevenueChart"></canvas>
    </div>
</div>
