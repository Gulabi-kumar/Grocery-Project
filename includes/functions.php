<?php
include '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first', 'redirect' => 'pages/login.php']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'add_to_cart':
            $product_id = $_POST['product_id'] ?? 0;

            if (!$product_id) {
                echo json_encode(['error' => 'Invalid product']);
                exit;
            }

            // Check if product exists
            $product_query = "SELECT * FROM products WHERE id = $product_id";
            $product_result = mysqli_query($conn, $product_query);

            if (!$product_result) {
                echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }

            if (mysqli_num_rows($product_result) == 0) {
                echo json_encode(['error' => 'Product not found']);
                exit;
            }

            // Check if already in cart
            $check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
            $check_result = mysqli_query($conn, $check_query);

            if (!$check_result) {
                echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }

            if (mysqli_num_rows($check_result) > 0) {
                // Update quantity
                $update_query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $product_id";
                $update_result = mysqli_query($conn, $update_query);
                if (!$update_result) {
                    echo json_encode(['error' => 'Failed to update cart: ' . mysqli_error($conn)]);
                    exit;
                }
            } else {
                // Add new
                $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)";
                $insert_result = mysqli_query($conn, $insert_query);
                if (!$insert_result) {
                    echo json_encode(['error' => 'Failed to add to cart: ' . mysqli_error($conn)]);
                    exit;
                }
            }

            // Get new cart count
            $count_query = "SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id";
            $count_result = mysqli_query($conn, $count_query);
            if (!$count_result) {
                echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }
            $count_row = mysqli_fetch_assoc($count_result);

            echo json_encode(['success' => true, 'count' => $count_row['count'] ?? 0]);
            break;

        case 'add_to_wishlist':
            $product_id = $_POST['product_id'] ?? 0;

            if (!$product_id) {
                echo json_encode(['error' => 'Invalid product']);
                exit;
            }

            // Check if already in wishlist
            $check_query = "SELECT * FROM wishlist WHERE user_id = $user_id AND product_id = $product_id";
            $check_result = mysqli_query($conn, $check_query);

            if (!$check_result) {
                echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }

            if (mysqli_num_rows($check_result) == 0) {
                $insert_query = "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)";
                $insert_result = mysqli_query($conn, $insert_query);
                if (!$insert_result) {
                    echo json_encode(['error' => 'Failed to add to wishlist: ' . mysqli_error($conn)]);
                    exit;
                }
            }

            // Get new wishlist count
            $count_query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
            $count_result = mysqli_query($conn, $count_query);
            if (!$count_result) {
                echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }
            $count_row = mysqli_fetch_assoc($count_result);

            echo json_encode(['success' => true, 'count' => $count_row['count']]);
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
    }
}
?>