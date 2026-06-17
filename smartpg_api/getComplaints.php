<?php
// ============================================
// getComplaints.php
// Fetches complaints with tenant names
// Optional: filter by user_id (for tenant view)
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_config.php';

$conn = getConnection();

// Check if a specific user_id filter is passed (tenant's own complaints)
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id > 0) {
    // Tenant view: only their complaints (match by user_id ~ tenant_id)
    $stmt = $conn->prepare(
        "SELECT c.complaint_id, c.description, c.date, c.status,
                t.name AS tenant_name
         FROM complaints c
         LEFT JOIN tenants t ON c.tenant_id = t.tenant_id
         WHERE c.tenant_id = ?
         ORDER BY c.date DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Admin view: all complaints
    $result = $conn->query(
        "SELECT c.complaint_id, c.description, c.date, c.status,
                t.name AS tenant_name
         FROM complaints c
         LEFT JOIN tenants t ON c.tenant_id = t.tenant_id
         ORDER BY c.date DESC"
    );
}

$complaints = [];
while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}

echo json_encode([
    'success'    => true,
    'complaints' => $complaints
]);

$conn->close();
?>
