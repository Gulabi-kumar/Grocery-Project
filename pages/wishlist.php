<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle add to cart from wishlist
if (isset($_GET['add_to_cart'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['add_to_cart']);

    // Check if already in cart
    $cart_check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'");
    if (!$cart_check) {
        header('Location: wishlist.php?error=failed');
        exit();
    }

    if (mysqli_num_rows($cart_check) > 0) {
        $result = mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = '$user_id' AND product_id = '$product_id'");
    } else {
        $result = mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', 1)");
    }

    if ($result !== false) {
        // Remove from wishlist after adding to cart
        mysqli_query($conn, "DELETE FROM wishlist WHERE user_id = '$user_id' AND product_id = '$product_id'");
        header('Location: wishlist.php?success=moved');
        exit();
    } else {
        header('Location: wishlist.php?error=failed');
        exit();
    }
}

// Handle remove from wishlist
if (isset($_GET['remove'])) {
    $wishlist_id = mysqli_real_escape_string($conn, $_GET['remove']);
    $result = mysqli_query($conn, "DELETE FROM wishlist WHERE id = '$wishlist_id' AND user_id = '$user_id'");
    if ($result) {
        header('Location: wishlist.php?success=removed');
        exit();
    } else {
        header('Location: wishlist.php?error=failed');
        exit();
    }
}

// Show success/error message from redirect
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'moved') {
        $success = "Product moved to cart successfully!";
    } elseif ($_GET['success'] == 'removed') {
        $success = "Product removed from wishlist!";
    }
}
if (isset($_GET['error'])) {
    $error = "Operation failed. Please try again.";
}

// Get wishlist items
$wishlist_query = "SELECT w.*, p.name, p.price, p.image, p.description 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = '$user_id' 
    ORDER BY w.id DESC";
$wishlist_result = mysqli_query($conn, $wishlist_query);

