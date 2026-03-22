<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>About Us - FreshGrocer</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        background-color: #ffffff;
        margin: 0;
        padding: 0;
        line-height: 1.6;
    }

    h1, h2, h3, h4, h5, h6 {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #111827;
        margin-top: 0;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    p {
        margin-bottom: 1rem;
        font-size: 15px;
        color: #6b7280;
    }

    ul {
        padding-left: 20px;
        margin-bottom: 1rem;
    }

    li {
        margin-bottom: 0.5rem;
        color: #6b7280;
    }

    .about-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    .page-header {
        text-align: center;
        padding: 40px 20px;
        background: #ffffff;
        border-radius: 10px;
        color: #111827;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #6b7280, #9ca3af, #d1d5db);
    }

    .page-header h1 {
        font-size: 36px;
        margin-bottom: 15px;
        color: #111827;
        position: relative;
    }

    .page-header p {
        font-size: 18px;
        max-width: 700px;
        margin: 0 auto;
        color: #6b7280;
        position: relative;
    }

    .about-hero {
        display: flex;
        align-items: center;
        gap: 40px;
        margin-bottom: 50px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        border: 1px solid #e5e7eb;
    }

    .hero-content {
        flex: 1;
    }

    .hero-image {
        flex: 1;
        text-align: center;
    }

    .hero-image img {
        max-width: 100%;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }

    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #e5e7eb;
        border-top: 4px solid #6b7280;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border-color: #d1d5db;
    }

    .feature-icon {
        font-size: 40px;
        color: #6b7280;
        margin-bottom: 20px;
        height: 80px;
        width: 80px;
        line-height: 80px;
        background: #f9fafb;
        border-radius: 50%;
        margin: 0 auto 20px;
        border: 1px solid #e5e7eb;
    }

    .feature-card h3 {
        font-size: 20px;
        margin-bottom: 15px;
        color: #111827;
    }

    .feature-card p {
        color: #6b7280;
    }

    .stats-section {
        background: #ffffff;
        color: #111827;
        padding: 60px 40px;
        border-radius: 10px;
        margin-bottom: 50px;
        text-align: center;
        border: 1px solid #e5e7eb;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
    }

    .stats-section h2 {
        color: #111827;
        margin-bottom: 40px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
    }

    .stat-item {
        padding: 20px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
    }

    .stat-item h2 {
        font-size: 40px;
        color: #111827;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .stat-item p {
        color: #6b7280;
        font-size: 16px;
    }

    .team-section {
        margin-bottom: 50px;
        background: white;
        padding: 40px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
    }

    .team-section h2 {
        text-align: center;
        font-size: 32px;
        margin-bottom: 40px;
        color: #111827;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 25px;
    }

    .team-member {
        background: #f9fafb;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        transition: transform 0.3s ease;
        border: 1px solid #e5e7eb;
    }

    .team-member:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .member-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #ffffff;
        margin: 0 auto 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        color: #6b7280;
        border: 1px solid #e5e7eb;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
    }

    .team-member h3 {
        font-size: 18px;
        margin-bottom: 5px;
        color: #111827;
    }

    .team-member p {
        color: #6b7280;
        font-size: 14px;
        margin-bottom: 15px;
    }

    .testimonials {
        background: #ffffff;
        padding: 50px 40px;
        border-radius: 10px;
        margin-bottom: 50px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
    }

    .testimonials h2 {
        text-align: center;
        font-size: 32px;
        margin-bottom: 40px;
        color: #111827;
    }

    .testimonial-slider {
        max-width: 800px;
        margin: 0 auto;
    }

    .testimonial-item {
        background: #f9fafb;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
        margin: 0 10px;
        text-align: center;
        border: 1px solid #e5e7eb;
    }

    .testimonial-text {
        font-size: 16px;
        font-style: italic;
        color: #6b7280;
        margin-bottom: 20px;
        position: relative;
        line-height: 1.7;
    }

    .testimonial-text::before,
    .testimonial-text::after {
        content: '"';
        color: #9ca3af;
        font-size: 24px;
        font-weight: bold;
    }

    .testimonial-author {
        font-weight: 600;
        color: #111827;
        margin-bottom: 5px;
    }

    .testimonial-role {
        color: #9ca3af;
        font-size: 14px;
    }

    .cta-section {
        text-align: center;
        padding: 50px 40px;
        background: #ffffff;
        border-radius: 10px;
        color: #111827;
        margin-bottom: 40px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
    }

    .cta-section h2 {
        color: #111827;
        font-size: 32px;
        margin-bottom: 20px;
    }

    .cta-section p {
        color: #6b7280;
        font-size: 18px;
        max-width: 700px;
        margin: 0 auto 30px;
    }

    .cta-button {
        display: inline-block;
        background: #6b7280;
        color: #ffffff;
        padding: 12px 30px;
        border-radius: 5px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid #6b7280;
    }

    .cta-button:hover {
        background: #4b5563;
        border-color: #4b5563;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .page-header {
            padding: 30px 15px;
        }
        
        .page-header h1 {
            font-size: 28px;
        }
        
        .page-header p {
            font-size: 16px;
        }
        
        .about-hero {
            flex-direction: column;
            padding: 20px;
            gap: 20px;
        }
        
        .features-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .team-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .testimonials {
            padding: 30px 20px;
        }
        
        .cta-section {
            padding: 30px 20px;
        }
        
        .cta-section h2 {
            font-size: 24px;
        }
        
        .team-section,
        .stats-section,
        .testimonials,
        .cta-section {
            padding: 30px 20px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .team-grid {
            grid-template-columns: 1fr;
        }
        
        .feature-card {
            padding: 20px;
        }
        
        .team-member {
            padding: 20px;
        }
        
        .stat-item h2 {
            font-size: 32px;
        }
        
        .page-header h1 {
            font-size: 24px;
        }
        
        .page-header p {
            font-size: 14px;
        }
    }
