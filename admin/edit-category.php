<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

$category_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $current_image = mysqli_real_escape_string($conn, $_POST['current_image']);
    $image_path = $current_image;

    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['category_image']['name'];
        $file_tmp = $_FILES['category_image']['tmp_name'];
        $file_size = $_FILES['category_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file extension
        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size <= 5242880) {
                // Generate unique filename
                $new_filename = uniqid('category_', true) . '.' . $file_ext;
                $upload_path = '../assets/categories/' . $new_filename;

                // Create uploads directory if it doesn't exist
                if (!is_dir('../assets/categories/')) {
                    mkdir('../assets/categories/', 0777, true);
                }

                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Delete old image if exists and not default
                    if (!empty($current_image) && $current_image != 'default.png' && file_exists('../assets/categories/' . $current_image)) {
                        unlink('../assets/categories/' . $current_image);
                    }
                    $image_path = $new_filename;
                }
            } else {
                $error = "Image size must be less than 5MB.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.";
        }
    }

    if (!empty($name)) {
        $query = "UPDATE categories SET name = '$name', image = '$image_path' WHERE id = $category_id";
        if (mysqli_query($conn, $query)) {
            $success = "Category updated successfully!";
        } else {
            $error = "Error updating category: " . mysqli_error($conn);
        }
    } else {
        $error = "Category name is required.";
    }
}

// Get category details
$query = "SELECT * FROM categories WHERE id = $category_id";
$result = mysqli_query($conn, $query);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    header('Location: categories.php');
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Category - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
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

        .image-preview-container {
            margin: 15px 0;
            text-align: center;
        }

        .image-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
            margin-bottom: 10px;
        }

        .current-image {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .form-group.image-upload {
            margin: 20px 0;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            margin: 10px 0;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-block;
            padding: 10px 20px;
            background: #f0f0f0;
            border: 2px dashed #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            background: #e8e8e8;
            border-color: #999;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php include 'admin-sidebar.php'; ?>

        <main class="main-content">
            <h1>Edit Category</h1>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="edit-form">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Category Name:</label>
                        <input type="text" name="name" required placeholder="Enter category name"
                            value="<?php echo htmlspecialchars($category['name']); ?>">
                    </div>

                    <div class="form-group image-upload">
                        <label>Category Image:</label>

                        <div class="image-preview-container">
                            <?php
                            $image_path = !empty($category['image']) ? '../assets/categories/' . $category['image'] : '../assets/categories/default.png';
                            ?>
                            <img src="<?php echo $image_path; ?>"
                                alt="Current category image"
                                class="image-preview"
                                id="imagePreview">
                            <div class="current-image">
                                Current Image: <?php echo !empty($category['image']) ? $category['image'] : 'default.png'; ?>
                            </div>
                        </div>

                        <div class="file-input-wrapper">
                            <label class="file-input-label">
                                Choose New Image
                                <input type="file" name="category_image" id="categoryImage"
                                    accept=".jpg,.jpeg,.png,.gif,.webp">
                            </label>
                        </div>

                        <input type="hidden" name="current_image"
                            value="<?php echo htmlspecialchars($category['image'] ?? ''); ?>">

                        <p style="font-size: 12px; color: #666; margin-top: 5px;">
                            Recommended size: 300x300 pixels. Max file size: 5MB.
                        </p>
                    </div>

                    <button type="submit" name="update_category" class="btn">Update Category</button>
                    <a href="categories.php" class="btn btn-secondary">Back to Categories</a>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Image view 
        document.getElementById('categoryImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                }

                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>