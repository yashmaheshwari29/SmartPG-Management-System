<?php
// ============================================
// getTenants.php
// Fetches all tenants with their room info
// Called by AngularJS $http.get
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_config.php';

$conn = getConnection();

// JOIN tenants with rooms to get room_number
$sql = "SELECT 
            t.tenant_id,
            t.name,
            t.phone,
            t.email,
            t.join_date,
            t.rent_status,
            r.room_number,
            r.floor
        FROM tenants t
        LEFT JOIN rooms r ON t.room_id = r.room_id
        ORDER BY t.tenant_id DESC";

$result = $conn->query($sql);

$tenants = [];
while ($row = $result->fetch_assoc()) {
    $tenants[] = $row;
}

echo json_encode([
    'success' => true,
    'tenants' => $tenants
]);

$conn->close();
?>
