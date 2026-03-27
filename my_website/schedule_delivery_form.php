<?php
session_start();
require 'helpers/delivery_db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p class='error_message'>⚠️ You must be logged in to schedule a delivery.</p>";
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        ':user_id' => $user_id,
        ':client_name' => $_POST['client_name'] ?? '',
        ':address' => $_POST['address'] ?? '',
        ':item' => $_POST['item'] ?? '',
        ':quantity' => intval($_POST['quantity'] ?? 0),
        ':delivery_date' => $_POST['delivery_date'] ?? '',
        ':status' => intval($_POST['status'] ?? 1),
        ':contact' => $_POST['contact'] ?? '',
        ':price' => 0
    ];

    if ($data[':client_name'] && $data[':address'] && $data[':item'] && $data[':quantity'] > 0 && $data[':delivery_date']) {
        try {
            insertDelivery($data);
            echo json_encode(['status' => 'success', 'message' => '✅ Delivery scheduled successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '⚠️ Please fill all required fields correctly.']);
    }
    exit;
}
?>
<link rel="stylesheet" href="/css/report.css">

<div class="delivery_form_wrapper fade-in">
    <div class="content">
        <div class="introduction_section">
            <h1>Plan Your Deliveries</h1>
            <p>Schedule your delivery items with ease. Set the item, quantity, and preferred delivery date — we’ll handle the rest.</p>
        </div>

        <div class="right_form">
            <h2>Schedule a Delivery</h2>
            <p id="responseMessage" class="response_message"></p>
            <form id="deliveryForm" method="post">
                <input type="text" name="client_name" class="textbox" placeholder="Client Name" required>
                <input type="text" name="address" class="textbox" placeholder="Address" required>
                <input type="text" name="item" class="textbox" placeholder="Item Name" required>
                <input type="number" name="quantity" class="textbox" placeholder="Quantity" min="1" required>
                <input type="date" name="delivery_date" class="textbox" required>
                <select name="status" class="textbox">
                    <option value="1">Pending</option>
                    <option value="2">In Transit</option>
                    <option value="3">Delivered</option>
                    <option value="4">Delayed</option>
                </select>
                <input type="text" name="contact" class="textbox" placeholder="Contact Number">
                <button type="submit" class="login_button">Schedule Delivery</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("deliveryForm");
    const messageBox = document.getElementById("responseMessage");

    form.addEventListener("submit", e => {
        e.preventDefault();
        fetch("schedule_delivery_form.php", {
            method: "POST",
            body: new FormData(form)
        })
        .then(res => res.json())
        .then(data => {
            messageBox.textContent = data.message;
            messageBox.className = "response_message " + (data.status === "success" ? "success" : "error");
            if (data.status === "success") form.reset();
        })
        .catch(err => console.error(err));
    });
});
</script>
