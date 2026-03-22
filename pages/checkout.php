<?php
include '../config/database.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: pages/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
if (!$user_query) {
    die("Database error: " . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($user_query);

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.id as product_id 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = $user_id";
$cart_result = mysqli_query($conn, $cart_query);
if (!$cart_result) {
    die("Database error: " . mysqli_error($conn));
}

// Calculate amounts
$subtotal = 0;
$product_subtotal=0;
$cart_items = [];
$product_order_items= [];
while ($item = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $item;
    $subtotal += $item['price'] * $item['quantity'];

    $product_order_items[] = [
        'id' => $item['product_id'],
        'product_name' => $item['name'],    
        'product_price' => $item['price'],
        'product_subtotal' => $item['price'] * $item['quantity'],
        'product_quantity'=>$item['quantity'],   
    ];
}

// Tax calculation (example: 18% GST)
$tax_rate = 18; // 18% tax
$tax_amount = ($subtotal * $tax_rate) / 100;
$total_amount = $subtotal + $tax_amount;


// Handle checkout form submission 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout_step']) && $_POST['checkout_step'] == 'shipping') {
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $city = mysqli_real_escape_string($conn, $_POST['city'] ?? '');
    $state = mysqli_real_escape_string($conn, $_POST['state'] ?? '');
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';

    // Validate required fields
    $errors = [];
    if (empty($address)) $errors[] = "Address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($state)) $errors[] = "State is required";
    if (empty($pincode)) $errors[] = "Pincode is required";
    if (!empty($pincode) && !preg_match('/^[1-9][0-9]{5}$/', $pincode)) $errors[] = "Enter a valid 6-digit pincode";
    if (empty($payment_method)) $errors[] = "Payment method is required";
    if (empty($cart_items)) $errors[] = "Your cart is empty";

    if (empty($errors)) {
        // Store in session
        $_SESSION['checkout_products'] = $product_order_items;
        $_SESSION['checkout_data'] = [
            'user_id' => $user_id,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'payment_method' => $payment_method,
            'total_amount' => $total_amount,
            'tax_amount' => $tax_amount,
            'Quantity' => array_sum(array_column($cart_items, 'quantity')),
            'product_price' => array_column($cart_items, 'price'),
        ];

        // Redirect to payment processing page based on payment method
        switch ($payment_method) {
            case 'cod':
                header('Location: payment_cod.php');
                exit();
            case 'card':
                header('Location: payment_card.php');
                exit();
            case 'paypal':
                header('Location: payment_paypal.php');
                exit();
            case 'upi':
                header('Location: payment_upi.php');
                exit();
            default:
                $error = "Invalid payment method selected";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

// Check if cart has items for display
$hasItems = !empty($cart_items);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Complete Your Order</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .checkout-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 15px;
        }

        .checkout-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .checkout-header h1 {
            font-size: 20px;
            color: #333;
            margin-bottom: 5px;
        }

        .checkout-header p {
            font-size: 13px;
            color: #666;
        }

        .checkout-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .shipping-section,
        .summary-section,
        .payment-section {
            background: white;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
        }

        .section-title {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #007bff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #007bff;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 13px;
        }

        .required {
            color: #dc3545;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.15);
        }

        .form-control[readonly] {
            background: #f8f9fa;
            color: #666;
            cursor: not-allowed;
        }

        textarea.form-control {
            min-height: 80px;
            resize: vertical;
            font-size: 13px;
        }

        .pincode-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .order-items {
            margin-bottom: 15px;
            max-height: 200px;
            overflow-y: auto;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 500;
            color: #333;
            font-size: 13px;
        }

        .item-qty {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        .item-price {
            font-weight: 600;
            color: #333;
            font-size: 13px;
        }

        .price-breakdown {
            border-top: 1px solid #eee;
            padding-top: 12px;
            margin-top: 12px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
        }

        .price-total {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            margin-top: 12px;
            font-size: 15px;
            font-weight: 600;
            color: #333;
            border: 1px solid #e0e0e0;
        }

        .payment-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 10px;
        }

        .payment-option {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .payment-option:hover {
            border-color: #007bff;
            background: #f8f9fa;
        }

        .payment-option.selected {
            border-color: #007bff;
            background: #e7f3ff;
            box-shadow: 0 0 0 1px #007bff;
        }

        .payment-option input {
            display: none;
        }

        .payment-icon {
            font-size: 20px;
            margin-bottom: 8px;
            color: #666;
        }

        .payment-option.selected .payment-icon {
            color: #007bff;
        }

        .payment-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .payment-desc {
            font-size: 11px;
            color: #666;
            line-height: 1.2;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            text-align: center;
            flex: 1;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-secondary:hover {
            background: #545b62;
            color: white;
        }

        .error-message {
            color: #dc3545;
            font-size: 11px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            font-size: 13px;
        }

        .error {
            border-color: #dc3545 !important;
        }

        .payment-error {
            color: #dc3545;
            font-size: 11px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 4px;
            text-align: center;
            justify-content: center;
        }

        .terms-box {
            margin-top: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
            border: 1px solid #e0e0e0;
        }

        .terms-box p {
            margin-bottom: 5px;
        }

        .terms-box p:last-child {
            margin-bottom: 0;
        }

        .payment-info {
            margin-top: 12px;
            font-size: 11px;
            color: #666;
            text-align: center;
        }

        .empty-cart {
            text-align: center;
            padding: 30px 15px;
            color: #666;
        }

        .empty-cart p {
            margin-bottom: 15px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .checkout-wrapper {
                max-width: 95%;
                padding: 10px;
            }

            .checkout-layout {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .pincode-row {
                grid-template-columns: 1fr;
            }

            .payment-options {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .shipping-section,
            .summary-section,
            .payment-section {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .checkout-header h1 {
                font-size: 18px;
            }

            .order-headline{
                margin-top: 40px;
            }
            .section-title {
                font-size: 15px;
            }

            .price-total {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="checkout-wrapper">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <p class="order-headline">Complete your purchase</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="checkout-layout">
            <!-- Shipping Details -->
            <div class="shipping-section">
                <h2 class="section-title"><i class="fas fa-shipping-fast"></i> Shipping Details</h2>

                <?php if ($hasItems): ?>
                    <form method="POST" id="checkoutForm">
                        <input type="hidden" name="checkout_step" value="shipping">

                        <input type="hidden" name="payment_method" id="payment_method" value="<?php echo isset($_POST['payment_method']) ? htmlspecialchars($_POST['payment_method']) : ''; ?>">

                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="required">*</span></label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Complete Address <span class="required">*</span></label>
                            <textarea name="address" id="address" class="form-control" rows="3" required
                                placeholder="House No., Building, Street, Area, Landmark"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                        </div>

                        <div class="pincode-row">
                            <div class="form-group">
                                <label class="form-label">City <span class="required">*</span></label>
                                <input type="text" name="city" id="city" class="form-control"
                                    value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>"
                                    placeholder="Enter city" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">State <span class="required">*</span></label>
                                <input type="text" name="state" id="state" class="form-control"
                                    value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>"
                                    placeholder="Enter state" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pincode <span class="required">*</span></label>
                            <input type="text" name="pincode" id="pincode" class="form-control"
                                value="<?php echo isset($_POST['pincode']) ? htmlspecialchars($_POST['pincode']) : ''; ?>"
                                placeholder="6-digit pincode" maxlength="6" required>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Proceed to Payment</button>
                            <a href="cart.php" class="btn btn-secondary">Back to Cart</a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="empty-cart">
                        <p>Your cart is empty</p>
                        <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <!-- Order Summary -->
                <div class="summary-section">
                    <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Order Summary</h2>

                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
                            <?php $item_subtotal = $item['price'] * $item['quantity']; ?>
                            <div class="order-item">
                                <div>
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-qty">Qty: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div class="item-price">₹<?php echo number_format($item_subtotal, 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Tax (GST <?php echo $tax_rate; ?>%)</span>
                            <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                        </div>
                        <div class="price-row price-total">
                            <span>Total Amount</span>
                            <span>₹<?php echo number_format($total_amount, 2); ?></span>
                        </div>
                    </div>

                    <div class="terms-box">
                        <p>By completing your purchase, you agree to our Terms of Service.</p>
                        <p>Free shipping on all orders over ₹500.</p>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="payment-section">
                    <h2 class="section-title"><i class="fas fa-credit-card"></i> Payment Method <span class="required">*</span></h2>

                    <div class="payment-options">
                        <label class="payment-option" data-value="cod">
                            <div class="payment-icon"><i class="fas fa-money-bill-wave"></i></div>
                            <div class="payment-name">Cash on Delivery</div>
                            <div class="payment-desc">Pay when delivered</div>
                        </label>

                        <label class="payment-option" data-value="card">
                            <div class="payment-icon"><i class="fas fa-credit-card"></i></div>
                            <div class="payment-name">Credit/Debit Card</div>
                            <div class="payment-desc">Secure card payment</div>
                        </label>

                        <label class="payment-option" data-value="paypal">
                            <div class="payment-icon"><i class="fab fa-paypal"></i></div>
                            <div class="payment-name">PayPal</div>
                            <div class="payment-desc">PayPal payment</div>
                        </label>

                        <label class="payment-option" data-value="upi">
                            <div class="payment-icon"><i class="fas fa-mobile-alt"></i></div>
                            <div class="payment-name">UPI</div>
                            <div class="payment-desc">Google Pay, PhonePe</div>
                        </label>
                    </div>

                    <div class="payment-info">
                        <p>Select your preferred payment method to proceed</p>
                    </div>

                    <!-- This will show payment selection error -->
                    <div id="paymentError" class="payment-error" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i> Please select a payment method
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Store selected payment method
        let selectedPaymentMethod = '<?php echo isset($_POST["payment_method"]) ? htmlspecialchars($_POST["payment_method"]) : ""; ?>';

        // Initialize payment selection
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight previously selected payment method
            if (selectedPaymentMethod) {
                const paymentOption = document.querySelector(`.payment-option[data-value="${selectedPaymentMethod}"]`);
                if (paymentOption) {
                    paymentOption.classList.add('selected');
                }
            }

            document.getElementById('payment_method').value = selectedPaymentMethod;
        });

        // Payment method selection
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all
                document.querySelectorAll('.payment-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                // Add selected class to clicked
                this.classList.add('selected');

                // Get payment method value
                selectedPaymentMethod = this.getAttribute('data-value');

                // Update hidden field
                document.getElementById('payment_method').value = selectedPaymentMethod;

                // Hide payment error if showing
                document.getElementById('paymentError').style.display = 'none';
            });
        });

        // Form validation
        document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
            const address = document.getElementById('address')?.value.trim();
            const city = document.getElementById('city')?.value.trim();
            const state = document.getElementById('state')?.value.trim();
            const pincode = document.getElementById('pincode')?.value.trim();
            const paymentMethod = document.getElementById('payment_method')?.value;
            let isValid = true;

            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));
            document.getElementById('paymentError').style.display = 'none';

            if (!address) {
                isValid = false;
                showError('address', 'Shipping address is required');
            }

            if (!city) {
                isValid = false;
                showError('city', 'City is required');
            }

            if (!state) {
                isValid = false;
                showError('state', 'State is required');
            }

            if (!pincode) {
                isValid = false;
                showError('pincode', 'Pincode is required');
            } else if (!/^[1-9][0-9]{5}$/.test(pincode)) {
                isValid = false;
                showError('pincode', 'Enter a valid 6-digit pincode');
            }

            if (!paymentMethod) {
                isValid = false;
                document.getElementById('paymentError').style.display = 'flex';
                document.getElementById('paymentError').style.justifyContent = 'center';

                // Scroll to payment section
                const paymentSection = document.querySelector('.payment-section');
                if (paymentSection) {
                    paymentSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            }

            if (!isValid) {
                e.preventDefault();
                return false;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;

            return true;
        });

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.classList.add('error');
                const errorMsg = document.createElement('div');
                errorMsg.className = 'error-message';
                errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
                field.parentNode.appendChild(errorMsg);
            }
        }

        // Focus on first empty field
        document.addEventListener('DOMContentLoaded', function() {
            const fields = ['address', 'city', 'state', 'pincode'];
            for (let fieldId of fields) {
                const field = document.getElementById(fieldId);
                if (field && !field.value.trim()) {
                    field.focus();
                    break;
                }
            }
        });

        // Auto-format pincode
        document.getElementById('pincode')?.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });

    </script>
</body>

</html>