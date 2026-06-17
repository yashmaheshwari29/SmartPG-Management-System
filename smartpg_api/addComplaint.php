<?php
// ============================================
// addComplaint.php
// Inserts a new complaint into the database
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

$tenant_id   = isset($data['tenant_id'])   ? intval($data['tenant_id'])       : 0;
$description = isset($data['description']) ? trim($data['description'])        : '';

if (empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Description is required.']);
    exit;
}

$conn = getConnection();

$date   = date('Y-m-d');
$status = 'open';

$stmt = $conn->prepare(
    "INSERT INTO complaints (tenant_id, description, date, status)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param('isss', $tenant_id, $description, $date, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Complaint submitted.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit complaint.']);
}

$stmt->close();
$conn->close();
?>
