<?php
// ============================================
// deleteTenant.php
// Removes a tenant record by tenant_id
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$tenant_id = isset($data['tenant_id']) ? intval($data['tenant_id']) : 0;

if ($tenant_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid tenant ID.']);
    exit;
}

$conn = getConnection();

$stmt = $conn->prepare("DELETE FROM tenants WHERE tenant_id = ?");
$stmt->bind_param('i', $tenant_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Tenant deleted.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed.']);
}

$stmt->close();
$conn->close();
?>
