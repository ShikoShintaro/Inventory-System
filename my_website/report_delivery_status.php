<?php
session_start();
require_once __DIR__ . '/helpers/delivery_db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p class='error_message'>⚠️ You must be logged in.</p>";
    exit;
}

$statusMap = [
    1 => 'Pending',
    2 => 'In Transit',
    3 => 'Delivered',
    4 => 'Delayed'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_id'])) {
    header('Content-Type: application/json');

    $delivery_id_raw = trim($_POST['delivery_id'] ?? '');
    $delivery_id = preg_replace('/[^A-Za-z0-9\s_-]/', '', $delivery_id_raw);
    $status = intval($_POST['status'] ?? 0);

    if ($delivery_id === '' || !isset($statusMap[$status])) {
        echo json_encode(['status' => 'error', 'message' => '⚠️ Invalid delivery ID or status.']);
        exit;
    }

    try {
        $conn = getDeliveryDB();
        $stmt = $conn->prepare("UPDATE deliveries SET status = :status WHERE delivery_id = :id");
        $stmt->execute([':status' => $status, ':id' => $delivery_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => '✅ Delivery status updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '⚠️ No matching delivery found.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => '⚠️ Database error: ' . $e->getMessage()]);
    }
    exit;
}

try {
    $conn = getDeliveryDB();
    $stmt = $conn->query("SELECT * FROM deliveries ORDER BY created_at DESC");
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p class='error_message'>⚠️ Database error: {$e->getMessage()}</p>";
    exit;
}
?>

<link rel="stylesheet" href="/css/track_delivery.css">

<div class="fade-in delivery_status_wrapper">
    <div class="page_header">
        <h1>Delivery Tracking</h1>
        <p>Monitor and manage your deliveries in real time. Click any row to update its status instantly.</p>
    </div>

    <div class="status_layout">
        <div class="status_left">
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
                            <th>Date</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($deliveries)): ?>
                            <?php foreach ($deliveries as $d): ?>
                                <tr data-id="<?= $d['delivery_id'] ?>">
                                    <td><?= $d['delivery_id'] ?></td>
                                    <td><?= htmlspecialchars($d['client_name']) ?></td>
                                    <td><?= htmlspecialchars($d['address']) ?></td>
                                    <td><?= htmlspecialchars($d['item']) ?></td>
                                    <td><?= intval($d['quantity']) ?></td>
                                    <td><?= $statusMap[$d['status']] ?? 'Unknown' ?></td>
                                    <td><?= htmlspecialchars($d['delivery_date']) ?></td>
                                    <td><?= htmlspecialchars($d['contact']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center;">No deliveries found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="status_right">
            <div class="form_card">
                <h2>Update Delivery Status</h2>
                <p id="responseMessage" class="response_message"></p>
                <form id="updateStatusForm" method="post">
                    <input type="text" name="delivery_id" class="textbox" placeholder="Delivery ID (e.g., 3)" required>
                    <select name="status" class="textbox" required>
                        <?php foreach ($statusMap as $id => $label): ?>
                            <option value="<?= $id ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="login_button">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
