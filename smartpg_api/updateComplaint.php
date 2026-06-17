<?php
// ============================================
// updateComplaint.php
// Updates complaint status (open -> closed)
// Called by admin to mark complaint resolved
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);

$complaint_id = isset($data['complaint_id']) ? intval($data['complaint_id']) : 0;
$status       = isset($data['status'])       ? trim($data['status'])         : '';

if ($complaint_id === 0 || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

$conn = getConnection();

$stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE complaint_id = ?");
$stmt->bind_param('si', $status, $complaint_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed.']);
}

$stmt->close();
$conn->close();
?>
