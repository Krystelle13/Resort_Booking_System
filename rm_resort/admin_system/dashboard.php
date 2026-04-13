<?php
session_start();
require_once '../public_site/db_connect.php';

// --- 1. ACTIONS (WITH AUTO-FILE DELETE) ---

// Mark as Fully Paid (Lipat sa Staying)
if(isset($_GET['mark_paid'])) { 
    $gname = $_GET['gname'];
    $conn->prepare("UPDATE bookings SET status='Paid' WHERE guest_name=?")->execute([$gname]); 
    header("Location: dashboard.php?tab=sec_bookings"); exit(); 
}

// Undo Payment
if(isset($_GET['undo_paid'])) { 
    $gname = $_GET['gname'];
    $conn->prepare("UPDATE bookings SET status='Pending' WHERE guest_name=?")->execute([$gname]); 
    header("Location: dashboard.php?tab=sec_bookings"); exit(); 
}

// Checkout (Automatic Delete Record)
if(isset($_GET['checkout'])) { 
    $gname = $_GET['gname'];
    $conn->prepare("DELETE FROM bookings WHERE guest_name=?")->execute([$gname]); 
    header("Location: dashboard.php?tab=sec_bookings"); exit(); 
}

// Delete Cottage (Mabubura pati yung file sa folder)
if(isset($_GET['del_room'])) { 
    $id = $_GET['del_room'];
    $stmt = $conn->prepare("SELECT image FROM rooms WHERE room_id = ?");
    $stmt->execute([$id]);
    $img_path = $stmt->fetchColumn();

    // Dito binubura yung file sa folder
    if ($img_path && file_exists($img_path)) {
        unlink($img_path); 
    }

    $conn->prepare("DELETE FROM rooms WHERE room_id=?")->execute([$id]); 
    header("Location: dashboard.php?tab=sec_cottages"); exit(); 
}

// Delete Gallery Post (Mabubura pati yung file sa folder)
if(isset($_GET['del_view'])) { 
    $id = $_GET['del_view'];
    $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $img_path = $stmt->fetchColumn();

    // Dito binubura yung file sa folder
    if ($img_path && file_exists($img_path)) {
        unlink($img_path); 
    }

    $conn->prepare("DELETE FROM gallery WHERE id=?")->execute([$id]); 
    header("Location: dashboard.php?tab=sec_gallery"); exit(); 
}

// --- 2. SAVE/EDIT LOGIC (COTTAGE & GALLERY) ---

// Save Cottage
if (isset($_POST['save_cottage'])) {
    $name = $_POST['c_name']; $price = $_POST['c_price'];
    if(!empty($_POST['c_id'])){
        $id = $_POST['c_id'];
        if(!empty($_FILES["c_image"]["name"])){
            // Burahin yung lumang file bago palitan
            $old = $conn->prepare("SELECT image FROM rooms WHERE room_id=?"); $old->execute([$id]);
            $old_path = $old->fetchColumn();
            if($old_path && file_exists($old_path)) unlink($old_path);

            $img = "uploads/".time()."_".$_FILES["c_image"]["name"];
            move_uploaded_file($_FILES["c_image"]["tmp_name"], $img);
            $conn->prepare("UPDATE rooms SET room_name=?, price=?, image=? WHERE room_id=?")->execute([$name, $price, $img, $id]);
        } else {
            $conn->prepare("UPDATE rooms SET room_name=?, price=? WHERE room_id=?")->execute([$name, $price, $id]);
        }
    } else {
        $img = "uploads/".time()."_".$_FILES["c_image"]["name"];
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES["c_image"]["tmp_name"], $img);
        $conn->prepare("INSERT INTO rooms (room_name, price, image) VALUES (?,?,?)")->execute([$name, $price, $img]);
    }
    header("Location: dashboard.php?tab=sec_cottages"); exit();
}

