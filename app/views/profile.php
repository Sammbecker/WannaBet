<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /WannaBet/login');
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Profile</h1>
        <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html> 