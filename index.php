<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Effortless Appointments - Medical Directory & Token System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* ========================================
           INDEX PAGE - PREMIUM MEDICAL SAAS
           ======================================== */

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #f0f4ff 100%);
            min-height: 100vh;
        }

        /* ========================================
           NAVBAR
           ======================================== */

        .navbar-theme {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        .navbar-theme.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        .navbar-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0 30px;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--dark);
            text-decoration: none;
        }
        
        .navbar-brand-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .navbar-links {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .navbar-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: color var(--transition-fast);
        }
        
        .navbar-links a:hover {
            color: var(--primary);
        }
        
        .navbar-cta {
            display: flex;
            gap: 15px;
        }

        /* ========================================
           HERO SECTION - PREMIUM MEDICAL HERO
           ======================================== */

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 60px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 50%, #f0f4ff 100%);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1), transparent);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
            filter: blur(40px);
            z-index: 0;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -5%;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.08), transparent);
            border-radius: 50%;
            animation: float 25s infinite ease-in-out reverse;
            filter: blur(50px);
            z-index: 0;
        }

        .hero-container {
            max-width: 1200px;
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-copy h1 {
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #0f172a 0%, #2563eb 50%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: slideUp 0.8s ease-out 0.2s both;
        }

        .hero-copy p {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.8;
            animation: slideUp 0.8s ease-out 0.4s both;
        }

        .hero-ctas {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            animation: slideUp 0.8s ease-out 0.6s both;
        }

        .hero-stat-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
            animation: slideUp 0.8s ease-out 0.8s both;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            transition: all var(--transition-normal);
        }

        .stat-card:hover {
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card-value {
            font-size: 2rem;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .stat-card-label {
            font-size: 0.85rem;
            color: var(--muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-visual {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: slideLeft 0.8s ease-out 0.3s both;
        }

        .hero-image-card {
            width: 100%;
            max-width: 400px;
            aspect-ratio: 1;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-light));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 60px rgba(37, 99, 235, 0.2);
            position: relative;
            overflow: hidden;
        }

        .hero-image-card::before {
            content: '';
            position: absolute;
            inset: -50%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .hero-image-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: var(--primary);
            font-weight: 900;
            font-size: 3rem;
        }

        /* ========================================
           FEATURES SECTION
           ======================================== */

        .features-section {
            padding: 80px 20px;
            background: var(--bg-secondary);
        }

        .features-header {
            text-align: center;
            margin-bottom: 60px;
            animation: slideUp 0.6s ease-out;
        }

        .features-header h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .features-header p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--border-color);
            transition: all var(--transition-normal);
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            gap: 16px;
            scroll-reveal;
            animation: slideUp 0.6s ease-out forwards;
        }

        .feature-card:nth-child(1) { animation-delay: 0.2s; }
        .feature-card:nth-child(2) { animation-delay: 0.4s; }
        .feature-card:nth-child(3) { animation-delay: 0.6s; }

        .feature-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-8px);
            border-color: var(--primary);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: bold;
        }

        .feature-card h3 {
            margin-bottom: 8px;
            color: var(--dark);
        }

        .feature-card p {
            color: var(--muted);
            font-size: 0.95rem;
            margin: 0;
        }

        /* ========================================
           SERVICES / PRICING SECTION
           ======================================== */

        .services-section {
            padding: 80px 20px;
            background: white;
        }

        .services-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .services-header h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .service-card {
            background: linear-gradient(135deg, var(--light) 0%, var(--light-secondary) 100%);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            transition: all var(--transition-normal);
            scroll-reveal;
            animation: slideUp 0.6s ease-out forwards;
        }

        .service-card:nth-child(1) { animation-delay: 0.2s; }
        .service-card:nth-child(2) { animation-delay: 0.4s; }
        .service-card:nth-child(3) { animation-delay: 0.6s; }
        .service-card:nth-child(4) { animation-delay: 0.8s; }

        .service-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-8px);
            background: white;
        }

        .service-card h3 {
            margin-bottom: 12px;
            color: var(--dark);
        }

        .service-card p {
            font-size: 0.95rem;
            color: var(--muted);
            margin-bottom: 20px;
        }

        .service-price {
            font-size: 2rem;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 20px;
        }

        /* ========================================
           TESTIMONIALS SECTION
           ======================================== */

        .testimonials-section {
            padding: 80px 20px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--secondary-light) 100%);
        }

        .testimonials-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .testimonials-header h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: var(--shadow-lg);
            transition: all var(--transition-normal);
            scroll-reveal;
            animation: slideUp 0.6s ease-out forwards;
        }

        .testimonial-card:nth-child(1) { animation-delay: 0.2s; }
        .testimonial-card:nth-child(2) { animation-delay: 0.4s; }
        .testimonial-card:nth-child(3) { animation-delay: 0.6s; }

        .testimonial-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-8px);
        }

        .testimonial-stars {
            color: #fbbf24;
            margin-bottom: 12px;
            font-size: 1.2rem;
            letter-spacing: 2px;
        }

        .testimonial-text {
            color: var(--dark);
            font-size: 0.95rem;
            font-style: italic;
            margin-bottom: 16px;
            line-height: 1.8;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .testimonial-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .testimonial-name {
            font-weight: 700;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .testimonial-role {
            color: var(--muted);
            font-size: 0.85rem;
        }

        /* ========================================
           DOCTOR PROFILE SECTION
           ======================================== */

        .doctor-section {
            padding: 80px 20px;
            background: white;
        }

        .doctor-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .doctor-header h2 {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }

        .doctor-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .doctor-image-wrapper {
            text-align: center;
            scroll-reveal;
        }

        .doctor-image {
            width: 100%;
            max-width: 380px;
            aspect-ratio: 1;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-light));
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            transition: all var(--transition-smooth);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary);
            font-weight: 900;
            border: 3px solid var(--primary-light);
            position: relative;
        }

        .doctor-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), transparent);
            pointer-events: none;
            border-radius: 20px;
        }

        .doctor-image:hover {
            transform: translateY(-10px) scale(1.05);
            box-shadow: 0 40px 80px rgba(37, 99, 235, 0.3);
            border-color: var(--primary);
        }

        .doctor-image:hover::before {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.2), transparent);
        }

        .doctor-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .doctor-info {
            scroll-reveal;
        }

        .doctor-info h3 {
            font-size: 2rem;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all var(--transition-normal);
        }

        .doctor-info h3:hover {
            transform: translateX(8px);
        }

        .doctor-specialty {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .doctor-specialty::before {
            content: '✓';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .doctor-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }

        .badge {
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-light));
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid var(--primary);
        }

        .doctor-bio {
            color: var(--muted);
            line-height: 1.8;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }

        .doctor-qualifications {
            margin-bottom: 24px;
        }

        .doctor-qualifications h4 {
            font-size: 1.1rem;
            margin-bottom: 12px;
            color: var(--dark);
        }

        .qualifications-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .qualifications-list li {
            display: flex;
            gap: 10px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .qualifications-list li::before {
            content: '✓';
            color: var(--success);
            font-weight: bold;
            flex-shrink: 0;
        }

        /* ========================================
           CTA SECTION
           ======================================== */

        .cta-section {
            padding: 60px 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            color: white;
            margin-bottom: 16px;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            margin-bottom: 24px;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-buttons .btn-primary {
            background: white;
            color: var(--primary);
        }

        .cta-buttons .btn-primary:hover {
            background: var(--light);
        }

        .cta-buttons .btn-outline {
            border-color: white;
            color: white;
        }

        .cta-buttons .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* ========================================
           FOOTER
           ======================================== */

        .footer-section {
            padding: 60px 20px 40px;
            background: var(--dark);
            color: white;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h4 {
            color: white;
            margin-bottom: 16px;
            font-size: 1rem;
        }

        .footer-column ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-column a {
            color: rgba(255, 255, 255, 0.7);
            transition: color var(--transition-fast);
        }

        .footer-column a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 24px;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* ========================================
           RESPONSIVE ADJUSTMENTS
           ======================================== */

        @media (max-width: 768px) {
            .hero-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-copy h1 {
                font-size: 2rem;
            }

            .hero-stat-cards {
                grid-template-columns: 1fr;
            }

            .doctor-container {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-section {
                padding: 100px 20px 40px;
                min-height: auto;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .cta-buttons .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .hero-copy h1 {
                font-size: 1.5rem;
            }

            .hero-copy p {
                font-size: 1rem;
            }

            .hero-ctas {
                flex-direction: column;
            }

            .hero-ctas .btn {
                width: 100%;
            }

            h2 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- ========================================
         NAVBAR
         ======================================== -->
    <nav class="navbar-theme">
        <div class="navbar-inner">
            <a href="index.php" class="navbar-brand">
                <div class="navbar-brand-icon">+</div>
                Effortless Appointments
            </a>
            <ul class="navbar-links">
                <li><a href="#features">Features</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#doctor">Our Doctor</a></li>
                <li><a href="#testimonials">Reviews</a></li>
            </ul>
            <div class="navbar-cta">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-outline" style="border-color: var(--primary); color: var(--primary);">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline" style="border-color: var(--primary); color: var(--primary);">Login</a>
                    <a href="patient_booking.php" class="btn btn-primary">Book Appointment</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ========================================
         HERO SECTION
         ======================================== -->
    <section class="hero-section" data-parallax="0.3">
        <div class="hero-container">
            <div class="hero-copy">
                <h1><i class="bi bi-heart-pulse" style="color: var(--primary); margin-right: 12px;"></i>Modern Medical Appointments, Simplified</h1>
                <p>Experience seamless appointment booking with real-time token management. Connect with top healthcare professionals instantly.</p>
                <div class="hero-ctas">
                    <a href="patient_booking.php" class="btn btn-primary">Book Now</a>
                    <a href="#features" class="btn btn-outline">Learn More</a>
                </div>
                <div class="hero-stat-cards">
                    <div class="stat-card">
                        <div class="stat-card-value">500+</div>
                        <div class="stat-card-label">Appointments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value">98%</div>
                        <div class="stat-card-label">Satisfaction</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-value">24/7</div>
                        <div class="stat-card-label">Support</div>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-image-card">
                    <div class="hero-image-content">
                        <i class="icon-medical">👨‍⚕️</i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================
         FEATURES SECTION
         ======================================== -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="features-header scroll-reveal">
                <h2>Why Choose Our System?</h2>
                <p>Built for modern healthcare delivery with efficiency and patient care at heart</p>
            </div>
            <div class="features-grid">
                <div class="feature-card" data-animation="slideUp">
                    <div class="feature-icon">⚡</div>
                    <h3>Lightning Fast</h3>
                    <p>Book appointments in seconds with our streamlined booking process</p>
                </div>
                <div class="feature-card" data-animation="slideUp">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure & Private</h3>
                    <p>Your medical data is protected with industry-standard encryption</p>
                </div>
                <div class="feature-card" data-animation="slideUp">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile Friendly</h3>
                    <p>Manage appointments anytime, anywhere on any device</p>
                </div>
                <div class="feature-card" data-animation="slideUp">
                    <div class="feature-icon">📊</div>
                    <h3>Real-Time Tracking</h3>
                    <p>Live token updates keep you informed every step of the way</p>
                </div>
                <div class="feature-card" data-animation="slideUp">
                    <div class="feature-icon">🎯</div>
                    <h3>Smart Scheduling</h3>
                    <p>Intelligent slot management prevents double bookings and delays</p>
                </div>
                <div class="feature-card" data-animation="slideUp">
                    <div class="feature-icon">💬</div>
                    <h3>24/7 Support</h3>
                    <p>Dedicated support team available round the clock</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================
         SERVICES SECTION
         ======================================== -->
    <section class="services-section" id="services">
        <div class="container">
            <div class="services-header scroll-reveal">
                <h2>Our Services</h2>
                <p>Comprehensive healthcare solutions for every patient</p>
            </div>
            <div class="services-grid">
                <div class="service-card" data-animation="slideUp">
                    <h3>General Consultation</h3>
                    <p>Expert medical advice and routine checkups</p>
                    <div class="service-price">1000</div>
                    <a href="patient_booking.php" class="btn btn-primary btn-sm">Book Consultation</a>
                </div>
                <div class="service-card" data-animation="slideUp">
                    <h3>Cardiology</h3>
                    <p>Specialized cardiac care and heart health management</p>
                    <div class="service-price">3000</div>
                    <a href="patient_booking.php" class="btn btn-primary btn-sm">Book Consultation</a>
                </div>
                <div class="service-card" data-animation="slideUp">
                    <h3>Follow-Up Visit</h3>
                    <p>Post-treatment monitoring and progress evaluation</p>
                    <div class="service-price">1500</div>
                    <a href="patient_booking.php" class="btn btn-primary btn-sm">Book Consultation</a>
                </div>
               
            </div>
        </div>
    </section>

    <!-- ========================================
         TESTIMONIALS SECTION
         ======================================== -->
    <section class="testimonials-section" id="testimonials">
        <div class="container">
            <div class="testimonials-header scroll-reveal">
                <h2>Patient Reviews</h2>
                <p>See what our patients have to say about their experience</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card" data-animation="slideUp">
                    <div class="testimonial-stars">★★★★★</div>
                    <div class="testimonial-text">"The appointment booking was incredibly smooth. Got my token within seconds and saw the doctor right on time!"</div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">A</div>
                        <div>
                            <div class="testimonial-name">Ahmed Hassan</div>
                            <div class="testimonial-role">Patient</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card" data-animation="slideUp">
                    <div class="testimonial-stars">★★★★★</div>
                    <div class="testimonial-text">"Finally, a system that respects my time! No more waiting around. The token system is genius and the doctors are amazing."</div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">F</div>
                        <div>
                            <div class="testimonial-name">Fatima Khan</div>
                            <div class="testimonial-role">Patient</div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card" data-animation="slideUp">
                    <div class="testimonial-stars">★★★★★</div>
                    <div class="testimonial-text">"Professional, efficient, and caring. Dr. Khan is top-notch and the entire system is user-friendly. Highly recommended!"</div>
                    <div class="testimonial-author">
                        <div class="testimonial-avatar">M</div>
                        <div>
                            <div class="testimonial-name">Muhammad Ali</div>
                            <div class="testimonial-role">Patient</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================
         DOCTOR PROFILE SECTION
         ======================================== -->
    <section class="doctor-section" id="doctor">
        <div class="container">
            <div class="doctor-header scroll-reveal">
                <h2>Meet Our Featured Doctor</h2>
                <p>Experienced and compassionate healthcare professional</p>
            </div>
            <div class="doctor-container">
                <div class="doctor-image-wrapper">
                    <div class="doctor-image" data-parallax="0.3">
<img src="assets/css/images/img1.png" alt="Dr. Ahmad Khan" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
                <div class="doctor-info scroll-reveal">
                    <h3>Dr. Ahmad Khan</h3>
                    <div class="doctor-specialty">Senior Consultant Cardiologist</div>
                    
                    <div class="doctor-badges">
                        <span class="badge">MBBS</span>
                        <span class="badge">FCPS</span>
                        <span class="badge">Board Certified</span>
                    </div>

                    <p class="doctor-bio">
                        Dr. Aisha Khan is a renowned cardiologist with over 20 years of clinical experience. She specializes in managing complex cardiac conditions and has helped thousands of patients achieve optimal heart health.
                    </p>

                    <div class="doctor-qualifications">
                        <h4>Qualifications & Experience</h4>
                        <ul class="qualifications-list">
                            <li>MBBS - Aga Khan University</li>
                            <li>FCPS in Cardiology - College of Physicians and Surgeons</li>
                            <li>20+ years of clinical practice</li>
                            <li>Specialized in coronary artery disease management</li>
                            <li>Expert in heart failure management</li>
                            <li>Fellowship in Preventive Cardiology</li>
                            <li>Published researcher in cardiac journals</li>
                            <li>Member of International Cardiology Associations</li>
                        </ul>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 24px;">
                        <a href="patient_booking.php" class="btn btn-primary">Book Appointment</a>
                        <a href="#testimonials" class="btn btn-outline">Read Reviews</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================
         CTA SECTION
         ======================================== -->
    <section class="cta-section" scroll-reveal>
        <div class="container">
            <h2>Ready to Experience Better Healthcare?</h2>
            <p>Join thousands of satisfied patients who've discovered the ease of modern appointment management</p>
            <div class="cta-buttons">
                <a href="patient_booking.php" class="btn btn-primary">Get Started Today</a>
                <a href="#features" class="btn btn-outline">Learn More</a>
            </div>
        </div>
    </section>

    <!-- ========================================
         FOOTER
         ======================================== -->
    <footer class="footer-section">
        <div class="footer-container">
            <div class="footer-column">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Our Mission</a></li>
                    <li><a href="#">Team</a></li>
                    <li><a href="#">Careers</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Services</h4>
                <ul>
                    <li><a href="#">Book Appointment</a></li>
                    <li><a href="#">View Doctors</a></li>
                    <li><a href="#">Check Status</a></li>
                    <li><a href="#">Pricing</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Connect</h4>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Twitter</a></li>
                    <li><a href="#">LinkedIn</a></li>
                    <li><a href="#">Instagram</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Effortless Appointments. All rights reserved. | Medical Excellence | Patient Care First</p>
        </div>
    </footer>

    <!-- ========================================
         SCRIPTS
         ======================================== -->
    <script>
        // Smooth navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar-theme');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && document.querySelector(href)) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
    <script src="assets/js/animations.js"></script>
</body>
</html>


