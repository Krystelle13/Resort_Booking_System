<?php
session_start();
require_once '../public_site/db_connect.php';

// --- 1. ALL-IN-ONE AUTH & SETTINGS LOGIC ---

$admin_user = "aura";
$admin_pass = "auraG5"; 

if (isset($_POST['login_btn'])) {
    if ($_POST['user'] === $admin_user && $_POST['pass'] === $admin_pass) {
        $_SESSION['authenticated'] = true;
        header("Location: dashboard.php"); exit();
    } else {
        header("Location: dashboard.php?error=invalid"); exit();
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: dashboard.php"); exit();
}

$logo_path = "../public_site/assets/logo.png"; 
if (isset($_POST['update_logo'])) {
    if(!empty($_FILES["new_logo"]["name"])){
        $target_dir = "../public_site/assets/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $target_file = $target_dir . "logo.png"; 
        if(move_uploaded_file($_FILES["new_logo"]["tmp_name"], $target_file)){
            header("Location: dashboard.php?tab=sec_settings&status=logo_updated"); exit();
        }
    }
}

if (isset($_POST['update_pass'])) {
    $curr = $_POST['current_pass'];
    if($curr === $admin_pass) {
        header("Location: dashboard.php?tab=sec_settings&status=pass_success"); exit();
    } else {
        header("Location: dashboard.php?tab=sec_settings&status=pass_error"); exit();
    }
}

// --- 2. DASHBOARD LOGIC ---
$rates_file = '../public_site/rates.json';
$resort_rates = ['day_adult'=>'100','day_teen'=>'80','day_kid'=>'50','night_adult'=>'150','night_teen'=>'120','night_kid'=>'80','pool_adult'=>'50','pool_kid'=>'30'];
if(file_exists($rates_file)) { $resort_rates = array_merge($resort_rates, json_decode(file_get_contents($rates_file), true)); }

if(isset($_POST['update_rates'])) {
    $new_rates = ['day_adult'=>$_POST['day_adult'],'day_teen'=>$_POST['day_teen'],'day_kid'=>$_POST['day_kid'],'night_adult'=>$_POST['night_adult'],'night_teen'=>$_POST['night_teen'],'night_kid'=>$_POST['night_kid'],'pool_adult'=>$_POST['pool_adult'],'pool_kid'=>$_POST['pool_kid']];
    file_put_contents($rates_file, json_encode($new_rates));
    header("Location: dashboard.php?tab=sec_rates&status=saved"); exit();
}

if(isset($_GET['mark_paid'])) { 
    $conn->prepare("UPDATE bookings SET status='Paid' WHERE guest_name=?")->execute([$_GET['gname']]); 
    header("Location: dashboard.php?tab=sec_bookings"); exit(); 
}
if(isset($_GET['undo_paid'])) { 
    $conn->prepare("UPDATE bookings SET status='Pending' WHERE guest_name=?")->execute([$_GET['gname']]); 
    header("Location: dashboard.php?tab=sec_bookings"); exit(); 
}
if(isset($_GET['checkout'])) { 
    $conn->prepare("DELETE FROM bookings WHERE guest_name=?")->execute([$_GET['gname']]); 
    header("Location: dashboard.php?tab=sec_bookings"); exit(); 
}
// Delete Cottage/Room
if(isset($_GET['del_room'])) { 
    $stmt = $conn->prepare("SELECT image FROM rooms WHERE room_id = ?");
    $stmt->execute([$_GET['del_room']]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists($img)) { unlink($img); } 
    $conn->prepare("DELETE FROM rooms WHERE room_id=?")->execute([$_GET['del_room']]); 
    header("Location: dashboard.php?tab=sec_cottages"); exit(); 
}
// Delete Gallery Image
if(isset($_GET['del_view'])) { 
    $stmt = $conn->prepare("SELECT image FROM gallery WHERE id = ?");
    $stmt->execute([$_GET['del_view']]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists($img)) { unlink($img); } 
    $conn->prepare("DELETE FROM gallery WHERE id=?")->execute([$_GET['del_view']]); 
    header("Location: dashboard.php?tab=sec_gallery"); exit(); 
}

if (isset($_POST['save_cottage'])) {
    $name = $_POST['c_name']; $price = $_POST['c_price'];
    if(!empty($_POST['c_id'])){
        if(!empty($_FILES["c_image"]["name"])){
            $img = "uploads/".time()."_".$_FILES["c_image"]["name"];
            move_uploaded_file($_FILES["c_image"]["tmp_name"], $img);
            $conn->prepare("UPDATE rooms SET room_name=?, price=?, image=? WHERE room_id=?")->execute([$name, $price, $img, $_POST['c_id']]);
        } else { $conn->prepare("UPDATE rooms SET room_name=?, price=? WHERE room_id=?")->execute([$name, $price, $_POST['c_id']]); }
    } else {
        $img = "uploads/".time()."_".$_FILES["c_image"]["name"];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES["c_image"]["tmp_name"], $img);
        $conn->prepare("INSERT INTO rooms (room_name, price, image) VALUES (?,?,?)")->execute([$name, $price, $img]);
    }
    header("Location: dashboard.php?tab=sec_cottages"); exit();
}

