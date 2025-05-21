<?php
// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /home");
    exit;
}

// Check for registration success message
$registration_success = $_SESSION['registration_success'] ?? null;
unset($_SESSION['registration_success']); // Clear the message after displaying

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../controllers/UserController.php';
    
    $userController = new UserController();
    $result = $userController->login();
    
    if ($result['success']) {
        header("Location: /home");
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WannaBet</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/css/common.css">
    <style>
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
            min-height: calc(100vh - 76px);
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.1) 0%, transparent 70%);
            z-index: -1;
            animation: rotate 20s linear infinite;
        }

        .login-card {
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

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg,rgb(0, 253, 93) 0%, #16a34a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-header p {
            color: var(--text-light);
            font-size: 1.1rem;
            background: linear-gradient(135deg,rgb(0, 253, 93) 0%, #16a34a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-footer {
            color: var(--text-light);

        .login-footer {
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
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
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

        .btn-primary {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            transform: translateY(-2px);
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .login-card {
                padding: 2rem;
                margin: 1rem;
            }

            .login-header h1 {
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

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your WannaBet account</p>
            </div>

            <?php if (isset($registration_success)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($registration_success); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="/register">Sign up</a></p>
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
