<?php
include '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);


    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header('Location: /Groceryproject/admin/dashboard.php');
            } else {
                header('Location: /Groceryproject/index.php');
            }
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login - FreshGrocer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
    .login-container {
        max-width: 380px;
        margin: 40px auto 30px auto;
        background-color: #fff;
        padding: 25px 25px;
        border-radius: 6px;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-container h2 {
        text-align: center;
        color: #166534;
        margin-bottom: 20px;
        font-size: 22px;
        font-weight: 600;
    }

    .login-container .error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 12px;
        text-align: center;
        font-size: 13px;
        line-height: 1.4;
    }

    .login-container .success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
        padding: 8px 12px;
        border-radius: 4px;
        margin-bottom: 12px;
        text-align: center;
        font-size: 13px;
        line-height: 1.4;
    }

    .login-container form .form-group {
        margin-bottom: 16px;
    }

    .login-container form label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 13px;
        color: #444;
    }

    .login-container form input[type="email"],
    .login-container form input[type="password"] {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
        color: #333;
    }

    .login-container form input[type="email"]:focus,
    .login-container form input[type="password"]:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.1);
    }

    .login-container form button {
        width: 100%;
        padding: 10px;
        background-color: #16a34a;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
    }

    .login-container form button:hover {
        background-color: #15803d;
    }

    .login-container form button:active {
        transform: scale(0.98);
    }

    .login-container form p {
        text-align: center;
        margin-top: 12px;
        color: #666;
    }

    .login-container form p a {
        color: #16a34a;
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
    }

    .login-container form p a:hover {
        text-decoration: underline;
    }

    @media (max-width: 500px) {
        .login-container {
            margin: 30px 15px;
            padding: 20px 18px;
            max-width: 100%;
        }

        .login-container h2 {
            font-size: 20px;
            margin-bottom: 18px;
        }
    }
</style>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Login</button>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>

</body>

</html>