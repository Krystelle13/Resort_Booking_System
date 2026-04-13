<?php
session_start();
require_once '../public_site/db_connect.php';

// Check kung naka-login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Logic para i-save ang Cottage Name sa Database
if (isset($_POST['add_cottage'])) {
    $name = $_POST['cottage_name'];
    $price = $_POST['price'];
    $type = $_POST['type'];

    try {
        $sql = "INSERT INTO rooms (room_name, price, room_type, status) VALUES (?, ?, ?, 'Available')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $price, $type]);
        $success = "Successfully added: " . $name;
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Cottages - RM Libodon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: #212529; color: white; }
        .nav-link { color: rgba(255,255,255,0.7); }
        .nav-link:hover { color: white; background: #343a40; }
        .card { border-radius: 15px; border: none; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0 sidebar">
            <div class="p-3 text-center border-bottom border-secondary">
                <h5 class="fw-bold">RM LIBODON</h5>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link p-3" href="dashboard.php"><i class="fas fa-home me-2"></i> Bookings</a>
                <a class="nav-link p-3 active bg-primary text-white" href="manage_rooms.php"><i class="fas fa-plus-circle me-2"></i> Add Cottages</a>
                <a class="nav-link p-3 text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </nav>
        </div>

        <div class="col-md-10 p-5">
            <h2 class="fw-bold mb-4">Manage Resort Units</h2>

            <?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm p-4">
                        <h5 class="mb-4">Add New Cottage</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Cottage Name</label>
                                <input type="text" name="cottage_name" class="form-control" placeholder="e.g. Dahican View" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Price per Day (₱)</label>
                                <input type="number" name="price" class="form-control" placeholder="1500" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Unit Type</label>
                                <select name="type" class="form-select">
                                    <option value="Small Cottage">Small Cottage</option>
                                    <option value="Large Cottage">Large Cottage</option>
                                    <option value="Standard Room">Standard Room</option>
                                </select>
                            </div>
                            <button type="submit" name="add_cottage" class="btn btn-primary w-100 py-2">SAVE COTTAGE</button>
                        </form>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm p-4">
                        <h5 class="mb-4">Existing Cottages</h5>
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Unit Name</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $get_rooms = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC");
                                while ($row = $get_rooms->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>
                                        <td><strong>{$row['room_name']}</strong></td>
                                        <td>{$row['room_type']}</td>
                                        <td>₱" . number_format($row['price']) . "</td>
                                        <td><span class='badge bg-success'>Available</span></td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>