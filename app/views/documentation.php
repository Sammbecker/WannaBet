<?php
session_start();
require_once __DIR__ . '/../controllers/BetController.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation - WannaBet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Update existing root variables */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --background-color: #f8fafc;
            --card-bg: #ffffff;
            --text-color: #1e293b;
            --text-light: #64748b;
            --border-color: #e2e8f0;
            --accent-color: #2563eb;
            --hover-accent: #1e40af;
            --success-color: #059669;
            --warning-color: #d97706;
            --error-color: #dc2626;
            --info-color: #0284c7;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.05), 0 2px 4px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.05), 0 4px 6px rgba(0,0,0,0.05);
        }

        /* Enhanced Layout */
        .doc-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .doc-header {
            text-align: left;
            margin-bottom: 3rem;
            padding: 3rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .doc-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .doc-subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            max-width: 600px;
            line-height: 1.6;
        }

        /* Improved Navigation */
        .doc-nav {
            position: sticky;
            top: 0;
            background: var(--background-color);
            padding: 1rem 0;
            margin-bottom: 3rem;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .doc-nav-item {
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.95rem;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .doc-nav-item:hover, .doc-nav-item.active {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }

        /* Enhanced Section Styling */
        .doc-section {
            margin-bottom: 4rem;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
        }

        .doc-section h2 {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .doc-section h2::before {
            content: '';
            width: 4px;
            height: 24px;
            background: var(--primary-color);
            border-radius: 4px;
        }

        .doc-section h3 {
            font-size: 1.5rem;
            margin: 2rem 0 1.5rem;
            color: var(--text-color);
        }

        /* Improved Process Steps */
        .process-step {
            background: var(--background-color);
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .process-step:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .process-step h4 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .step-number {
            width: 32px;
            height: 32px;
            font-size: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }

        /* Enhanced Feature Cards */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .feature-card {
            padding: 1.5rem;
            background: var(--background-color);
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* Improved Info Boxes */
        .example-box {
            background: #f1f5f9;
            border-left: 4px solid var(--primary-color);
            padding: 1.25rem;
            margin: 1.25rem 0;
            border-radius: 0 1rem 1rem 0;
        }

        .tip-box, .warning-box {
            padding: 1.25rem;
            margin: 1.25rem 0;
            border-radius: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .tip-box {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .warning-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
        }

        .tip-box i, .warning-box i {
            font-size: 1.25rem;
        }

        /* Enhanced PDF Export Button */
        .pdf-export-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1rem 2rem;
            border-radius: 9999px;
            font-weight: 500;
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }

        .pdf-export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.04);
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .doc-container {
                padding: 1rem;
            }

            .doc-header {
                padding: 2rem 0;
            }

            .doc-title {
                font-size: 2.5rem;
            }

            .doc-nav {
                padding: 0.75rem;
                margin: -1rem -1rem 2rem -1rem;
                width: calc(100% + 2rem);
            }

            .doc-section {
                padding: 1.5rem;
                margin-bottom: 2rem;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            :root {
                --background-color: #0f172a;
                --card-bg: #1e293b;
                --text-color: #f8fafc;
                --text-light: #94a3b8;
                --border-color: #334155;
            }

            .example-box {
                background: #1e293b;
            }

            .tip-box {
                background: #064e3b;
                border-color: #065f46;
            }

            .warning-box {
                background: #7c2d12;
                border-color: #9a3412;
            }
        }

        /* Print Optimizations */
        @media print {
            .doc-nav, .pdf-export-btn {
                display: none;
            }

            .doc-section {
                break-inside: avoid;
                page-break-inside: avoid;
                margin-bottom: 2rem;
                box-shadow: none;
            }

            .doc-container {
                max-width: none;
            }

            body {
                background: white;
                color: black;
            }
        }
    </style>
</head>
<body>
    <div class="doc-container">
        <div class="doc-header">
            <h1 class="doc-title">WannaBet User Guide</h1>
            <p class="doc-subtitle">Everything you need to know about using the WannaBet platform</p>
        </div>

        <div class="legal-notice doc-section" style="background: #fee2e2; border: 1px solid #fecaca; margin-bottom: 2rem;">
            <h2 style="color: #dc2626;">Legal Notice & Intellectual Property Rights</h2>
            
            <div class="warning-box" style="background: #fff1f2; border-color: #fecaca;">
                <i class="fas fa-gavel"></i>
                <div>
                    <strong>IMPORTANT:</strong> By accessing and reading this documentation, you acknowledge and agree to the following terms:
                </div>
            </div>

            <div class="process-step" style="background: #fff1f2; border-color: #fecaca;">
                <h4>Copyright Notice</h4>
                <p>© 2024 Samuel Becker. All rights reserved. This platform, including its code, design, functionality, and documentation, is the exclusive property of Samuel Becker.</p>
            </div>

            <div class="process-step" style="background: #fff1f2; border-color: #fecaca;">
                <h4>Legal Protection</h4>
                <p>This work is protected under South African law, including but not limited to:</p>
                <ul>
                    <li>Copyright Act 98 of 1978 (as amended)</li>
                    <li>Intellectual Property Laws Amendment Act of 2013</li>
                    <li>Electronic Communications and Transactions Act 25 of 2002</li>
                </ul>
                <p>Any unauthorized copying, reproduction, or distribution of this platform or its components constitutes a criminal offense under these laws.</p>
            </div>

            <div class="process-step" style="background: #fff1f2; border-color: #fecaca;">
                <h4>Prohibited Actions</h4>
                <p>You are strictly prohibited from:</p>
                <ul>
                    <li>Copying, reproducing, or replicating any part of the WannaBet platform</li>
                    <li>Sharing, distributing, or publishing this documentation</li>
                    <li>Creating derivative works based on the WannaBet concept</li>
                    <li>Reverse engineering the platform's functionality</li>
                    <li>Using any part of the platform's code or design elements in other projects</li>
                </ul>
            </div>

            <div class="process-step" style="background: #fff1f2; border-color: #fecaca;">
                <h4>Legal Consequences</h4>
                <p>Violation of these terms will result in:</p>
                <ul>
                    <li>Immediate legal action under South African law</li>
                    <li>Claims for damages and lost profits</li>
                    <li>Criminal charges under applicable intellectual property laws</li>
                    <li>Injunctive relief to prevent further unauthorized use</li>
                </ul>
            </div>

            <div class="warning-box" style="background: #fff1f2; border-color: #fecaca;">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>By continuing to read this documentation:</strong> You acknowledge that you have read, understood, and agreed to these terms. Any violation will be prosecuted to the fullest extent of the law.
                </div>
            </div>
        </div>

        <nav class="doc-nav">
            <a href="#legal-notice" class="doc-nav-item" style="background: #dc2626; color: white;">Legal Notice</a>
            <a href="#getting-started" class="doc-nav-item">Getting Started</a>
            <a href="#betting-types" class="doc-nav-item">Betting Types</a>
            <a href="#trust-score" class="doc-nav-item">Trust Score</a>
            <a href="#how-it-works" class="doc-nav-item">How It Works</a>
            <a href="#payments" class="doc-nav-item">Payments</a>
            <a href="#safety" class="doc-nav-item">Safety & Security</a>
            <a href="#faqs" class="doc-nav-item">FAQs</a>
        </nav>

        <div id="getting-started" class="doc-section">
            <h2>Getting Started</h2>
            <p>Welcome to WannaBet! This guide will walk you through everything you need to know about creating and managing bets with your friends.</p>
            
            <div class="process-step">
                <h4><span class="step-number">1</span> Create Your Account</h4>
                <p>Sign up with your email address and create a secure password. We'll send you a verification email to confirm your account.</p>
            </div>

            <div class="process-step">
                <h4><span class="step-number">2</span> Add Friends</h4>
                <p>Connect with friends by sending them friend requests. You can only create bets with people in your friends list.</p>
            </div>

            <div class="process-step">
                <h4><span class="step-number">3</span> Set Up Your Payment Method</h4>
                <p>For money bets, add your payment details through our secure Paystack integration. Your payment information is encrypted and never stored on our servers.</p>
            </div>

            <div class="tip-box">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Pro Tip:</strong> Start with a favor bet to get familiar with the platform before trying money bets.
                </div>
            </div>
        </div>

        <div id="betting-types" class="doc-section">
            <h2>Types of Bets</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <h4>One-on-One Money Bets</h4>
                    <p>A direct bet between two people where each person puts up the same amount of money.</p>
                    <div class="example-box">
                        <strong>Example:</strong> You bet R100 that you can run 5km faster than your friend. Both you and your friend put up R100, and the winner takes R200.
                    </div>
                </div>

                <div class="feature-card">
                    <h4>Group Money Bets</h4>
                    <p>Multiple people join the bet, each contributing the same stake amount to create a prize pool.</p>
                    <div class="example-box">
                        <strong>Example:</strong> Five friends bet R50 each on who will score highest in an exam. The total prize pool is R250.
                    </div>
                </div>

                <div class="feature-card">
                    <h4>Favor Bets</h4>
                    <p>Instead of money, the stake is a task or favor that the loser must do for the winner.</p>
                    <div class="example-box">
                        <strong>Example:</strong> Loser has to cook dinner for the winner, or wash the winner's car.
                    </div>
                </div>
            </div>
        </div>

        <div id="trust-score" class="doc-section">
            <h2>Trust Score System</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <h4>What is Trust Score?</h4>
                    <p>The trust score is a reliability metric that reflects how trustworthy a user is based on their betting history and payment behavior.</p>
                    <div class="example-box">
                        <strong>Base Score:</strong> All users start with a base score of 70 points.
                    </div>
                </div>

                <div class="feature-card">
                    <h4>How It's Calculated</h4>
                    <ul class="security-features">
                        <li>On-time payments increase your score</li>
                        <li>Completed bets improve reliability</li>
                        <li>Payment delays decrease your score</li>
                        <li>Scores update automatically after each bet</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h4>Benefits of High Trust Score</h4>
                    <ul class="security-features">
                        <li>Higher visibility in the community</li>
                        <li>Increased betting limits</li>
                        <li>Access to special betting features</li>
                        <li>Better chances of bet acceptance</li>
                    </ul>
                </div>
            </div>

            <div class="tip-box">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Pro Tip:</strong> Maintain a high trust score by always paying on time and honoring your bets. Your score is visible to other users and affects their decision to bet with you.
                </div>
            </div>
        </div>

        <div id="how-it-works" class="doc-section">
            <h2>How It Works: Step by Step</h2>

            <h3>Creating a Bet</h3>
            <div class="process-step">
                <h4><span class="step-number">1</span> Choose Your Bet Type</h4>
                <p>Select whether you want to create a one-on-one bet or a group bet. Then choose if it's a money bet or a favor bet.</p>
            </div>

            <div class="process-step">
                <h4><span class="step-number">2</span> Set Up the Bet Details</h4>
                <ul>
                    <li>Describe what the bet is about</li>
                    <li>Set the stake (money amount or favor)</li>
                    <li>Choose a deadline</li>
                    <li>Select friends to invite</li>
                </ul>
            </div>

            <div class="process-step">
                <h4><span class="step-number">3</span> Waiting for Responses</h4>
                <p>Your friends will receive invitations to join the bet. They can accept or decline the invitation.</p>
            </div>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Important:</strong> For money bets, the bet only becomes active after all participants have paid their stakes.
                </div>
            </div>

            <h3>During the Bet</h3>
            <div class="process-step">
                <h4><span class="step-number">1</span> Tracking Progress</h4>
                <p>Monitor your active bets in the "My Bets" section. You can see deadlines, stakes, and participant status.</p>
            </div>

            <div class="process-step">
                <h4><span class="step-number">2</span> Declaring Results</h4>
                <p>When the deadline arrives, participants confirm the outcome. For group bets, majority agreement determines the winner.</p>
            </div>

            <div class="tip-box">
                <i class="fas fa-lightbulb"></i>
                <div>
                    <strong>Tip:</strong> Take photos or videos as proof when relevant to your bet. This helps avoid disputes about the outcome.
                </div>
            </div>
        </div>

        <div id="payments" class="doc-section">
            <h2>Payment System</h2>
            
            <h3>How Payments Work</h3>
            <div class="process-step">
                <h4><span class="step-number">1</span> Paying Your Stake</h4>
                <p>When you create or accept a money bet, you'll be prompted to pay your stake. This is done securely through Paystack.</p>
            </div>

            <div class="process-step">
                <h4><span class="step-number">2</span> Stake Holding</h4>
                <p>All stakes are held securely in an escrow account. Nobody can access the money until the bet is completed.</p>
            </div>

            <div class="process-step">
                <h4><span class="step-number">3</span> Automatic Payouts</h4>
                <p>When a winner is declared, the entire prize pool is automatically transferred to their account within 24 hours.</p>
            </div>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Safety Note:</strong> Never send payments outside the platform. All legitimate transactions happen through our secure payment system.
                </div>
            </div>
        </div>

        <div id="safety" class="doc-section">
            <h2>Safety & Security</h2>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <h4>Secure Payments</h4>
                    <ul class="security-features">
                        <li>Bank-level encryption for all transactions</li>
                        <li>Automatic refunds if a bet is cancelled</li>
                        <li>No storage of credit card information</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h4>Fair Play</h4>
                    <ul class="security-features">
                        <li>Dispute resolution system</li>
                        <li>Majority voting for group bets</li>
                        <li>Clear deadline enforcement</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h4>Account Protection</h4>
                    <ul class="security-features">
                        <li>Two-factor authentication option</li>
                        <li>Login alerts for suspicious activity</li>
                        <li>Regular security audits</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="faqs" class="doc-section">
            <h2>Frequently Asked Questions</h2>
            
            <div class="process-step">
                <h4>What happens if someone doesn't pay their stake?</h4>
                <p>The bet remains inactive until all participants have paid. If someone doesn't pay within 48 hours, the bet is automatically cancelled and any paid stakes are refunded. This will also negatively affect their trust score.</p>
            </div>

            <div class="process-step">
                <h4>Can I cancel a bet?</h4>
                <p>You can cancel a bet before it becomes active (before all participants have paid). Once active, a bet can only be cancelled if all participants agree.</p>
            </div>

            <div class="process-step">
                <h4>What if there's a dispute about the outcome?</h4>
                <p>Our dispute resolution system allows participants to submit evidence and explanations. For unresolved disputes, our moderation team will make a final decision based on the evidence provided.</p>
            </div>

            <div class="process-step">
                <h4>How long do payouts take?</h4>
                <p>Automatic payouts are processed within 24 hours of a winner being declared. The actual time to receive funds depends on your bank but typically takes 1-3 business days.</p>
            </div>

            <div class="process-step">
                <h4>How is my trust score calculated?</h4>
                <p>Your trust score starts at 70 and changes based on your betting behavior. On-time payments and completed bets increase your score, while delays or disputes decrease it. The score is automatically updated after each bet completion.</p>
            </div>

            <div class="process-step">
                <h4>Can I improve my trust score?</h4>
                <p>Yes! Consistently paying on time, completing bets without disputes, and maintaining a good betting history will gradually improve your score. The system automatically recognizes and rewards reliable behavior.</p>
            </div>

            <div class="process-step">
                <h4>Why did my trust score decrease?</h4>
                <p>Trust scores can decrease due to late payments, unresolved disputes, or cancelled bets. Each incident is evaluated based on severity and context. You can improve your score by maintaining good betting practices going forward.</p>
            </div>
        </div>

        <a href="#" class="pdf-export-btn" onclick="window.print()">
            <i class="fas fa-file-pdf"></i>
            Export as PDF
        </a>
    </div>

    <script>
        // Smooth scrolling for navigation
        document.querySelectorAll('.doc-nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                targetElement.scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Active section highlighting
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.doc-section');
            const navItems = document.querySelectorAll('.doc-nav-item');
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 60) {
                    current = section.getAttribute('id');
                }
            });

            navItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href').substring(1) === current) {
                    item.classList.add('active');
                }
            });
        });

        // Print-specific modifications
        window.onbeforeprint = function() {
            document.querySelectorAll('.doc-section').forEach(section => {
                section.style.pageBreakInside = 'avoid';
            });
        };
    </script>
</body>
</html> 