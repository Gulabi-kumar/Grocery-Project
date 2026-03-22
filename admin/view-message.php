<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../pages/login.php');
    exit();
}

// Get message ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: messages.php');
    exit();
}

$message_id = intval($_GET['id']);

// Get message details
$query = "SELECT * FROM messages WHERE id = $message_id";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    header('Location: messages.php');
    exit();
}

$message = mysqli_fetch_assoc($result);

// Mark as read
if (!$message['is_read']) {
    mysqli_query($conn, "UPDATE messages SET is_read = TRUE WHERE id = $message_id");
    $message['is_read'] = true;
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_reply'])) {
    $reply_subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $reply_message = mysqli_real_escape_string($conn, $_POST['message']);
    $admin_name = $_SESSION['user_name'];
    
    mysqli_query($conn, "UPDATE messages SET replied = TRUE WHERE id = $message_id");
    
    // Save reply to database 
    $reply_query = "INSERT INTO message_replies (message_id, admin_id, subject, message) 
                    VALUES ($message_id, {$_SESSION['user_id']}, '$reply_subject', '$reply_message')";
    mysqli_query($conn, $reply_query);
    
    $reply_success = true;
}

// Get previous replies
$replies_query = "SELECT mr.*, u.name as admin_name 
                  FROM message_replies mr 
                  LEFT JOIN users u ON mr.admin_id = u.id 
                  WHERE mr.message_id = $message_id 
                  ORDER BY mr.created_at DESC";
