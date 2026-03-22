<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

// Handle delete user
if(isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    // Don't allow deleting yourself 
    if($user_id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id AND role != 'admin'");
        $success = "User deleted!";
    } else {
        $error = "Cannot delete your own account!";
    }
}

// Get all users
$users_query = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
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
</style>
<body>
    <div class="admin-container">
        <?php include 'admin-sidebar.php'; ?>
        
        <main class="main-content">
            <h1>Manage Users</h1>
            
            <?php if(isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" class="btn-small delete" 
                                       onclick="return confirm('Delete this user?')">Delete</a>
                                <?php else: ?>
                                    <span class="text-muted">Current user</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>