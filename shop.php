<?php
session_start();
include 'config/database.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Shop - FreshGrocer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            color: #333333;
            font-size: 14px;
            line-height: 1.5;
        }

        .green {
            color: #16a34a;
        }

        .bg-green {
            background: #16a34a;
        }

        .white {
            color: #ffffff;
        }

        .bg-white {
            background: #ffffff;
        }

        .gray {
            color: #666666;
        }

        .bg-gray {
            background: #f8f9fa;
        }

        .border {
            border: 1px solid #e5e7eb;
        }

        .shop-container {
            display: flex;
            gap: 20px;
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .filters {
            flex: 0 0 200px;
            background: #ffffff;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            position: sticky;
            top: 15px;
            border: 1px solid #e5e7eb;
            height: fit-content;
        }

        .filters h3 {
            font-size: 15px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #333333;
        }

        .filters ul {
            list-style: none;
            margin-bottom: 15px;
        }

        .filters ul li {
            margin-bottom: 6px;
        }

        .filters ul li a {
            text-decoration: none;
            color: #333333;
            display: block;
            padding: 6px 0;
            font-size: 13px;
            border-radius: 4px;
        }

        .filters ul li a:hover {
            color: #16a34a;
            background: #f8f9fa;
            padding-left: 6px;
        }

        .filters select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #333333;
            font-size: 13px;
            cursor: pointer;
        }

        .filters select:focus {
            border-color: #16a34a;
            outline: none;
        }

        .products-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .products-section h2 {
            font-size: 18px;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333333;
        }

        .products-count {
            color: #666666;
            font-size: 12px;
            margin-bottom: 12px;
            font-weight: 400;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .product-card {
            background: #ffffff;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 1px solid #e5e7eb;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: #16a34a;
        }

        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: contain;
            border-radius: 4px;
            margin-bottom: 10px;
            background: #f8f9fa;
            padding: 8px;
        }

        .product-card h3 {
            font-size: 13px;
            margin-bottom: 6px;
            color: #333333;
            height: 32px;
            overflow: hidden;
            font-weight: 500;
        }

        .product-card .price {
            font-size: 14px;
            font-weight: 600;
            color: #16a34a;
            margin-bottom: 10px;
        }

        .product-card .btn {
            padding: 8px 12px;
            background: #16a34a;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 5px;
        }

        .product-card .btn:hover {
            background: #15803d;
        }

        .product-card .btn.wishlist-btn {
            background: transparent;
            color: #666666;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .product-card .btn.wishlist-btn:hover {
            background: #fee2e2;
            color: #ef4444;
            border-color: #ef4444;
        }

        .product-card .btn.wishlist-btn.active {
            background: #fee2e2;
            color: #ef4444;
            border-color: #ef4444;
        }

        .product-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .product-image-container {
            position: relative;
            width: 100%;
        }

        /* Alert Messages */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 350px;
        }

        .alert-message {
            background: #ffffff;
            border-radius: 6px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-left: 4px solid;
            margin-bottom: 8px;
        }

        .alert-message.success {
            background: #f0fdf4;
            border-left-color: #22c55e;
            color: #166534;
        }

        .alert-message.error {
            background: #fef2f2;
            border-left-color: #ef4444;
            color: #991b1b;
        }

        .alert-message.info {
            background: #eff6ff;
            border-left-color: #3b82f6;
            color: #1e40af;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .alert-icon {
            font-size: 18px;
            font-weight: 600;
        }

        .alert-text {
            font-size: 13px;
            font-weight: 500;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #666666;
            padding: 0 4px;
            margin-left: 10px;
        }

        .alert-close:hover {
            color: #333333;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .alert-message.fade-out {
            animation: slideOut 0.3s ease forwards;
        }

        .alert-message {
            animation: slideIn 0.3s ease forwards;
        }

        .filters-section {
            margin-bottom: 20px;
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .clear-all {
            font-size: 11px;
            color: #dc2626;
            text-decoration: none;
        }

        .clear-all:hover {
            text-decoration: underline;
        }

        .active-category {
            color: #16a34a !important;
            font-weight: 600 !important;
            background: #f0f9ff !important;
            border-left: 2px solid #16a34a !important;
            padding-left: 8px !important;
        }

        .no-products {
            grid-column: 1/-1;
            text-align: center;
            padding: 30px;
            color: #666666;
            font-size: 13px;
        }

        .login-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 24px;
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .modal-content h3 {
            color: #0f172a;
            margin-bottom: 15px;
            font-size: 28px;
            font-weight: 700;
        }

        .modal-content p {
            color: #475569;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .modal-btn {
            padding: 14px 30px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border: 2px solid transparent;
            flex: 1;
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
            border: 2px solid #e2e8f0;
        }

        .modal-btn.cancel:hover {
            background: #e2e8f0;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #16a34a;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        @media (max-width: 1024px) {
            .product-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .shop-container {
                flex-direction: column;
                margin: 20px auto;
                padding: 0 12px;
            }

            .filters {
                width: 100%;
                position: static;
            }

            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .alert-container {
                left: 20px;
                right: 20px;
                max-width: none;
            }
        }

        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }

            .product-card img {
                height: 130px;
            }

            .modal-content {
                padding: 30px 20px;
            }

            .modal-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="shop-container">
        <aside class="filters">
            <div class="filters-section">
                <div class="filter-header">
                    <h3>Categories</h3>
                    <?php if (isset($_GET['category'])): ?>
                        <a href="shop.php" class="clear-all">Clear</a>
                    <?php endif; ?>
                </div>
                <ul>
                    <?php
                    $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                    $current_category = isset($_GET['category']) ? $_GET['category'] : '';
                    while ($cat = mysqli_fetch_assoc($categories)) {
                        $active_class = ($current_category == $cat['id']) ? 'active-category' : '';
                        echo "<li><a href='shop.php?category={$cat['id']}' class='{$active_class}'>{$cat['name']}</a></li>";
                    }
                    ?>
                </ul>
            </div>

            <div class="filters-section">
                <div class="filter-header">
                    <h3>Sort by</h3>
                    <?php if (isset($_GET['sort'])): ?>
                        <a href="shop.php<?php echo isset($_GET['category']) ? '?category=' . $_GET['category'] : ''; ?>" class="clear-all">Clear</a>
                    <?php endif; ?>
                </div>
                <select onchange="sortProducts(this.value)">
                    <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name A-Z</option>
                    <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name Z-A</option>
                    <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price Low to High</option>
                    <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price High to Low</option>
                </select>
            </div>
        </aside>

        <main class="products-section">
            <h2>All Products</h2>
            <?php
            $count_query = "SELECT COUNT(*) as total FROM products";
            if (isset($_GET['category'])) {
                $category_id = mysqli_real_escape_string($conn, $_GET['category']);
                $count_query .= " WHERE category_id = '$category_id'";
            }
            $count_result = mysqli_query($conn, $count_query);
            $count_row = mysqli_fetch_assoc($count_result);
            ?>
            <p class="products-count">Showing <?php echo $count_row['total']; ?> products</p>

            <div class="product-grid">
                <?php
                $query = "SELECT p.*, c.name as category_name FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id";

                if (isset($_GET['category'])) {
                    $category_id = mysqli_real_escape_string($conn, $_GET['category']);
                    $query .= " WHERE p.category_id = '$category_id'";
                }

                if (isset($_GET['sort'])) {
                    switch ($_GET['sort']) {
                        case 'price_asc':
                            $query .= " ORDER BY p.price ASC";
                            break;
                        case 'price_desc':
                            $query .= " ORDER BY p.price DESC";
                            break;
                        case 'name_desc':
                            $query .= " ORDER BY p.name DESC";
                            break;
                        default:
                            $query .= " ORDER BY p.name ASC";
                    }
                } else {
                    $query .= " ORDER BY p.name ASC";
                }

                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($product = mysqli_fetch_assoc($result)) {
                        $image_field = 'image'; // default
                        $columns = mysqli_query($conn, "SHOW COLUMNS FROM products");
                        while ($col = mysqli_fetch_assoc($columns)) {
                            if (in_array($col['Field'], ['product_image'])) {
                                $image_field = $col['Field'];
                                break;
                            }
                        }

                        $img_path = 'assets/images/default-product.jpg';
                        $filename = $product[$image_field] ?? '';

                        if (!empty($filename)) {
                            $paths = [
                                '../assets/images/' . $filename,
                            ];

                            foreach ($paths as $path) {
                                if (file_exists($path)) {
                                    $img_path = $path;
                                    break;
                                }
                            }

                            if ($img_path == 'assets/images/default-product.jpg' && $filename) {
                                $img_path = 'assets/images/' . $filename;
                            }
                        }
                ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="<?php echo $img_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                    onerror="this.onerror=null; this.src='assets/images/default-product.jpg';">
                            </div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="price">₹<?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-actions">
                                <button class="btn add-to-cart"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                                    <span style="<?php echo ($product['stock'] <= 0) ? 'color: orange;' : ''; ?>">
                                        <?php echo ($product['stock'] <= 0) ? 'Out of Stock' : '+ Add to Cart'; ?>
                                    </span>
                                </button>
                                <button class="btn wishlist-btn"
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                    ♥ Wishlist
                                </button>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo '<div class="no-products">No products found in this category.</div>';
                }
                ?>
            </div>
        </main>
    </div>

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

        const AlertSystem = {
            init: function() {
                if (!document.querySelector('.alert-container')) {
                    let container = document.createElement('div');
                    container.className = 'alert-container';
                    document.body.appendChild(container);
                }
            },
            show: function(message, type = 'success', duration = 3000) {
                this.init();
                let container = document.querySelector('.alert-container');
                let alertId = 'alert-' + Date.now();
                let alert = document.createElement('div');
                alert.className = 'alert-message ' + type;
                alert.id = alertId;

                let icon = '✓';
                if (type == 'error') icon = '✕';
                if (type == 'warning') icon = '⚠';
                if (type == 'info') icon = 'ℹ';

                alert.innerHTML = '<div class="alert-content"><span class="alert-icon">' + icon + '</span><span class="alert-text">' + message + '</span></div><button class="alert-close" onclick="this.parentElement.classList.add(\'fade-out\'); setTimeout(() => this.parentElement.remove(), 300);">&times;</button>';

                container.appendChild(alert);

                setTimeout(function() {
                    let el = document.getElementById(alertId);
                    if (el) {
                        el.classList.add('fade-out');
                        setTimeout(function() {
                            el.remove();
                        }, 300);
                    }
                }, duration);
            }
        };

        function addToCart(productId, productName, btn) {
            if (!isLoggedIn) {
                loginModal.style.display = 'flex';
                return;
            }

            let originalHTML = btn.innerHTML;
            btn.innerHTML = '<span class="loading-spinner"></span> Adding...';
            btn.disabled = true;

            fetch('pages/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(productId) + '&quantity=1'
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (data.success) {
                        AlertSystem.show(productName + ' added to cart!', 'success');
                        updateCartCount();
                    } else {
                        AlertSystem.show(data.message || 'Failed to add to cart', 'error');
                    }
                })
                .catch(function(error) {
                    AlertSystem.show('Failed to add item to cart', 'error');
                })
                .finally(function() {
                    btn.innerHTML = '+ Add to Cart';
                    btn.disabled = false;
                });
        }

        function toggleWishlist(productId, productName, btn) {
            if (!isLoggedIn) {
                loginModal.style.display = 'flex';
                return;
            }

            let isInWishlist = btn.classList.contains('active');
            btn.innerHTML = '<span class="loading-spinner"></span>';
            btn.disabled = true;

            let url = isInWishlist ? 'pages/remove_from_wishlist.php' : 'pages/add_to_wishlist.php';

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(productId)
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (data.success) {
                        btn.classList.toggle('active');
                        let msg = isInWishlist ? productName + ' removed from wishlist' : productName + ' added to wishlist';
                        let type = isInWishlist ? 'info' : 'success';
                        AlertSystem.show(msg, type);
                    } else {
                        AlertSystem.show(data.message || 'Failed to update wishlist', 'error');
                    }
                })
                .catch(function(error) {
                    AlertSystem.show('Failed to update wishlist', 'error');
                })
                .finally(function() {
                    btn.innerHTML = '♥ Wishlist';
                    btn.disabled = false;
                });
        }

        function updateCartCount() {
            if (!isLoggedIn) return;
            fetch('pages/get_cart_count.php')
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (data.success) {
                        let elements = document.querySelectorAll('.cart-count');
                        for (let i = 0; i < elements.length; i++) {
                            elements[i].textContent = data.count;
                            elements[i].style.display = data.count > 0 ? 'inline-flex' : 'none';
                        }
                    }
                })
                .catch(function(err) {
                    console.error(err);
                });
        }

        function checkWishlistStatus() {
            if (!isLoggedIn) return;
            fetch('pages/get_wishlist_items.php')
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    if (data.success && data.items) {
                        let btns = document.querySelectorAll('.wishlist-btn');
                        for (let i = 0; i < btns.length; i++) {
                            let pid = parseInt(btns[i].dataset.productId);
                            if (data.items.indexOf(pid) > -1) {
                                btns[i].classList.add('active');
                            }
                        }
                    }
                })
                .catch(function(err) {
                    console.error(err);
                });
        }

        function sortProducts(value) {
            let urlParams = new URLSearchParams(window.location.search);
            urlParams.delete('sort');
            if (value) urlParams.set('sort', value);
            window.location.href = 'shop.php?' + urlParams.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            AlertSystem.init();

            let addBtns = document.querySelectorAll('.add-to-cart');
            for (let i = 0; i < addBtns.length; i++) {
                addBtns[i].addEventListener('click', function(e) {
                    e.preventDefault();
                    addToCart(this.dataset.productId, this.dataset.productName, this);
                });
            }

            let wishBtns = document.querySelectorAll('.wishlist-btn');
            for (let i = 0; i < wishBtns.length; i++) {
                wishBtns[i].addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleWishlist(this.dataset.productId, this.dataset.productName, this);
                });
            }

            if (isLoggedIn) {
                updateCartCount();
                checkWishlistStatus();
            }

            let urlParams = new URLSearchParams(window.location.search);
            let catId = urlParams.get('category');
            if (catId) {
                let links = document.querySelectorAll('.filters ul li a');
                for (let i = 0; i < links.length; i++) {
                    if (links[i].href.indexOf('category=' + catId) > -1) {
                        links[i].classList.add('active-category');
                    }
                }
            }
        });

        window.onclick = function(event) {
            if (event.target === loginModal) {
                loginModal.style.display = 'none';
            }
        };
    </script>
</body>

</html>