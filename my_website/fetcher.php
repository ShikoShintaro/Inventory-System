<?php
require_once __DIR__ . '/helpers/delivery_db.php';

header('Content-Type: application/json');

try {
    $stats = getInventoryStats();
    echo json_encode(['status' => 'success', 'data' => $stats]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
