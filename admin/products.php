<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_product'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $stock = $_POST['stock'];

        // Handle image upload
        $image = 'default.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = time() . '_' . $_FILES['image']['name'];
            $target = "../assets/images/" . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = $image_name;
            }
        }

        $query = "INSERT INTO products (name, price, image, category_id, description, stock) 
            VALUES ('$name', $price, '$image', $category_id, '$description', $stock)";

        if (mysqli_query($conn, $query)) {
            $success = "Product added successfully!";
        } else {
            $error = "Failed to add product!";
        }
    }

    // Handle delete
    if (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];
        mysqli_query($conn, "DELETE FROM products WHERE id = $product_id");
        $success = "Product deleted!";
    }
}

// Get all products
$products = mysqli_query($conn, "SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Products - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        color: #333;
        font-size: 14px;
    }

    .admin-container {
        display: flex;
    }

    .sidebar {
        width: 200px;
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        background: #1e293b;
    }

    .main-content {
        margin-left: 220px;
        width: calc(100% - 220px);
        max-width: 1400px;
        padding: 25px;
    }

    h1 {
        font-size: 24px;
        margin-bottom: 25px;
        color: #1e293b;
    }

    h2 {
        font-size: 18px;
        margin-bottom: 20px;
        color: #334155;
    }

    .success {
        background: #16a34a;
        color: white;
        padding: 12px 18px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .error {
        background: #dc2626;
        color: white;
        padding: 12px 18px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .add-form {
        background: white;
        padding: 25px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        margin-bottom: 30px;
    }

    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }

    .form-group {
        flex: 1;
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #1e293b;
        font-size: 14px;
    }

    .form-group input[type="text"],
    .form-group input[type="number"],
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 14px;
        font-family: Arial, sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #16a34a;
        outline: none;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-group input[type="file"] {
        padding: 8px 0;
        font-size: 13px;
    }

    .btn {
        background: #16a34a;
        color: white;
        padding: 10px 24px;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        font-size: 14px;
    }

    .btn:hover {
        background: #15803d;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 13px;
    }

    .data-table th {
        background: #f8fafc;
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
    }

    .data-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .data-table tr:hover {
        background: #f8fafc;
    }

    .data-table img {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .btn-small {
        display: inline-block;
        padding: 6px 14px;
        font-size: 12px;
        text-decoration: none;
        border-radius: 3px;
        border: none;
        cursor: pointer;
        margin-right: 5px;
    }

    .btn-small {
        background: #3b82f6;
        color: white;
    }

    .btn-small:hover {
        background: #2563eb;
    }

    .btn-small.delete {
        background: #ef4444;
        color: white;
    }

    .btn-small.delete:hover {
        background: #dc2626;
    }

    .data-table form {
        display: inline;
    }
    .data-table th:nth-child(1) {
        width: 8%;
    }

    .data-table th:nth-child(2) {
        width: 10%;
    }
    .data-table th:nth-child(3) {
        width: 25%;
    }
    .data-table th:nth-child(4) {
        width: 10%;
    }

    .data-table th:nth-child(5) {
        width: 15%;
    }

    .data-table th:nth-child(6) {
        width: 10%;
    }

    .data-table th:nth-child(7) {
        width: 22%;
    }
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 20px;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }

        .data-table {
            display: block;
            overflow-x: auto;
        }

        .data-table th:nth-child(1),
        .data-table td:nth-child(1) {
            display: none;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 15px;
        }

        h1 {
            font-size: 20px;
        }

        .add-form {
            padding: 20px;
        }
    }
</style>

<body>
    <div class="admin-container">
        <?php include 'admin-sidebar.php'; ?>

        <main class="main-content">
            <h1>Manage Products</h1>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Product Form -->
            <div class="add-form">
                <h2>Add New Product</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Product Name:</label>
                            <input type="text" name="name" required>
                        </div>

                        <div class="form-group">
                            <label>Price:</label>
                            <input type="number" name="price" step="0.01" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Category:</label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php
                                $categories = mysqli_query($conn, "SELECT * FROM categories");
                                while ($cat = mysqli_fetch_assoc($categories)) {
                                    echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Stock:</label>
                            <input type="number" name="stock" value="100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Product Image:</label>
                        <input type="file" name="image" accept="image/*">
                    </div>

                    <button type="submit" name="add_product" class="btn">Add Product</button>
                </form>
            </div>

            <!-- Products List -->
            <div class="products-list">
                <h2>All Products</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <img src="../assets/images/<?php echo $product['image']; ?>" style="width: 70px; height: 70px; object-fit: cover;" alt="<?php echo $product['name']; ?>">
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td>₹<?php echo $product['price']; ?></td>
                                <td><?php echo $product['category_name']; ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="btn-small">Edit</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="delete_product" class="btn-small delete"
                                            onclick="return confirm('Delete this product?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>