</style>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="about-container">
        <div class="page-header">
            <h1>About FreshGrocer</h1>
            <p>Your trusted partner for fresh groceries delivered to your doorstep. Quality, freshness, and convenience since 2020.</p>
        </div>

        <div class="about-hero">
            <div class="hero-content">
                <h2>Our Story</h2>
                <p>FreshGrocer started in 2020 with a simple yet powerful vision: to revolutionize the way people shop for groceries. What began as a small local delivery service has grown into a trusted name in the community, serving thousands of happy customers.</p>
                <p>Our founders, passionate about fresh food and community health, saw an opportunity to bridge the gap between local farmers and urban consumers. Today, we're proud to continue that mission while embracing innovation and sustainability.</p>
            </div>
            <div class="hero-image">
                <div style="background: #f9fafb; padding: 40px; border-radius: 10px; height: 300px; display: flex; align-items: center; justify-content: center; color: #6b7280; border: 1px solid #e5e7eb;">
                    <i class="fas fa-leaf" style="font-size: 100px; opacity: 0.7;"></i>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3>Farm Fresh</h3>
                <p>Direct partnerships with local farmers ensure you get the freshest produce possible, often harvested just hours before delivery.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Get your groceries delivered within 2 hours. We prioritize speed without compromising on quality or freshness.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-award"></i>
                </div>
                <h3>Quality Guarantee</h3>
                <p>Every product is carefully selected and inspected. If you're not satisfied, we'll replace it or refund your money.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h3>Community Focus</h3>
                <p>We support local farmers and producers, contributing to sustainable agriculture and the local economy.</p>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <h2>Our Impact in Numbers</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <h2>10,000+</h2>
                    <p>Happy Customers</p>
                </div>
                <div class="stat-item">
                    <h2>50+</h2>
                    <p>Local Farms Partnered</p>
                </div>
                <div class="stat-item">
                    <h2>99%</h2>
                    <p>Customer Satisfaction</p>
                </div>
                <div class="stat-item">
                    <h2>24/7</h2>
                    <p>Customer Support</p>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="team-section">
            <h2>Meet Our Leadership Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Gulabi kumar</h3>
                    <p>Founder & CEO</p>
                    <p>10+ years in retail management</p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Aman kumar</h3>
                    <p>Head of Operations</p>
                    <p>Supply chain expert</p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Raj</h3>
                    <p>Quality Control Director</p>
                    <p>Former food safety inspector</p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Rohit kumar</h3>
                    <p>Customer Experience</p>
                    <p>Customer service champion</p>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <div class="testimonials">
            <h2>What Our Customers Say</h2>
            <div class="testimonial-slider">
                <div class="testimonial-item">
                    <div class="testimonial-text">
                        FreshGrocer has changed how I shop for groceries. The quality is consistently excellent and delivery is always on time. Highly recommended!
                    </div>
                    <div class="testimonial-author">Priya Sharma</div>
                    <div class="testimonial-role">Regular Customer for 2 years</div>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="cta-section">
            <h2>Join Our Fresh Community</h2>
            <p>Experience the convenience of fresh groceries delivered to your doorstep. Sign up today and get 20% off your first order!</p>
            <a href="register.php" class="cta-button">Get Started Now</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Simple testimonial slider
        document.addEventListener('DOMContentLoaded', function() {
            const testimonials = [
                {
                    text: "FreshGrocer has changed how I shop for groceries. The quality is consistently excellent and delivery is always on time. Highly recommended!",
                    author: "Priya Sharma",
                    role: "Regular Customer for 2 years"
                },
                {
                    text: "As a working professional, FreshGrocer saves me hours every week. The produce is fresher than what I find at supermarkets.",
                    author: "Sumit kumar",
                    role: "Customer for 1 year"
                },
                {
                    text: "I love supporting local farmers through FreshGrocer. Knowing where my food comes from gives me peace of mind.",
                    author: "Ajay kumar",
                    role: "Health Conscious Customer"
                }
            ];

            let currentTestimonial = 0;
            const testimonialContainer = document.querySelector('.testimonial-item');

            function rotateTestimonial() {
                currentTestimonial = (currentTestimonial + 1) % testimonials.length;
                const testimonial = testimonials[currentTestimonial];
                
                testimonialContainer.innerHTML = `
                    <div class="testimonial-text">${testimonial.text}</div>
                    <div class="testimonial-author">${testimonial.author}</div>
                    <div class="testimonial-role">${testimonial.role}</div>
                `;
                
                // Add fade animation
                testimonialContainer.style.opacity = '0';
                setTimeout(() => {
                    testimonialContainer.style.opacity = '1';
                }, 300);
            }

            // Rotate testimonials every 5 seconds
            setInterval(rotateTestimonial, 5000);
        });
    </script>
</body>

</html>