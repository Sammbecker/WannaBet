<?php
/**
 * Utility functions for the WannaBet application
 */

/**
 * Display JSON response and exit
 */
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect if user is not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Sanitize output to prevent XSS
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 */
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('M j, Y');
}

/**
 * Format currency for display
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Get bet status with formatted HTML
 */
function getBetStatusHtml($status) {
    switch ($status) {
        case 'pending':
            return '<span class="status-badge pending">Pending</span>';
        case 'active':
            return '<span class="status-badge active">Active</span>';
        case 'completed':
            return '<span class="status-badge completed">Completed</span>';
        default:
            return '<span class="status-badge">' . h($status) . '</span>';
    }
}

/**
 * Display error messages
 */
function displayErrors($errors) {
    if (!empty($errors)) {
        echo '<div class="error-container">';
        foreach ($errors as $error) {
            echo '<p class="error-message">' . h($error) . '</p>';
        }
        echo '</div>';
    }
}

/**
 * Display success message
 */
function displaySuccess($message) {
    if (!empty($message)) {
        echo '<div class="success-container">';
        echo '<p class="success-message">' . h($message) . '</p>';
        echo '</div>';
    }
}
?> 