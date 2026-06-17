<?php
// ============================================
// addTenant.php
// Inserts a new tenant into the database
// Called by AngularJS $http.post
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

// Read POST data from AngularJS
$data = json_decode(file_get_contents('php://input'), true);

$name        = isset($data['name'])        ? trim($data['name'])        : '';
$phone       = isset($data['phone'])       ? trim($data['phone'])       : '';
$email       = isset($data['email'])       ? trim($data['email'])       : '';
$room_id     = isset($data['room_id'])     ? intval($data['room_id'])   : 0;
$join_date   = isset($data['join_date'])   ? $data['join_date']         : date('Y-m-d');
$rent_status = isset($data['rent_status']) ? $data['rent_status']       : 'pending';

// Validate required fields
if (empty($name) || empty($phone) || $room_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Name, phone, and room are required.']);
    exit;
}

$conn = getConnection();

// Insert new tenant
$stmt = $conn->prepare(
    "INSERT INTO tenants (name, phone, email, room_id, join_date, rent_status)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('sssiss', $name, $phone, $email, $room_id, $join_date, $rent_status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Tenant added successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add tenant: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
