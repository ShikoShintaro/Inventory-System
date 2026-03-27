<?php
session_start();
require_once __DIR__ . '/helpers/delivery_db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p class='error_message'>⚠️ You must be logged in to view this report.</p>";
    exit;
}

$statusMap = [
    1 => 'Pending',
    2 => 'In Transit',
    3 => 'Delivered',
    4 => 'Delayed'
];
?>

<link rel="stylesheet" href="/css/report.css">

<div class="fade-in delivery_report_wrapper">
    <div class="header_section">
        <h1>📦 Delivery Report</h1>
        <p>Search and analyze delivery records easily. Filter by status or search by client, address, or item.</p>
    </div>

    <div class="filter_section">
        <input type="text" id="searchBox" class="textbox" placeholder="🔍 Search by name, address, or item">
        <select id="statusFilter" class="textbox">
            <option value="0">All Status</option>
            <?php foreach ($statusMap as $id => $label): ?>
                <option value="<?= $id ?>"><?= $label ?></option>
            <?php endforeach; ?>
        </select>
        <button id="filterBtn" class="login_button">Apply</button>
    </div>

    <div class="table_container">
        <table class="delivery_table" id="deliveryTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Address</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Delivery Date</th>
                    <th>Contact</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="8" style="text-align:center;">Loading data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.querySelector("#deliveryTable tbody");
    const searchBox = document.getElementById("searchBox");
    const statusFilter = document.getElementById("statusFilter");
    const filterBtn = document.getElementById("filterBtn");

    const loadData = () => {
        const search = encodeURIComponent(searchBox.value.trim());
        const status = encodeURIComponent(statusFilter.value);

        tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center;">Loading...</td></tr>`;

        fetch(`delivery_report_fetch.php?search=${search}&status=${status}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === "success" && data.data.length > 0) {
                    tableBody.innerHTML = "";
                    data.data.forEach(row => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${row.delivery_id}</td>
                            <td>${row.client_name}</td>
                            <td>${row.address}</td>
                            <td>${row.item}</td>
                            <td>${row.quantity}</td>
                            <td>${row.status}</td>
                            <td>${row.delivery_date}</td>
                            <td>${row.contact ?? ""}</td>
                        `;
                        tableBody.appendChild(tr);
                    });
                } else {
                    tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center;">No results found.</td></tr>`;
                }
            })
            .catch(() => {
                tableBody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:red;">⚠️ Failed to load data.</td></tr>`;
            });
    };

    filterBtn.addEventListener("click", loadData);
    searchBox.addEventListener("keypress", e => {
        if (e.key === "Enter") loadData();
    });

    loadData(); // auto-load on page open
});
</script>

<style>
    .response_message {
        font-weight: bold;
        margin-bottom: 10px;
        text-align: center;
    }

    .response_message.success {
        color: limegreen;
    }

    .response_message.error {
        color: red;
    }

    #reportResults table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    #reportResults th,
    #reportResults td {
        border: 1px solid #fff;
        padding: 8px 10px;
        text-align: left;
    }

    #reportResults th {
        background: rgba(255, 255, 255, 0.2);
    }

    .status.Pending {
        color: #FFD700;
        font-weight: bold;
    }

    .status['In Transit'] {
        color: #00BFFF;
        font-weight: bold;
    }

    .status.Delivered {
        color: #00FF7F;
        font-weight: bold;
    }

    .status.Delayed {
        color: #FF4500;
        font-weight: bold;
    }
</style>