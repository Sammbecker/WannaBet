<?php
session_start();
require_once __DIR__ . '/../controllers/UserController.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

// Initialize controller
$userController = new UserController();

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userController->register();
    if (isset($result['success']) && $result['success']) {
        // Registration successful, redirect happens in controller
    } else {
        $errors = $result['errors'] ?? ['An unknown error occurred'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WannaBet - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --background-color: #f9f9f9;
            --card-bg: #ffffff;
            --text-color: #111111;
            --text-light: #555555;
            --border-color: #eeeeee;
            --accent-color: #000000;
            --hover-accent: #333333;
            --error-color: #ef4444;
            --success-color: #10b981;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.1);
            --gradient-black: linear-gradient(145deg, #000000, #222222);
        }

        [data-theme="dark"] {
            --primary-color: #ffffff;
            --secondary-color: #cccccc;
            --background-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #f5f5f5;
            --text-light: #aaaaaa;
            --border-color: #333333;
            --accent-color: #ffffff;
            --hover-accent: #cccccc;
            --error-color: #f87171;
            --success-color: #34d399;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .auth-container {
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: var(--gradient-black);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .auth-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.95rem;
        }

        input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s;
            background: var(--card-bg);
            color: var(--text-color);
        }

        input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        }

        .btn {
            background: var(--gradient-black);
            color: white;
            padding: 1.25rem;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .btn:hover:before {
            left: 100%;
        }

        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .auth-footer a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .auth-footer a:hover {
            color: var(--hover-accent);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: none;
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.25rem;
        }

        .requirement i {
            font-size: 0.75rem;
        }

        .requirement.valid {
            color: var(--success-color);
        }

        .theme-toggle {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .theme-toggle:hover {
            background: var(--border-color);
            transform: rotate(30deg);
        }

        @media (max-width: 640px) {
            .auth-container {
                padding: 2rem;
            }

            .auth-header h1 {
                font-size: 2rem;
            }

            body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon"></i>
    </button>

    <div class="auth-container">
        <div class="auth-header">
            <h1>WannaBet</h1>
            <p>Create your account</p>
        </div>

        <?php if (isset($errors)): ?>
            <div class="error-message">
                <?php 
                    foreach ($errors as $error) {
                        echo htmlspecialchars($error) . '<br>';
                    }
                ?>
            </div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Choose a username">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Create a password">
                <div class="password-requirements">
                    <div class="requirement" id="lengthReq">
                        <i class="fas fa-circle"></i>
                        At least 8 characters
                    </div>
                    <div class="requirement" id="upperReq">
                        <i class="fas fa-circle"></i>
                        One uppercase letter
                    </div>
                    <div class="requirement" id="numberReq">
                        <i class="fas fa-circle"></i>
                        One number
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
            </div>

            <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const body = document.body;
            const isDark = body.getAttribute('data-theme') === 'dark';
            body.setAttribute('data-theme', isDark ? 'light' : 'dark');
            
            const themeIcon = document.querySelector('.theme-toggle i');
            themeIcon.className = isDark ? 'fas fa-moon' : 'fas fa-sun';
            
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        }

        // Password validation
        const password = document.getElementById('password');
        const lengthReq = document.getElementById('lengthReq');
        const upperReq = document.getElementById('upperReq');
        const numberReq = document.getElementById('numberReq');

        password.addEventListener('input', function() {
            const val = this.value;
            
            // Length requirement
            if (val.length >= 8) {
                lengthReq.classList.add('valid');
                lengthReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                lengthReq.classList.remove('valid');
                lengthReq.querySelector('i').className = 'fas fa-circle';
            }
            
            // Uppercase requirement
            if (/[A-Z]/.test(val)) {
                upperReq.classList.add('valid');
                upperReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                upperReq.classList.remove('valid');
                upperReq.querySelector('i').className = 'fas fa-circle';
            }
            
            // Number requirement
            if (/[0-9]/.test(val)) {
                numberReq.classList.add('valid');
                numberReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                numberReq.classList.remove('valid');
                numberReq.querySelector('i').className = 'fas fa-circle';
            }
        });

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validate password requirements
            if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                e.preventDefault();
                alert('Please meet all password requirements');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return;
            }
        });

        // Load saved theme
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.body.setAttribute('data-theme', savedTheme);
            const themeIcon = document.querySelector('.theme-toggle i');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        });
    </script>
</body>
</html>
