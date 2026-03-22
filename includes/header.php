<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/../config/database.php';
}

if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $query = "SELECT COUNT(*) as cart_count FROM cart WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $cart_count = $row['cart_count'];

    $query = "SELECT COUNT(*) as wishlist_count FROM wishlist WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $wishlist_count = $row['wishlist_count'];
} else {
    $cart_count = 0;
    $wishlist_count = 0;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshGrocer</title>
    <style>
        body {
            margin: 0;
            padding-top: 70px;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: #16a34a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 30px;
            z-index: 1000;
            box-sizing: border-box;
        }

        .logo {
            flex: 0 0 auto;
        }

        .logo h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .logo a {
            color: white;
            text-decoration: none;
        }

        .search-bar {
            flex: 1;
            display: flex;
            justify-content: center;
            margin: 0 20px;
        }

        .search-bar input {
            width: 400px;
            max-width: 100%;
            padding: 10px 18px;
            border-radius: 30px;
            border: none;
            outline: none;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .search-bar input:focus {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .nav-links {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 30px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-links a:hover {
            background: #15803d;
        }

        .count {
            background: white;
            color: #16a34a;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 5px;
            display: inline-block;
            min-width: 20px;
            text-align: center;
        }

        .menu-toggle {
            display: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            padding: 8px 12px;
            transition: all 0.2s;
            margin-left: 10px;
        }

        .menu-toggle:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
        }

        .menu-toggle i {
            font-size: 20px;
        }

        @media (max-width: 992px) {
            .navbar {
                padding: 10px 20px;
            }

            .search-bar input {
                width: 300px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-wrap: wrap;
                padding: 10px 15px;
            }

            .logo {
                order: 1;
                flex: 1;
            }

            .logo h1 {
                font-size: 22px;
            }

            .menu-toggle {
                display: block;
                order: 2;
                margin-left: auto;
            }

            .search-bar {
                order: 3;
                width: 100%;
                margin: 12px 0 5px 0;
                flex: 0 0 100%;
            }

            .search-bar input {
                width: 100%;
                padding: 10px 15px;
            }

            .nav-links {
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: #15803d;
                flex-direction: column;
                align-items: stretch;
                display: none;
                padding: 10px 0;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                border-top: 1px solid rgba(255, 255, 255, 0.2);
                z-index: 999;
            }

            .nav-links a {
                width: 100%;
                padding: 14px 20px;
                border-radius: 0;
                justify-content: space-between;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .nav-links a:last-child {
                border-bottom: none;
            }

            .nav-links a:hover {
                background: #166534;
            }

            .nav-links.show {
                display: flex;
            }

            .count {
                background: white;
                color: #15803d;
            }
        }

        @media (max-width: 480px) {
            .navbar {
                padding: 8px 12px;
            }

            .logo h1 {
                font-size: 20px;
            }

            .menu-toggle {
                padding: 6px 10px;
                font-size: 20px;
            }

            .nav-links a {
                padding: 12px 15px;
                font-size: 14px;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .nav-links.show {
            animation: slideDown 0.3s ease forwards;
        }
    </style>
</head>

<body>
    <header class="navbar">
        <div class="logo">
            <h1><a href="/Groceryproject/">FreshGrocer</a></h1>
        </div>

        <div class="search-bar">
            <input type="text" placeholder="Search products...">
        </div>

        <!-- HAMBURGER MENU  -->
        <div class="menu-toggle" onclick="toggleMenu()">
            ☰
        </div>

        <nav id="navLinks" class="nav-links">
            <a href="/Groceryproject/">
                Home
            </a>
            <a href="/Groceryproject/shop.php">
                Shop
            </a>

            <?php if (isset($_SESSION['user_id'])):
            ?>

                <a href="/Groceryproject/pages/profile.php">
                    Profile
                </a>
                <a href="/Groceryproject/pages/wishlist.php">
                    Wishlist
                    <span class="count"><?php echo $wishlist_count; ?></span>
                </a>
                <a href="/Groceryproject/pages/cart.php">
                    Cart
                    <span class="count"><?php echo $cart_count; ?></span>
                </a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                    <a href="/Groceryproject/admin/dashboard.php">
                        Admin
                    </a>
                <?php endif; ?>
                <a href="/Groceryproject/logout.php">
                    Logout
                </a>
            <?php else: ?>
                <a href="/Groceryproject/pages/login.php">
                    Login
                </a>
                <a href="/Groceryproject/pages/register.php">
                    Register
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <script>
        function toggleMenu() {
            const navLinks = document.getElementById("navLinks");
            navLinks.classList.toggle("show");
        }

        // Close menu when clicking outside
        document.addEventListener('click', function(event) {
            const navLinks = document.getElementById("navLinks");
            const menuToggle = document.querySelector('.menu-toggle');

            if (!navLinks.contains(event.target) && !menuToggle.contains(event.target)) {
                navLinks.classList.remove('show');
            }
        });

        // Close menu on window resize 
        window.addEventListener('resize', function() {
            const navLinks = document.getElementById("navLinks");
            if (window.innerWidth > 768) {
                navLinks.classList.remove('show');
            }
        });
    </script>
</body>

</html>