<?php
session_start();
require_once 'db_conn.php';
require_once 'functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
        logLoginAttempt($username, 'failure', 'Invalid username format');
        header('Location: index.php?error=Invalid username format');
        exit; //Alphanumeric check
    }
    
    if (isAccountLocked($username)) {
        logLoginAttempt($username, 'failure', 'Account locked');
        header('Location: index.php?error=Account is locked. Please try again later.');
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['realname'] = $user['realname'];

            logLoginAttempt($username, 'success');
            header('Location: dashboard.php');
            exit;
        } else {
            $failedAttempts = RecentFailed($username);

            if ($failedAttempts >= 2) {
                $stmt = $db->prepare("INSERT INTO login_attempts (username, is_locked) VALUES (?, 1)");
                $stmt->execute([$username]);
                logLoginAttempt($username, 'failure', 'Account locked due to multiple failures');
                header('Location: index.php?error=Account locked for 5 minutes due to multiple failed attempts');
            } else {
                $stmt = $db->prepare("INSERT INTO login_attempts (username, is_locked) VALUES (?, 0)");
                $stmt->execute([$username]);
                logLoginAttempt($username, 'failure', 'Invalid credentials');
                header('Location: index.php?error=Invalid username or password');
            }
            exit;
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        logLoginAttempt($username, 'failure', 'Database error');
        header('Location: index.php?error=An error occurred');
        exit;
    }
}