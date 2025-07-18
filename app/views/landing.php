<?php
// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /home");
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
    <link rel="stylesheet" href="/css/common.css">
    <style>
        /* Landing page specific styles */
        .hero {
            padding: 8rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            z-index: -1;
            animation: rotate 20s linear infinite;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 1s ease-out;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto 2.5rem;
            animation: fadeInUp 1s ease-out 0.2s backwards;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            animation: fadeInUp 1s ease-out 0.4s backwards;
        }

        .features {
            padding: 6rem 0;
            background-color: var(--card-background);
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-title p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: rgba(31, 41, 55, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            transition: all 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-color);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            font-size: 1.5rem;
        }

        .how-it-works {
            padding: 6rem 0;
            background-color: var(--background-color);
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .step {
            text-align: center;
            padding: 2rem;
            background: rgba(31, 41, 55, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            transition: all 0.3s;
        }

        .step:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-weight: bold;
            font-size: 1.25rem;
        }

        .testimonials {
            padding: 6rem 0;
            background-color: var(--card-background);
        }

        .testimonials-slider {
            margin-top: 3rem;
            display: flex;
            overflow-x: hidden;
            scroll-behavior: smooth;
            gap: 2rem;
            padding: 1rem;
        }

        .testimonial {
            min-width: 100%;
            padding: 2rem;
        }

        .testimonial-content {
            background: rgba(31, 41, 55, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 2rem;
            transition: all 0.3s;
        }

        .testimonial-content:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
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
            transition: all 0.3s;
        }

        .testimonial-dot.active {
            background: var(--gradient-primary);
            transform: scale(1.2);
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .footer-logo i {
            color: var(--primary-color);
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: var(--gradient-primary);
            transform: translateY(-3px);
        }

        .footer-links h3 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links a {
            color: var(--text-light);
            text-decoration: none;
            display: block;
            margin-bottom: 0.75rem;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: var(--text-color);
            transform: translateX(5px);
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="#" class="logo">
                    <i class="fas fa-handshake"></i>
                    WannaBet
                </a>
                <div class="nav-links">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it Works</a>
                    <a href="#testimonials">Testimonials</a>
                </div>
                <div>
                    <a href="/login" class="btn btn-outline" onclick="console.log('Login button clicked, navigating to:', '/login')">Log In</a>
                    <a href="/register" class="btn btn-primary">Sign Up</a>
                </div>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Friendly Betting Made Simple</h1>
            <p>WannaBet makes it easy to create fun, friendly bets with your friends, track outcomes, and settle up afterward - all in one place.</p>
            <div class="hero-buttons">
                <a href="/register" class="btn btn-primary">Get Started</a>
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
                        <div class="testimonial-author">- Karla L.</div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"I love the favor betting feature! It's not always about money - sometimes the stakes are lunch or household chores. WannaBet makes it fun and easy to track."</p>
                        <div class="testimonial-author">- Kiko G.</div>
                    </div>
                </div>
                <div class="testimonial">
                    <div class="testimonial-content">
                        <p>"The trust score system keeps everyone honest, and the payment integration means we can settle up without hassle. Highly recommend!"</p>
                        <div class="testimonial-author">- Mandla K.</div>
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
                    <a href="#" class="footer-logo">
                        <i class="fas fa-handshake"></i>
                        WannaBet
                    </a>
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
                        <li><a href="/register">Sign Up</a></li>
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
            <div class="footer">
                <p>Don't have an account? <a href="/register">Sign up</a></p>
                <p>&copy; 2025 WannaBet. All rights reserved.</p>
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

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Debug information
        console.log('Current URL:', window.location.href);
        console.log('Base URL:', window.location.origin);
        console.log('Pathname:', window.location.pathname);

        // Add click handlers to all navigation links
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('Link clicked:', {
                    href: this.href,
                    pathname: this.pathname,
                    origin: window.location.origin,
                    currentPath: window.location.pathname
                });
            });
        });
    </script>
</body>
</html> 