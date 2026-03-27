<?php
session_start();
require __DIR__ . '/helpers/db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/SignUp.css">
</head>

<body>

    <div class="background"></div>
    <div class="content">

        <div class="introduction_section">
            <div class="title">
                <img src="images/Logo.png" alt="Logo" class="logo_image">
                <h1>InventaRise</h1>
            </div>
                <h2 class="sub_header">
                    A Micro Retail Supply Chain Logistics and Inventory Management System
                </h2>
        </div>

         <div class="signup_section">
            <h1 class="signup_title">Reset password</h1>

            <?php
            if (isset($_SESSION['errors'])) {
                foreach ($_SESSION['errors'] as $error) {
                    echo "<p style='color:red;'>$error</p>";
                }
                unset($_SESSION['errors']);
            }
            if (isset($_SESSION['success'])) {
                echo "<p style='color:green;'>" . $_SESSION['success'] . "</p>";
                unset($_SESSION['success']);
            }
            ?>

            <form action="/handlers/forgot_password_process.php" method="post" class="signup_form">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="textbox" placeholder="Your Email" required>

                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" class="textbox" placeholder="New Password"
                    required>

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="textbox"
                    placeholder="Confirm Password" required>

                <button class="signup_button" type="submit">Reset Password</button>
            </form>

            <div class="signup_links">
                <a href="LogIn.php" class="signup_link_button">Back to Log In</a>
            </div>
        </div>
    </div>
</body>

</html>