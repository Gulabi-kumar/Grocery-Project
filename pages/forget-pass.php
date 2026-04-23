<?php
$conn = new mysqli("localhost", "root", "", "grocery_store");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$debug = "";

echo $message;
echo $debug;

if (isset($_POST['submit'])) {

    $email = trim($_POST['email']);

    $debug .= "Email Entered: " . htmlspecialchars($email) . "<br>";

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    $debug .= "Rows Found: " . $stmt->num_rows . "<br>";

    if ($stmt->num_rows > 0) {

        $debug .= "✅ Email exists in DB<br>";

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            $mail->Username = 'workshopit26@gmail.com';
            $mail->Password = 'enlrdzmtwnavfmmr';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom('workshopit26@gmail.com', 'Fresh Grocery');
            $mail->addAddress($email);

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $link = $protocol . $host . $path . '/reset.php?email=' . urlencode($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password';
            $mail->Body = "
                <h3>Password Reset</h3>
                <p>Click below link:</p>
                <a href='$link'>$link</a>
            ";

            if ($mail->send()) {
                $debug .= "✅ Mail Sent Successfully<br>";
                $message = "Reset link sent to your email.";
            } else {
                $debug .= "❌ Mail Not Sent<br>";
                $message = "Mail not sent!";
            }
        } catch (Exception $e) {
            $debug .= "❌ Mailer Error: " . $mail->ErrorInfo . "<br>";
            $message = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $debug .= "❌ Email NOT found in DB<br>";
        $message = "Email not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Forgot Password</title>

    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .box {
            background: #fff;
            padding: 35px 30px;
            border-radius: 15px;
            width: 360px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 15px;
            transition: 0.3s;
        }

        input:focus {
            border-color: #2575fc;
            box-shadow: 0 0 6px rgba(37, 117, 252, 0.5);
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(90deg, #6a11cb, #2575fc);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        button:active {
            transform: scale(0.98);
        }

        .msg {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .debug {
            font-size: 12px;
            background: #f4f6f8;
            padding: 12px;
            margin-top: 15px;
            border-radius: 8px;
            text-align: left;
            max-height: 120px;
            overflow-y: auto;
            border: 1px solid #ddd;
        }

        /* Smooth animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="box">

        <h2>Forgot Password</h2>

        <p id="msg" style="color:green;">
            <?php if (isset($message)) echo $message; ?>
        </p>

        <form method="POST">
            <input type="email" name="email" id="email" placeholder="Enter email" required>

            <button type="submit" name="submit" id="sendBtn">Send</button>
        </form>

    </div>


</body>

</html>
