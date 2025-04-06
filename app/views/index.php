<?php
session_start();
// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WannaBet - Friendly Betting Made Simple</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --text-color: #374151;
            --text-light: #6b7280;
            --background-color: #f9fafb;
            --card-background: #ffffff;
            --border-color: #e5e7eb;
            --danger-color: #ef4444;
            --success-color: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
            line-height: 1.6;
        }

        header {
            background-color: var(--card-background);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 0.5rem;
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .hero {
            padding: 5rem 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 400px;
            height: 400px;
            background: var(--primary-color);
            opacity: 0.05;
            border-radius: 50%;
            z-index: -1;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 300px;
            height: 300px;
            background: var(--secondary-color);
            opacity: 0.05;
            border-radius: 50%;
            z-index: -1;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-light);
            max-width: 700px;
            margin-bottom: 2rem;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
        }

        .features {
            padding: 5rem 0;
            background-color: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.25rem;
            color: var(--text-color);
        }

        .section-title p {
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background-color: var(--card-background);
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: rgba(59, 130, 246, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon i {
            font-size: 1.75rem;
            color: var(--primary-color);
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .feature-card p {
            color: var(--text-light);
        }

        .how-it-works {
            padding: 5rem 0;
            background-color: var(--background-color);
        }

        .steps {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 2rem;
            margin-top: 3rem;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            max-width: 250px;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .step h3 {
            margin-bottom: 1rem;
        }

        .step p {
            color: var(--text-light);
        }

        .testimonials {
            background-color: white;
            padding: 5rem 0;
        }

        .testimonials-slider {
            margin-top: 3rem;
            display: flex;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        .testimonial {
            min-width: 100%;
            padding: 2rem;
            text-align: center;
        }

        .testimonial-content {
            max-width: 700px;
            margin: 0 auto;
            background-color: var(--card-background);
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }

        .testimonial p {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        .testimonial-author {
            font-weight: bold;
            color: var(--primary-color);
        }

        .testimonial-controls {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .testimonial-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--border-color);
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .testimonial-dot.active {
            background-color: var(--primary-color);
        }

        footer {
            background-color: var(--text-color);
            color: white;
            padding: 3rem 0;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .footer-logo i {
            margin-right: 0.5rem;
        }

        .footer-info {
            max-width: 300px;
        }

        .footer-info p {
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-links h3 {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .social-links a:hover {
            background-color: var(--primary-color);
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .steps {
                flex-direction: column;
                align-items: center;
            }

            .footer-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="#" class="logo"><i class="fas fa-handshake"></i> WannaBet</a>
                <div class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it Works</a>
                    <a href="#testimonials">Testimonials</a>
                </div>
                <div>
                    <a href="login.php" class="btn btn-outline">Log In</a>
                    <a href="register.php" class="btn">Sign Up</a>
                </div>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Friendly Betting Made Simple</h1>
            <p>WannaBet makes it easy to create fun, friendly bets with your friends, track outcomes, and settle up afterward - all in one place.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn">Get Started</a>
                <a href="#how-it-works" class="btn btn-outline">Learn More</a>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose WannaBet?</h2>
                <p>Discover what makes WannaBet the perfect platform for your friendly wagers</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Trustworthy</h3>
                    <p>Our trust scoring system ensures all bets are handled fairly with secure payment processing.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3>Social Betting</h3>
                    <p>Connect with friends, challenge them to bets, and keep track of your betting history.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>Multiple Stake Types</h3>
                    <p>Bet with money or favors - WannaBet supports various stake types to keep things interesting.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Real-time Notifications</h3>
                    <p>Stay updated with bet invitations, outcomes, and payments through our notification system.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Track Your Stats</h3>
                    <p>Keep track of your wins, losses, and overall betting performance over time.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Secure Payments</h3>
                    <p>Integrated payment system ensures smooth and secure transactions between users.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>How It Works</h2>
                <p>Four simple steps to get started with WannaBet</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Create an Account</h3>
                    <p>Sign up for WannaBet in less than a minute with just a few details.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Add Friends</h3>
                    <p>Connect with friends to create a network of potential betting partners.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Create a Bet</h3>
                    <p>Define the terms, set the stakes, and invite friends to your bet.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Settle Up</h3>
                    <p>Record the outcome and settle up using our secure payment system.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>What Our Users Say</h2>
                <p>Hear from people who are already enjoying WannaBet</p>
            </div>
            <div class="testimonials-slider" id="testimonials-slider">
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"WannaBet has completely changed how my friends and I handle our friendly wagers. No more awkward reminders or forgotten bets - everything is tracked clearly."</p>
                        <div class="testimonial-author">- Alex R.</div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"I love the favor betting feature! It's not always about money - sometimes the stakes are lunch or household chores. WannaBet makes it fun and easy to track."</p>
                        <div class="testimonial-author">- Sam T.</div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"The trust score system keeps everyone honest, and the payment integration means we can settle up without hassle. Highly recommend!"</p>
                        <div class="testimonial-author">- Jordan K.</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-controls">
                <div class="testimonial-dot active" data-slide="0"></div>
                <div class="testimonial-dot" data-slide="1"></div>
                <div class="testimonial-dot" data-slide="2"></div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <a href="#" class="footer-logo"><i class="fas fa-handshake"></i> WannaBet</a>
                    <p>WannaBet is the ultimate platform for friendly wagers between friends. Track bets, settle up, and have fun!</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#testimonials">Testimonials</a></li>
                        <li><a href="register.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><a href="mailto:support@wannabet.com">support@wannabet.com</a></li>
                        <li><a href="tel:+1234567890">(123) 456-7890</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; 2025 WannaBet. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Testimonials Slider
        const slider = document.getElementById('testimonials-slider');
        const dots = document.querySelectorAll('.testimonial-dot');
        
        dots.forEach(dot => {
            dot.addEventListener('click', () => {
                const slideIndex = dot.dataset.slide;
                slider.scrollLeft = slideIndex * slider.offsetWidth;
                
                // Update active dot
                dots.forEach(d => d.classList.remove('active'));
                dot.classList.add('active');
            });
        });

        // Auto-scroll testimonials
        let currentSlide = 0;
        setInterval(() => {
            currentSlide = (currentSlide + 1) % 3;
            slider.scrollLeft = currentSlide * slider.offsetWidth;
            
            // Update active dot
            dots.forEach(d => d.classList.remove('active'));
            dots[currentSlide].classList.add('active');
        }, 5000);
    </script>
</body>
</html> 