<?php
// ============================================
// getDashboard.php
// Returns summary stats for admin dashboard:
//   - total tenants
//   - total/available rooms
//   - pending payments count
//   - open complaints count
//   - recent tenants (last 5)
//   - recent open complaints (last 5)
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_config.php';

$conn = getConnection();

// Count total tenants
$totalTenants = $conn->query("SELECT COUNT(*) AS cnt FROM tenants")->fetch_assoc()['cnt'];

// Count total rooms
$totalRooms = $conn->query("SELECT COUNT(*) AS cnt FROM rooms")->fetch_assoc()['cnt'];

// Count available rooms
$availableRooms = $conn->query("SELECT COUNT(*) AS cnt FROM rooms WHERE status = 'available'")->fetch_assoc()['cnt'];

// Count pending payments
$pendingPayments = $conn->query("SELECT COUNT(*) AS cnt FROM payments WHERE status = 'pending'")->fetch_assoc()['cnt'];

// Count open complaints
$openComplaints = $conn->query("SELECT COUNT(*) AS cnt FROM complaints WHERE status = 'open'")->fetch_assoc()['cnt'];

// Recent 5 tenants
$recentResult = $conn->query(
    "SELECT t.name, t.rent_status, r.room_number
     FROM tenants t
     LEFT JOIN rooms r ON t.room_id = r.room_id
     ORDER BY t.tenant_id DESC
     LIMIT 5"
);
$recentTenants = [];
while ($row = $recentResult->fetch_assoc()) {
    $recentTenants[] = $row;
}

// Recent 5 open complaints
$complaintsResult = $conn->query(
    "SELECT c.description, c.date, c.status, t.name AS tenant_name
     FROM complaints c
     LEFT JOIN tenants t ON c.tenant_id = t.tenant_id
     WHERE c.status = 'open'
     ORDER BY c.date DESC
     LIMIT 5"
);
$recentComplaints = [];
while ($row = $complaintsResult->fetch_assoc()) {
    $recentComplaints[] = $row;
}

echo json_encode([
    'success' => true,
    'stats' => [
        'totalTenants'    => (int)$totalTenants,
        'totalRooms'      => (int)$totalRooms,
        'availableRooms'  => (int)$availableRooms,
        'pendingPayments' => (int)$pendingPayments,
        'openComplaints'  => (int)$openComplaints,
    ],
    'recentTenants'    => $recentTenants,
    'recentComplaints' => $recentComplaints
]);

$conn->close();
?>
