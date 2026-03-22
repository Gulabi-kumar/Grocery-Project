<?php
include '../config/database.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Handle profile picture upload
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_pic']['tmp_name'];
        $fileName = basename($_FILES['profile_pic']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];
        if(in_array($ext, $allowed)) {
            $newName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','', $fileName);
            $dest = __DIR__ . '/../assets/images/' . $newName;
            if(move_uploaded_file($fileTmp, $dest)) {
                // Update image field
                mysqli_query($conn, "UPDATE users SET image = '$newName' WHERE id = $user_id");
            }
        }
    }

    $update_query = "UPDATE users SET name = '$name', phone = '$phone', address = '$address' WHERE id = $user_id";
    if(mysqli_query($conn, $update_query)) {
        $_SESSION['success'] = 'Profile updated successfully.';
        header('Location: profile.php');
        exit();
    } else {
        $error = 'Failed to update profile.';
    }
}

// Fetch current user info
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile - FreshGrocer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
.edit-profile-container {
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border: 1px solid #eaeaea;
    width: 50%;
}

.edit-profile-container h2 {
    color: #333;
    margin: 0 0 20px 0;
    font-size: 22px;
    text-align: center;
    padding-bottom: 10px;
    border-bottom: 2px solid #4CAF50;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #555;
    margin-bottom: 5px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #4CAF50;
}

.form-group input[readonly] {
    background: #f9f9f9;
    color: #777;
}

.form-group textarea {
    min-height: 80px;
    resize: vertical;
}

.form-group img {
    max-height: 80px;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-top: 5px;
}

.form-group input[type="file"] {
    padding: 5px;
    font-size: 13px;
    border: 1px solid #ddd;
    background: #f9f9f9;
}

.error {
    background: #ffebee;
    color: #d32f2f;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 13px;
    margin-bottom: 15px;
    border-left: 3px solid #f44336;
}

.button-group {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    flex: 1;
    padding: 9px 15px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    text-align: center;
    transition: background 0.2s;
}

.btn[type="submit"] {
    background: #4CAF50;
    color: white;
}

.btn[type="submit"]:hover {
    background: #45a049;
}

.btn[href] {
    background: #6c757d;
    color: white;
}

.btn[href]:hover {
    background: #5a6268;
}
</style>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="edit-profile-container">
        <h2>Edit Profile</h2>

        <?php if(isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email (readonly)</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Profile Picture</label>
                <?php if(!empty($user['image'])): ?>
                    <div><img src="../assets/images/<?php echo $user['image']; ?>" width="120" alt="Profile"></div>
                <?php endif; ?>
                <input type="file" name="profile_pic" accept="image/*">
            </div>

            <button type="submit" class="btn">Save Changes</button>
            <a href="profile.php" class="btn">Cancel</a>
        </form>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
