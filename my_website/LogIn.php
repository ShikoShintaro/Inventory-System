<?php
session_start();
require __DIR__ . '/helpers/db.php';

$login_error = '';
$username_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $username_value = htmlspecialchars($username); 

    if (empty($username) || empty($password)) {
        $login_error = "Please fill in both fields.";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $login_error = "Username does not exist.";
        } else {
            // Verify password
            if (password_verify($password, $user['password'])) {
               
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: home.php");
                exit;
            } else {
                $login_error = "Incorrect password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InventaRise</title>
    <link rel="stylesheet" href="css/LogIn.css">
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

        
        <div class="login_section">
            <h1 class="login_title">Log In</h1>

            <!-- show if there's an error -->
            <?php if (!empty($login_error)): ?>
                <p style="color:red; margin-bottom:10px;"><?php echo $login_error; ?></p>
            <?php endif; ?>

            <form action="LogIn.php" method="post" class="login_form">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" class="textbox" placeholder="Username"
                    value="<?php echo $username_value; ?>" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="textbox" placeholder="Password" required>

                <button class="login_button" type="submit">Log In</button>
            </form>

            <div class="login_links">
                <a href="ForgotPassword.php" class="login_link_button">Forgot Password?</a>
                <a href="SignUp.php" class="login_link_button">Don’t have an account? Sign Up</a>
            </div>
        </div>

    </div>
</body>

</html>