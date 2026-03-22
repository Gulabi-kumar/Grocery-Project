<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $image_path = 'default.png';


    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['category_image']['name'];
        $file_tmp = $_FILES['category_image']['tmp_name'];
        $file_size = $_FILES['category_image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_extensions)) {
            if ($file_size <= 5242880) {
                $new_filename = uniqid('category_', true) . '.' . $file_ext;
                $upload_path = '../assets/categories/' . $new_filename;

                if (!is_dir('../assets/categories/')) {
                    mkdir('../assets/categories/', 0777, true);
                }

                if (move_uploaded_file($file_tmp, $upload_path)) {
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
        $query = "INSERT INTO categories (name, description, image) VALUES ('$name', '$description', '$image_path')";
        if (mysqli_query($conn, $query)) {
            $success = "Category added successfully!";
        } else {
            $error = "Error adding category: " . mysqli_error($conn);
        }
    } else {
        $error = "Category name is required.";
    }
}

if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);

    $image_query = "SELECT image FROM categories WHERE id = $category_id";
    $image_result = mysqli_query($conn, $image_query);
    $image_data = mysqli_fetch_assoc($image_result);
    $category_image = $image_data['image'] ?? 'default.png';

    $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = $category_id";
    $check_result = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($check_result);

    if ($row['count'] == 0) {
        if (mysqli_query($conn, "DELETE FROM categories WHERE id = $category_id")) {
            if ($category_image != 'default.png' && file_exists('../assets/categories/' . $category_image)) {
                unlink('../assets/categories/' . $category_image);
            }
            $success = "Category deleted successfully!";
        }
    } else {
        $error = "Cannot delete category with products!";
    }
}


$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$categories_list = [];
while ($category = mysqli_fetch_assoc($categories)) {
    $categories_list[] = $category;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Categories - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            margin-left: 200px;
            width: calc(100% - 200px);
            padding: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 25px;
            color: #1e293b;
            font-weight: 600;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #334155;
            font-weight: 600;
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
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #1e293b;
            font-size: 14px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            max-width: 450px;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #16a34a;
            outline: none;
        }

        /* FILE INPUT */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
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
            padding: 8px 18px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: #334155;
        }

        .file-input-label:hover {
            background: #e2e8f0;
        }

        .hint-text {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
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
            border-radius: 8px;
            font-size: 14px;
            table-layout: fixed;
        }

        .data-table th {
            background: #f8fafc;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background: #f8fafc;
        }

        .table-image-cell {
            width: 70px;
        }

        .category-image {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            display: block;
        }

        .description-preview {
            color: #475569;
            font-size: 13px;
            line-height: 1.5;
            max-width: 280px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .no-description {
            color: #94a3b8;
            font-style: italic;
            font-size: 12px;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-small {
            display: inline-block;
            padding: 6px 14px;
            font-size: 13px;
            text-decoration: none;
            border-radius: 3px;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background: #2563eb;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }

        .data-table th:nth-child(1) {
            width: 8%;
        }

        .data-table th:nth-child(2) {
            width: 10%;
        }

        .data-table th:nth-child(3) {
            width: 15%;
        }

        .data-table th:nth-child(4) {
            width: 42%;
        }

        .data-table th:nth-child(5) {
            width: 25%;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .form-group input[type="text"],
            .form-group textarea {
                max-width: 100%;
            }

            .data-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <?php include 'admin-sidebar.php'; ?>

        <main class="main-content">
            <h1>Manage Categories</h1>

            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Add Category Form -->
            <div class="add-form">
                <h2>Add New Category</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Category Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="name" required placeholder="e.g., Fruits, Vegetables">
                    </div>

                    <div class="form-group">
                        <label>Category Description</label>
                        <textarea name="description" placeholder="Describe this category..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <div class="hint-text">Max 500 characters</div>
                    </div>

                    <div class="form-group">
                        <label>Category Image</label>
                        <div class="file-input-wrapper">
                            <label class="file-input-label">
                                Choose Image
                                <input type="file" name="category_image" accept=".jpg,.jpeg,.png,.gif,.webp">
                            </label>
                        </div>
                        <div class="hint-text">JPEG, PNG, GIF, WEBP. Max 5MB.</div>
                    </div>

                    <button type="submit" name="add_category" class="btn">Add Category</button>
                </form>
            </div>

            <!-- Categories List -->
            <div class="categories-list">
                <h2>All Categories</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories_list as $category): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td class="table-image-cell">
                                    <?php
                                    $image_src = '../assets/categories/' . htmlspecialchars($category['image'] ?? 'default.png');
                                    if (!file_exists($image_src)) {
                                        $image_src = '../assets/categories/default.png';
                                    }
                                    ?>
                                    <img src="<?php echo $image_src; ?>"
                                        alt="<?php echo htmlspecialchars($category['name']); ?>"
                                        class="category-image"
                                        loading="lazy"
                                        onerror="this.src='../assets/categories/default.png'; this.onerror=null;">
                                </td>
                                <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                <td>
                                    <?php if (!empty($category['description'])): ?>
                                        <div class="description-preview">
                                            <?php echo htmlspecialchars($category['description']); ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-description">No description</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-category.php?id=<?php echo $category['id']; ?>" class="btn-small btn-edit">Edit</a>
                                        <a href="?delete=<?php echo $category['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($categories_list)): ?>
                            <tr>
                                <td colspan="5" class="empty-state">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.querySelector('input[type="file"]');
            const fileLabel = document.querySelector('.file-input-label');

            if (fileInput && fileLabel) {
                fileInput.addEventListener('change', function() {
                    if (this.files[0]) {
                        fileLabel.innerHTML = this.files[0].name;
                    } else {
                        fileLabel.innerHTML = 'Choose Image';
                    }
                });
            }
        });
    </script>
</body>

</html>