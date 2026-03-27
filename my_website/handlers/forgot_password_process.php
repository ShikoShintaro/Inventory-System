<?php
session_start();
require __DIR__ . '/../helpers/db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Check empty fields
    if(empty($email) || empty($new_password) || empty($confirm_password)){
        $errors[] = "All fields are required.";
    }

    // Check password match
    if($new_password !== $confirm_password){
        $errors[] = "Passwords do not match.";
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=:email");
    $stmt->execute([':email'=>$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user){
        $errors[] = "Email not found.";
    }

    if(!empty($errors)){
        $_SESSION['errors'] = $errors;
        header("Location: /../ForgotPassword.php");
        exit;
    }

    // Update password immediately
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=:password WHERE email=:email");
    $stmt->execute([
        ':password' => $hashed,
        ':email' => $email
    ]);

    $_SESSION['success'] = "Password reset successful! You can now log in.";
    header("Location: /../ForgotPassword.php");
    exit;
}
