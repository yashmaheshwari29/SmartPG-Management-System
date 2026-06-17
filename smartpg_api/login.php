<?php
// ============================================
// login.php
// Validates username, password, and role
// Returns user info on success
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

// Read POST data sent by AngularJS $http.post
$data = json_decode(file_get_contents('php://input'), true);

$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';
$role     = isset($data['role'])     ? trim($data['role'])     : '';

// Basic validation
if (empty($username) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$conn = getConnection();

// Query the users table
$stmt = $conn->prepare(
    "SELECT user_id, username, name, role, phone, email 
     FROM users 
     WHERE username = ? AND password = ? AND role = ?"
);
$stmt->bind_param('sss', $username, $password, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'user'    => $user
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username, password, or role.'
    ]);
}

$stmt->close();
$conn->close();
?>
