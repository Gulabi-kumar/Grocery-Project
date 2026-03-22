<!-- <?php
session_start();
require_once 'db.php';

// Check if order was placed
if (!isset($_SESSION['last_order'])) {
    header('Location: index.php');
    exit();
}

$order = $_SESSION['last_order'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful</title>
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            text-align: center;
            border: 2px solid #4CAF50;
            border-radius: 10px;
            background-color: #f9fff9;
        }
        .success-icon {
            color: #4CAF50;
            font-size: 80px;
            margin-bottom: 20px;
        }
        .order-details {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .order-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-details td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for your purchase. Your order has been confirmed.</p>
        
        <div class="order-details">
            <h3>Order Details</h3>
            <table>
                <tr>
                    <td><strong>Order Number:</strong></td>
                    <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Total Amount:</strong></td>
                    <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                </tr>
                <tr>
                    <td><strong>Payment Method:</strong></td>
                    <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td><span style="color: #4CAF50;">Confirmed</span></td>
                </tr>
                <tr>
                    <td><strong>Estimated Delivery:</strong></td>
                    <td>5-7 Business Days</td>
                </tr>
            </table>
        </div>
        
        <p>You will receive an email confirmation shortly at your registered email address.</p>
        <p>For Cash on Delivery orders, please keep the exact amount ready for our delivery executive.</p>
        
        <a href="index.php" class="btn">Continue Shopping</a>
        <a href="order_tracking.php?order=<?php echo $order['order_number']; ?>" class="btn" style="background: #007bff;">Track Order</a>
        
        <p style="margin-top: 30px; color: #666;">
            <small>Need help? Contact our customer support at support@yourstore.com or call +91-XXXXXXXXXX</small>
        </p>
    </div>
    
</body>
</html> -->