<?php
include '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

// Function to safely execute queries
function safeQuery($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Query failed: " . mysqli_error($conn) . " | SQL: " . $sql);
        return false;
    }
    return $result;
}

// Get today's date for filtering
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last_week = date('Y-m-d', strtotime('-7 days'));

// 1. User Statistics
$result = safeQuery($conn, "SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

$result = safeQuery($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = '$today'");
$stats['new_users_today'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;

// 2. Product Statistics
$result = safeQuery($conn, "SELECT COUNT(*) as total FROM products");
$stats['total_products'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

$result = safeQuery($conn, "SELECT COUNT(*) as count FROM products WHERE stock <= 5");
$stats['critical_stock'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;

$result = safeQuery($conn, "SELECT COUNT(*) as count FROM products WHERE stock = 0");
$stats['out_of_stock'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;

// 3. Order Statistics
$result = safeQuery($conn, "SELECT COUNT(*) as total FROM orders");
$stats['total_orders'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

$result = safeQuery($conn, "SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = '$today'");
$stats['orders_today'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;

$result = safeQuery($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $result ? mysqli_fetch_assoc($result)['count'] : 0;

$result = safeQuery($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
$stats['total_revenue'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

$result = safeQuery($conn, "SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = '$today' AND status = 'completed'");
$stats['revenue_today'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

// 4. Category Statistics
$result = safeQuery($conn, "SELECT COUNT(*) as total FROM categories");
$stats['total_categories'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

// 5. Message Statistics
$result = safeQuery($conn, "SELECT COUNT(*) as total FROM messages WHERE status = 'unread'");
$stats['unread_messages'] = $result ? mysqli_fetch_assoc($result)['total'] : 0;

//  ANALYTICS DATA 

// Top Selling Products (Last 7 days)
$top_products = safeQuery($conn, "
    SELECT p.id, p.name, p.image, p.price, 
           COALESCE(SUM(oi.quantity), 0) as total_sold,
           p.stock
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id 
        AND o.status = 'completed'
        AND o.order_date >= '$last_week'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");

// Recent Orders with Details
$recent_orders = safeQuery($conn, "
    SELECT o.*, u.name as customer_name, u.email as customer_email,
           COUNT(oi.id) as items_count
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.order_date DESC 
    LIMIT 6
");

// Critical Stock Products
$critical_stock = safeQuery($conn, "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.stock <= 5 
    ORDER BY p.stock ASC 
    LIMIT 5
");

// Recent Customer Messages
$recent_messages = safeQuery($conn, "
    SELECT m.*, 
           CASE 
               WHEN TIMESTAMPDIFF(HOUR, m.created_at, NOW()) < 24 
               THEN CONCAT(TIMESTAMPDIFF(HOUR, m.created_at, NOW()), 'h ago')
               ELSE CONCAT(TIMESTAMPDIFF(DAY, m.created_at, NOW()), 'd ago')
           END as time_ago
    FROM messages m 
    ORDER BY m.created_at DESC 
    LIMIT 5
");

// Category-wise Product Count
$category_stats = safeQuery($conn, "
    SELECT c.name, COUNT(p.id) as product_count,
           SUM(CASE WHEN p.stock = 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY product_count DESC
    LIMIT 6
");

// ALERTS & NOTIFICATIONS 

$alerts = [];

// Check for critical stock
if ($stats['critical_stock'] > 0) {
    $alerts[] = [
        'type' => 'warning',
        'message' => $stats['critical_stock'] . ' products have critical stock levels',
        'link' => 'products.php?filter=low_stock'
    ];
}

// Check for pending orders
if ($stats['pending_orders'] > 0) {
    $alerts[] = [
        'type' => 'info',
        'message' => $stats['pending_orders'] . ' orders are pending',
        'link' => 'orders.php?status=pending'
    ];
}

// Check for unread messages
if ($stats['unread_messages'] > 0) {
    $alerts[] = [
        'type' => 'danger',
        'message' => $stats['unread_messages'] . ' unread messages',
        'link' => 'messages.php'
    ];
}

// Check for out of stock products
if ($stats['out_of_stock'] > 0) {
    $alerts[] = [
        'type' => 'danger',
        'message' => $stats['out_of_stock'] . ' products are out of stock',
        'link' => 'products.php?filter=out_of_stock'
    ];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery Store Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            color: #333;
        }
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #2c3e50;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #34495e;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .user-info h4 {
            color: white;
            font-size: 0.9rem;
            margin: 0;
        }

        .user-info p {
            color: #bdc3c7;
            font-size: 0.8rem;
            margin: 0;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: #34495e;
            color: white;
        }

        .nav-icon {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .nav-badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            margin-left: auto;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .dashboard-header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .header-title h1 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .header-title p {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .quick-stats {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .stat-badge {
            background: #e9ecef;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .alerts-section {
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: white;
            border: 1px solid #dee2e6;
        }

        .alert-warning {
            border-left: 4px solid #ffc107;
            background: #fff3cd;
        }

        .alert-info {
            border-left: 4px solid #17a2b8;
            background: #d1ecf1;
        }

        .alert-danger {
            border-left: 4px solid #dc3545;
            background: #f8d7da;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-message {
            flex: 1;
        }

        .alert-link {
            color: inherit;
            text-decoration: none;
            font-weight: 500;
        }

        .alert-link:hover {
            text-decoration: underline;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            transition: all 0.3s;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-title h3 {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .stat-icon.users { background: #3498db; }
        .stat-icon.products { background: #e74c3c; }
        .stat-icon.orders { background: #2ecc71; }
        .stat-icon.revenue { background: #f39c12; }
        .stat-icon.categories { background: #9b59b6; }
        .stat-icon.messages { background: #1abc9c; }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .stat-change.positive { color: #28a745; }
        .stat-change.negative { color: #dc3545; }

        .quick-actions {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }

        .quick-actions h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            text-decoration: none;
            color: #495057;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .action-btn i {
            color: #3498db;
            font-size: 1.1rem;
        }

        .data-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .data-card {
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            overflow: hidden;
        }

        .data-card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .data-card-header h3 {
            font-size: 1.1rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .view-all {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .data-card-body {
            padding: 20px;
            max-height: 350px;
            overflow-y: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 12px 10px;
            color: #6c757d;
            font-weight: 600;
            font-size: 0.85rem;
            border-bottom: 2px solid #dee2e6;
        }

        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .product-img {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #dee2e6;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }

        .stock-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-critical { background: #ffeaa7; color: #856404; }
        .stock-out { background: #f8d7da; color: #721c24; }
        .stock-ok { background: #d4edda; color: #155724; }

        .dashboard-footer {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 0.9rem;
            border-top: 1px solid #dee2e6;
            margin-top: 30px;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                background: #2c3e50;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 4px;
                cursor: pointer;
                z-index: 1000;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .data-section {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                grid-template-columns: 1fr;
            }
            
            .quick-stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="menuToggle" style="display: none;">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-store"></i> Grocery Admin</h2>
            <div class="user-profile">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="user-info">
                    <h4><?php echo $_SESSION['username'] ?? 'Administrator'; ?></h4>
                    <p><?php echo $_SESSION['user_role'] ?? 'Admin'; ?></p>
                </div>
            </div>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link">
                    <i class="nav-icon fas fa-box"></i>
                    <span>Products</span>
                    <?php if($stats['critical_stock'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['critical_stock']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link">
                    <i class="nav-icon fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link">
                    <i class="nav-icon fas fa-shopping-bag"></i>
                    <span>Orders</span>
                    <?php if($stats['pending_orders'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['pending_orders']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="messages.php" class="nav-link">
                    <i class="nav-icon fas fa-envelope"></i>
                    <span>Messages</span>
                    <?php if($stats['unread_messages'] > 0): ?>
                        <span class="nav-badge"><?php echo $stats['unread_messages']; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../index.php" class="nav-link">
                    <i class="nav-icon fas fa-store-alt"></i>
                    <span>Visit Store</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="nav-link">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <div class="header-title">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h1>
                <p>Welcome back! Here's what's happening with your store today.</p>
            </div>
            <div class="quick-stats">
                <span class="stat-badge">
                    <i class="fas fa-shopping-cart"></i> <?php echo $stats['orders_today']; ?> Orders Today
                </span>
                <span class="stat-badge">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $stats['critical_stock']; ?> Low Stock
                </span>
                <span class="stat-badge">
                    <i class="fas fa-envelope"></i> <?php echo $stats['unread_messages']; ?> Unread
                </span>
            </div>
        </div>

        <!-- Alerts Section -->
        <?php if(!empty($alerts)): ?>
        <div class="alerts-section">
            <?php foreach($alerts as $alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?>">
                <div class="alert-content">
                    <?php if($alert['type'] == 'warning'): ?>
                        <i class="fas fa-exclamation-triangle"></i>
                    <?php elseif($alert['type'] == 'info'): ?>
                        <i class="fas fa-info-circle"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-circle"></i>
                    <?php endif; ?>
                    <div class="alert-message">
                        <a href="<?php echo $alert['link']; ?>" class="alert-link">
                            <?php echo $alert['message']; ?>
                        </a>
                    </div>
                </div>
                <a href="<?php echo $alert['link']; ?>">
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Total Customers</h3>
                    </div>
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-change <?php echo $stats['new_users_today'] > 0 ? 'positive' : ''; ?>">
                    <?php if($stats['new_users_today'] > 0): ?>
                        <i class="fas fa-arrow-up"></i> <?php echo $stats['new_users_today']; ?> new today
                    <?php else: ?>
                        No new users today
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Total Products</h3>
                    </div>
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                <div class="stat-change <?php echo $stats['out_of_stock'] > 0 ? 'negative' : 'positive'; ?>">
                    <?php if($stats['out_of_stock'] > 0): ?>
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $stats['out_of_stock']; ?> out of stock
                    <?php else: ?>
                        <i class="fas fa-check"></i> All products available
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Today's Orders</h3>
                    </div>
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['orders_today']); ?></div>
                <div class="stat-change <?php echo $stats['pending_orders'] > 0 ? 'negative' : 'positive'; ?>">
                    <?php if($stats['pending_orders'] > 0): ?>
                        <i class="fas fa-clock"></i> <?php echo $stats['pending_orders']; ?> pending
                    <?php else: ?>
                        <i class="fas fa-check"></i> All orders processed
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Total Revenue</h3>
                    </div>
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-value">₹<?php echo number_format($stats['total_revenue'], 2); ?></div>
                <div class="stat-change positive">
                    <?php if($stats['revenue_today'] > 0): ?>
                        <i class="fas fa-arrow-up"></i> ₹<?php echo number_format($stats['revenue_today'], 2); ?> today
                    <?php else: ?>
                        No revenue today
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Categories</h3>
                    </div>
                    <div class="stat-icon categories">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['total_categories']); ?></div>
                <div class="stat-change">
                    Manage product categories
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Messages</h3>
                    </div>
                    <div class="stat-icon messages">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($stats['unread_messages']); ?></div>
                <div class="stat-change">
                    <?php if($stats['unread_messages'] > 0): ?>
                        <span class="negative">Unread messages</span>
                    <?php else: ?>
                        <span class="positive">All messages read</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="products.php" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Add New Product</span>
                </a>
                <a href="orders.php?status=pending" class="action-btn">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Process Orders</span>
                </a>
                <a href="reports.php" class="action-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Generate Reports</span>
                </a>
                <a href="categories.php" class="action-btn">
                    <i class="fas fa-tags"></i>
                    <span>Manage Categories</span>
                </a>
            </div>
        </div>

        <!-- Data Tables Section -->
        <div class="data-section">
            <!-- Recent Orders -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3><i class="fas fa-shopping-bag"></i> Recent Orders</h3>
                    <a href="orders.php" class="view-all">View All →</a>
                </div>
                <div class="data-card-body">
                    <?php if($recent_orders && mysqli_num_rows($recent_orders) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></div>
                                        <small style="color: #666; font-size: 0.85rem;"><?php echo $order['items_count']; ?> items</small>
                                    </td>
                                    <td><strong>₹<?php echo number_format($order['total_amount'] ?? 0, 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status'] ?? 'pending'; ?>">
                                            <?php echo ucfirst($order['status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 0; color: #6c757d;">
                            <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                            No recent orders
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Selling Products -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3><i class="fas fa-chart-line"></i> Top Selling Products</h3>
                    <a href="reports.php" class="view-all">View Report →</a>
                </div>
                <div class="data-card-body">
                    <?php if($top_products && mysqli_num_rows($top_products) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = mysqli_fetch_assoc($top_products)): 
                                    $stock_class = $product['stock'] == 0 ? 'stock-out' : ($product['stock'] <= 5 ? 'stock-critical' : 'stock-ok');
                                ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <img src="../assets/images/<?php echo $product['image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                 class="product-img"
                                                 onerror="this.src='data:image/svg+xml;utf8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2240%22 height=%2240%22%3E%3Crect width=%2240%22 height=%2240%22 fill=%22%23f8f9fa%22/%3E%3C/svg%3E'"
                                            <div>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <small style="color: #666;">₹<?php echo number_format($product['price'], 2); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-weight: 600;"><?php echo $product['total_sold']; ?></td>
                                    <td>
                                        <span class="stock-badge <?php echo $stock_class; ?>">
                                            <?php echo $product['stock']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 0; color: #6c757d;">
                            <i class="fas fa-chart-line" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                            No sales data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Critical Stock -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Critical Stock</h3>
                    <a href="products.php?filter=low_stock" class="view-all">View All →</a>
                </div>
                <div class="data-card-body">
                    <?php if($critical_stock && mysqli_num_rows($critical_stock) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = mysqli_fetch_assoc($critical_stock)): 
                                    $stock_class = $product['stock'] == 0 ? 'stock-out' : 'stock-critical';
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>
                                        <span class="stock-badge <?php echo $stock_class; ?>">
                                            <?php echo $product['stock']; ?> units
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 0; color: #28a745;">
                            <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            All products have sufficient stock
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="dashboard-footer">
            <p>© <?php echo date('Y'); ?> Grocery Store Admin Panel | 
                <span>Last updated: <?php echo date('H:i:s'); ?></span>
            </p>
        </div>
    </main>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        
        function checkScreenSize() {
            if (window.innerWidth <= 992) {
                menuToggle.style.display = 'block';
                sidebar.style.transform = 'translateX(-100%)';
            } else {
                menuToggle.style.display = 'none';
                sidebar.style.transform = 'translateX(0)';
            }
        }
        
        menuToggle.addEventListener('click', () => {
            sidebar.style.transform = sidebar.style.transform === 'translateX(0px)' ? 'translateX(-100%)' : 'translateX(0)';
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992 && 
                !sidebar.contains(e.target) && 
                e.target !== menuToggle && 
                !menuToggle.contains(e.target)) {
                sidebar.style.transform = 'translateX(-100%)';
            }
        });
        
        window.addEventListener('resize', checkScreenSize);
        checkScreenSize();

        // Auto-refresh time every second
        function updateTime() {
            const timeElements = document.querySelectorAll('.dashboard-footer span');
            if (timeElements.length > 1) {
                const now = new Date();
                timeElements[1].textContent = 'Last updated: ' + now.toLocaleTimeString();
            }
        }
        setInterval(updateTime, 1000);

        // Auto-refresh dashboard every 5 minutes
        setTimeout(() => {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>