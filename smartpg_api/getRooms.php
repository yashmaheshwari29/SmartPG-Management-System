<?php
// ============================================
// getRooms.php
// Fetches all rooms from the database
// ============================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_config.php';

$conn = getConnection();

$result = $conn->query(
    "SELECT room_id, room_number, capacity, rent_amount, status, floor
     FROM rooms
     ORDER BY room_number ASC"
);

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode([
    'success' => true,
    'rooms'   => $rooms
]);

$conn->close();
?>
