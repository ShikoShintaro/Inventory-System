<?php
// ===========================================================
// helpers/delivery_db.php
// Core database logic for Deliveries and Inventory
// ===========================================================

define('LOW_STOCK_THRESHOLD', 1000);

// DATABASE CONNECTION
function getDeliveryDB(): PDO
{
    $db_file = __DIR__ . '/../db/deliveries.db';

    // Checks if this part has the file path correct
    if (!file_exists(dirname($db_file))) {
        mkdir(dirname($db_file), 0755, true);
    }

    try {
        $conn = new PDO("sqlite:" . $db_file);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Ensure tables exist
        createDeliveriesTable($conn);
        createInventoryTable($conn);

        // Ensure price column exists
        $columns = $conn->query("PRAGMA table_info(deliveries)")->fetchAll(PDO::FETCH_ASSOC);
        $hasPrice = false;
        foreach ($columns as $col) {
            if ($col['name'] === 'price') {
                $hasPrice = true;
                break;
            }
        }
        if (!$hasPrice) {
            $conn->exec("ALTER TABLE deliveries ADD COLUMN price REAL DEFAULT 0");
        }

        return $conn;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

// TABLE CREATION
function createDeliveriesTable(PDO $conn): void
{
    $conn->exec("
        CREATE TABLE IF NOT EXISTS deliveries (
            delivery_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            client_name TEXT NOT NULL,
            address TEXT NOT NULL,
            item TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            delivery_date DATE NOT NULL,
            status INTEGER CHECK(status IN (1,2,3,4)) DEFAULT 1,
            contact TEXT,
            price REAL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

function createInventoryTable(PDO $conn): void
{
    $conn->exec("
        CREATE TABLE IF NOT EXISTS inventory (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            item TEXT NOT NULL,
            quantity INTEGER NOT NULL DEFAULT 0,
            price REAL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ");
}

// DELIVERY OPERATIONS
function getAllDeliveries(): array
{
    $conn = getDeliveryDB();
    $stmt = $conn->query("SELECT * FROM deliveries ORDER BY delivery_id ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertDelivery(array $data): int
{
    $conn = getDeliveryDB();
    $stmt = $conn->prepare("
        INSERT INTO deliveries (user_id, client_name, address, item, quantity, delivery_date, status, contact, price)
        VALUES (:user_id, :client_name, :address, :item, :quantity, :delivery_date, :status, :contact, :price)
    ");
    $stmt->execute($data);
    return (int) $conn->lastInsertId();
}

// STATUS LABELS

// inputs a status code and returns the corresponding label
// 
function statusLabel(int $statusCode): string
{
    $map = [
        1 => 'Pending',
        2 => 'In Transit',
        3 => 'Delivered',
        4 => 'Delayed'
    ];
    return $map[$statusCode] ?? 'Unknown';
}

// INVENTORY / DASHBOARD STATS
function getInventoryStats(): array
{
    $conn = getDeliveryDB();

    $stmtTotal = $conn->query("SELECT SUM(quantity) AS total_items FROM deliveries");
    $total_items = (int) ($stmtTotal->fetch(PDO::FETCH_ASSOC)['total_items'] ?? 0);

    $stmtPending = $conn->query("SELECT COUNT(*) AS pending_orders FROM deliveries WHERE status IN (1, 2)");
    $pending_orders = (int) ($stmtPending->fetch(PDO::FETCH_ASSOC)['pending_orders'] ?? 0);

    $stmtLowStock = $conn->query("
        SELECT COUNT(*) AS low_stock
        FROM (
            SELECT item, SUM(quantity) AS total_qty
            FROM deliveries
            GROUP BY item
            HAVING total_qty <= " . LOW_STOCK_THRESHOLD . "
        )
    ");
    $low_stock = (int) ($stmtLowStock->fetch(PDO::FETCH_ASSOC)['low_stock'] ?? 0);

    $suppliers = 15;

    return [
        'total_items' => $total_items,
        'low_stock' => $low_stock,
        'suppliers' => $suppliers,
        'pending_orders' => $pending_orders
    ];
}

// REPORT ANALYTICS
function getLowStockItems($threshold = LOW_STOCK_THRESHOLD): array
{
    $db = getDeliveryDB();
    $stmt = $db->prepare("
        SELECT item, SUM(quantity) AS total_qty
        FROM deliveries
        GROUP BY item
        HAVING total_qty <= :threshold
    ");
    $stmt->execute([':threshold' => $threshold]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMonthlyDeliveries(): array
{
    $db = getDeliveryDB();
    $stmt = $db->query("
        SELECT strftime('%Y-%m', delivery_date) AS month, COUNT(*) AS deliveries
        FROM deliveries
        GROUP BY month ORDER BY month ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRevenueOverview(): array
{
    $db = getDeliveryDB();
    $stmt = $db->query("
        SELECT strftime('%Y-%m', delivery_date) AS month, SUM(price * quantity) AS revenue
        FROM deliveries WHERE status = 3
        GROUP BY month ORDER BY month ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
