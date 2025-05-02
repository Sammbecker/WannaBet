<?php
require_once __DIR__ . '/../controllers/UserController.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /home');
    exit();
}

// Initialize controller
$userController = new UserController();

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userController->register();
    if (isset($result['success']) && $result['success']) {
        header('Location: /login');
        exit;
    } else {
        $errors = isset($result['message']) ? [$result['message']] : ['An unknown error occurred'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WannaBet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/common.css">
    <style>
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
            min-height: calc(100vh - 76px);
        }

        .register-container::before {
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

        .register-card {
            background: rgba(31, 41, 55, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 3rem;
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
            animation: fadeInUp 1s ease-out;
        }

        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .register-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .register-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .register-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-light);
        }

        .theme-toggle {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s;
            z-index: 1000;
        }

        .theme-toggle:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .register-card {
                padding: 2rem;
                margin: 1rem;
            }

            .register-header h1 {
                font-size: 2rem;
            }

            .theme-toggle {
                bottom: 1rem;
                right: 1rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="/" class="logo">
                    <i class="fas fa-handshake"></i>
                    WannaBet
                </a>
            </nav>
        </div>
    </header>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Join WannaBet and start betting with friends</p>
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

            <form method="POST" action="/register">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <div class="register-footer">
                <p>Already have an account? <a href="/login">Sign in</a></p>
                <p>&copy; 2025 WannaBet. All rights reserved.</p>
            </div>
        </div>
    </div>

    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const icon = themeToggle.querySelector('i');
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.documentElement.setAttribute('data-theme', 'light');
            icon.classList.replace('fa-moon', 'fa-sun');
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'light') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'dark');
                icon.classList.replace('fa-sun', 'fa-moon');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
                icon.classList.replace('fa-moon', 'fa-sun');
            }
        });
    </script>
</body>
</html>
