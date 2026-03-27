<?php
require_once __DIR__ . '/delivery_db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$delivery_id = intval($_POST['delivery_id'] ?? 0);
if (!$delivery_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid delivery ID.']);
    exit;
}

try {
    $conn = getDeliveryDB();


    $conn->beginTransaction();

   
    $stmt = $conn->prepare("DELETE FROM deliveries WHERE delivery_id = :id");
    $stmt->execute([':id' => $delivery_id]);

    if ($stmt->rowCount() > 0) {
    
        $conn->exec("
            DELETE FROM sqlite_sequence WHERE name='deliveries';
        ");

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => '✅ Delivery cancelled successfully. Primary key reset.']);
    } else {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => '⚠️ Delivery not found or already deleted.']);
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => '⚠️ Database error: ' . $e->getMessage()]);
}