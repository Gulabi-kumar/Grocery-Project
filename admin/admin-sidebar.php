<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Grocery Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }


        .sidebar {
            width: 260px;
            background: #2c3e50;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 0 20px 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #34495e;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.4rem;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .sidebar-header h2 i {
            color: #3498db;
            margin-right: 8px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .user-info h4 {
            color: white;
            font-size: 0.95rem;
            margin: 0 0 4px 0;
        }

        .user-info p {
            color: #bdc3c7;
            font-size: 0.8rem;
            margin: 0;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: #ecf0f1;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #34495e;
            color: white;
        }

        .nav-icon {
            font-size: 1.1rem;
            width: 22px;
            text-align: center;
        }

        .nav-badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: auto;
        }

        .mobile-menu-toggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 6px;
            width: 45px;
            height: 45px;
            font-size: 1.3rem;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        .mobile-menu-toggle:hover {
            background: #34495e;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }


        .main-content {
            margin-left: 260px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        @media screen and (max-width: 768px) {

            .mobile-menu-toggle {
                display: flex !important;
            }


            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }


            .sidebar.active {
                transform: translateX(0);
                box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
            }


            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }


            .sidebar-header h2 i {
                display: inline-block;
            }
        }

        @media screen and (max-width: 480px) {
            .sidebar {
                width: 85%;
                max-width: 300px;
            }

            .mobile-menu-toggle {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
                top: 12px;
                left: 12px;
            }

            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .sidebar-header h2 {
                font-size: 1.2rem;
            }

            .nav-link {
                padding: 10px 12px;
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>

    <button class="mobile-menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>


    <div class="sidebar-overlay" id="overlay"></div>


    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-store"></i> Grocery Admin</h2>
            <div class="user-profile">
                <div class="user-avatar">
                    A
                </div>
                <div class="user-info">
                    <h4>Administrator</h4>
                    <p>Admin</p>
                </div>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="products.php" class="nav-link">
                    <i class="nav-icon fas fa-box"></i>
                    <span>Products</span>
                    <span class="nav-badge">2</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="categories.php" class="nav-link">
                    <i class="nav-icon fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="orders.php" class="nav-link">
                    <i class="nav-icon fas fa-shopping-bag"></i>
                    <span>Orders</span>
                    <span class="nav-badge">5</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="users.php" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="messages.php" class="nav-link">
                    <i class="nav-icon fas fa-envelope"></i>
                    <span>Messages</span>
                    <span class="nav-badge">12</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reports.php" class="nav-link">
                    <i class="nav-icon fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../index.php" class="nav-link">
                    <i class="nav-icon fas fa-store-alt"></i>
                    <span>Visit Store</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="nav-link">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');


            function toggleMenu() {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');


                const icon = menuToggle.querySelector('i');
                if (sidebar.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');

                    document.body.style.overflow = 'hidden';
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                    document.body.style.overflow = '';
                }
            }


            if (menuToggle) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu();
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    if (sidebar.classList.contains('active')) {
                        toggleMenu();
                    }
                });
            }


            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        if (sidebar.classList.contains('active')) {
                            toggleMenu();
                        }
                    }
                });
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {

                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';

                    const icon = menuToggle.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });

            let touchStartX = 0;
            let touchEndX = 0;

            document.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, false);

            document.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;

                if (window.innerWidth <= 768) {

                    if (touchStartX > 200 && touchStartX - touchEndX > 50) {
                        if (sidebar.classList.contains('active')) {
                            toggleMenu();
                        }
                    }
                }
            }, false);
        });
    </script>
</body>

</html>