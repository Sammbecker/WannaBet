<?php
// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /WannaBet/home");
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
        header("Location: /WannaBet/home");
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
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-dark: #2563eb;
            --secondary-color: #10b981;
            --accent-color: #f59e0b;
            --text-color: #f3f4f6;
            --text-light: #9ca3af;
            --background-color: #111827;
            --card-background: #1f2937;
            --border-color: #374151;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --gradient-primary: linear-gradient(135deg, #3b82f6, #2563eb);
            --gradient-accent: linear-gradient(135deg, #f59e0b, #d97706);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: rgba(31, 41, 55, 0.8);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-lg);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--border-color);
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
            color: var(--text-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }

        .logo:hover {
            transform: translateY(-2px);
        }

        .logo i {
            color: var(--primary-color);
        }

        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
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

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-header p {
            color: var(--text-light);
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background: rgba(31, 41, 55, 0.3);
            color: var(--text-color);
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-control::placeholder {
            color: var(--text-light);
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .error-message {
            color: var(--danger-color);
            background: rgba(239, 68, 68, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-light);
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
        }

        .login-footer a:hover {
            color: var(--text-color);
            text-decoration: underline;
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

        .success-message {
            color: var(--success-color);
            background: rgba(16, 185, 129, 0.1);
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <a href="/WannaBet" class="logo">
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

            <form action="/WannaBet/login" method="POST">
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
                <p>Don't have an account? <a href="/WannaBet/register">Sign up</a></p>
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
