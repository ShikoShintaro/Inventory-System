<?php
// helpers/report_analytics_data.php
header("Content-Type: application/json");
require_once __DIR__ . '/delivery_db.php';

try {
    // Gather analytics data from delivery_db.php functions
    $data = [
        "low_stock" => getLowStockItems(),
        "monthly_deliveries" => getMonthlyDeliveries(),
        "revenue_overview" => getRevenueOverview()
    ];

    echo json_encode([
        "status" => "success",
        "data" => $data
    ]);

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
