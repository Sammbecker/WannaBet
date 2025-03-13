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

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $userController->login();
    if (isset($result['success']) && $result['success']) {
        // Login successful, redirect happens in controller
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
    <title>WannaBet - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7C3AED;
            --secondary-color: #4C1D95;
            --background-color: #F3F4F6;
            --card-bg: #FFFFFF;
            --text-color: #1F2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            margin: 1rem;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            color: var(--primary-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #E5E7EB;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            font-size: 1rem;
        }

        .btn:hover {
            background: var(--secondary-color);
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #FEE2E2;
            color: #991B1B;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .test-credentials {
            margin-top: 1rem;
            padding: 1rem;
            background: #F3F4F6;
            border-radius: 0.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: #4B5563;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h1>WannaBet</h1>
            <p>Sign in to start betting with friends</p>
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

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Enter your email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="test-credentials">
            <p><strong>Test Credentials:</strong></p>
            <p>Email: test@example.com</p>
            <p>Password: test123</p>
        </div>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
    </div>
</body>
</html>
