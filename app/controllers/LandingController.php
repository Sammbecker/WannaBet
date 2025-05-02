<?php

class LandingController {
    public function index() {
        // If already logged in, redirect to home
        if (isset($_SESSION['user_id'])) {
            header('Location: /home');
            exit;
        }

        // Display landing page
        require_once BASE_PATH . '/app/views/landing.php';
    }
}
