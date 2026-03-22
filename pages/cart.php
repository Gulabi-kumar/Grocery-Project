<?php
session_start();
include '../config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle quantity update/remove
if (isset($_POST['update_quantity'])) {
    $q = $_POST['quantity'] > 0 ? "UPDATE cart SET quantity = {$_POST['quantity']}" : "DELETE FROM cart";
    mysqli_query($conn, "$q WHERE id = '{$_POST['cart_id']}' AND user_id = '$user_id'");
    header('Location: cart.php');
    exit();
}

if (isset($_GET['remove'])) {
    mysqli_query($conn, "DELETE FROM cart WHERE id = '{$_GET['remove']}' AND user_id = '$user_id'");
    header('Location: cart.php');
    exit();
}

// Get image column
$img_col = 'image';
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM products");
while ($col = mysqli_fetch_assoc($col_check)) {
    if ($col['Field'] == 'product_image') $img_col = 'product_image';
}

// Get cart items
$cart_result = mysqli_query($conn, "SELECT c.*, p.name, p.price, p.$img_col as img FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = '$user_id'");

// Calculate subtotal
$subtotal = 0;
$items = [];
while ($item = mysqli_fetch_assoc($cart_result)) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $subtotal += $item['subtotal'];
    $items[] = $item;
}

// Coupon handling
$discount = 0;
$msg = $error = '';

if (isset($_POST['apply_coupon'])) {
    $code = mysqli_real_escape_string($conn, $_POST['coupon_code']);

    $coupon = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT * FROM coupons WHERE code = '$code' AND is_active = 1 
         AND valid_from <= NOW() AND valid_to >= NOW() 
         AND (usage_limit = 0 OR used_count < usage_limit)"
    ));

    if ($coupon) {
        if ($subtotal >= $coupon['min_order']) {
            $discount = $coupon['discount_type'] == 'percentage' ?
                min($subtotal * $coupon['discount_value'] / 100, $coupon['max_discount'] ?: INF) :
                $coupon['discount_value'];

            $_SESSION['coupon'] = ['code' => $coupon['code'], 'discount' => $discount];
            $msg = "Coupon applied! Saved ₹" . number_format($discount, 2);
        } else $error = "Min order ₹" . number_format($coupon['min_order'], 2);
    } else $error = "Invalid/expired coupon";
}

if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['coupon']);
    header('Location: cart.php');
    exit();
}

if (isset($_SESSION['coupon'])) {
    $discount = $_SESSION['coupon']['discount'];
    $coupon_code = $_SESSION['coupon']['code'];
}
$total = $subtotal - $discount;
?>
<!DOCTYPE html>
<html>

