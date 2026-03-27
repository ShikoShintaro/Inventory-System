<?php
require_once __DIR__ . '/helpers/delivery_db.php';

// Ensure database + table exist
getDeliveryDB();

// Fetch all deliveries
$deliveries = getAllDeliveries();

// Map numeric status to labels
$statusMap = [
    1 => 'Pending',
    2 => 'In Transit',
    3 => 'Delivered',
    4 => 'Delayed'
];
?>

<link rel="stylesheet" href="/css/track_delivery.css">

<div class="fade-in track_delivery_container">
    <div class="delivery_box">
        <h1 class="delivery_title">📦 Delivery Tracking</h1>
        <p class="delivery_desc">Monitor, manage, and cancel deliveries in real time.</p>

        <!-- Search + Filter -->
        <div class="filter_bar" style="margin-top:15px;">
            <input type="text" id="searchInput" placeholder="Search by client or item..." onkeyup="filterTable()">
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Statuses</option>
                <?php foreach ($statusMap as $id => $label): ?>
                    <option value="<?= $id ?>"><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Table -->
        <div class="table_container">
            <table class="delivery_table" id="deliveryTable">
                <thead>
                    <tr>
                        <th>Delivery ID</th>
                        <th>Client Name</th>
                        <th>Address</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Estimated Delivery</th>
                        <th>Contact</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($deliveries)): ?>
                        <?php foreach ($deliveries as $delivery): 
                            $did = "DID " . str_pad($delivery['delivery_id'], 3, "0", STR_PAD_LEFT);
                            $statusId = intval($delivery['status']);
                            $statusLabel = $statusMap[$statusId] ?? 'Unknown';
                            $statusClass = strtolower(str_replace(' ', '-', $statusLabel));
                        ?>
                        <tr data-id="<?= $delivery['delivery_id'] ?>">
                            <td><?= $did ?></td>
                            <td><?= htmlspecialchars($delivery['client_name']) ?></td>
                            <td><?= htmlspecialchars($delivery['address']) ?></td>
                            <td><?= htmlspecialchars($delivery['item']) ?></td>
                            <td><?= intval($delivery['quantity']) ?></td>
                            <td><span class="status <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                            <td><?= htmlspecialchars($delivery['delivery_date']) ?></td>
                            <td><?= htmlspecialchars($delivery['contact']) ?></td>
                            <td><?= htmlspecialchars($delivery['created_at']) ?></td>
                            <td>
                                <button class="cancel_btn" onclick="cancelDelivery(<?= $delivery['delivery_id'] ?>)">❌ Cancel</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" style="text-align:center;">No deliveries found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function cancelDelivery(id) {
    if (!confirm("Are you sure you want to cancel this delivery?")) return;

    // Send a proper POST request with form data
    const formData = new FormData();
    formData.append("delivery_id", id);

    fetch("helpers/delivery_cancel.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.status === "success") {
            const row = document.querySelector(`tr[data-id='${id}']`);
            if (row) row.remove();
        }
    })
    .catch(err => {
        console.error("Cancel delivery error:", err);
        alert("⚠️ Failed to cancel delivery. Check console for details.");
    });
}
</script>

<style>
.cancel_btn {
    background: #ff4444;
    color: white;
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.2s ease;
}
.cancel_btn:hover {
    background: #cc0000;
}
</style>
