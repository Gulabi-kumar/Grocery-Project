<?php
include '../config/database.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check  admin is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

function safeQuery($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Query failed: " . mysqli_error($conn) . " | SQL: " . $sql);
        return false;
    }
    return $result;
}

// Set date ranges

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$last_week_start = date('Y-m-d', strtotime('-7 days'));
$last_month_start = date('Y-m-d', strtotime('-30 days'));
$current_year = date('Y');
$last_year = $current_year - 1;

// Default to this month if no period specified
$period = isset($_GET['period']) ? $_GET['period'] : 'this_month';

switch($period) {
    case 'today':
        $start_date = $today;
        $end_date = $today;
        break;
    case 'yesterday':
        $start_date = $yesterday;
        $end_date = $yesterday;
        break;
    case 'this_week':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = $today;
        break;
    case 'last_week':
        $start_date = date('Y-m-d', strtotime('monday last week'));
        $end_date = date('Y-m-d', strtotime('sunday last week'));
        break;
    case 'this_month':
        $start_date = date('Y-m-01');
        $end_date = $today;
        break;
    case 'last_month':
        $start_date = date('Y-m-01', strtotime('-1 month'));
        $end_date = date('Y-m-t', strtotime('-1 month'));
        break;
    case 'this_year':
        $start_date = $current_year . '-01-01';
        $end_date = $today;
        break;
    case 'last_year':
        $start_date = $last_year . '-01-01';
        $end_date = $last_year . '-12-31';
        break;
    default:
        $start_date = date('Y-m-01');
        $end_date = $today;
}

// SALES REPORTS 

