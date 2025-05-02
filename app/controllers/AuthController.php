<?php

class AuthController {
    public function login() {
        // If already logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: /home');
            exit;
        }
        
        // Display login form
        require_once BASE_PATH . '/app/views/login.php';
    }

    public function register() {
        // If already logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: /home');
            exit;
        }
        
        // Display registration form
        require_once BASE_PATH . '/app/views/register.php';
    }

    public function logout() {
        // Clear session
        session_start();
        session_destroy();
        
        // Redirect to login page
        header('Location: /login');
        exit;
    }
} 