<head>
    <title>My Cart - FreshGrocer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .cart-container h2 {
            text-align: center;
            color: #166534;
            margin-bottom: 30px;
        }
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .cart-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            color: #374151;
            font-weight: 600;
        }

        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .cart-table tr:hover {
            background: #f9fafb;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-info img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #e5e7eb;
            padding: 5px;
            background: #f8f9fa;
        }

        .product-name {
            font-weight: 500;
            color: #111827;
        }

        .quantity-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .quantity-form input[type="number"] {
            width: 70px;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            text-align: center;
        }

        .quantity-form button {
            padding: 8px 15px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .quantity-form button:hover {
            background: #15803d;
        }

        .remove-btn {
            color: #dc2626;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #dc2626;
            border-radius: 5px;
            display: inline-block;
            font-size: 14px;
        }

        .remove-btn:hover {
            background: #dc2626;
            color: white;
        }

        .coupon-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .coupon-input-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
            font-size: 14px;
        }

        .coupon-input-wrapper {
            display: flex;
            gap: 10px;
        }

        .coupon-input-wrapper input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 14px;
        }

        .coupon-input-wrapper button {
            padding: 10px 25px;
            background: #16a34a;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
        }

        .coupon-input-wrapper button:hover {
            background: #15803d;
        }

        .applied-coupon {
            background: #d1e7dd;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .coupon-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .coupon-badge {
            background: #166534;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .coupon-savings {
            color: #0f5132;
            font-weight: 600;
        }

        .remove-coupon {
            color: #dc2626;
            text-decoration: none;
            padding: 5px 15px;
            border: 1px solid #dc2626;
            border-radius: 5px;
            font-size: 14px;
        }

        .remove-coupon:hover {
            background: #dc2626;
            color: white;
        }

        .coupon-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .coupon-error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .cart-summary {
            text-align: right;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .summary-row.total {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
            border-top: 2px solid #e5e7eb;
            margin-top: 10px;
            padding-top: 15px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin-left: 10px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }

        .btn:hover {
            background: #15803d;
        }

        .btn.continue {
            background: #6b7280;
        }

        .btn.continue:hover {
            background: #4b5563;
        }

        .empty-cart {
            text-align: center;
            padding: 50px;
        }

        .empty-cart p {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .cart-table {
                display: block;
                overflow-x: auto;
            }

            .cart-table th,
            .cart-table td {
                white-space: nowrap;
            }

            .cart-table .product-info {
                white-space: normal;
                min-width: 200px;
            }

            .quantity-form {
                flex-direction: column;
                gap: 5px;
            }

            .coupon-section {
                flex-direction: column;
                align-items: stretch;
            }

            .coupon-input-wrapper {
                flex-direction: column;
            }

            .cart-summary {
                text-align: center;
            }

            .summary-row {
                justify-content: center;
            }

            .btn {
                display: block;
                margin: 10px 0;
                width: 100%;
            }

            .applied-coupon {
                flex-direction: column;
                text-align: center;
            }

            .coupon-info {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .cart-container {
                margin: 20px 10px;
                padding: 15px;
            }

            .btn{
                width: 80%;
            }
            .product-info {
                flex-direction: column;
                text-align: center;
            }

            .cart-table td {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="cart-container">
        <h2>My Shopping Cart</h2>

        <?php if ($msg): ?><div class="coupon-success"><?= $msg ?></div><?php endif; ?>
        <?php if ($error): ?><div class="coupon-error"><?= $error ?></div><?php endif; ?>

        <?php if ($items): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $img = $item['img'] ? "../assets/images/{$item['img']}" : '../assets/images/default-product.jpg'; ?>
                        <tr>
                            <td>
                                <div class="product-info"><img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='../assets/images/default-product.jpg';"><span><?= htmlspecialchars($item['name']) ?></span></div>
                            </td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <form method="POST" class="quantity-form"><input type="hidden" name="cart_id" value="<?= $item['id'] ?>"><input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1"><button type="submit" name="update_quantity">Update</button></form>
                            </td>
                            <td>₹<?= number_format($item['subtotal'], 2) ?></td>
                            <td><a href="?remove=<?= $item['id'] ?>" class="remove-btn" onclick="return confirm('Remove this item?')">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (isset($_SESSION['coupon'])): ?>
                <div class="applied-coupon">
                    <div><span class="coupon-badge"><?= $_SESSION['coupon']['code'] ?></span> <span class="coupon-savings">Saved: ₹<?= number_format($_SESSION['coupon']['discount'], 2) ?></span></div>
                    <a href="?remove_coupon=1" class="remove-coupon">Remove</a>
                </div>
            <?php else: ?>
                <div class="coupon-section">
                    <form method="POST" class="coupon-input-wrapper">
                        <input type="text" name="coupon_code" placeholder="Enter coupon code" value="<?= htmlspecialchars($coupon_code ?? '') ?>">
                        <button type="submit" name="apply_coupon">Apply</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="cart-summary">
                <div class="summary-row"><span>Subtotal:</span><span>₹<?= number_format($subtotal, 2) ?></span></div>
                <?php if ($discount > 0): ?><div class="summary-row"><span>Discount:</span><span style="color:#16a34a;">-₹<?= number_format($discount, 2) ?></span></div><?php endif; ?>
                <div class="summary-row total"><span>Total:</span><span>₹<?= number_format($total, 2) ?></span></div>
                <a href="checkout.php" class="btn">Checkout</a>
                <a href="../shop.php" class="btn continue">Shop</a>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="../shop.php" class="btn">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>

</html>