// Total Sales for selected period
$result = safeQuery($conn, "
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value,
        COUNT(DISTINCT user_id) as unique_customers
    FROM orders 
    WHERE status = 'completed' 
    AND DATE(order_date) BETWEEN '$start_date' AND '$end_date'
");
$sales_stats = $result ? mysqli_fetch_assoc($result) : [];

// Daily sales trend (last 30 days)
$daily_sales = safeQuery($conn, "
    SELECT 
        DATE(order_date) as sale_date,
        COUNT(*) as orders_count,
        SUM(total_amount) as daily_revenue
    FROM orders 
    WHERE status = 'completed' 
    AND order_date >= DATE_SUB('$today', INTERVAL 30 DAY)
    GROUP BY DATE(order_date)
    ORDER BY sale_date ASC
");

// Monthly sales for chart
$monthly_sales = safeQuery($conn, "
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') as month,
        COUNT(*) as orders,
        SUM(total_amount) as revenue
    FROM orders 
    WHERE status = 'completed'
    AND order_date >= DATE_SUB('$today', INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month ASC
");

// PRODUCT REPORTS 

// Top selling products
$top_products = safeQuery($conn, "
    SELECT 
        p.id,
        p.name,
        p.price,
        c.name as category,
        SUM(oi.quantity) as total_sold,
        SUM(oi.quantity * p.price) as revenue,
        p.stock
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE o.status = 'completed'
    AND o.order_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
");

// Category-wise sales
$category_sales = safeQuery($conn, "
    SELECT 
        c.id,
        c.name,
        COUNT(DISTINCT o.id) as orders,
        SUM(oi.quantity) as items_sold,
        SUM(oi.quantity * p.price) as revenue
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
    WHERE (o.order_date BETWEEN '$start_date' AND '$end_date' OR o.id IS NULL)
    GROUP BY c.id
    ORDER BY revenue DESC
");

// CUSTOMER REPORTS 

// Top customers
$top_customers = safeQuery($conn, "
    SELECT 
        u.id,
        u.name,
        u.email,
        COUNT(o.id) as orders_count,
        SUM(o.total_amount) as total_spent,
        MAX(o.order_date) as last_order
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id AND o.status = 'completed'
    WHERE o.order_date BETWEEN '$start_date' AND '$end_date' OR o.id IS NULL
    GROUP BY u.id
    HAVING orders_count > 0
    ORDER BY total_spent DESC
    LIMIT 10
");

// New customers trend
$new_customers = safeQuery($conn, "
    SELECT 
        DATE(created_at) as join_date,
        COUNT(*) as new_customers
    FROM users
    WHERE created_at BETWEEN '$start_date' AND '$end_date'
    GROUP BY DATE(created_at)
    ORDER BY join_date ASC
");

// INVENTORY REPORTS 

// Low stock products
$low_stock = safeQuery($conn, "
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.stock <= 10 AND p.stock > 0
    ORDER BY p.stock ASC
    LIMIT 10
");

// Out of stock products
$out_of_stock = safeQuery($conn, "
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.stock = 0
    ORDER BY p.name ASC
");

//  GENERATE CSV REPORT 
if(isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="grocery_report_' . $export_type . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    switch($export_type) {
        case 'sales':
            fputcsv($output, ['Date', 'Orders', 'Revenue']);
            while($row = mysqli_fetch_assoc($daily_sales)) {
                fputcsv($output, [
                    $row['sale_date'],
                    $row['orders_count'],
                    $row['daily_revenue']
                ]);
            }
            break;
            
        case 'products':
            fputcsv($output, ['Product ID', 'Product Name', 'Category', 'Price', 'Sold Qty', 'Revenue', 'Stock']);
            while($row = mysqli_fetch_assoc($top_products)) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['category'],
                    $row['price'],
                    $row['total_sold'],
                    $row['revenue'],
                    $row['stock']
                ]);
            }
            break;
            
        case 'customers':
            fputcsv($output, ['Customer ID', 'Name', 'Email', 'Orders', 'Total Spent', 'Last Order']);
            while($row = mysqli_fetch_assoc($top_customers)) {
                fputcsv($output, [
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['orders_count'],
                    $row['total_spent'],
                    $row['last_order']
                ]);
            }
            break;
    }
    
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Grocery Store Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
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
            margin-top: 0;
            margin-bottom: 20px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
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

        .nav-link:hover, 
        .nav-link.active {
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
            margin-left: 250px;
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }

        .back-button:hover {
            background: #2980b9;
        }

        .reports-header {
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

        .period-selector {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .period-btn {
            padding: 8px 15px;
            background: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
            text-decoration: none;
            color: #333;
        }

        .period-btn:hover {
            background: #dee2e6;
            text-decoration: none;
        }

        .period-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .export-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .export-btn {
            padding: 8px 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .export-btn:hover {
            background: #219653;
            text-decoration: none;
        }

        .export-btn.secondary {
            background: #6c757d;
        }

        .export-btn.secondary:hover {
            background: #5a6268;
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

        .stat-icon.revenue { background: #3498db; }
        .stat-icon.orders { background: #e74c3c; }
        .stat-icon.customers { background: #2ecc71; }
        .stat-icon.avg { background: #f39c12; }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-period {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .charts-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .chart-header {
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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

        .data-card-body {
            padding: 20px;
            max-height: 400px;
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
            position: sticky;
            top: 0;
            background: #f8f9fa;
        }

        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .stock-status {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .stock-low { background: #fff3cd; color: #856404; }
        .stock-out { background: #f8d7da; color: #721c24; }
        .stock-ok { background: #d4edda; color: #155724; }

        .summary-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
        }

        .summary-card h3 {
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .summary-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
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
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .data-section {
                grid-template-columns: 1fr;
            }
            
            .period-selector {
                flex-direction: column;
            }
            
            .export-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
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
                <a href="dashboard.php" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link">
                    <i class="nav-icon fas fa-box"></i>
                    <span>Products</span>
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
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link active">
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
        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>

        <!-- Reports Header -->
        <div class="reports-header">
            <div class="header-title">
                <h1><i class="fas fa-chart-bar"></i> Sales & Analytics Reports</h1>
                <p>Analyze your store performance from <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?></p>
            </div>
            
            <!-- Period Selector -->
            <div class="period-selector">
                <a href="?period=today" class="period-btn <?php echo $period == 'today' ? 'active' : ''; ?>">
                    Today
                </a>
                <a href="?period=yesterday" class="period-btn <?php echo $period == 'yesterday' ? 'active' : ''; ?>">
                    Yesterday
                </a>
                <a href="?period=this_week" class="period-btn <?php echo $period == 'this_week' ? 'active' : ''; ?>">
                    This Week
                </a>
                <a href="?period=last_week" class="period-btn <?php echo $period == 'last_week' ? 'active' : ''; ?>">
                    Last Week
                </a>
                <a href="?period=this_month" class="period-btn <?php echo $period == 'this_month' ? 'active' : ''; ?>">
                    This Month
                </a>
                <a href="?period=last_month" class="period-btn <?php echo $period == 'last_month' ? 'active' : ''; ?>">
                    Last Month
                </a>
                <a href="?period=this_year" class="period-btn <?php echo $period == 'this_year' ? 'active' : ''; ?>">
                    This Year
                </a>
                <a href="?period=last_year" class="period-btn <?php echo $period == 'last_year' ? 'active' : ''; ?>">
                    Last Year
                </a>
            </div>
            
            <!-- Export Actions -->
            <div class="export-actions">
                <a href="?export=sales&period=<?php echo $period; ?>" class="export-btn">
                    <i class="fas fa-file-export"></i> Export Sales CSV
                </a>
                <a href="?export=products&period=<?php echo $period; ?>" class="export-btn secondary">
                    <i class="fas fa-file-export"></i> Export Products CSV
                </a>
                <a href="?export=customers&period=<?php echo $period; ?>" class="export-btn secondary">
                    <i class="fas fa-file-export"></i> Export Customers CSV
                </a>
                <a href="#" onclick="window.print()" class="export-btn secondary">
                    <i class="fas fa-print"></i> Print Report
                </a>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-card">
            <h3>Period Summary</h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Total Revenue</div>
                    <div class="summary-value">₹<?php echo number_format($sales_stats['total_revenue'] ?? 0, 2); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Orders</div>
                    <div class="summary-value"><?php echo number_format($sales_stats['total_orders'] ?? 0); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Average Order Value</div>
                    <div class="summary-value">₹<?php echo number_format($sales_stats['avg_order_value'] ?? 0, 2); ?></div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Unique Customers</div>
                    <div class="summary-value"><?php echo number_format($sales_stats['unique_customers'] ?? 0); ?></div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Total Revenue</h3>
                    </div>
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-value">₹<?php echo number_format($sales_stats['total_revenue'] ?? 0, 2); ?></div>
                <div class="stat-period">Revenue generated</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Total Orders</h3>
                    </div>
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($sales_stats['total_orders'] ?? 0); ?></div>
                <div class="stat-period">Completed orders</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Avg Order Value</h3>
                    </div>
                    <div class="stat-icon avg">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">₹<?php echo number_format($sales_stats['avg_order_value'] ?? 0, 2); ?></div>
                <div class="stat-period">Per order average</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">
                        <h3>Customers</h3>
                    </div>
                    <div class="stat-icon customers">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-value"><?php echo number_format($sales_stats['unique_customers'] ?? 0); ?></div>
                <div class="stat-period">Active customers</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <!-- Sales Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Sales Trend (Last 30 Days)</h3>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Category Performance</h3>
                </div>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Data Tables Section -->
        <div class="data-section">
            <!-- Top Products -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3><i class="fas fa-star"></i> Top Selling Products</h3>
                    <span>Top 10</span>
                </div>
                <div class="data-card-body">
                    <?php if($top_products && mysqli_num_rows($top_products) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Revenue</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = mysqli_fetch_assoc($top_products)): 
                                    $stock_class = $product['stock'] == 0 ? 'stock-out' : ($product['stock'] <= 10 ? 'stock-low' : 'stock-ok');
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <small style="color: #666;"><?php echo htmlspecialchars($product['category']); ?></small>
                                    </td>
                                    <td><strong><?php echo $product['total_sold']; ?></strong></td>
                                    <td><strong>₹<?php echo number_format($product['revenue'], 2); ?></strong></td>
                                    <td>
                                        <span class="stock-status <?php echo $stock_class; ?>">
                                            <?php echo $product['stock']; ?> units
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

            <!-- Top Customers -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3><i class="fas fa-crown"></i> Top Customers</h3>
                    <span>By Spending</span>
                </div>
                <div class="data-card-body">
                    <?php if($top_customers && mysqli_num_rows($top_customers) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($customer = mysqli_fetch_assoc($top_customers)): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($customer['name']); ?></div>
                                        <small style="color: #666;"><?php echo htmlspecialchars($customer['email']); ?></small>
                                    </td>
                                    <td><?php echo $customer['orders_count']; ?></td>
                                    <td><strong>₹<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px 0; color: #6c757d;">
                            <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                            No customer data available
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Low Stock Alert -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h3>
                    <span>Need Restocking</span>
                </div>
                <div class="data-card-body">
                    <?php if($low_stock && mysqli_num_rows($low_stock) > 0): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = mysqli_fetch_assoc($low_stock)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>
                                        <span class="stock-status stock-low">
                                            <?php echo $product['stock']; ?> units left
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

        <!-- Print summary  -->
        <div style="display: none;">
            <h2>Report Summary</h2>
            <p>Period: <?php echo date('M d, Y', strtotime($start_date)); ?> to <?php echo date('M d, Y', strtotime($end_date)); ?></p>
            <p>Generated on: <?php echo date('F d, Y H:i:s'); ?></p>
            <p>Total Revenue: ₹<?php echo number_format($sales_stats['total_revenue'] ?? 0, 2); ?></p>
            <p>Total Orders: <?php echo number_format($sales_stats['total_orders'] ?? 0); ?></p>
        </div>
    </main>

    <script>
        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: [
                        <?php 
                        $dates = [];
                        $revenues = [];
                        if($daily_sales) {
                            mysqli_data_seek($daily_sales, 0);
                            while($row = mysqli_fetch_assoc($daily_sales)) {
                                $dates[] = "'" . date('M d', strtotime($row['sale_date'])) . "'";
                                $revenues[] = $row['daily_revenue'] ?? 0;
                            }
                        }
                        echo implode(',', $dates);
                        ?>
                    ],
                    datasets: [{
                        label: 'Daily Revenue',
                        data: [<?php echo implode(',', $revenues); ?>],
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Category Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        <?php 
                        $categories = [];
                        $categoryRevenue = [];
                        if($category_sales) {
                            mysqli_data_seek($category_sales, 0);
                            $count = 0;
                            while($row = mysqli_fetch_assoc($category_sales) && $count < 10) {
                                if($row['revenue'] > 0) {
                                    $categories[] = "'" . addslashes($row['name']) . "'";
                                    $categoryRevenue[] = $row['revenue'];
                                    $count++;
                                }
                            }
                        }
                        echo implode(',', $categories);
                        ?>
                    ],
                    datasets: [{
                        data: [<?php echo implode(',', $categoryRevenue); ?>],
                        backgroundColor: [
                            '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6',
                            '#1abc9c', '#d35400', '#c0392b', '#7f8c8d', '#34495e'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        }
                    }
                }
            });

            // Auto-refresh reports every 5 minutes
            setTimeout(() => {
                window.location.reload();
            }, 300000);
        });
    </script>
</body>
</html>