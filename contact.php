<?php
include 'config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $error = "Please login to send a message!";
    } else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $user_id = $_SESSION['user_id']; 

        $query = "INSERT INTO messages (name, email, message) 
            VALUES ('$name', '$email', '$message')";

        if (mysqli_query($conn, $query)) {
            $success = "Message sent successfully!";
        } else {
            $error = "Failed to send message!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us - FreshGrocer</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
    .contact-container {
        max-width: 1000px;
        margin: 40px auto 30px auto;
        background-color: #fff;
        padding: 25px 25px;
        border-radius: 6px;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .contact-container h2 {
        text-align: center;
        color: #166534;
        margin-bottom: 25px;
        font-size: 22px;
        font-weight: 600;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .contact-container .success {
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 13px;
        line-height: 1.4;
    }

    .contact-container .error {
        background-color: #f8d7da;
        color: #842029;
        border: 1px solid #f5c2c7;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
        font-size: 13px;
        line-height: 1.4;
    }

    .login-prompt {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .login-prompt a {
        color: #166534;
        font-weight: 600;
        text-decoration: none;
    }
    
    .login-prompt a:hover {
        text-decoration: underline;
    }

    .contact-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-top: 20px;
    }

    .contact-info {
        background-color: #f9fafb;
        padding: 20px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .contact-info h3 {
        color: #166534;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #d1d5db;
    }

    .contact-info p {
        margin-bottom: 12px;
        font-size: 13px;
        color: #4b5563;
        line-height: 1.5;
    }

    .contact-info p strong {
        color: #374151;
        font-weight: 600;
        display: inline-block;
        min-width: 70px;
    }

    .contact-form {
        background-color: #f9fafb;
        padding: 20px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .contact-form h3 {
        color: #166534;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 1px solid #d1d5db;
    }

    .contact-form .form-group {
        margin-bottom: 16px;
    }

    .contact-form label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        font-size: 13px;
        color: #444;
    }

    .contact-form input[type="text"],
    .contact-form input[type="email"],
    .contact-form textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #d1d5db;
        border-radius: 5px;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s;
        color: #333;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .contact-form input[type="text"]:focus,
    .contact-form input[type="email"]:focus,
    .contact-form textarea:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.1);
    }

    .contact-form textarea {
        resize: vertical;
        min-height: 100px;
        line-height: 1.4;
    }

    .contact-form .btn {
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

    .contact-form .btn:hover {
        background-color: #15803d;
    }

    .contact-form .btn:active {
        transform: scale(0.98);
    }
    
    .contact-form .btn:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .contact-container {
            margin: 30px 15px;
            padding: 20px 18px;
            max-width: 100%;
        }

        .contact-content {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .contact-info,
        .contact-form {
            padding: 18px;
        }

        .contact-container h2 {
            font-size: 20px;
            margin-bottom: 20px;
        }

        .contact-info h3,
        .contact-form h3 {
            font-size: 16px;
        }

        .contact-info p {
            font-size: 12px;
        }
    }

    @media (max-width: 480px) {
        .contact-container {
            margin: 20px 10px;
            padding: 15px 12px;
        }

        .contact-info,
        .contact-form {
            padding: 15px;
        }

        .contact-container h2 {
            font-size: 18px;
        }

        .contact-info h3,
        .contact-form h3 {
            font-size: 15px;
        }

        .contact-info p {
            font-size: 11px;
            margin-bottom: 10px;
        }

        .contact-form .form-group {
            margin-bottom: 14px;
        }

        .contact-form input[type="text"],
        .contact-form input[type="email"],
        .contact-form textarea {
            padding: 7px 9px;
            font-size: 12px;
        }

        .contact-form .btn {
            padding: 9px;
            font-size: 13px;
        }
    }

    @media (max-width: 360px) {
        .contact-info p strong {
            display: block;
            min-width: auto;
            margin-bottom: 2px;
        }
    }
</style>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="contact-container">
        <h2>Contact Us</h2>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="contact-content">
            <div class="contact-info">
                <h3>Store Information</h3>
                <p><strong>Address:</strong> 123 Grocery Street, City, Country</p>
                <p><strong>Phone:</strong> +1 (234) 567-8900</p>
                <p><strong>Email:</strong> info@freshgrocer.com</p>
                <p><strong>Hours:</strong> Mon-Sat: 8AM-10PM, Sun: 9AM-8PM</p>
            </div>

            <div class="contact-form">
                <h3>Send us a Message</h3>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="login-prompt">
                        🔒 Please <a href="./pages/login.php">login</a> to send a message
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Your Name:</label>
                        <input type="text" name="name" required <?php echo (!isset($_SESSION['user_id'])) ? 'disabled' : ''; ?>>
                    </div>

                    <div class="form-group">
                        <label>Your Email:</label>
                        <input type="email" name="email" required <?php echo (!isset($_SESSION['user_id'])) ? 'disabled' : ''; ?>>
                    </div>

                    <div class="form-group">
                        <label>Message:</label>
                        <textarea name="message" rows="5" required <?php echo (!isset($_SESSION['user_id'])) ? 'disabled' : ''; ?>></textarea>
                    </div>

                    <button type="submit" class="btn" <?php echo (!isset($_SESSION['user_id'])) ? 'disabled' : ''; ?>>
                        <?php echo (isset($_SESSION['user_id'])) ? 'Send Message' : 'Login to Send'; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>