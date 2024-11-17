<?php

session_start();
require_once 'db_conn.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $new_password)) {
    header('Location: change_password.php?error=Password must be at least 8 characters and include numbers, uppercase and lowercase letters');
    exit;
}

if ($new_password !== $confirm_password) {
    header('Location: change_password.php?error=Passwords do not match');
    exit;
}

try {
    $stmt = $db->prepare("SELECT password, password1, password2, password3 FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current_password, $user['password'])) {
        header('Location: change_password.php?error=Current password is incorrect');
        exit;
    }
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $previous_passwords = [$user['password'], $user['password1'], $user['password2'], $user['password3']];

    foreach ($previous_passwords as $old_password) {
        if ($old_password && password_verify($new_password, $old_password)) {
            header('Location: change_password.php?error=New password cannot be the same as previous passwords');
            exit;
        }
    }

    $stmt = $db->prepare("UPDATE users SET 
        password3 = password2,
        password2 = password1,
        password1 = password,
        password = ?,
        last_password_change = NOW()
        WHERE id = ?");
    $stmt->execute([$new_password_hash, $_SESSION['user_id']]);

    header('Location: dashboard.php?message=Password changed successfully');
    exit;

} catch (PDOException $e) {
    error_log("Password Change Error: " . $e->getMessage());
    header('Location: change_password.php?error=An error occurred');
    exit;
}