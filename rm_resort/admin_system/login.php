<?php
session_start();
require_once '../public_site/db_connect.php'; 

$error = "";

if (isset($_POST['login_btn'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    // Ginagamit ang PDO para sa safe na query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :u AND password = :p AND role = 'admin' LIMIT 1");
    $stmt->execute(['u' => $user, 'p' => $pass]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // I-set ang Session variables
        $_SESSION['admin_id'] = $admin['user_id'];
        $_SESSION['admin_user'] = $admin['username'];
        
        // ITO ANG REDIRECT: Siguraduhin na tama ang filename ng dashboard mo
        header("Location: dashboard.php");
        exit(); 
    } else {
        $error = "Mali ang Username o Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="height: 100vh;">
    <div class="card p-4 mx-auto shadow" style="width: 350px; border-radius: 15px;">
        <h3 class="text-center mb-4">Admin Login</h3>
        <?php if($error != ""): ?>
            <div class="alert alert-danger py-1 small text-center"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login_btn" class="btn btn-primary w-100 py-2">LOG IN</button>
        </form>
    </div>
</body>
</html>