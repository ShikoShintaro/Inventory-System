<?php
session_start();
require __DIR__ . '/helpers/db.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the logged-in user from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback if somehow not found
$username = $user ? $user['username'] : 'User';

// Capitalize first letter 
$displayName = ucfirst((string) $username);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InventaRise Dashboard</title>
    <link rel="stylesheet" href="css/home.css">
</head>

<body>
   
    <div class="navbar">
        <div class="nav_left">
            <img src="images/Logo.png" alt="Logo" class="logo_images">
            <h1 class="nav_title">InventaRise</h1>
        </div>
        <div class="nav_right">
            <button onclick="location.href='home.php'" class="nav_btn active">Home</button>
            <button onclick="location.href='services.php'" class="nav_btn">Services</button>
            <button onclick="location.href='Login.php'" class="nav_btn logout_btn">Logout</button>
        </div>
    </div>

    
    <div class="main_content">
        <div class="Welcome">
            <h1 class="header">Welcome <?php echo htmlspecialchars($displayName); ?>!</h1>
            <h2 class="header_2">
                At InventaRise, we help you streamline your retail operations,<br>
                track deliveries, monitor inventory, and generate powerful insights — all from one centralized platform.
            </h2>
        </div>

        
        <div class="services">
            <div class="service">
                <h1>Inventory Management</h1>
                <button class="service_btn" onclick="window.location.href='services.php'">View</button>
            </div>
            <div class="service">
                <h1>Track Deliveries</h1>
                <button class="service_btn" onclick="window.location.href='track_delivery.php'">View</button>
            </div>
            <div class="service">
                <h1>Report Analytics</h1>
                <button class="service_btn" onclick="loadPageWithFade('report_analytics_content.php')">View</button>
            </div>
        </div>

    </div>
<script>
    function loadPageWithFade(url) {
    const mainContent = document.querySelector(".main_content");
    mainContent.classList.add("fade-out");

    setTimeout(() => {
        fetch(url)
            .then(response => response.text())
            .then(data => {
                mainContent.innerHTML = data;

                //  Re-run <script> tags inside the loaded content
                mainContent.querySelectorAll("script").forEach(oldScript => {
                    const newScript = document.createElement("script");
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else {
                        newScript.textContent = oldScript.textContent;
                    }
                    document.body.appendChild(newScript);
                    document.body.removeChild(newScript);
                });

                mainContent.classList.remove("fade-out");
                mainContent.classList.add("fade-in");
                setTimeout(() => mainContent.classList.remove("fade-in"), 500);
            })
            .catch(error => {
                mainContent.innerHTML = `<p style='color:red;'>Failed to load content.</p>`;
                console.error(error);
            });
     }, 400);
    }
</script>
</body>

</html>