// Save Gallery
if (isset($_POST['save_view'])) {
    $cap = $_POST['v_caption'];
    if(!empty($_POST['v_id'])){
        $id = $_POST['v_id'];
        if(!empty($_FILES["v_image"]["name"])){
            // Burahin yung lumang file
            $old = $conn->prepare("SELECT image FROM gallery WHERE id=?"); $old->execute([$id]);
            $old_path = $old->fetchColumn();
            if($old_path && file_exists($old_path)) unlink($old_path);

            $img = "uploads/gallery/".time()."_".$_FILES["v_image"]["name"];
            move_uploaded_file($_FILES["v_image"]["tmp_name"], $img);
            $conn->prepare("UPDATE gallery SET caption=?, image=? WHERE id=?")->execute([$cap, $img, $id]);
        } else {
            $conn->prepare("UPDATE gallery SET caption=? WHERE id=?")->execute([$cap, $id]);
        }
    } else {
        $img = "uploads/gallery/".time()."_".$_FILES["v_image"]["name"];
        if (!is_dir('uploads/gallery')) mkdir('uploads/gallery', 0777, true);
        move_uploaded_file($_FILES["v_image"]["tmp_name"], $img);
        $conn->prepare("INSERT INTO gallery (caption, image) VALUES (?,?)")->execute([$cap, $img]);
    }
    header("Location: dashboard.php?tab=sec_gallery"); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --dark: #1a1a27; --primary: #007bff; }
        body { background: #eff1f4; font-family: 'Segoe UI', sans-serif; display: flex; }
        .sidebar { width: 260px; background: var(--dark); min-height: 100vh; position: fixed; color: white; padding: 20px; }
        .main-content { margin-left: 260px; width: 100%; padding: 40px; }
        .nav-link { color: #a2a3b7; padding: 12px; border-radius: 8px; cursor: pointer; text-decoration: none; display: block; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { background: #2b2b40; color: white; }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); background: white; padding: 25px; margin-bottom: 30px; }
        .section { display: none; }
        .section.active { display: block; }
        .btn-paid { background: #28a745; color: white; border-radius: 20px; padding: 5px 15px; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="fw-bold mb-4 text-info text-center">Island Aura</h4>
    <a class="nav-link active" onclick="showTab('sec_dashboard', this)"><i class="fas fa-chart-line me-2"></i> Dashboard</a>
    <a class="nav-link" onclick="showTab('sec_bookings', this)"><i class="fas fa-calendar-check me-2"></i> Reservations</a>
    <a class="nav-link" onclick="showTab('sec_cottages', this)"><i class="fas fa-umbrella-beach me-2"></i> Cottages</a>
    <a class="nav-link" onclick="showTab('sec_gallery', this)"><i class="fas fa-images me-2"></i> Gallery</a>
</div>

<div class="main-content">

    <div id="sec_dashboard" class="section active">
        <h2 class="fw-bold mb-4">Dashboard Overview</h2>
        <div class="row g-4">
            <div class="col-md-6"><div class="card-custom text-center border-bottom border-warning border-5"><h6>WAITLIST</h6><h2><?php echo $conn->query("SELECT COUNT(DISTINCT guest_name) FROM bookings WHERE status='Pending'")->fetchColumn(); ?></h2></div></div>
            <div class="col-md-6"><div class="card-custom text-center border-bottom border-success border-5"><h6>STAYING</h6><h2><?php echo $conn->query("SELECT COUNT(DISTINCT guest_name) FROM bookings WHERE status='Paid'")->fetchColumn(); ?></h2></div></div>
        </div>
    </div>

    <div id="sec_bookings" class="section">
        <h2 class="fw-bold mb-4">Manage Bookings</h2>
        
        <div class="card-custom">
            <h5 class="fw-bold text-warning mb-3">Waitlist</h5>
            <table class="table align-middle">
                <thead><tr><th>Guest</th><th>Cottages</th><th>Total</th><th>Action</th></tr></thead>
                <tbody>
                    <?php 
                    $res = $conn->query("SELECT b.guest_name, GROUP_CONCAT(r.room_name SEPARATOR ', ') as cottage_names, SUM(r.price) as total_price 
                                         FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.status = 'Pending' GROUP BY b.guest_name");
                    while($r = $res->fetch()){ ?>
                        <tr>
                            <td><strong><?php echo $r['guest_name']; ?></strong></td>
                            <td><span class="badge bg-light text-dark"><?php echo $r['cottage_names']; ?></span></td>
                            <td class="fw-bold text-primary">₱<?php echo number_format($r['total_price'], 2); ?></td>
                            <td><a href="dashboard.php?mark_paid=1&gname=<?php echo urlencode($r['guest_name']); ?>" class="btn-paid">Fully Paid</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="card-custom">
            <h5 class="fw-bold text-success mb-3">Staying</h5>
            <table class="table align-middle">
                <thead><tr><th>Guest</th><th>Cottages</th><th>Paid</th><th>Action</th></tr></thead>
                <tbody>
                    <?php 
                    $res = $conn->query("SELECT b.guest_name, GROUP_CONCAT(r.room_name SEPARATOR ', ') as cottage_names, SUM(r.price) as total_price 
                                         FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.status = 'Paid' GROUP BY b.guest_name");
                    while($r = $res->fetch()){ ?>
                        <tr>
                            <td><strong><?php echo $r['guest_name']; ?></strong></td>
                            <td><span class="badge bg-success"><?php echo $r['cottage_names']; ?></span></td>
                            <td class="fw-bold">₱<?php echo number_format($r['total_price'], 2); ?></td>
                            <td>
                                <a href="dashboard.php?undo_paid=1&gname=<?php echo urlencode($r['guest_name']); ?>" class="btn btn-outline-secondary btn-sm me-2">Undo</a>
                                <a href="dashboard.php?checkout=1&gname=<?php echo urlencode($r['guest_name']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Checkout and delete record?')">Checkout</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="sec_cottages" class="section">
        <div class="d-flex justify-content-between mb-4">
            <h2 class="fw-bold">Cottages</h2>
            <button class="btn btn-primary px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#cottageModal">+ Add Cottage</button>
        </div>
        <div class="card-custom">
            <table class="table align-middle">
                <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $rooms = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC");
                    while($rm = $rooms->fetch()){ ?>
                        <tr>
                            <td><img src="<?php echo $rm['image']; ?>" width="70" class="rounded"></td>
                            <td><strong><?php echo $rm['room_name']; ?></strong></td>
                            <td>₱<?php echo number_format($rm['price']); ?></td>
                            <td>
                                <button class="btn btn-light btn-sm" onclick="editCottage(<?php echo htmlspecialchars(json_encode($rm)); ?>)"><i class="fas fa-edit text-primary"></i></button>
                                <a href="dashboard.php?del_room=<?php echo $rm['room_id']; ?>" class="btn btn-light btn-sm text-danger" onclick="return confirm('Delete this cottage and its image?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="sec_gallery" class="section">
        <div class="d-flex justify-content-between mb-4">
            <h2 class="fw-bold">Gallery</h2>
            <button class="btn btn-primary px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#galleryModal">+ Add Gallery Post</button>
        </div>
        <div class="card-custom">
            <table class="table align-middle">
                <thead><tr><th>Image</th><th>Caption</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $gal = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
                    while($g = $gal->fetch()){ ?>
                        <tr>
                            <td><img src="<?php echo $g['image']; ?>" width="100" class="rounded"></td>
                            <td><?php echo $g['caption']; ?></td>
                            <td>
                                <button class="btn btn-light btn-sm" onclick="editGallery(<?php echo htmlspecialchars(json_encode($g)); ?>)"><i class="fas fa-edit text-primary"></i></button>
                                <a href="dashboard.php?del_view=<?php echo $g['id']; ?>" class="btn btn-light btn-sm text-danger" onclick="return confirm('Delete this post and its image?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="cottageModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0"><form method="POST" enctype="multipart/form-data"><div class="modal-body p-4"><h5 class="fw-bold mb-3">Cottage Details</h5><input type="hidden" name="c_id" id="c_id"><input type="text" name="c_name" id="c_name" class="form-control mb-3" placeholder="Name" required><input type="number" name="c_price" id="c_price" class="form-control mb-3" placeholder="Price" required><input type="file" name="c_image" class="form-control mb-2"><button type="submit" name="save_cottage" class="btn btn-primary w-100 mt-3 rounded-pill">Save Cottage</button></div></form></div></div></div>

<div class="modal fade" id="galleryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 border-0"><form method="POST" enctype="multipart/form-data"><div class="modal-body p-4"><h5 class="fw-bold mb-3">Gallery Post</h5><input type="hidden" name="v_id" id="v_id"><input type="text" name="v_caption" id="v_caption" class="form-control mb-3" placeholder="Caption" required><input type="file" name="v_image" class="form-control mb-2"><button type="submit" name="save_view" class="btn btn-primary w-100 mt-3 rounded-pill">Post to Gallery</button></div></form></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showTab(id, el) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        el.classList.add('active');
        const url = new URL(window.location); url.searchParams.set('tab', id); window.history.pushState({}, '', url);
    }
    function editCottage(data) {
        document.getElementById('c_id').value = data.room_id;
        document.getElementById('c_name').value = data.room_name;
        document.getElementById('c_price').value = data.price;
        new bootstrap.Modal(document.getElementById('cottageModal')).show();
    }
    function editGallery(data) {
        document.getElementById('v_id').value = data.id;
        document.getElementById('v_caption').value = data.caption;
        new bootstrap.Modal(document.getElementById('galleryModal')).show();
    }
    window.onload = () => {
        const tab = new URLSearchParams(window.location.search).get('tab') || 'sec_dashboard';
        const link = document.querySelector(`[onclick*="${tab}"]`);
        if(link) link.click();
    };
</script>
</body>
</html>