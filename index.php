<?php
session_start();
include 'config/database.php';

if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Message handling
$cart_message = $_SESSION['cart_message'] ?? '';
unset($_SESSION['cart_message']);

// Get categories
$categories = [];
$cat_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC LIMIT 12");
while ($row = mysqli_fetch_assoc($cat_result)) $categories[] = $row;

// Get products
$products = [];
$prod_result = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC");
while ($row = mysqli_fetch_assoc($prod_result)) $products[] = $row;

$products_count = count($products);
$isLoggedIn = isset($_SESSION['user_id']);

// Get image column
$img_col = 'image';
$cols = mysqli_query($conn, "SHOW COLUMNS FROM products");
while ($col = mysqli_fetch_assoc($cols)) {
    if (in_array($col['Field'], ['product_image', 'img', 'photo', 'picture'])) $img_col = $col['Field'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshGrocer - Online Grocery Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.6;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .banner {
            margin-top: -10px;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/banner.jpg');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }

        .banner h1 {
            font-size: 40px;
            margin-bottom: 20px;
        }

        .banner p {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .banner .btn {
            display: inline-block;
            padding: 12px 36px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
        }

        .banner .btn:hover {
            background: #15803d;
        }

        .section-heading {
            text-align: center;
            margin: 50px 0 30px;
            font-size: 32px;
            font-weight: 700;
        }

        .section-heading span {
            color: #16a34a;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 50px;
        }

        .category-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: #16a34a;
        }

        .category-image-wrapper {
            height: 200px;
            background: #f1f5f9;
        }

        .category-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .category-content {
            padding: 20px;
        }

        .category-content h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .category-description {
            color: #475569;
            font-size: 13px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .category-content a {
            display: inline-block;
            padding: 10px 24px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
        }

        .category-content a:hover {
            background: #15803d;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 50px;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            border-color: #16a34a;
        }

        .product-image-wrapper {
            height: 200px;
            width: 100%;
            background: #f8fafc;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-card h3 {
            font-size: 17px;
            margin-bottom: 8px;
            color: #0f172a;
            height: 46px;
            overflow: hidden;
            line-height: 1.4;
        }

        .product-category {
            font-size: 12px;
            color: #16a34a;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price-container {
            margin-bottom: 15px;
            display: flex;
            align-items: baseline;
        }

        .current-price {
            font-weight: 700;
            color: #16a34a;
            font-size: 22px;
        }

        .old-price {
            font-size: 14px;
            color: #94a3b8;
            text-decoration: line-through;
            margin-left: 8px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .product-actions button {
            padding: 12px 16px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex: 1;
            transition: all 0.2s ease;
        }

        .add-to-cart {
            background: #16a34a;
            color: white;
        }

        .add-to-cart:hover {
            background: #15803d;
        }

        .add-to-cart:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .wishlist-btn {
            background: white;
            color: #3b82f6;
            border: 2px solid #dbeafe !important;
            flex: 0.5;
            font-size: 16px;
        }

        .wishlist-btn:hover {
            background: #dbeafe;
        }

        .wishlist-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6 !important;
        }

 
        .product-count {
            background: #16a34a;
            color: white;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-left: 10px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #16a34a;
            color: white;
            padding: 14px 24px;
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.4s;
            font-size: 14px;
        }

        .notification.error {
            background: #dc2626;
        }

        .notification.wishlist {
            background: #3b82f6;
        }

        .notification.info {
            background: #3b82f6;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }

            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }

        .loading-spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .login-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .modal-content h3 {
            font-size: 24px;
            margin-bottom: 12px;
        }

        .modal-content p {
            color: #475569;
            margin-bottom: 25px;
            font-size: 15px;
        }

        .modal-buttons {
            display: flex;
            gap: 12px;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            flex: 1;
            font-size: 15px;
        }

        .modal-btn.login {
            background: #16a34a;
            color: white;
        }

        .modal-btn.login:hover {
            background: #15803d;
        }

        .modal-btn.cancel {
            background: #f1f5f9;
            color: #0f172a;
        }

        .modal-btn.cancel:hover {
            background: #e2e8f0;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #475569;
            grid-column: 1/-1;
            background: white;
            border-radius: 20px;
            border: 2px dashed #cbd5e1;
        }

        .empty-state h3 {
            font-size: 22px;
            margin-bottom: 12px;
        }

        .empty-state div {
            font-size: 56px !important;
        }

        .footer {
            background: white;
            padding: 50px 20px 25px;
            border-top: 1px solid #e2e8f0;
            margin-top: 70px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .footer-section p,
        .footer-section a {
            color: #475569;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .footer-section a:hover {
            color: #16a34a;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
            margin-top: 30px;
            color: #64748b;
            font-size: 14px;
        }

        @media (max-width: 1024px) {

            .category-grid,
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .banner {
                height: 350px;
            }

            .banner h1 {
                font-size: 28px;
            }

            .banner p {
                font-size: 16px;
            }

            .banner .btn {
                padding: 10px 30px;
            }

            .category-grid,
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .product-actions {
                flex-direction: column;
            }

            .wishlist-btn {
                flex: 1;
            }

            .modal-buttons {
                flex-direction: column;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .banner {
                height: 300px;
            }

            .banner h1 {
                font-size: 22px;
            }

            .banner p {
                font-size: 15px;
            }

            .banner .btn {
                padding: 8px 24px;
                font-size: 14px;
            }

            .category-grid,
            .product-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }

            .product-image-wrapper {
                height: 220px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <!-- Notification -->
    <?php if ($cart_message): ?>
        <div id="notification" class="notification <?php echo strpos($cart_message, 'Error') !== false ? 'error' : ''; ?>">
            <span>✓</span> <?php echo htmlspecialchars($cart_message); ?>
        </div>
        <script>
            setTimeout(() => {
                let n = document.getElementById('notification');
                if (n) {
                    n.style.animation = 'slideOutRight 0.4s forwards';
                    setTimeout(() => n.remove(), 400);
                }
            }, 3000);
        </script>
    <?php endif; ?>

    <!-- Banner -->
    <section class="banner">
        <div class="banner-content">
            <h1>Fresh Groceries Delivered to Your Doorstep</h1>
            <p>Quality products at affordable prices</p>
            <a href="shop.php" class="btn">Shop Now</a>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories">
        <div class="container">
            <h2 class="section-heading">Shop by <span>Category</span></h2>
            <div class="category-grid">
                <?php if ($categories): ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        $img = '';
                        foreach (['image', 'category_image', 'img', 'photo', 'picture'] as $f) {
                            if (!empty($cat[$f]) && file_exists("assets/categories/" . $cat[$f])) {
                                $img = "assets/categories/" . $cat[$f];
                                break;
                            }
                        }
                        if (!$img) $img = "assets/images/default-category.jpg";
                        $desc = $cat['description'] ?? 'Fresh and quality products available';
                        ?>
                        <div class="category-card">
                            <div class="category-image-wrapper">
                                <img src="<?php echo $img; ?>" alt="<?php echo $cat['name']; ?>" class="category-image" loading="lazy" onerror="this.src='assets/images/default-category.jpg';">
                            </div>
                            <div class="category-content">
                                <h3><?php echo $cat['name']; ?></h3>
                                <p class="category-description"><?php echo htmlspecialchars($desc); ?></p>
                                <a href="shop.php?category=<?php echo $cat['id']; ?>">View Products →</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div></div>
                        <h3>No Categories Found</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Products -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-heading">All <span>Products</span></h2>
            <div class="product-grid">
                <?php if ($products): ?>
                    <?php foreach ($products as $p): ?>
                        <?php
                        $img = '';
                        $fn = $p[$img_col] ?? '';
                        $paths = [
                            "assets/images/" . $fn,
                        ];
                        foreach ($paths as $path) {
                            if ($fn && file_exists($path)) {
                                $img = $path;
                                break;
                            }
                        }
                        if (!$img) $img = "assets/images/default-product.jpg";
                        $price = "₹" . number_format($p['price'], 2);
                        $old = !empty($p['old_price']) ? "₹" . number_format($p['old_price'], 2) : '';
                        $cat_name = $p['category_name'] ?? 'Uncategorized';
                        ?>
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <img src="<?php echo $img; ?>" alt="<?php echo $p['name']; ?>" class="product-image" loading="lazy" onerror="this.src='assets/images/default-product.jpg';">
                            </div>
                            <div class="product-category"><?php echo $cat_name; ?></div>
                            <h3><?php echo $p['name']; ?></h3>
                            <div class="price-container">
                                <span class="current-price"><?php echo $price; ?></span>
                                <?php if ($old): ?><span class="old-price"><?php echo $old; ?></span><?php endif; ?>
                            </div>
                            <div class="product-actions">
                                
                                <button class="add-to-cart <?php echo ($p['stock'] <= 0) ? 'out-of-stock' : '';?>"
                                    <?php echo ($p['stock'] <= 0) ? 'disabled' : ''; ?>
                                    <?php if ($p['stock'] > 0): ?>
                                    data-food-id="<?php echo $p['id']; ?>"
                                    data-product-name="<?php echo $p['name']; ?>"
                                    data-price="<?php echo $p['price']; ?>"
                                    data-stock="<?php echo $p['stock']; ?>"
                                    <?php endif; ?>
                                    style="<?php echo ($p['stock'] <= 0) ? 'background-color:green;color: orange;' : ''; ?>">
                                    <?php echo ($p['stock'] <= 0) ? 'Out of Stock' : '+ Add to Cart'; ?>
                                </button>
                                <button class="wishlist-btn" data-food-id="<?php echo $p['id']; ?>" data-product-name="<?php echo $p['name']; ?>">♥</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div></div>
                        <h3>No Products Found</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div class="login-modal" id="loginModal">
        <div class="modal-content">
            <h3>Login Required</h3>
            <p>Please login to add items to your cart or wishlist.</p>
            <div class="modal-buttons">
                <a href="pages/login.php" class="modal-btn login">Login Now</a>
                <button class="modal-btn cancel" onclick="document.getElementById('loginModal').style.display='none'">Cancel</button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const loginModal = document.getElementById('loginModal');

        function showNotification(msg, type = 'success') {
            let old = document.querySelector('.notification');
            if (old) old.remove();
            let n = document.createElement('div');
            n.className = 'notification ' + type;
            let icon = type == 'success' ? '✓' : type == 'error' ? '⚠' : type == 'wishlist' ? '♥' : 'i';
            n.innerHTML = `<span>${icon}</span>${msg}`;
            document.body.appendChild(n);
            setTimeout(() => {
                n.style.animation = 'slideOutRight 0.4s forwards';
                setTimeout(() => n.remove(), 400);
            }, 3000);
        }

        function addToCart(id, name, btn) {
            if (!isLoggedIn) {
                loginModal.style.display = 'flex';
                return;
            }
            btn.innerHTML = '<span class="loading-spinner"></span> Adding...';
            btn.disabled = true;
            fetch('pages/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(id) + '&quantity=1'
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        showNotification(name + ' added to cart!', 'success');
                        updateCartCount();
                    } else showNotification(d.message || 'Failed', 'error');
                })
                .catch(() => showNotification('Failed to add item', 'error'))
                .finally(() => {
                    btn.innerHTML = '+ Add to Cart';
                    btn.disabled = false;
                });
        }

        function toggleWishlist(id, name, btn) {
            if (!isLoggedIn) {
                loginModal.style.display = 'flex';
                return;
            }
            let isActive = btn.classList.contains('active');
            btn.innerHTML = '<span class="loading-spinner"></span>';
            btn.disabled = true;
            let url = isActive ? 'pages/remove_from_wishlist.php' : 'pages/add_to_wishlist.php';
            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(id)
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        btn.classList.toggle('active');
                        showNotification(isActive ? name + ' removed from wishlist' : name + ' added to wishlist', isActive ? 'info' : 'wishlist');
                    } else showNotification(d.message || 'Failed', 'error');
                })
                .catch(() => showNotification('Failed to update wishlist', 'error'))
                .finally(() => {
                    btn.innerHTML = '♥';
                    btn.disabled = false;
                });
        }

        function updateCartCount() {
            if (!isLoggedIn) return;
            fetch('pages/get_cart_count.php')
                .then(r => r.json())
                .then(d => {
                    if (d.success) document.querySelectorAll('.cart-count').forEach(e => {
                        e.textContent = d.count;
                        e.style.display = d.count > 0 ? 'inline-flex' : 'none';
                    });
                })
                .catch(console.error);
        }

        function checkWishlistStatus() {
            if (!isLoggedIn) return;
            fetch('pages/get_wishlist_items.php')
                .then(r => r.json())
                .then(d => {
                    if (d.success && d.items) document.querySelectorAll('.wishlist-btn').forEach(b => {
                        if (d.items.includes(parseInt(b.dataset.foodId))) b.classList.add('active');
                    });
                })
                .catch(console.error);
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-to-cart').forEach(b => {
                b.addEventListener('click', e => {
                    e.preventDefault();
                    addToCart(b.dataset.foodId, b.dataset.productName, b);
                });
            });
            document.querySelectorAll('.wishlist-btn').forEach(b => {
                b.addEventListener('click', e => {
                    e.preventDefault();
                    toggleWishlist(b.dataset.foodId, b.dataset.productName, b);
                });
            });
            if (isLoggedIn) {
                updateCartCount();
                checkWishlistStatus();
            }
        });

        window.onclick = e => {
            if (e.target === loginModal) loginModal.style.display = 'none';
        };
    </script>
</body>

</html>