<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = intval($_GET['id']);

// Get product data
$product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
if (mysqli_num_rows($product_query) == 0) {
    header('Location: products.php');
    exit();
}
$product = mysqli_fetch_assoc($product_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $stock = intval($_POST['stock']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : NULL;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Generate slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    // Handle image upload
    $image = $product['image']; // Keep existing image by default

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $file_type = $_FILES['image']['type'];
        $file_size = $_FILES['image']['size'];

        // Check file type
        if (!in_array($file_type, $allowed_types)) {
            $error = "Only JPG, PNG, and GIF images are allowed!";
        }
        // Check file size
        elseif ($file_size > $max_size) {
            $error = "Image size must be less than 2MB!";
        } else {
            // Generate unique filename
            $image_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = time() . '_' . uniqid() . '.' . $image_extension;
            $target_dir = "../assets/images/products/";

            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Delete old image if not default
                if ($image && $image != 'default-product.jpg') {
                    $old_image_path = "../assets/images/" . $image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $image = 'products/' . $image_name;
            } else {
                $error = "Failed to upload image!";
            }
        }
    }

    // Update product if no error
    if (!isset($error)) {
        $query = "UPDATE products SET 
                 name = '$name',
                 slug = '$slug',
                 price = $price,
                 old_price = " . ($old_price ?: 'NULL') . ",
                 image = '$image',
                 category_id = $category_id,
                 description = '$description',
                 details = '$details',
                 stock = $stock,
                 unit = '$unit',
                 weight = '$weight',
                 brand = '$brand',
                 is_featured = $is_featured
                 WHERE id = $product_id";

        if (mysqli_query($conn, $query)) {
            $success = "Product updated successfully!";

            // Refresh product data
            $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
            $product = mysqli_fetch_assoc($product_query);
        } else {
            $error = "Failed to update product: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Product - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .current-image {
            max-width: 200px;
            border-radius: 8px;
            border: 2px solid #ddd;
            margin-bottom: 15px;
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
        }

        .main-content {
            margin-left: 300px;
            width: calc(100% - 200px);
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php include 'admin-sidebar.php'; ?>

        <main class="main-content">
            <h1>Edit Product</h1>

            <a href="products.php" class="btn">← Back to Products</a>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="edit-form">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-section">
                        <h4>Basic Information</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Product Name:</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Price:</label>
                                <input type="number" name="price" step="0.01" min="0"
                                    value="<?php echo $product['price']; ?>" required>
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
                                        $selected = ($cat['id'] == $product['category_id']) ? 'selected' : '';
                                        echo "<option value='{$cat['id']}' $selected>{$cat['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Stock Quantity:</label>
                                <input type="number" name="stock" min="0"
                                    value="<?php echo $product['stock']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>Additional Details</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Unit:</label>
                                <select name="unit">
                                    <option value="piece" <?php echo $product['unit'] == 'piece' ? 'selected' : ''; ?>>Piece</option>
                                    <option value="kg" <?php echo $product['unit'] == 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                                    <option value="g" <?php echo $product['unit'] == 'g' ? 'selected' : ''; ?>>Gram</option>
                                    <option value="liter" <?php echo $product['unit'] == 'liter' ? 'selected' : ''; ?>>Liter</option>
                                    <option value="ml" <?php echo $product['unit'] == 'ml' ? 'selected' : ''; ?>>Milliliter</option>
                                    <option value="pack" <?php echo $product['unit'] == 'pack' ? 'selected' : ''; ?>>Pack</option>
                                    <option value="dozen" <?php echo $product['unit'] == 'dozen' ? 'selected' : ''; ?>>Dozen</option>
                                    <option value="bottle" <?php echo $product['unit'] == 'bottle' ? 'selected' : ''; ?>>Bottle</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Weight/Size:</label>
                                <input type="text" name="weight"
                                    value="<?php echo htmlspecialchars($product['weight']); ?>"
                                    placeholder="e.g., 1kg, 500ml, 6 pieces">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Brand:</label>
                                <input type="text" name="brand"
                                    value="<?php echo htmlspecialchars($product['brand']); ?>">
                            </div>

                            <div class="form-group">
                                <label>Old Price (Optional):</label>
                                <input type="number" name="old_price" step="0.01" min="0"
                                    value="<?php echo $product['old_price']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>Product Description</h4>
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Product Details:</label>
                            <textarea name="details" rows="3"><?php echo htmlspecialchars($product['details']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>Product Image</h4>

                        <div class="current-image-container">
                            <p><strong>Current Image:</strong></p>
                            <img src="../assets/images/<?php echo $product['image']; ?>"
                                alt="Current Product Image" class="current-image">
                        </div>

                        <div class="form-group">
                            <label>Upload New Image (Optional):</label>
                            <div class="image-upload-container">
                                <div class="image-preview" id="imagePreview">
                                    <img src="" alt="Image Preview" class="image-preview__image" style="display: none;">
                                    <span class="image-preview__default-text">No new image selected</span>
                                </div>
                                <input type="file" name="image" id="imageUpload" accept="image/*"
                                    onchange="previewImage()">
                                <small>Leave empty to keep current image. Accepted formats: JPG, PNG, GIF. Max size: 2MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h4>Settings</h4>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_featured" value="1"
                                    <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                Featured Product
                            </label>
                        </div>
                    </div>

                    <button type="submit" name="update_product" class="btn">Update Product</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function previewImage() {
            const fileInput = document.getElementById('imageUpload');
            const preview = document.getElementById('imagePreview');
            const previewImage = preview.querySelector('.image-preview__image');
            const defaultText = preview.querySelector('.image-preview__default-text');

            const file = fileInput.files[0];

            if (file) {
                const reader = new FileReader();

                reader.addEventListener("load", function() {
                    previewImage.style.display = "block";
                    previewImage.src = reader.result;
                    defaultText.style.display = "none";
                });

                reader.readAsDataURL(file);
            } else {
                previewImage.style.display = "none";
                previewImage.src = "";
                defaultText.style.display = "block";
            }
        }
    </script>
</body>

</html>