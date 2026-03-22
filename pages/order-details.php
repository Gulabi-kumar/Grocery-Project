<?php
include '../config/database.php';

// Get product_id from URL
$pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

$product_name = @mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = $pid"));
$pName = $product_name ? $product_name['name'] : 'Product #' . $pid;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $insert = mysqli_query($conn, "INSERT INTO reviews (product_id, user_id, rating, comment, is_approved, created_at) 
        VALUES ($pid, $uid, $rating, '$comment', 0, NOW())");
    if ($insert) {
        $msg = "<p style='color:green;'>✓ Review submitted successfully!</p>";
    } else {
        $msg = "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pName; ?> - Reviews</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            margin: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h2, h3 {
            color: #000;
            margin: 20px 0 10px 0;
        }
        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            background: #f0f0f0;
            padding: 8px;
            border: 1px solid #ccc;
        }
        td {
            padding: 8px;
            border: 1px solid #ccc;
        }
        .form-table {
            width: 100%;
        }
        .form-table td {
            border: none;
            padding: 8px 0;
        }
        .form-table label {
            font-weight: bold;
        }
        select, textarea {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            font-family: Arial, sans-serif;
        }
        .btn {
            padding: 8px 20px;
            border: 1px solid #ccc;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-green {
            background: #4CAF50;
            color: white;
            border: 1px solid #3d8b40;
        }
        .btn-grey {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ccc;
        }
        .btn-grey:hover {
            background: #e0e0e0;
        }
        .review-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .review-table td {
            border: 1px solid #ccc;
            padding: 10px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 100px;
            background: #f9f9f9;
        }
        .stars {
            color: #FFD700;
        }
        .badge {
            background: #ffc107;
            padding: 2px 8px;
            font-size: 12px;
        }
        .badge.approved {
            background: #4CAF50;
            color: white;
        }
        .empty {
            padding: 20px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            text-align: center;
        }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Back Button -->
        <div class="header-bar">
            <h2>Product Reviews for <?php echo $pName; ?></h2>
            <a href="javascript:history.back()" class="btn btn-grey">← Back</a>
        </div>
        <hr>
        
        <?php if(isset($msg)) echo $msg; ?>      
        
        <!-- Review Form -->
        <h3>Write a Review</h3>
        <form method="POST">
            <table class="form-table">
                <tr>
                    <td style="width:100px;"><label>Rating:</label></td>
                    <td>
                        <select name="rating" required>
                            <option value="5">5 ★ - Excellent</option>
                            <option value="4">4 ★ - Good</option>
                            <option value="3">3 ★ - Average</option>
                            <option value="2">2 ★ - Poor</option>
                            <option value="1">1 ★ - Terrible</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label>Your Review:</label></td>
                    <td><textarea name="comment" rows="4" placeholder="Write your feedback..." required></textarea></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <button type="submit" name="submit_review" class="btn btn-green">Submit Review</button>
                        <a href="javascript:history.back()" class="btn btn-grey">Cancel</a>
                    </td>
                </tr>
            </table>
        </form>
        
        <hr>
        
        <h3>All Reviews</h3>
        
        <?php
        $reviews = mysqli_query($conn, "SELECT * FROM reviews ORDER BY created_at DESC");
        
        if (mysqli_num_rows($reviews) > 0) {
            while ($row = mysqli_fetch_assoc($reviews)) {
        ?>
                <table class="review-table">
                    <tr>
                        <td class="label" style="width:120px;">Product:</td>
                        <td><strong>Product #<?php echo $row['product_id']; ?></strong> | User #<?php echo $row['user_id']; ?></td>
                    </tr>
                    <tr>
                        <td class="label">Rating:</td>
                        <td class="stars">
                            <?php
                            for($i = 1; $i <= 5; $i++) {
                                echo $i <= $row['rating'] ? '★' : '☆';
                            }
                            ?> (<?php echo $row['rating']; ?>/5)
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Comment:</td>
                        <td><?php echo nl2br(htmlspecialchars($row['comment'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Date:</td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <td class="label">Status:</td>
                        <td>
                            <span class="badge <?php echo $row['is_approved'] ? 'approved' : ''; ?>">
                                <?php echo $row['is_approved'] ? '✓ Approved' : 'Pending'; ?>
                            </span>
                        </td>
                    </tr>
                </table>
                <br>
        <?php
            }
        } else {
            echo '<div class="empty">No reviews yet</div>';
        }
        ?>
    
        <hr>
        <div style="text-align: center; margin-top: 20px;">
            <a href="javascript:history.back()" class="btn btn-grey">← Go Back</a>
            <a href="../shop.php" class="btn btn-grey">Continue Shopping</a>
        </div>
    
    </div>
</body>
</html>