<?php
// ============================================
// getPayments.php
// Fetches payment records with tenant names
// Optional: filter by user_id for tenant view
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_config.php';

$conn = getConnection();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id > 0) {
    // Tenant view: only their payments
    $stmt = $conn->prepare(
        "SELECT p.payment_id, p.amount, p.payment_date, p.status,
                t.name AS tenant_name
         FROM payments p
         LEFT JOIN tenants t ON p.tenant_id = t.tenant_id
         WHERE p.tenant_id = ?
         ORDER BY p.payment_date DESC"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Admin view: all payments
    $result = $conn->query(
        "SELECT p.payment_id, p.amount, p.payment_date, p.status,
                t.name AS tenant_name
         FROM payments p
         LEFT JOIN tenants t ON p.tenant_id = t.tenant_id
         ORDER BY p.payment_date DESC"
    );
}

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode([
    'success'  => true,
    'payments' => $payments
]);

$conn->close();
?>
