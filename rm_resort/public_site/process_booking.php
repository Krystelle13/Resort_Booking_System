<?php
header('Content-Type: application/json'); 
require_once 'db_connect.php';

if (isset($_POST['submit_booking'])) {
    $name = $_POST['guest_name'];
    $phone = $_POST['guest_phone'];
    $check_in = $_POST['check_in'];
    $cottages = isset($_POST['selected_cottages']) ? $_POST['selected_cottages'] : [];

    if (empty($cottages)) {
        echo json_encode(['status' => 'error', 'message' => 'No cottage selected.']);
        exit;
    }

    try {
        $conn->beginTransaction();

        foreach ($cottages as $room_id) {
            // TINANGGAL ANG 'created_at' DITO PARA HINDI MAG-ERROR
            $stmt = $conn->prepare("INSERT INTO bookings (guest_name, guest_phone, room_id, check_in, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->execute([$name, $phone, $room_id, $check_in]);

            // I-update ang status ng room
            $update = $conn->prepare("UPDATE rooms SET status = 'Pending' WHERE room_id = ?");
            $update->execute([$room_id]);
        }

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
