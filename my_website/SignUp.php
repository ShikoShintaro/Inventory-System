<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InventaRise Sign Up</title>
    <link rel="stylesheet" href="/css/SignUp.css">
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
            <h1 class="signup_title">Sign Up</h1>

            <!-- show if there's an error -->
            <?php if (!empty($errors)): ?>
                <div class="error_messages">
                    <?php foreach ($errors as $err): ?>
                        <p style="color:red;"><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="/handlers/signup_process.php" method="post" class="signup_form">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="textbox" placeholder="Username"
                    value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="textbox" placeholder="Email"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="textbox" placeholder="Password" required>

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="textbox"
                    placeholder="Confirm Password" required>

                <button class="signup_button" type="submit">Sign Up</button>
            </form>
            <div class="signup_links">
                <a href="ForgotPassword.php" class="signup_link_button">Forgot Password?</a>
            </div>
        </div>

    </div>
</body>

</html>