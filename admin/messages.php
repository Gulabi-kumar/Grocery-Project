<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

$messages_query = "SELECT * FROM messages ORDER BY date_sent DESC";
$messages_result = mysqli_query($conn, $messages_query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages - Admin</title>
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
            <h1>Customer Messages</h1>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($message = mysqli_fetch_assoc($messages_result)): ?>
                        <tr>
                            <td><?php echo $message['id']; ?></td>
                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                            <td><?php echo htmlspecialchars($message['email']); ?></td>
                            <td>
                                <div class="message-preview">
                                    <?php echo substr(htmlspecialchars($message['message']), 0, 100); ?>
                                    <?php if(strlen($message['message']) > 100): ?>...<?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($message['date_sent'])); ?></td>
                            <td>
                                <a href="view-message.php?id=<?php echo $message['id']; ?>" class="btn-small">View</a>
                                <a href="mailto:<?php echo $message['email']; ?>?subject=Re: Your Message" class="btn-small">Reply</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <?php if(mysqli_num_rows($messages_result) == 0): ?>
                <div class="no-data">
                    <p>No messages yet.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>