$replies_result = mysqli_query($conn, $replies_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .message-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .message-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .message-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .message-title h2 {
            color: #2c3e50;
            margin: 0;
        }
        
        .message-meta {
            display: flex;
            gap: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-unread {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .status-read {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-replied {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .sender-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 15px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .message-body {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            line-height: 1.8;
        }
        
        .message-body h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .message-content {
            font-size: 15px;
            color: #34495e;
            white-space: pre-wrap;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #4CAF50;
        }
        
        .replies-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .replies-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .reply-item {
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .reply-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reply-admin {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .reply-time {
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .reply-subject {
            font-weight: 600;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .reply-message {
            color: #34495e;
            line-height: 1.6;
            white-space: pre-wrap;
        }
        
        .reply-form-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-input, .form-textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
        }
        
        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn-back {
            background: #7f8c8d;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-back:hover {
            background: #6c7b7d;
        }
        
        .btn-send {
            background: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-send:hover {
            background: #43a047;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .no-replies {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .no-replies-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .email-link {
            color: #3498db;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .email-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .message-container {
                padding: 15px;
            }
            
            .message-title {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .message-meta {
                flex-wrap: wrap;
            }
            
            .sender-info {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'admin-sidebar.php'; ?>

        <main class="main-content">
            <header class="main-header">
                <h1>View Message</h1>
                <div class="header-actions">
                    <a href="messages.php" class="btn-back">← Back to Messages</a>
                </div>
            </header>

            <div class="message-container">
                <?php if (isset($reply_success)): ?>
                    <div class="success-message">
                        ✓ Reply sent successfully! The customer has been notified.
                    </div>
                <?php endif; ?>

                <!-- Message Header -->
                <div class="message-header">
                    <div class="message-title">
                        <h2><?php echo htmlspecialchars($message['subject']); ?></h2>
                        <div class="message-meta">
                            <span class="status-badge <?php echo $message['is_read'] ? 'status-read' : 'status-unread'; ?>">
                                <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                            </span>
                            <span class="status-badge <?php echo $message['replied'] ? 'status-replied' : ''; ?>">
                                <?php echo $message['replied'] ? 'Replied' : 'Not Replied'; ?>
                            </span>
                            <span>Received: <?php echo date('F j, Y g:i A', strtotime($message['date_sent'])); ?></span>
                        </div>
                    </div>

                    <div class="sender-info">
                        <div class="info-item">
                            <span class="info-label">From</span>
                            <span class="info-value"><?php echo htmlspecialchars($message['name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <a href="<?php echo 'mailto:' . htmlspecialchars($message['email']); ?>" class="info-value email-link">
                                <?php echo htmlspecialchars($message['email']); ?>
                            </a>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Message ID</span>
                            <span class="info-value">#<?php echo str_pad($message['id'], 5, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date Received</span>
                            <span class="info-value"><?php echo date('M d, Y', strtotime($message['date_sent'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="message-body">
                    <h3>Message Details</h3>
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                </div>

                <!-- Previous Replies -->
                <div class="replies-section">
                    <div class="replies-header">
                        <h3>Previous Replies</h3>
                        <?php if (mysqli_num_rows($replies_result) > 0): ?>
                            <span class="info-label"><?php echo mysqli_num_rows($replies_result); ?> replies</span>
                        <?php endif; ?>
                    </div>

                    <?php if (mysqli_num_rows($replies_result) > 0): ?>
                        <?php while ($reply = mysqli_fetch_assoc($replies_result)): ?>
                            <div class="reply-item">
                                <div class="reply-header">
                                    <span class="reply-admin"><?php echo htmlspecialchars($reply['admin_name']); ?></span>
                                    <span class="reply-time"><?php echo date('M d, Y g:i A', strtotime($reply['created_at'])); ?></span>
                                </div>
                                <?php if (!empty($reply['subject'])): ?>
                                    <div class="reply-subject">Re: <?php echo htmlspecialchars($reply['subject']); ?></div>
                                <?php endif; ?>
                                <div class="reply-message">
                                    <?php echo nl2br(htmlspecialchars($reply['message'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-replies">
                            <div class="no-replies-icon">💬</div>
                            <h4>No replies yet</h4>
                            <p>Send the first reply to this customer.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Reply Form -->
                <div class="reply-form-section">
                    <h3>Send Reply</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-input" 
                                   value="<?php echo 'Re: ' . htmlspecialchars($message['subject']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">To</label>
                            <input type="text" class="form-input" 
                                   value="<?php echo htmlspecialchars($message['name']) . ' &lt;' . htmlspecialchars($message['email']) . '&gt;'; ?>" 
                                   readonly disabled>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Your Reply</label>
                            <textarea name="message" class="form-textarea" required placeholder="Type your reply here..."></textarea>
                        </div>

                        <div class="action-buttons">
                            <a href="messages.php" class="btn-back">← Back</a>
                            <button type="submit" name="send_reply" class="btn-send">
                                📧 Send Reply
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Quick Actions -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="<?php echo 'mailto:' . htmlspecialchars($message['email']) . '?subject=Re: ' . urlencode($message['subject']); ?>" 
                               class="btn" target="_blank">
                                📧 Open in Email Client
                            </a>
                            <a href="<?php echo 'messages.php?mark_read=' . $message['id']; ?>" 
                               class="btn" style="background: #6c757d;">
                                ✓ Mark as Read
                            </a>
                            <a href="<?php echo 'messages.php?delete=' . $message['id']; ?>" 
                               class="btn" style="background: #dc3545;"
                               onclick="return confirm('Delete this message?')">
                                🗑️ Delete Message
                            </a>
                            <button onclick="printMessage()" class="btn" style="background: #17a2b8;">
                                🖨️ Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Print message function
        function printMessage() {
            const printContent = `
                <div style="padding: 20px; font-family: Arial, sans-serif;">
                    <h2>Message Details</h2>
                    <p><strong>From:</strong> <?php echo addslashes($message['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo addslashes($message['email']); ?></p>
                    <p><strong>Subject:</strong> <?php echo addslashes($message['subject']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($message['date_sent'])); ?></p>
                    <hr>
                    <h3>Message:</h3>
                    <div style="white-space: pre-wrap; background: #f5f5f5; padding: 15px; border-radius: 5px;">
                        <?php echo addslashes($message['message']); ?>
                    </div>
                </div>
            `;
            
            const printWindow = window.open('', '', 'width=800,height=600');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Message #<?php echo $message['id']; ?> - <?php echo addslashes($message['name']); ?></title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            @media print {
                                @page { margin: 0.5in; }
                            }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        // Auto-resize textarea
        document.querySelector('.form-textarea').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + Enter to send reply
            if (e.ctrlKey && e.key === 'Enter') {
                document.querySelector('button[name="send_reply"]').click();
            }
            
            // Escape to go back
            if (e.key === 'Escape') {
                window.location.href = 'messages.php';
            }
        });
    </script>
</body>
</html>