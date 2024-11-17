<?php
function logLoginAttempt($username, $status, $failure_reason = null)
{
    global $db;
    $ip = $_SERVER['REMOTE_ADDR'];
    try {
        $sql = "INSERT INTO login_logs (username, ip_address, status, failure_reason, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $ip, $status, $failure_reason]);
        return true;
    } catch (PDOException $e) {
        error_log("Login Log Error: " . $e->getMessage());
        return false;
    }
}

function isAccountLocked($username)
{
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts 
        WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
        AND is_locked = 1");
    $stmt->execute([$username]);
    return $stmt->fetchColumn() > 0;
}

function RecentFailed($username)
{
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts 
        WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
        AND is_locked = 0");
    $stmt->execute([$username]);
    return $stmt->fetchColumn();
}