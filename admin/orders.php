<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

// Update order status
if (isset($_POST['update_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_query = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Order #" . $order_id . " status updated to " . ucfirst($status) . "!";
    } else {
        $error = "Error updating order: " . mysqli_error($conn);
    }
}

// Get all orders with user details
$orders_query = "SELECT o.*, u.name as user_name, u.email 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.id 
                 ORDER BY 
                    CASE 
                        WHEN o.status = 'pending' THEN 1
                        WHEN o.status = 'processing' THEN 2
                        WHEN o.status = 'shipped' THEN 3
                        WHEN o.status = 'delivered' THEN 4
                        ELSE 5
                    END,
                    o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

if (!$orders_result) {
    $error = "Error fetching orders: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Orders - Admin | FreshGrocer</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8fafc;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            background: #1e293b;
            color: white;
            overflow-y: auto;
        }

        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 25px 30px;
        }

        .main-content h1 {
            font-size: 28px;
            color: #0f172a;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .main-content h1 i {
            color: #16a34a;
        }

        .success {
            background: #16a34a;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        .error {
            background: #dc2626;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .data-table {
            width: 100%;
            background: white;
            border-radius: 12px;
            border-collapse: collapse;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        .data-table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table th {
            padding: 16px 12px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 14px;
            color: #1e293b;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .customer-info {
            display: flex;
            flex-direction: column;
        }

        .customer-name {
            font-weight: 600;
            color: #0f172a;
        }

        .customer-email {
            font-size: 12px;
            color: #64748b;
            margin-top: 3px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-form {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-form select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 13px;
            color: #1e293b;
            background: white;
            cursor: pointer;
            outline: none;
        }

        .status-form select:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.1);
        }

        .status-form select:hover {
            border-color: #94a3b8;
        }

        .update-btn {
            padding: 8px 16px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .update-btn:hover {
            background: #15803d;
        }

        .update-btn i {
            font-size: 12px;
        }

        .address-preview {
            font-size: 13px;
            color: #475569;
            line-height: 1.5;
            max-width: 200px;
        }

        .btn-small {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: #f1f5f9;
            color: #334155;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
        }

        .btn-small:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-small i {
            font-size: 12px;
        }

        .order-id {
            font-weight: 600;
            color: #16a34a;
        }

        .amount {
            font-weight: 600;
            color: #0f172a;
        }

        .order-date {
            font-size: 13px;
            color: #64748b;
        }

        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .no-orders i {
            font-size: 48px;
            color: #94a3b8;
            margin-bottom: 15px;
        }

        .no-orders p {
            font-size: 16px;
        }

        @media (max-width: 1024px) {
            .data-table {
                display: block;
                overflow-x: auto;
            }

            .status-form {
                flex-direction: column;
                align-items: flex-start;
            }

            .status-form select {
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .sidebar {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php include 'admin-sidebar.php'; ?>

        <main class="main-content">
            <h1>
                <i class="fas fa-shopping-cart"></i>
                All Orders
            </h1>

            <?php if (isset($success)): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($orders_result && mysqli_num_rows($orders_result) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td>
                                    <span class="order-id">#<?php echo $order['id']; ?></span>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <span class="customer-name"><?php echo htmlspecialchars($order['user_name']); ?></span>
                                        <span class="customer-email"><?php echo htmlspecialchars($order['email']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="amount">₹<?php echo number_format($order['total_price'], 2); ?></span>
                                </td>
                                <td>
                                    <span class="order-date"><?php echo date('d M Y', strtotime($order['order_date'])); ?></span>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="update-btn">
                                            <i class="fas fa-sync-alt"></i>
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="address-preview">
                                        <?php 
                                        $address = $order['shipping_address'] ?? 'No address provided';
                                        echo htmlspecialchars(substr($address, 0, 50));
                                        if (strlen($address) > 50): ?>...<?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-orders">
                    <i class="fas fa-box-open"></i>
                    <p>No orders found</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>