if (isset($_POST['save_view'])) {
    $cap = $_POST['v_caption'];
    if(!empty($_POST['v_id'])){
        if(!empty($_FILES["v_image"]["name"])){
            $img = "uploads/gallery/".time()."_".$_FILES["v_image"]["name"];
            move_uploaded_file($_FILES["v_image"]["tmp_name"], $img);
            $conn->prepare("UPDATE gallery SET caption=?, image=? WHERE id=?")->execute([$cap, $img, $_POST['v_id']]);
        } else { $conn->prepare("UPDATE gallery SET caption=? WHERE id=?")->execute([$cap, $_POST['v_id']]); }
    } else {
        $img = "uploads/gallery/".time()."_".$_FILES["v_image"]["name"];
        if(!is_dir('uploads/gallery')) mkdir('uploads/gallery', 0777, true);
        move_uploaded_file($_FILES["v_image"]["tmp_name"], $img);
        $conn->prepare("INSERT INTO gallery (caption, image) VALUES (?,?)")->execute([$cap, $img]);
    }
    header("Location: dashboard.php?tab=sec_gallery"); exit();
}

// Get Notification Count (Pending Bookings)
$notif_count = $conn->query("SELECT COUNT(DISTINCT guest_name) FROM bookings WHERE status='Pending'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --dark: #1e1e2d; --primary: #007bff; }
        body { background: #f4f6f9; font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; margin:0; }
        .login-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #f4f6f9; z-index: 9999; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 750px; display: flex; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.08); border: 1px solid #eee; }
        .login-left { flex: 1.2; background: #fff; display: flex; align-items: center; justify-content: center; padding: 40px; border-right: 1px solid #f8f9fa; }
        .login-left img { max-width: 100%; height: auto; max-height: 220px; object-fit: contain; }
        .login-right { flex: 1; padding: 50px 45px; }
        .sidebar { width: 260px; background: var(--dark); min-height: 100vh; position: fixed; color: white; padding: 25px 15px; display: flex; flex-direction: column; z-index: 100; }
        .sidebar .nav-link { color: #9899ac; padding: 14px 18px; border-radius: 12px; margin-bottom: 8px; cursor: pointer; text-decoration: none; display: flex; align-items: center; justify-content: space-between; transition: 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #2b2b40; color: #fff; }
        .badge-notif { background: #ff4d4d; color: white; font-size: 10px; padding: 4px 8px; border-radius: 50px; font-weight: bold; }
        .main-content { margin-left: 260px; width: 100%; padding: 45px; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); background: #fff; padding: 30px; margin-bottom: 30px; }
        .section { display: none; }
        .section.active { display: block; animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .stat-box { border-radius: 18px; padding: 25px; text-align: center; color: white; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .stat-pending { background: linear-gradient(45deg, #ff9f43, #ff6b6b); }
        .stat-paid { background: linear-gradient(45deg, #28c76f, #48da89); }
    </style>
</head>
<body>

<?php if(!isset($_SESSION['authenticated'])): ?>
<div class="login-overlay">
    <div class="login-card">
        <div class="login-left">
            <img src="<?php echo $logo_path . '?v=' . time(); ?>" alt="Resort Logo">
        </div>
        <div class="login-right">
            <h3>Admin Login</h3>
            <p>Enter your details to manage the resort.</p>
            <form method="POST">
                <div class="mb-3">
                    <label class="small fw-bold text-muted mb-1">USERNAME</label>
                    <input type="text" name="user" class="form-control form-control-lg fs-6" required>
                </div>
                <div class="mb-4">
                    <label class="small fw-bold text-muted mb-1">PASSWORD</label>
                    <input type="password" name="pass" class="form-control form-control-lg fs-6" required>
                </div>
                <button type="submit" name="login_btn" class="btn btn-primary w-100 fw-bold py-3 rounded-3 shadow-sm">Sign In</button>
                <?php if(isset($_GET['error'])) echo '<p class="text-danger small mt-3 text-center fw-bold">Invalid username or password!</p>'; ?>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="sidebar shadow">
    <div class="text-center mb-5">
        <img src="<?php echo $logo_path . '?v=' . time(); ?>" width="70" class="mb-2">
        <h5 class="fw-bold text-white mb-0">AURA ADMIN</h5>
        <a href="../public_site/index.php" target="_blank" class="btn btn-sm btn-outline-info mt-3 px-3 rounded-pill" style="font-size: 11px;">VIEW PUBLIC SITE</a>
    </div>
    <a class="nav-link active" onclick="showTab('sec_dashboard', this)"><span><i class="fas fa-th-large me-2"></i> Overview</span></a>
    
    <a class="nav-link" onclick="showTab('sec_bookings', this)">
        <span><i class="fas fa-book-open me-2"></i> Reservations</span>
        <?php if($notif_count > 0): ?>
            <span class="badge-notif"><?php echo $notif_count; ?></span>
        <?php endif; ?>
    </a>

    <a class="nav-link" onclick="showTab('sec_cottages', this)"><span><i class="fas fa-home me-2"></i> Cottages</span></a>
    <a class="nav-link" onclick="showTab('sec_rates', this)"><span><i class="fas fa-dollar-sign me-2"></i> Entrance & Pool</span></a>
    <a class="nav-link" onclick="showTab('sec_gallery', this)"><span><i class="fas fa-camera-retro me-2"></i> Gallery</span></a>
    <a class="nav-link" onclick="showTab('sec_settings', this)"><span><i class="fas fa-cog me-2"></i> Settings</span></a>
    
    <div class="mt-auto">
        <hr class="text-secondary opacity-25">
        <a href="dashboard.php?logout=1" class="nav-link text-danger" onclick="return confirm('Logout?')"><span><i class="fas fa-sign-out-alt me-2"></i> Logout</span></a>
    </div>
</div>

<div class="main-content">

    <div id="sec_dashboard" class="section active">
        <h2 class="fw-bold mb-4">Dashboard Overview</h2>
        <div class="row g-4 text-center">
            <div class="col-md-6"><div class="stat-box stat-pending"><h6>WAITLIST (PENDING)</h6><h1><?php echo $notif_count; ?></h1></div></div>
            <div class="col-md-6"><div class="stat-box stat-paid"><h6>CHECKED-IN (PAID)</h6><h1><?php echo $conn->query("SELECT COUNT(DISTINCT guest_name) FROM bookings WHERE status='Paid'")->fetchColumn(); ?></h1></div></div>
        </div>
    </div>

    <div id="sec_bookings" class="section">
        <h2 class="fw-bold mb-4">Reservations Management</h2>
        <div class="card-custom">
            <h5 class="fw-bold text-warning mb-4"><i class="fas fa-clock me-2"></i>New Bookings (Waitlist)</h5>
            <table class="table align-middle">
                <thead><tr><th>Guest Name</th><th>Date of Visit</th><th>Cottages</th><th>Action</th></tr></thead>
                <tbody>
                    <?php 
                    // Query improved to show check_in date
                    $res = $conn->query("SELECT b.guest_name, b.check_in, GROUP_CONCAT(r.room_name) as names FROM bookings b JOIN rooms r ON b.room_id=r.room_id WHERE b.status='Pending' GROUP BY b.guest_name, b.check_in ORDER BY b.check_in ASC");
                    while($r = $res->fetch()){ ?>
                        <tr>
                            <td><b><?php echo $r['guest_name']; ?></b></td>
                            <td><span class="badge bg-light text-dark border"><?php echo date('M d, Y', strtotime($r['check_in'])); ?></span></td>
                            <td><?php echo $r['names']; ?></td>
                            <td><a href="dashboard.php?mark_paid=1&gname=<?php echo urlencode($r['guest_name']); ?>" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">Confirm & Paid</a></td>
                        </tr>
                    <?php } ?>
                    <?php if($notif_count == 0) echo '<tr><td colspan="4" class="text-center text-muted py-4">No pending reservations.</td></tr>'; ?>
                </tbody>
            </table>
        </div>

        <div class="card-custom">
            <h5 class="fw-bold text-success mb-4"><i class="fas fa-check-circle me-2"></i>Currently Staying / Paid</h5>
            <table class="table align-middle">
                <thead><tr><th>Guest Name</th><th>Visit Date</th><th>Cottages</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $res = $conn->query("SELECT b.guest_name, b.check_in, GROUP_CONCAT(r.room_name) as names FROM bookings b JOIN rooms r ON b.room_id=r.room_id WHERE b.status='Paid' GROUP BY b.guest_name, b.check_in");
                    while($r = $res->fetch()){ ?>
                        <tr>
                            <td><b><?php echo $r['guest_name']; ?></b></td>
                            <td><span class="badge bg-light text-success border"><?php echo date('M d, Y', strtotime($r['check_in'])); ?></span></td>
                            <td><?php echo $r['names']; ?></td>
                            <td>
                                <a href="dashboard.php?undo_paid=1&gname=<?php echo urlencode($r['guest_name']); ?>" class="btn btn-light btn-sm me-2 border">Undo</a>
                                <button onclick="confirmCheckout('<?php echo urlencode($r['guest_name']); ?>')" class="btn btn-danger btn-sm rounded-pill px-3">Checkout</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="sec_rates" class="section">
        <h2 class="fw-bold mb-4 text-primary">Rates Management</h2>
        <form method="POST">
            <div class="row g-4">
                <div class="col-md-4"><div class="card-custom border-top border-primary border-5"><h6>DAY TOUR</h6><label class="small">Adult</label><input type="number" name="day_adult" class="form-control mb-2" value="<?php echo $resort_rates['day_adult']; ?>"><label class="small">Teens</label><input type="number" name="day_teen" class="form-control mb-2" value="<?php echo $resort_rates['day_teen']; ?>"><label class="small">Kids</label><input type="number" name="day_kid" class="form-control mb-2" value="<?php echo $resort_rates['day_kid']; ?>"></div></div>
                <div class="col-md-4"><div class="card-custom border-top border-dark border-5"><h6>OVERNIGHT</h6><label class="small">Adult</label><input type="number" name="night_adult" class="form-control mb-2" value="<?php echo $resort_rates['night_adult']; ?>"><label class="small">Teens</label><input type="number" name="night_teen" class="form-control mb-2" value="<?php echo $resort_rates['night_teen']; ?>"><label class="small">Kids</label><input type="number" name="night_kid" class="form-control mb-2" value="<?php echo $resort_rates['night_kid']; ?>"></div></div>
                <div class="col-md-4"><div class="card-custom border-top border-info border-5"><h6>POOL USE</h6><label class="small">Pool Adult</label><input type="number" name="pool_adult" class="form-control mb-2" value="<?php echo $resort_rates['pool_adult']; ?>"><label class="small">Pool Kids</label><input type="number" name="pool_kid" class="form-control mb-2" value="<?php echo $resort_rates['pool_kid']; ?>"></div></div>
                <div class="col-12"><button type="submit" name="update_rates" class="btn btn-primary btn-lg rounded-pill px-5 shadow">Save All Rates</button></div>
            </div>
        </form>
    </div>

    <div id="sec_cottages" class="section">
        <div class="d-flex justify-content-between mb-4"><h2 class="fw-bold">Manage Cottages</h2><button class="btn btn-primary rounded-pill px-4" onclick="openCottageModal()">+ Add New</button></div>
        <div class="card-custom">
            <table class="table align-middle">
                <thead><tr><th>Image</th><th>Name</th><th>Price</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $rooms = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC");
                    while($rm = $rooms->fetch()){ ?>
                        <tr><td><img src="<?php echo $rm['image']; ?>" width="55" height="55" style="object-fit:cover" class="rounded shadow-sm"></td><td><b><?php echo $rm['room_name']; ?></b></td><td class="text-primary fw-bold">₱<?php echo number_format($rm['price']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-light border me-1" onclick='editCottage(<?php echo json_encode($rm); ?>)'><i class="fas fa-edit text-primary"></i></button>
                                <a href="dashboard.php?del_room=<?php echo $rm['room_id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this cottage?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="sec_gallery" class="section">
        <div class="d-flex justify-content-between mb-4"><h2 class="fw-bold">Resort Gallery</h2><button class="btn btn-primary rounded-pill px-4" onclick="openGalleryModal()">+ Add Photo</button></div>
        <div class="row g-3">
            <?php $gal = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
            while($g = $gal->fetch()){ ?>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <img src="<?php echo $g['image']; ?>" class="w-100" style="height:160px; object-fit:cover;">
                        <div class="p-3 text-center">
                            <small class="d-block mb-2 text-muted fw-bold"><?php echo strtoupper($g['caption']); ?></small>
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-light border" onclick='editGallery(<?php echo json_encode($g); ?>)'><i class="fas fa-edit text-primary"></i></button>
                                <a href="dashboard.php?del_view=<?php echo $g['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this photo?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div id="sec_settings" class="section">
        <h2 class="fw-bold mb-4">System Settings</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card-custom">
                    <h5 class="fw-bold mb-4"><i class="fas fa-image me-2 text-primary"></i>Resort Branding</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-4 p-4 border rounded-4 text-center bg-light">
                            <img src="<?php echo $logo_path . '?v=' . time(); ?>" width="120" class="mb-2">
                            <div class="small text-muted">Current System Logo</div>
                        </div>
                        <label class="small fw-bold mb-2">Upload New Logo (PNG recommended)</label>
                        <input type="file" name="new_logo" class="form-control mb-4" required>
                        <button type="submit" name="update_logo" class="btn btn-primary w-100 rounded-pill fw-bold py-2">Apply New Logo</button>
                    </form>
                    <?php if(isset($_GET['status']) && $_GET['status'] == 'logo_updated') echo '<p class="text-success small mt-3 text-center">Branding updated successfully!</p>'; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-custom">
                    <h5 class="fw-bold mb-4"><i class="fas fa-key me-2 text-warning"></i>Admin Security</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Current Password</label>
                            <input type="password" name="current_pass" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="small fw-bold mb-1">New Password</label>
                            <input type="password" name="new_pass" class="form-control" required>
                        </div>
                        <button type="submit" name="update_pass" class="btn btn-warning w-100 rounded-pill fw-bold py-2">Update Credentials</button>
                    </form>
                    <?php 
                        if(isset($_GET['status'])){
                            if($_GET['status'] == 'pass_success') echo '<p class="text-success small mt-3 text-center">Password changed successfully!</p>';
                            if($_GET['status'] == 'pass_error') echo '<p class="text-danger small mt-3 text-center">Current password is incorrect!</p>';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="cottageModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4 shadow"><form method="POST" enctype="multipart/form-data"><div class="modal-body p-4 text-center"><h5 class="fw-bold mb-4" id="c_modal_title">Cottage Details</h5><input type="hidden" name="c_id" id="c_id"><input type="text" name="c_name" id="c_name" class="form-control mb-3" placeholder="Name" required><input type="number" name="c_price" id="c_price" class="form-control mb-3" placeholder="Price" required><input type="file" name="c_image" id="c_image" class="form-control mb-4"><button type="submit" name="save_cottage" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">SAVE</button></div></form></div></div></div>
<div class="modal fade" id="galleryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4 shadow"><form method="POST" enctype="multipart/form-data"><div class="modal-body p-4 text-center"><h5 class="fw-bold mb-4" id="v_modal_title">Gallery Post</h5><input type="hidden" name="v_id" id="v_id"><input type="text" name="v_caption" id="v_caption" class="form-control mb-3" placeholder="Caption" required><input type="file" name="v_image" id="v_image" class="form-control mb-4"><button type="submit" name="save_view" class="btn btn-primary w-100 py-2 rounded-pill fw-bold">SAVE POST</button></div></form></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showTab(id, el) {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        if(el) el.classList.add('active');
        const url = new URL(window.location); url.searchParams.set('tab', id); window.history.pushState({}, '', url);
    }
    
    function confirmCheckout(gname) { if(confirm("Checkout guest: " + decodeURIComponent(gname) + "?")) { window.location.href = "dashboard.php?checkout=1&gname=" + gname; } }
    function openCottageModal() { document.getElementById('c_id').value = ''; document.getElementById('c_name').value = ''; document.getElementById('c_price').value = ''; document.getElementById('c_modal_title').innerText = 'Add New Cottage'; new bootstrap.Modal(document.getElementById('cottageModal')).show(); }
    function editCottage(data) { document.getElementById('c_id').value = data.room_id; document.getElementById('c_name').value = data.room_name; document.getElementById('c_price').value = data.price; document.getElementById('c_modal_title').innerText = 'Edit Cottage'; new bootstrap.Modal(document.getElementById('cottageModal')).show(); }
    function openGalleryModal() { document.getElementById('v_id').value = ''; document.getElementById('v_caption').value = ''; document.getElementById('v_modal_title').innerText = 'Add Photo'; new bootstrap.Modal(document.getElementById('galleryModal')).show(); }
    function editGallery(data) { document.getElementById('v_id').value = data.id; document.getElementById('v_caption').value = data.caption; document.getElementById('v_modal_title').innerText = 'Edit Post'; new bootstrap.Modal(document.getElementById('galleryModal')).show(); }

    window.onload = () => {
        const tab = new URLSearchParams(window.location.search).get('tab') || 'sec_dashboard';
        const link = document.querySelector(`[onclick*="${tab}"]`);
        if(link) link.click();
    };
</script>
</body>
</html>
