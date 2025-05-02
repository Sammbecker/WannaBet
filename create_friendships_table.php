<?php
require_once __DIR__ . '/app/config/db.php';

// Connect to the database
$conn = getDB();

echo "Checking friendships table structure...\n\n";

// Get table information
$sql = "SHOW TABLES LIKE 'friendships'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$tableExists = $stmt->rowCount() > 0;

if (!$tableExists) {
    echo "ERROR: Friendships table does not exist!\n";
    exit;
}

// Show table structure using a more reliable method
$sql = "SHOW CREATE TABLE friendships";
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $tableInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($tableInfo) {
        echo "Table Structure:\n";
        echo "==============================================================\n";
        echo $tableInfo['Create Table'] . "\n";
        echo "==============================================================\n\n";
    }
} catch (PDOException $e) {
    echo "Error getting table structure: " . $e->getMessage() . "\n";
}

// Check columns
$sql = "DESCRIBE friendships";
$stmt = $conn->prepare($sql);
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Column Information:\n";
echo "==============================================================\n";
echo sprintf("%-15s %-30s %-10s %-10s %-20s\n", "FIELD", "TYPE", "NULL", "KEY", "DEFAULT");
echo str_repeat("-", 85) . "\n";

foreach ($columns as $column) {
    echo sprintf(
        "%-15s %-30s %-10s %-10s %-20s\n",
        $column['Field'],
        $column['Type'],
        $column['Null'],
        $column['Key'],
        $column['Default'] ?? 'NULL'
    );
}
echo "==============================================================\n\n";

// Display sample records
$sql = "SELECT * FROM friendships LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Sample Records (" . count($records) . "):\n";
echo "==============================================================\n";
if (empty($records)) {
    echo "No friendship records found.\n";
} else {
    echo sprintf("%-6s %-10s %-10s %-15s %-20s %-20s\n", "ID", "USER_ID", "FRIEND_ID", "STATUS", "CREATED_AT", "UPDATED_AT");
    echo str_repeat("-", 85) . "\n";
    
    foreach ($records as $record) {
        echo sprintf(
            "%-6s %-10s %-10s %-15s %-20s %-20s\n",
            $record['id'],
            $record['user_id'],
            $record['friend_id'],
            $record['status'],
            $record['created_at'],
            $record['updated_at']
        );
    }
}
echo "==============================================================\n";
?> 