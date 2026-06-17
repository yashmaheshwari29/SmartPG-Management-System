<?php
// ============================================
// db_config.php
// Database connection settings
// Change these values to match your server
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Your MySQL username
define('DB_PASS', '');           // Your MySQL password
define('DB_NAME', 'hostel_management');

// Create a database connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }

    // Set character set
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>
