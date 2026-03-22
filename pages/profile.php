<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
$orders_query = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
</head>
<style>
    .container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .content {
        display: flex;
        gap: 30px;
        margin-top: 30px;
    }

    .left-box {
        flex: 1;
        min-width: 300px;
    }

    .right-box {
        flex: 2;
        min-width: 500px;
    }

    .profile-img {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        margin-bottom: 20px;
        background: #f0f0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid #4CAF50;
    }

    .profile-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .no-img {
        font-size: 60px;
        color: #4CAF50;
        font-weight: bold;
    }

    .profile-details {
        background: #f9f9f9;
        padding: 25px;
        border-radius: 10px;
        border: 1px solid #ddd;
    }

    .profile-details p {
        margin: 12px 0;
        color: #333;
        font-size: 16px;
    }

    .profile-details strong {
        color: #555;
        display: inline-block;
        width: 120px;
    }

    .btn {
        display: inline-block;
        background: #4CAF50;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px 5px 0 0;
        font-size: 14px;
        transition: background 0.3s;
    }

    .btn:hover {
        background: #45a049;
    }

    .orders-container {
        margin-top: 20px;
    }

    .order-card {
        background: white;
        border-left: 4px solid #4CAF50;
        margin-bottom: 15px;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s;
    }

    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .order-id {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .order-date {
        font-size: 14px;
        color: #666;
    }
    .order-details {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .order-info {
        flex: 1;
    }

    .order-info p {
        margin: 8px 0;
        font-size: 14px;
        color: #555;
    }

    .order-info strong {
        color: #333;
        display: inline-block;
        width: 120px;
    }

    .order-total {
        font-size: 18px;
        font-weight: 700;
        color: #4CAF50;
        min-width: 100px;
        text-align: right;
    }

    .order-items {
        margin-top: 15px;
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: #fafafa;
        border-radius: 5px;
    }

    .order-items th {
        background: #f0f0f0;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #ddd;
        font-weight: 600;
        color: #333;
    }

    .order-items td {
        padding: 10px 12px;
        border-bottom: 1px solid #e0e0e0;
    }

    .order-items tr:last-child td {
        border-bottom: none;
    }

    .order-items tr:hover td {
        background: #f5f5f5;
    }

    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        margin-top: 15px;
        border-top: 1px solid #eee;
    }

    .status {
        padding: 6px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status.pending {
        background: #FFF3CD;
        color: #856404;
        border: 1px solid #FFEEBA;
    }

    .status.processing {
        background: #CCE5FF;
        color: #004085;
        border: 1px solid #B8DAFF;
    }

    .status.completed {
        background: #D4EDDA;
        color: #155724;
        border: 1px solid #C3E6CB;
    }

    .status.cancelled {
        background: #F8D7DA;
        color: #721C24;
        border: 1px solid #F5C6CB;
    }

    .status.shipped {
        background: #D1ECF1;
        color: #0C5460;
        border: 1px solid #BEE5EB;
    }

    .view-btn {
        color: #4CAF50;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 4px;
        transition: all 0.3s;
        border: 1px solid #4CAF50;
    }

    .view-btn:hover {
        background: #4CAF50;
        color: white;
        text-decoration: none;
    }

    .right-box>p {
        background: #f9f9f9;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        color: #666;
        border: 1px dashed #ddd;
        font-size: 16px;
    }

    .right-box>p a {
        color: #4CAF50;
        text-decoration: none;
        font-weight: 600;
        margin-left: 5px;
    }

    .right-box>p a:hover {
        text-decoration: underline;
    }

    @media (max-width: 768px) {
        .content {
            flex-direction: column;
        }

        .left-box,
        .right-box {
            min-width: 100%;
        }

        .order-header,
        .order-details,
        .order-footer {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .order-total {
            text-align: left;
        }
        
        .order-items {
            display: block;
            overflow-x: auto;
        }
        
        .order-info strong {
            width: 100px;
        }
    }
</style>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container">
        <h2>My Profile</h2>

        <div class="content">
            <div class="left-box">
                <div class="profile-img">
                    <?php if (!empty($user['image']) && file_exists('../assets/images/' . $user['image'])): ?>
                        <img src="../assets/images/<?php echo $user['image']; ?>" alt="Profile">
                    <?php else: ?>
                        <div class="no-img"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                    <?php endif; ?>
                </div>

                <div class="profile-details">
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong>Email Address:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone Number:</strong> <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span style="color:#999;">Not provided</span>'; ?></p>
                    <p><strong>Delivery Address:</strong> <?php echo !empty($user['address']) ? htmlspecialchars($user['address']) : '<span style="color:#999;">Not provided</span>'; ?></p>
                    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>

                    <a href="edit-profile.php" class="btn"> Edit Profile</a>
                    <a href="change-password.php" class="btn">Change Password</a>
                </div>
            </div>

            <!--  ORDERS SECTION -->
            <div class="right-box">
                <h3>Order History</h3>

                <?php if (mysqli_num_rows($orders_query) > 0): ?>
                    <div class="orders-container">
                        <?php while ($order = mysqli_fetch_assoc($orders_query)): ?>
                            <?php 
                            $items_query = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = {$order['id']}");
                            $items_count = mysqli_num_rows($items_query);
                            
                            ?>
                            
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-id">Order #<?php echo $order['id']; ?></div>
                                    <div class="order-date"> <?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                                </div>
                                <div class="order-details">
                                    <div class="order-info">
                                        <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                                        <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
                                        <p><strong>Total Items:</strong> <?php echo $items_count; ?> item(s)</p>
                                    </div>
                                    <div class="order-total">₹<?php echo number_format($order['total_price'], 2); ?> Tax 18% </div>
                                </div>
                                <?php if ($items_count > 0): ?>
                                <table class="order-items">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = mysqli_fetch_assoc($items_query)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>
                                                <?php 
                                                
                                                if (isset($item['price']) && $item['price'] > 0) {
                                                    echo '₹' . number_format($item['price'], 2);
                                                } else {
                                                    
                                                    if (isset($item['product_price'])) {
                                                        echo '₹' . number_format($item['product_price'], 2);
                                                    } 
                                               
                                                    elseif (isset($item['unit_price'])) {
                                                        echo '₹' . number_format($item['unit_price'], 2);
                                                    }
                                                  
                                                    elseif (isset($item['amount'])) {
                                                        echo '₹' . number_format($item['amount'] / $item['quantity'], 2);
                                                    }
                                                    else {
                                                        echo '<span style="color:#999;">Price not available</span>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>
                                                <?php $id = $item['product_id'];  ?>
                                                <?php 
                                                
                                                $subtotal = 0;
                                                if (isset($item['price']) && $item['price'] > 0) {
                                                    $subtotal = $item['price'] * $item['quantity'];
                                                } elseif (isset($item['product_price'])) {
                                                    $subtotal = $item['product_price'] * $item['quantity'];
                                                } elseif (isset($item['unit_price'])) {
                                                    $subtotal = $item['unit_price'] * $item['quantity'];
                                                } elseif (isset($item['amount'])) {
                                                    $subtotal = $item['amount'];
                                                }
                                                
                                                if ($subtotal > 0) {
                                                    echo '₹' . number_format($subtotal, 2);
                                                } else {
                                                    echo '<span style="color:#999;">-</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <?php endif; ?>

                                <div class="order-footer">
                                    <span class="status <?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    
                                    <a href="order-details.php?pid=<?php echo $id ?>&uid=<?php echo $_SESSION['user_id']; ?>" class="view-btn">View Full Details →</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>You haven't placed any orders yet. <a href="../shop.php">Start shopping</a> to see your orders here!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>