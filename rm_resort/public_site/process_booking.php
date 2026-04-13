<?php
require_once 'db_connect.php';

if(isset($_POST['submit_booking'])){
    $guest_name = $_POST['guest_name'];
    $guest_phone = $_POST['guest_phone']; 
    $check_in = $_POST['check_in'];
    $cottages = isset($_POST['selected_cottages']) ? $_POST['selected_cottages'] : [];

    if(!empty($cottages)){
        try {
            foreach($cottages as $room_id){
                $sql = "INSERT INTO bookings (room_id, guest_name, guest_phone, check_in_date, status) 
                        VALUES (:rid, :name, :phone, :cdate, 'Pending')";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':rid'   => $room_id,
                    ':name'  => $guest_name,
                    ':phone' => $guest_phone,
                    ':cdate' => $check_in
                ]);
            }
            header("Location: index.php?status=success");
            exit();
        } catch (PDOException $e) {
            echo "System Error: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Please select a cottage first.'); window.location.href='index.php';</script>";
    }
}
?>