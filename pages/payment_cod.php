<?php
session_start();
$conn = mysqli_connect('localhost', 'root', '', 'grocery_store');

if (!$conn) die("Connection failed");

echo '<style>
    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7f6;
        margin: 0;
        padding: 0;
    }
    .container {
        font-weight: normal;
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        text-align: center;
    }
    .success-card {
        border: 2px solid #4CAF50;
        padding: 30px;
        border-radius: 10px;
        background-color: #f9fff9;
    }
    .success-icon {
        color: #4CAF50;
        font-size: 60px;
        margin-bottom: 20px;
    }
    .order-number {
        font-size: 15px;
        color: #333;
        margin-bottom: 20px;
    }
    .order-details {
        text-align: left;
        margin-bottom: 20px;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
    }
    .detail-label {
        font-weight: bold;
        color: #555;
    }
    .detail-value {
        color: #333;
    }
    .total-row {
        border-top: 1px solid #eee;
        font-size: 15px;
    }
    .products-list {
        text-align: left;
    }
    .product-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    .product-info h4 {
        margin: 0 0 5px 0;
    }
    .product-meta {
        color: #777;
        font-size: 14px;
    }
    .action-buttons {
        margin-top: 20px;
    }
    .action-buttons a {
        display: inline-block;
        padding: 12px 25px;
        margin: 0 10px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 600;
    }
    .btn-primary {
        background-color: #4CAF50; 
        color: white; 
    }
    .btn-secondary {
        background-color: #6c757d; 
        color: white;
    }
    .cart-notice {
        background-color: #e8f5e9;
        color: #2e7d32;
        padding: 10px;
        border-radius: 5px;
        margin: 15px 0;
        font-size: 14px;
    }
</style>';

if (isset($_SESSION['checkout_data'])) {
    $data = $_SESSION['checkout_data'];
    $products = $_SESSION['checkout_products'] ?? [];

    $user_id = $_SESSION['user_id'] ?? 0;
    
    mysqli_begin_transaction($conn);
    
    try {
        $address = str_replace("\r\n", ", ", $data['address']);
        $shipping_address = mysqli_real_escape_string($conn, "$address, {$data['city']}, {$data['pincode']}, {$data['state']}");

        $order_number = 'ORD' . time() . rand(1000, 9999);

        // Calculate total items
        $total_items = 0;
        foreach ($products as $product) {
            $total_items += $product['product_quantity'];
        }

        // Insert order
        $sql = "INSERT INTO orders SET
            order_number = '$order_number',
            user_id = '$user_id',
            total_price = '" . ($data['total_amount'] ?? 0) . "',
            shipping_address = '$shipping_address',
            status = 'pending',
            payment_method = '" . ($data['payment_method'] ?? 'cod') . "',
            payment_status = 'pending'";

        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Order insertion failed: " . mysqli_error($conn));
        }

        $order_id = mysqli_insert_id($conn);
        
        // Collect product IDs for cart removal
        $product_ids = [];
        
        // Insert order items
        foreach ($products as $product) {
            $product_id = $product['id'] ?? 0;
            $product_ids[] = $product_id;
            
            $item_sql = "INSERT INTO order_items SET
                order_id = '$order_id',
                product_id = '$product_id',
                product_name = '" . mysqli_real_escape_string($conn, $product['product_name'] ?? '') . "',
                product_price = '" . ($product['product_price'] ?? 0) . "',
                quantity = '" . ($product['product_quantity'] ?? 1) . "',
                subtotal = '" . ($product['product_subtotal'] ?? 0) . "'";
            
            if (!mysqli_query($conn, $item_sql)) {
                throw new Exception("Order item insertion failed: " . mysqli_error($conn));
            }
        }
        
        // Remove items from cart table
        $removed_count = 0;
        if (!empty($product_ids) && $user_id > 0) {
            // Create comma-separated list of product IDs
            $product_ids_str = implode(',', array_map('intval', $product_ids));
            
            // Delete from cart where user_id and product_id match
            $delete_cart_sql = "DELETE FROM cart 
                WHERE user_id = '$user_id' 
                AND product_id IN ($product_ids_str)";
            
            if (!mysqli_query($conn, $delete_cart_sql)) {
                throw new Exception("Cart cleanup failed: " . mysqli_error($conn));
            }
            
            $removed_count = mysqli_affected_rows($conn);
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Clear session
        unset($_SESSION['checkout_data']);
        unset($_SESSION['checkout_products']);
        
        // Also clear cart session if exists
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }

        // Display success message
        echo '<div class="container">
                <div class="success-card">
                    <div class="success-icon">✓</div>
                    <h2>🎉 Order Confirmed!</h2>
                    <p>Thank you for your purchase. Your order has been placed successfully.</p>';
        
        // Show cart removal notice
        if ($removed_count > 0) {
            echo '<div class="cart-notice">✅ ' . $removed_count . ' item(s) removed from your cart</div>';
        }
        
        echo '<div class="order-number">' . $order_number . '</div>
                    
                    <div class="order-details">
                        <div class="detail-row">
                            <span class="detail-label">Order ID:</span>
                            <span class="detail-value">#' . $order_id . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">' . strtoupper($data['payment_method'] ?? 'COD') . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Items:</span>
                            <span class="detail-value">' . $total_items . ' items</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Delivery Address:</span>
                            <span class="detail-value">' . htmlspecialchars($shipping_address) . '</span>
                        </div>
                        <div class="detail-row total-row">
                            <span>Total Amount:</span>
                            <span>₹' . number_format($data['total_amount'], 2) . '</span>
                        </div>
                    </div>';

        // Display products
        if (!empty($products)) {
            echo '<div class="products-list">
                    <h3 style="text-align:left; margin-bottom:20px; color:#333;">Order Summary</h3>';

            foreach ($products as $product) {
                $tax_amount = $product['product_price'] * 0.18;
                echo '<div class="product-item">
                        <div class="product-info">
                            <h4>' . htmlspecialchars($product['product_name']) . '</h4>
                            <div class="product-meta">
                                Tax: ₹' . number_format($tax_amount, 2) . ' |                                              
                                Quantity: ' . $product['product_quantity'] . ' × ₹' . $product['product_price'] . '
                            </div>
                        </div>
                        <div class="product-price">
                            ₹' . number_format($product['product_subtotal'], 2) . '
                        </div>
                      </div>';
            }

            echo '</div>';
        }

        // Action buttons
        echo '<div class="action-buttons">
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                <a href="profile.php" class="btn btn-secondary">View My Orders</a>
              </div>
            </div>
          </div>';
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        echo '<div class="container">
                <div class="success-card" style="background:#fff5f5; border:2px solid #feb2b2;">
                    <div class="success-icon" style="color:#f44336;">✗</div>
                    <h2 style="color:#c53030;">Order Failed</h2>
                    <p>There was an error processing your order. Please try again.</p>
                    <p style="color:#666; font-size:14px;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
                    <div class="action-buttons">
                        <a href="checkout.php" class="btn btn-primary">Try Again</a>
                        <a href="index.php" class="btn btn-secondary">Go Home</a>
                    </div>
                </div>
              </div>';
    }
    
    mysqli_close($conn);
    
} else {
    echo '<div class="container">
            <div class="success-card" style="background:#fff5f5; border:2px solid #feb2b2;">
                <div class="success-icon" style="color:#ff9800;">⚠</div>
                <h2 style="color:#c05621;">No Checkout Data</h2>
                <p>Please complete your checkout process first.</p>
                <div class="action-buttons">
                    <a href="cart.php" class="btn btn-primary">View Cart</a>
                    <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
          </div>';
}
?>