<?php

class HomeController {
    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Display home page
        require_once BASE_PATH . '/app/views/home.php';
    }
}
