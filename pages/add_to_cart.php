<?php
session_start();
header('Content-Type: application/json');
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Check if product_id is provided
if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// Check if product exists and has stock
$product_check = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
if (mysqli_num_rows($product_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

$product = mysqli_fetch_assoc($product_check);
if ($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit();
}

// Check if product already in cart
$check_query = "SELECT * FROM cart WHERE user_id = '$user_id' AND product_id = '$product_id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    // Update quantity
    $cart_item = mysqli_fetch_assoc($check_result);
    $new_quantity = $cart_item['quantity'] + $quantity;
    $update_query = "UPDATE cart SET quantity = '$new_quantity' WHERE id = '{$cart_item['id']}'";
    
    if (mysqli_query($conn, $update_query)) {
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
} else {
    // Insert new item
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity, added_at) 
        VALUES ('$user_id', '$product_id', '$quantity', NOW())";
    
    if (mysqli_query($conn, $insert_query)) {
        echo json_encode(['success' => true, 'message' => 'Product added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
    }
}
?>