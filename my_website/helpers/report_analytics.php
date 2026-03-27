<?php
header('Content-Type: application/json');

require_once __DIR__ . '/helpers/delivery_db.php';
$conn = getDeliveryDB();

// --- Example static demo data ---
$data = [
    "low_stock" => [
        ["item" => "Toner Cartridges", "total_qty" => 3],
        ["item" => "Printer Ink", "total_qty" => 5],
    ],
    "monthly_deliveries" => [
        ["month" => "Jan", "deliveries" => 50],
        ["month" => "Feb", "deliveries" => 70],
        ["month" => "Mar", "deliveries" => 60],
    ],
    "revenue_overview" => [
        ["month" => "Jan", "revenue" => 5000],
        ["month" => "Feb", "revenue" => 6800],
        ["month" => "Mar", "revenue" => 7500],
    ]
];

echo json_encode(["status" => "success", "data" => $data]);
