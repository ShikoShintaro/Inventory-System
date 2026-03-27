<?php
session_start();
require_once __DIR__ . '/helpers/delivery_db.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['status' => 'error', 'message' => '⚠️ You must be logged in.']);
    exit;
}

$conn = getDeliveryDB();
$statusMap = [
    1 => 'Pending',
    2 => 'In Transit',
    3 => 'Delivered',
    4 => 'Delayed'
];

$search = trim($_GET['search'] ?? '');
$statusFilter = intval($_GET['status'] ?? 0);

$query = "SELECT * FROM deliveries WHERE 1=1";
$params = [];

if ($search !== '') {
    $query .= " AND (client_name LIKE :search OR address LIKE :search OR item LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if ($statusFilter > 0 && isset($statusMap[$statusFilter])) {
    $query .= " AND status = :status";
    $params[':status'] = $statusFilter;
}

$query .= " ORDER BY delivery_date DESC";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $formatted = [];
    foreach ($rows as $r) {
        $formatted[] = [
            'delivery_id' => $r['delivery_id'],
            'client_name' => $r['client_name'],
            'address' => $r['address'],
            'item' => $r['item'],
            'quantity' => $r['quantity'],
            'delivery_date' => $r['delivery_date'],
            'status' => $statusMap[$r['status']] ?? 'Unknown',
            'contact' => $r['contact']
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $formatted]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '⚠️ Database error: ' . $e->getMessage()]);
}
?>
