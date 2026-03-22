<?php
include '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($current) || empty($new) || empty($confirm)) {
        $error = 'Please fill all fields.';
    } elseif ($new !== $confirm) {
        $error = 'New password and confirm password do not match.';
    } elseif (strlen($new) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check current password
        $q = mysqli_query($conn, "SELECT password FROM users WHERE id = $user_id");
        $row = mysqli_fetch_assoc($q);
        if (!$row || !password_verify($current, $row['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            // Update
            $hash = password_hash($new, PASSWORD_DEFAULT);
            if (mysqli_query($conn, "UPDATE users SET password = '$hash' WHERE id = $user_id")) {
                $_SESSION['success'] = 'Password changed successfully.';
                header('Location: profile.php');
                exit();
            } else {
                $error = 'Failed to update password. Try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Change Password - FreshGrocer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
    <style>

    .change-password-container {
        max-width: 450px;
        margin: 30px auto;
        padding: 25px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #eaeaea;
        width: 50%;
    }

    .change-password-container h2 {
        color: #333;
        margin: 0 0 20px 0;
        font-size: 22px;
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 2px solid #4CAF50;
    }

    .error {
        background: #ffebee;
        color: #d32f2f;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        font-size: 14px;
        border-left: 4px solid #f44336;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #555;
        margin-bottom: 6px;
        font-size: 14px;
    }

    .form-group input[type="password"] {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 15px;
        transition: border 0.3s;
    }

    .form-group input[type="password"]:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
    }

    .password-hint {
        font-size: 12px;
        color: #777;
        margin-top: 4px;
    }

    .button-group {
        display: flex;
        gap: 12px;
        margin-top: 10px;
    }

    .btn {
        flex: 1;
        padding: 11px 20px;
        border: none;
        border-radius: 5px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        text-align: center;
        transition: all 0.2s;
    }

    .btn[type="submit"] {
        background: #4CAF50;
        color: white;
    }

    .btn[type="submit"]:hover {
        background: #45a049;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(76, 175, 80, 0.2);
    }

    .btn[href] {
        background: #6c757d;
        color: white;
    }

    .btn[href]:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }

    @media (max-width: 480px) {
        .change-password-container {
            margin: 20px 15px;
            padding: 20px;
        }

        .button-group {
            flex-direction: column;
        }
    }
</style>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="change-password-container">
        <h2>Change Password</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn">Change Password</button>
            <a href="profile.php" class="btn">Cancel</a>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>