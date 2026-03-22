<?php
include 'config/database.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: shop.php');
    exit();
}

$product_id = $_GET['id'];
$product_query = "SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = $product_id";
$product_result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($product_result) == 0) {
    header('Location: shop.php');
    exit();
}

$product = mysqli_fetch_assoc($product_result);

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_SESSION['user_id'])) {
    $quantity = $_POST['quantity'] ?? 1;
    $user_id = $_SESSION['user_id'];

    addToCart($user_id, $product_id, $quantity);
    $success = "Product added to cart!";
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $product['name']; ?> - FreshGrocer</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<style>
    .product-detail-container {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }

    .product-images {
        flex: 1 1 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-images img {
        width: 100%;
        max-width: 450px;
        border-radius: 10px;
        object-fit: cover;
    }

    .product-info {
        flex: 1 1 400px;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .product-info h1 {
        font-size: 28px;
        color: #16a34a;
    }

    .product-info .category {
        font-size: 14px;
        color: #555;
    }

    .product-info .price {
        font-size: 22px;
        font-weight: bold;
        color: #15803d;
    }

    .product-description h3 {
        font-size: 18px;
        color: #333;
        margin-bottom: 8px;
    }

    .product-description p {
        font-size: 14px;
        color: #555;
        line-height: 1.5;
    }

    .success {
        background-color: #d1fae5;
        color: #065f46;
        padding: 10px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .add-to-cart-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-selector label {
        font-weight: 500;
    }

    .quantity-selector input {
        width: 60px;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .action-buttons .btn {
        padding: 10px 20px;
        background-color: #16a34a;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .action-buttons .btn:hover {
        background-color: #15803d;
    }

    @media (max-width: 1024px) {
        .product-detail-container {
            flex-direction: column;
            align-items: center;
        }

        .product-images img {
            max-width: 100%;
            margin-bottom: 20px;
        }

        .product-info {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .product-info h1 {
            font-size: 24px;
        }

        .product-info .price {
            font-size: 20px;
        }
    }
</style>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="product-detail-container">
        <div class="product-images">
            <img src="assets/images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        </div>

        <div class="product-info">
            <h1><?php echo $product['name']; ?></h1>
            <p class="category">Category: <?php echo $product['category_name']; ?></p>
            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>

            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo $product['description']; ?></p>
            </div>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" class="add-to-cart-form">
                <div class="quantity-selector">
                    <label>Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" max="10">
                </div>

                <div class="action-buttons">
                    <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                    <button type="button" onclick="addToWishlist(<?php echo $product['id']; ?>)" class="btn">
                        Add to Wishlist
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        function addToWishlist(productId) {
            <?php if (isset($_SESSION['user_id'])): ?>
                fetch('includes/functions.php?action=add_to_wishlist', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'product_id=' + productId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Added to wishlist!');
                        }
                    });
            <?php else: ?>
                alert('Please login to add to wishlist!');
                window.location.href = 'pages/login.php';
            <?php endif; ?>
        }
    </script>
</body>

</html>