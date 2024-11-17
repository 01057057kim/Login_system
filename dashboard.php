<?php
session_start();
require_once 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $db->prepare("SELECT ip_address, created_at, status 
                        FROM login_logs 
                        WHERE username = ? 
                        ORDER BY created_at DESC 
                        LIMIT 5");
    $stmt->execute([$_SESSION['username']]);
    $login_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT last_password_change 
                        FROM users 
                        WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $error_message = "An error while loading the dashboard";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: rgb(216,0,145);
            background: linear-gradient(90deg, rgba(216,0,145,1) 40%, rgba(211,0,0,1) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            min-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .welcome-message {
            font-size: 24px;
            color: #333;
        }

        .user-info {
            background-color: #f8f9fa;
            padding-left: 15px;
            padding-right: 15px;
            padding-top: 5px;
            padding-bottom: 5px;
            border-radius: 5px;
        }

        .login-history {
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th,
        td {
            padding: 9px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
        }

        .status-success {
            color: green;
        }

        .status-failure {
            color: red;
        }

        .button-group {
            margin-top: 20px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }

        .change-password-btn {
            background-color: #007bff;
        }

        .logout-btn {
            background-color: #dc3545;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <div class="header">
            <h1 class="welcome-message">Welcome,
                <?php echo htmlspecialchars($_SESSION['realname']); ?>!
            </h1>
            <form action="logout.php" method="POST" style="display: inline;">
                <button type="submit" class="button logout-btn">Logout</button>
            </form>
        </div>

        <div class="user-info">
            <h2>Account Information</h2>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?>
            </p>
            <p><strong>Last Password Change:</strong>
                <?php
                if (isset($user_data['last_password_change'])) {
                    echo htmlspecialchars(date('F j, Y, g:i a', strtotime($user_data['last_password_change'])));
                } else {
                    echo 'Not available';
                }
                ?>
            </p>
        </div>

        <div class="login-history">
            <h2>Recent Login History</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>IP Address</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($login_history as $log): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($log['ip_address']); ?>
                            </td>
                            <td class="status-<?php echo $log['status'] === 'success' ? 'success' : 'failure'; ?>">
                                <?php echo htmlspecialchars(ucfirst($log['status'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="button-group">
            <a href="change_password.php" class="button change-password-btn">Change Password</a>
        </div>
    </div>
</body>

</html>