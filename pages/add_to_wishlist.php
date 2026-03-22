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

// Check if product exists
$product_check = mysqli_query($conn, "SELECT * FROM products WHERE id = '$product_id'");
if (mysqli_num_rows($product_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Check if already in wishlist
$check_query = "SELECT * FROM wishlist WHERE user_id = '$user_id' AND product_id = '$product_id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    // Add to wishlist
    $insert_query = "INSERT INTO wishlist (user_id, product_id, added_at) 
        VALUES ('$user_id', '$product_id', NOW())";
    
    if (mysqli_query($conn, $insert_query)) {
        echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
}
?>