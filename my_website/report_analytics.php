<?php
require_once __DIR__ . '/helpers/delivery_db.php';

header('Content-Type: application/json');

// Prevent accidental output 
ob_clean();
ob_start();

try {
    $low_stock = getLowStockItems();
    $monthly_deliveries = getMonthlyDeliveries();
    $revenue_overview = getRevenueOverview();

    $data = [
        'low_stock' => $low_stock,
        'monthly_deliveries' => $monthly_deliveries,
        'revenue_overview' => $revenue_overview
    ];

    echo json_encode(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

exit;

