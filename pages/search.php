<?php
include 'config/database.php';

if(isset($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, $_GET['q']);
    
    $query = "SELECT * FROM products WHERE name LIKE '%$search%' LIMIT 10";
    $result = mysqli_query($conn, $query);
    
    $products = array();
    while($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($products);
}
?>