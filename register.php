<?php
session_start();
require_once 'db_conn.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New User</title>
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
            min-height: 100vh;
        }

        .container {
            min-width: 300px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .form-group {
            margin-bottom: 25px;
            width: 250px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
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

        .button-group {
            margin-top: 20px;
            justify-content: center;
            display: flex;
        }

        .btn-back {
            background-color: #28a745;
            margin-left: 10px;
            color: white;
            padding: 4px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Register New User</h2>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $realname = $_POST['realname'] ?? '';
            $errors = [];
            if (!preg_match('/^[a-zA-Z0-9]{3,16}$/', $username)) {
                $errors[] = "Username must be 3-16 characters and contain only letters and numbers";
            }
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
                $errors[] = "Password must be at least 8 characters and include uppercase, lowercase, numbers, and special characters";
            }
            if ($password !== $confirm_password) {
                $errors[] = "Passwords do not match";
            }
            if (empty($realname) || strlen($realname) > 16) {
                $errors[] = "Real name is required and must not exceed 16 characters";
            }
            if (empty($errors)) {
                try {
                    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetchColumn() > 0) {
                        $errors[] = "Username already exists";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $db->prepare("INSERT INTO users (username, password, realname) VALUES (?, ?, ?)");
                        $stmt->execute([$username, $hashed_password, $realname]);
                        echo '<div class="alert alert-success">User registered successfully!</div>';
                        $_POST = array();
                    }
                } catch (PDOException $e) {
                    error_log("Registration Error: " . $e->getMessage());
                    $errors[] = "An error occurred during registration";
                }
            }

            if (!empty($errors)) {
                echo '<div class="alert alert-error">';
                foreach ($errors as $error) {
                    echo htmlspecialchars($error) . '<br>';
                }
                echo '</div>';
            }
        }
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="realname">Real Name:</label>
                <input type="text" id="realname" name="realname"
                    value="<?php echo htmlspecialchars($_POST['realname'] ?? ''); ?>" required>
            </div>
            <div class="button-group">
                <button type="submit" class="btn">Register</button>
                <a href="index.php" class="btn-back">Back to Login</a>
            </div>
        </div>
        </form>

        
    </div>
</body>

</html>
