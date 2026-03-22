<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreshGrocer Footer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
    body {
        display: flex;
        flex-direction: column;
        min-height: 90vh;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
    }

    .main-content {
        flex: 1;
    }

    .footer {
        background-color: #ffffff;
        color: #333333;
        padding: 30px 15px 15px 15px; 
        position: relative;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.03);
        font-size: 13px; 
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px; 
        background: linear-gradient(90deg, #6b7280, #9ca3af, #d1d5db);
    }

    .footer a {
        color: #4b5563;
        text-decoration: none;
        display: block;
        margin-bottom: 8px; 
        transition: all 0.2s ease; 
        font-size: 12px; 
        position: relative;
        padding-left: 3px; 
    }

    .footer a:hover {
        color: #111827;
        transform: translateX(3px); 
    }

    .footer a:hover::before {
        content: '›';
        position: absolute;
        left: -10px; 
        color: #6b7280;
        font-size: 11px;
    }

    .footer-content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 20px;
        max-width: 1000px; 
        margin: 0 auto;
        padding-bottom: 20px; 
    }

    .footer-section {
        padding: 0 10px; 
    }

    .footer-section h3 {
        margin-bottom: 15px; 
        font-size: 15px; 
        color: #111827;
        position: relative;
        padding-bottom: 8px; 
        font-weight: 600;
    }

    .footer-section h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 30px; 
        height: 1px; 
        background: #6b7280;
    }

    .footer-section p {
        margin: 6px 0;
        font-size: 12px; 
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 8px; 
        line-height: 1.4;
    }

    .footer-section i {
        color: #6b7280;
        width: 16px; 
        text-align: center;
        font-size: 12px; 
    }

    .footer-logo {
        margin-bottom: 12px; 
    }

    .footer-logo h2 {
        color: #111827;
        font-size: 18px; 
        margin: 0;
        font-weight: 600;
    }

    .footer-logo .tagline {
        color: #6b7280;
        font-size: 11px; 
        margin-top: 3px; 
    }

    /* Social icons */
    .social-icons {
        display: flex;
        gap: 8px;
        margin-top: 15px; 
    }

    .social-icons a {
        width: 28px; 
        height: 28px; 
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease; 
        color: #6b7280;
        margin-bottom: 0;
        font-size: 11px; 
    }

    .social-icons a:hover {
        background: #6b7280;
        color: #ffffff;
        transform: translateY(-2px); 
    }

    .newsletter {
        margin-top: 12px; 
    }

    .newsletter-input {
        display: flex;
        gap: 8px; 
        margin-top: 8px; 
    }

    .newsletter-input input {
        flex: 1;
        padding: 8px 12px; 
        border: 1px solid #d1d5db;
        border-radius: 4px; 
        background: #ffffff;
        color: #333333;
        font-size: 12px; 
        transition: border-color 0.2s;
        height: 32px; 
    }

    .newsletter-input input:focus {
        outline: none;
        border-color: #6b7280;
        box-shadow: 0 0 0 2px rgba(107, 114, 128, 0.1);
    }

    .newsletter-input input::placeholder {
        color: #9ca3af;
        font-size: 11px;
    }

    .newsletter-input button {
        padding: 8px 15px;
        background: #6b7280;
        color: white;
        border: none;
        border-radius: 4px; 
        cursor: pointer;
        transition: background 0.2s ease;
        font-weight: 600;
        font-size: 12px;
        height: 32px; 
        white-space: nowrap;
    }

    .newsletter-input button:hover {
        background: #4b5563;
    }

    .footer-bottom {
        text-align: center;
        padding-top: 20px; 
        font-size: 11px; 
        border-top: 1px solid #e5e7eb;
        margin-top: 15px; 
        color: #6b7280;
    }

    .footer-bottom p {
        margin: 3px 0; 
    }

    .footer-links {
        margin-top: 8px; 
    }

    .footer-links a {
        display: inline-block;
        margin: 0 8px; 
        color: #6b7280;
        font-size: 11px;
    }

    .footer-links a:hover {
        color: #111827;
    }

    .payment-methods {
        display: flex;
        gap: 8px; 
        margin-top: 12px; 
        justify-content: center;
        flex-wrap: wrap;
    }

    .payment-methods i {
        font-size: 18px;
        color: #9ca3af;
    }

    .store-badges {
        display: flex;
        gap: 8px; 
        margin-top: 15px; 
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .store-badge {
        padding: 6px 10px; 
        background: #f3f4f6;
        border-radius: 4px; 
        color: #4b5563;
        font-size: 10px;
        display: flex;
        align-items: center;
        gap: 4px; 
        text-decoration: none;
        transition: background 0.2s;
    }

    .store-badge:hover {
        background: #e5e7eb;
        color: #111827;
    }

    @media (max-width: 768px) {
        .footer {
            padding: 20px 10px 10px 10px; 
        }
        
        .footer-content {
            grid-template-columns: 1fr;
            text-align: center;
            gap: 15px; 
        }

        .footer-section h3::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .footer-section p {
            justify-content: center;
        }

        .social-icons {
            justify-content: center;
        }

        .newsletter-input {
            flex-direction: column;
        }

        .newsletter-input input,
        .newsletter-input button {
            width: 100%;
        }

        .payment-methods {
            justify-content: center;
        }

        .store-badges {
            justify-content: center;
        }

        .footer-logo {
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .footer {
            padding: 15px 8px 8px 8px; 
        }
        
        .footer-content {
            gap: 12px;
        }
        
        .footer-section {
            padding: 0 5px;
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px); 
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .footer-section {
        animation: fadeIn 0.4s ease forwards; 
    }

    .footer-section:nth-child(1) { animation-delay: 0.1s; }
    .footer-section:nth-child(2) { animation-delay: 0.15s; }
    .footer-section:nth-child(3) { animation-delay: 0.2s; }
    .footer-section:nth-child(4) { animation-delay: 0.25s; }
</style>

<body>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <h2>FreshGrocer</h2>
                    <p class="tagline">Fresh groceries delivered to your door</p>
                </div>
                <p>Your trusted partner for fresh, high-quality groceries since 2020.</p>
                
                <div class="store-badges">
                    <a href="#" class="store-badge">
                        <i class="fab fa-apple"></i>
                        <span>App Store</span>
                    </a>
                    <a href="#" class="store-badge">
                        <i class="fab fa-google-play"></i>
                        <span>Google Play</span>
                    </a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="/Groceryproject/index.php"><i class="fas fa-home"></i> Home</a>
                <a href="/Groceryproject/shop.php"><i class="fas fa-shopping-cart"></i> Shop</a>
                <a href="/Groceryproject/about.php"><i class="fas fa-info-circle"></i> About</a>
                <a href="/Groceryproject/contact.php"><i class="fas fa-envelope"></i> Contact</a>
            </div>

            <div class="footer-section">
                <h3>My Account</h3>
                <a href="/Groceryproject/pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="/Groceryproject/pages/register.php"><i class="fas fa-user-plus"></i> Register</a>
                <a href="/Groceryproject/pages/profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="/Groceryproject/pages/orders.php"><i class="fas fa-shopping-bag"></i> Orders</a>
            </div>

            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Grocery Street</p>
                <p><i class="fas fa-phone"></i> +1 (234) 567-8900</p>
                <p><i class="fas fa-envelope"></i> info@freshgrocer.com</p>
                
                <div class="social-icons">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="payment-methods">
                <i class="fab fa-cc-visa" title="Visa"></i>
                <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                <i class="fab fa-cc-paypal" title="PayPal"></i>
            </div>
            
            <p>&copy; 2024 FreshGrocer. All rights reserved.</p>
            <div class="footer-links">
                <a href="/privacy-policy.php">Privacy</a>
                <a href="/terms-of-service.php">Terms</a>
                <a href="/refund-policy.php">Refund</a>
            </div>
        </div>
    </footer>

    <script>
        // Newsletter subscription functionality
        document.querySelector('.newsletter-input button').addEventListener('click', function() {
            const emailInput = document.querySelector('.newsletter-input input');
            const email = emailInput.value.trim();
            
            if (email && validateEmail(email)) {
                alert('Thank you for subscribing!');
                emailInput.value = '';
            } else {
                alert('Please enter a valid email address.');
                emailInput.focus();
            }
        });

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    </script>
</body>

</html>