<?php
include '../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Validation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email exists
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email already exists!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Check if mobile column exists in the table
            $check_table = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'phone'");
            $mobile_column_exists = (mysqli_num_rows($check_table) > 0);

            if ($mobile_column_exists) {
                // Insert user with mobile number
                $query = "INSERT INTO users (name, email, phone, password) 
                    VALUES ('$name', '$email', '$mobile', '$hashed_password')";
            } else {
                // Insert user without mobile number (fallback)
                $query = "INSERT INTO users (name, email, password) 
                    VALUES ('$name', '$email', '$hashed_password')";
            }

            if (mysqli_query($conn, $query)) {
                if ($mobile_column_exists) {
                    $success = "Successfully registered! Your mobile number is: " . $mobile;
                } else {
                    $success = "Successfully registered! (Mobile number field not available in database)";
                }
                // Clear form data
                $_POST = array();
            } else {
                $error = "Registration failed! Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - FreshGrocer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
    .register-container {
        max-width: 400px;
        margin: 40px auto 30px auto;
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .register-container h2 {
        text-align: center;
        color: #166534;
        margin-bottom: 20px;
        font-size: 22px;
        font-weight: 600;
    }

    .register-container .error {
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

    .register-container .success {
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

    .register-container form .form-group {
        margin-bottom: 16px;
        margin-left: 5px;
    }

    .register-container form label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 13px;
        color: #444;
    }

    .register-container form input[type="text"],
    .register-container form input[type="email"],
    .register-container form input[type="tel"],
    .register-container form input[type="password"] {
        width: 90%;
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
        color: #333;
    }

    .register-container form input[type="text"]:focus,
    .register-container form input[type="email"]:focus,
    .register-container form input[type="tel"]:focus,
    .register-container form input[type="password"]:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.1);
    }

    .register-container form button {
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

    .register-container form button:hover {
        background-color: #15803d;
    }

    .register-container form button:active {
        transform: scale(0.98);
    }

    .register-container form p {
        text-align: center;
        margin-top: 12px;
        font-size: 12px;
        color: #666;
    }

    .register-container form p a {
        color: #16a34a;
        text-decoration: none;
        font-weight: 600;
        font-size: 12px;
    }

    .register-container form p a:hover {
        text-decoration: underline;
    }

    @media (max-width: 500px) {
        .register-container {
            margin: 30px 15px;
            padding: 20px 18px;
            max-width: 100%;
        }

        .register-container h2 {
            font-size: 20px;
            margin-bottom: 18px;
        }
    }

    @media (max-width: 500px) {
        .register-container {
            margin: 40px 20px;
            padding: 30px 20px;
        }
    }
</style>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="register-container">
        <h2>Create Account</h2>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Mobile Number:</label>
                <input type="tel" name="mobile" value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>" pattern="[0-9]{10}" title="10-digit mobile number" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit">Register</button>
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>

</body>

</html>