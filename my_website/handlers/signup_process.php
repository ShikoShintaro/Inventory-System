<?php
session_start();
require __DIR__ . '/../helpers/db.php'; // loads $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Check empty fields
    if (!$username || !$email || !$password || !$confirm_password) {
        $errors[] = "All fields are required.";
    }

    // Check password match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        $errors[] = "Username '$username' is already in use.";
    }


    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=:email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        $errors[] = "Email '$email' is already in use.";
    }

    // Save old input
    $_SESSION['old'] = ['username' => $username, 'email' => $email];

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: /../SignUp.php");
        exit;
    }

    // Insert new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username,email,password,role) VALUES (:username,:email,:password,'user')");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password' => $hashed_password
    ]);

    $_SESSION['success'] = "Sign-Up successful! You can now log in.";
    header("Location: /../LogIn.php");
    exit;
}