// Check for query errors
if (!$wishlist_result) {
    $error = "Database error: " . mysqli_error($conn);
    $wishlist_result = null;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - FreshGrocer</title>
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
            background: #ffffff;
            color: #333333;
            font-size: 13px;
        }

        .wishlist-page {
            max-width: 1000px;
            margin: 0 auto;
            padding: 15px;
            
        }

        .page-header {
            text-align: center;
            margin-bottom: 20px;
            
        }

        .page-header h1 {
            font-size: 22px;
            
            color: #333333;
            margin-bottom: 3px;
            
        }

        .page-header .subtitle {
            color: #666666;
            font-size: 13px;
            
        }

        .header-icon {
            color: #16a34a;
            background: #e8f5e9;
            width: 45px;
            
            height: 45px;
            
            line-height: 45px;
            
            border-radius: 50%;
            margin: 0 auto 8px;
            
            font-size: 18px;
            
        }

        .success-message {
            background: #16a34a;
            color: white;
            padding: 8px 12px;
            
            border-radius: 4px;
            margin-bottom: 15px;
            
            display: flex;
            align-items: center;
            gap: 6px;
            
            font-size: 13px;
           
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 8px 12px;
            
            border-radius: 4px;
            margin-bottom: 15px;
    
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
       
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        
        }

        .wishlist-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
           
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .wishlist-card:hover {
            border-color: #16a34a;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            
        }

       
        .card-image {
            width: 100%;
            height: 140px;
      
            background: #f8f9fa;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
            
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }


        .wishlist-badge {
            position: absolute;
            top: 8px;

            right: 8px;
   
            background: #16a34a;
            color: white;
            padding: 3px 6px;
           
            border-radius: 4px;
            font-size: 10px;
      
            font-weight: 600;
        }

        .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 14px;
         
            font-weight: 600;
            color: #333333;
            margin-bottom: 6px;

            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 36px;
         
            line-height: 1.3;
            
        }

        .product-description {
            color: #666666;
            font-size: 11px;
            
            line-height: 1.3;
            
            margin-bottom: 10px;
           
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 30px;
            
        }

        .price-section {
            margin-bottom: 10px;
            
        }

        .price {
            font-size: 16px;
            
            font-weight: 700;
            color: #16a34a;
        }

        .price::before {
            content: '₹';
            font-size: 13px;
        
            font-weight: 600;
        }

        .card-actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
          
            margin-top: 3px;
         
        }

        .action-btn {
            padding: 6px 10px;
          
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 11px;

            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
           
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cart {
            background: #16a34a;
            color: white;
        }

        .btn-cart:hover {
            background: #15803d;
        }

        .btn-remove {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .btn-remove:hover {
            background: #dc2626;
            color: white;
        }

        .btn-details {
            background: #f3f4f6;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }

        .btn-details:hover {
            background: #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
          
            background: white;
            border-radius: 8px;
            border: 2px dashed #d1d5db;
            grid-column: 1 / -1;
        }

        .empty-icon {
            font-size: 42px;
            
            color: #9ca3af;
            margin-bottom: 12px;
            
        }

        .empty-state h3 {
            font-size: 18px;
            
            color: #333333;
            margin-bottom: 8px;
            
        }

        .empty-state p {
            color: #666666;
            font-size: 13px;
      
            margin-bottom: 16px;
            
        }

        .btn-explore {
            display: inline-flex;
            align-items: center;
            gap: 6px;
           
            padding: 8px 20px;
            background: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
            
        }

        .btn-explore:hover {
            background: #15803d;
        }


        .loading {
            opacity: 0.6;
            pointer-events: none;
            position: relative;
        }

  
        @media (max-width: 992px) {
            .wishlist-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                
            }
        }

        @media (max-width: 768px) {
            .wishlist-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                
            }

            .wishlist-page {
                padding: 12px 10px;
                
            }

            .page-header h1 {
                font-size: 20px;
            }

            .card-image {
                height: 160px;
                
            }
        }

        @media (max-width: 480px) {
            .wishlist-grid {
                grid-template-columns: 1fr;
                gap: 12px;
                
            }

            .wishlist-page {
                padding: 10px 8px;
                
            }

            .page-header h1 {
                font-size: 18px;
                
            }

            .card-image {
                height: 180px;
            }

            .empty-state {
                padding: 30px 15px;
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main class="wishlist-page">
        <div class="page-header">
            <div class="header-icon">
                <i class="fas fa-heart"></i>
            </div>
            <h1>My Wishlist</h1>
            <p class="subtitle">Products you've saved for later</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($wishlist_result && mysqli_num_rows($wishlist_result) > 0): ?>
            <div class="wishlist-grid">
                <?php while ($item = mysqli_fetch_assoc($wishlist_result)): ?>
                    <div class="wishlist-card">
                        <div class="wishlist-badge">
                            <i class="fas fa-heart"></i> Saved
                        </div>

                        <div class="card-image">
                            <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                onerror="this.src='../assets/images/default-product.jpg'">
                        </div>

                        <div class="card-content">
                            <h3 class="product-title"><?php echo htmlspecialchars($item['name']); ?></h3>

                            <?php if (!empty($item['description'])): ?>
                                <p class="product-description">
                                    <?php echo htmlspecialchars(substr($item['description'], 0, 100)) . '...'; ?>
                                </p>
                            <?php endif; ?>

                            <div class="price-section">
                                <span class="price"><?php echo number_format($item['price'], 2); ?></span>
                            </div>

                            <div class="card-actions">
                                <a href="?add_to_cart=<?php echo $item['product_id']; ?>"
                                    class="action-btn btn-cart"
                                    onclick="return confirm('Add this product to your cart?')">
                                    <i class="fas fa-shopping-cart"></i>
                                    Add to Cart
                                </a>
                                <a href="?remove=<?php echo $item['id']; ?>"
                                    class="action-btn btn-remove"
                                    onclick="return confirm('Remove from wishlist?')">
                                    <i class="fas fa-trash"></i>
                                    Remove
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h3>Your Wishlist is Empty</h3>
                <p>You haven't added any products to your wishlist yet.</p>
                <a href="../shop.php" class="btn-explore">
                    <i class="fas fa-store"></i>
                    Browse Products
                </a>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Loading states
        document.addEventListener('DOMContentLoaded', function() {
            const actionButtons = document.querySelectorAll('.action-btn');

            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const card = this.closest('.wishlist-card');
                    if (card && (this.classList.contains('btn-cart') || this.classList.contains('btn-remove'))) {
                        card.classList.add('loading');
                        setTimeout(() => {
                            card.classList.remove('loading');
                        }, 3000);
                    }
                });
            });

            // Auto hide success message
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                setTimeout(() => {
                    const successMsg = document.querySelector('.success-message');
                    if (successMsg) {
                        successMsg.style.opacity = '0';
                        setTimeout(() => successMsg.remove(), 500);
                    }
                }, 3000);

                // Clean URL
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
            }
        });
    </script>
</body>

</html>