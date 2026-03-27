<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InventaRise Dashboard</title>
    <link rel="stylesheet" href="/css/services.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Cancel Notification styling */
        .cancel_notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s ease;
            z-index: 9999;
        }

        .cancel_notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        .cancel_notification.success {
            background: #28a745;
        }

        .cancel_notification.error {
            background: #dc3545;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="nav_left">
            <img src="images/Logo.png" alt="Logo" class="logo_images">
            <h1 class="nav_title">InventaRise</h1>
        </div>
        <div class="nav_right">
            <button onclick="location.href='home.php'" class="Home_btn">Home</button>
            <button onclick="location.href='Login.php'" class="logout_btn">Logout</button>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2 class="sidebar_title">Main Menu</h2>
        <div class="sidebar_buttons">
            <button class="side_btn inventory_btn" data-page="services_content.php">Inventory Management</button>
            <button class="side_btn reports_btn" data-page="partials/report_analytics_services.php">Report Analytics
                (Table)</button>
            <button class="side_btn track_delivery" data-page="track_delivery.php">Track Delivery</button>

            <!-- Delivery Dropdown -->
            <div class="dropdown_section">
                <button class="side_btn delivery_btn dropdown_toggle">Delivery ▼</button>
                <div class="dropdown_content">
                    <button class="sub_btn" data-page="schedule_delivery_form.php">Schedule Delivery</button>
                    <button class="sub_btn" data-page="report_delivery_status.php">Report Delivery Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main_content">
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
    </div>

    <!-- Cancel Notification container -->
    <div id="cancel_notification" class="cancel_notification"></div>

    <!-- JS -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const mainContent = document.querySelector(".main_content");
        const cancel_notification = document.getElementById("cancel_notification");

        //  Cancel Notification function
        function showCancelNotification(message, type = "success") {
            cancel_notification.textContent = message;
            cancel_notification.className = `cancel_notification show ${type}`;
            setTimeout(() => cancel_notification.classList.remove("show"), 2500);
        }

        //  Cancel button logic
        function attachCancelButtons() {
            const cancelButtons = document.querySelectorAll(".cancel_btn");
            cancelButtons.forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.closest("tr").dataset.id;
                    if (!confirm("Are you sure you want to cancel this delivery?")) return;

                    const formData = new FormData();
                    formData.append("delivery_id", id);

                    fetch("helpers/delivery_cancel.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        showCancelNotification(data.message, data.status === "success" ? "success" : "error");
                        if (data.status === "success") {
                            btn.closest("tr").remove();
                        }
                    })
                    .catch(err => {
                        console.error("Cancel delivery error:", err);
                        showCancelNotification("⚠️ Failed to cancel delivery.", "error");
                    });
                });
            });
        }

        //  Load dynamic pages
        function loadPageWithFade(url) {
            mainContent.classList.add("fade-out");

            setTimeout(() => {
                fetch(url, { cache: "no-cache" })
                    .then(res => {
                        if (!res.ok) throw new Error(`Failed to load ${url}`);
                        return res.text();
                    })
                    .then(html => {
                        mainContent.innerHTML = html;
                        mainContent.classList.remove("fade-out");
                        mainContent.classList.add("fade-in");
                        setTimeout(() => mainContent.classList.remove("fade-in"), 500);

                        // Reinitialize logic for dynamic content
                    if (url.includes("report_analytics_services.php")) initReportAnalyticsCharts();
                        if (url.includes("schedule_delivery_form.php")) attachDeliveryFormHandler();
                        if (url.includes("report_delivery_status.php")) attachDeliveryStatusHandler();
                        if (url.includes("services_content.php")) loadInventoryStats();
                        if (url.includes("track_delivery.php")) attachCancelButtons(); // ✅ Important
                    })
                    .catch(err => {
                        mainContent.innerHTML = `<p style='color:red;'>❌ Failed to load ${url}</p>`;
                        console.error(err);
                    });
            }, 400);
        }

        //  Form Handlers
        function attachDeliveryFormHandler() {
            const form = mainContent.querySelector("#deliveryForm");
            const messageBox = mainContent.querySelector("#responseMessage");
            if (!form) return;

            form.addEventListener("submit", e => {
                e.preventDefault();
                fetch("schedule_delivery_form.php", {
                    method: "POST",
                    body: new FormData(form)
                })
                .then(res => res.json())
                .then(data => {
                    messageBox.textContent = data.message;
                    messageBox.className = "response_message " + (data.status === "success" ? "success" : "error");
                    if (data.status === "success") form.reset();
                })
                .catch(err => console.error(err));
            });
        }

        function attachDeliveryStatusHandler() {
            const form = mainContent.querySelector("#updateStatusForm");
            const messageBox = mainContent.querySelector("#responseMessage");
            const table = mainContent.querySelector("#deliveryTable");
            if (!form) return;

            form.addEventListener("submit", e => {
                e.preventDefault();
                fetch("report_delivery_status.php", {
                    method: "POST",
                    body: new FormData(form)
                })
                .then(res => res.json())
                .then(data => {
                    messageBox.textContent = data.message;
                    messageBox.className = "response_message " + (data.status === "success" ? "success" : "error");

                    if (data.status === "success") {
                        const id = form.delivery_id.value.trim();
                        const row = table?.querySelector(`tr[data-id='${id}']`);
                        if (row) {
                            const newStatus = form.status.options[form.status.selectedIndex].text;
                            row.cells[5].textContent = newStatus;
                            row.style.backgroundColor = "#d4edda";
                            setTimeout(() => row.style.backgroundColor = "", 800);
                        }
                        form.reset();
                    }
                })
                .catch(err => console.error(err));
            });
        }

        //  Sidebar and dropdown handlers
        document.querySelectorAll("[data-page]").forEach(btn => {
            btn.addEventListener("click", e => {
                e.preventDefault();
                loadPageWithFade(btn.getAttribute("data-page"));
            });
        });

        document.querySelectorAll(".dropdown_toggle").forEach(btn => {
            btn.addEventListener("click", e => {
                e.stopPropagation();
                const next = btn.nextElementSibling;
                if (next) next.classList.toggle("show");
            });
        });

        //  Load Inventory stats
        function loadInventoryStats() {
            fetch("fetcher.php?rand=" + Date.now())
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        const stats = data.data;
                        document.getElementById("stat-total-items").textContent = stats.total_items;
                        document.getElementById("stat-low-stock").textContent = stats.low_stock;
                        document.getElementById("stat-pending-orders").textContent = stats.pending_orders;
                    } else {
                        console.error("Failed to load stats:", data.message);
                    }
                })
                .catch(err => console.error("Error loading stats:", err));
        }

        //  Initialize charts
        async function initReportAnalyticsCharts() {
            try {
                const res = await fetch("report_analytics.php?rand=" + Date.now());
                const json = await res.json();

                if (json.status !== "success") throw new Error(json.message);
                const { low_stock, monthly_deliveries, revenue_overview } = json.data;

                const deliveryCtx = document.getElementById('analyticsDeliveryChart');
                if (deliveryCtx) new Chart(deliveryCtx, {
                    type: 'line',
                    data: {
                        labels: monthly_deliveries.map(m => m.month),
                        datasets: [{
                            label: 'Deliveries Completed',
                            data: monthly_deliveries.map(m => m.deliveries),
                            borderColor: '#00d4ff',
                            backgroundColor: 'rgba(0,212,255,0.3)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: { scales: { y: { beginAtZero: true } } }
                });

                const stockCtx = document.getElementById('analyticsStockChart');
                if (stockCtx) new Chart(stockCtx, {
                    type: 'bar',
                    data: {
                        labels: low_stock.length ? low_stock.map(i => i.item) : ['No Low Stock Items'],
                        datasets: [{
                            label: 'Available Quantity',
                            data: low_stock.length ? low_stock.map(i => i.total_qty) : [0],
                            backgroundColor: low_stock.map(i => i.total_qty <= 5 ? '#d00000' : '#ffb703')
                        }]
                    },
                    options: { scales: { y: { beginAtZero: true } } }
                });

                const revenueCtx = document.getElementById('analyticsRevenueChart');
                if (revenueCtx) new Chart(revenueCtx, {
                    type: 'doughnut',
                    data: {
                        labels: revenue_overview.length ? revenue_overview.map(r => r.month) : ['No Data'],
                        datasets: [{
                            data: revenue_overview.length ? revenue_overview.map(r => r.revenue) : [1],
                            backgroundColor: ['#00b4d8', '#0077b6', '#90e0ef', '#48cae4', '#0096c7']
                        }]
                    }
                });

            } catch (err) {
                console.error("Analytics load failed:", err);
            }
        }

        loadInventoryStats();
    });
    </script>
</body>

</html>