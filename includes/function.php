<?php
// Get cart count
function getCartCount($user_id) {
    global $conn;
    if (!$conn) return 0;
    $query = "SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    if (!$result) return 0;
    $row = mysqli_fetch_assoc($result);
    return $row['count'] ? $row['count'] : 0;
}

// Get wishlist count
function getWishlistCount($user_id) {
    global $conn;
    if (!$conn) return 0;
    $query = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    if (!$result) return 0;
    $row = mysqli_fetch_assoc($result);
    return $row['count'] ?? 0;
}

// Add to cart
function addToCart($user_id, $product_id, $quantity = 1) {
    global $conn;
    
    // Check if product already in cart
    $check_query = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if(mysqli_num_rows($check_result) > 0) {
        // Update quantity
        $update_query = "UPDATE cart SET quantity = quantity + $quantity 
                        WHERE user_id = $user_id AND product_id = $product_id";
        return mysqli_query($conn, $update_query);
    } else {
        // Add new item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) 
                        VALUES ($user_id, $product_id, $quantity)";
        return mysqli_query($conn, $insert_query);
    }
}
?>