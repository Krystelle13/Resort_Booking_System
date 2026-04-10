<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RM Libodon Beach Resort - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">RM LIBODON BEACH RESORT</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Available Rooms</h2>
    <div class="row">
        <?php
        $stmt = $conn->query("SELECT * FROM rooms WHERE status = 'Available'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $row['room_name']; ?></h5>
                    <p class="card-text text-muted"><?php echo $row['room_type']; ?> Room</p>
                    <h6 class="text-primary">₱<?php echo number_format($row['price_per_night'], 2); ?> / night</h6>
                    <button class="btn btn-success mt-2 w-100">Book Now</button>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

</body>
</html>