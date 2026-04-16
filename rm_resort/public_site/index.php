<?php 
require_once 'db_connect.php'; 

// --- DYNAMIC RATES LOGIC ---
$rates_file = 'rates.json'; 
$resort_rates = [
    'day_adult' => '100', 'day_teen' => '80', 'day_kid' => '50', 'pool_adult' => '50',
    'night_adult' => '150', 'night_teen' => '120', 'night_kid' => '80', 'pool_kid' => '75'
];

if(file_exists($rates_file)) {
    $json_data = json_decode(file_get_contents($rates_file), true);
    if($json_data) {
        $resort_rates = array_merge($resort_rates, $json_data);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Island Aura Beach Resort - Mati City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --aura-orange: #FF8C00; --aura-blue: #007bff; --aura-dark: #1a1a1a; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }

        /* HERO SECTION */
        .hero-container { position: relative; height: 95vh; overflow: hidden; display: flex; align-items: center; justify-content: center; color: white; }
        .hero-bg-img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; filter: brightness(0.6); cursor: pointer; }
        .hero-content { text-align: center; z-index: 1; }
        .hero-content h1 { font-size: 5.5rem; font-weight: 800; text-shadow: 3px 3px 15px rgba(0,0,0,0.4); }

        .btn-aura { background: var(--aura-orange); color: white; border-radius: 50px; padding: 14px 40px; font-weight: bold; border: none; transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-aura:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(255,140,0,0.3); color: white; }

        /* NAVBAR LOGO */
        .resort-logo-img { height: 60px; width: auto; cursor: pointer; transition: 0.3s; position: relative; z-index: 1020; }
        .resort-logo-img:hover { transform: scale(1.1); }

        /* GALLERY SECTION */
        #explore-section { padding: 80px 0; background: white; }
        .view-card { border-radius: 20px; overflow: hidden; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.1); height: 350px; background: #fff; }
        .view-card:hover { transform: scale(1.03); }
        .view-card img { width: 100%; height: 80%; object-fit: cover; }
        .view-caption { padding: 15px; background: #fff; height: 20%; display: flex; align-items: center; justify-content: center; font-weight: 500; color: #555; }

        /* RATES SECTION */
        #rates-section { padding: 80px 0; background: #f8f9fa; }
        .price-card { border: none; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: 0.3s; height: 100%; }
        .price-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .rate-header { background: var(--aura-blue); color: white; padding: 25px; border-radius: 20px 20px 0 0; }
        .price-list { list-style: none; padding: 25px; margin: 0; }
        .price-list li { padding: 12px 0; border-bottom: 1px dashed #eee; display: flex; justify-content: space-between; align-items: center; }
        .price-list li:last-child { border-bottom: none; }
        .price-list li span { color: #666; font-size: 0.95rem; }
        .price-list li strong { color: var(--aura-dark); font-size: 1.1rem; }

        /* CONTACT & PAYMENT SECTION */
        #contact-section { padding: 80px 0; background: #fff; }
        .info-box { padding: 30px; border-radius: 20px; background: #fdfdfd; border: 1px solid #f0f0f0; height: 100%; }
        .payment-icon { width: 60px; height: auto; margin: 10px; filter: grayscale(100%); transition: 0.3s; }
        .payment-icon:hover { filter: grayscale(0%); }

        /* MODAL & LIGHTBOX */
        .cottage-item { background: #fff; border-radius: 12px; margin-bottom: 12px; transition: 0.2s; border: 1px solid #eee; display: flex; align-items: center; padding: 15px; position: relative; }
        .cottage-item:hover { border-color: var(--aura-blue); background: #f0f7ff; }
        
        /* CSS para sa Reserved Cottage */
        .cottage-item.is-reserved { opacity: 0.7; background: #f8f9fa; border-color: #ebccd1; }
        .cottage-item.is-reserved:hover { background: #f8f9fa; border-color: #ebccd1; }
        .reserved-label { font-size: 0.75rem; font-weight: bold; background: #dc3545; color: white; padding: 2px 8px; border-radius: 5px; margin-left: 10px; }

        .cottage-img-thumb { width: 100px; height: 70px; object-fit: cover; border-radius: 8px; cursor: pointer; margin: 0 15px; }
        
        #imageLightbox {
            display: none; position: fixed; z-index: 999999; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95); align-items: center; justify-content: center; backdrop-filter: blur(5px);
        }
        #imageLightbox img { max-width: 85%; max-height: 85%; border-radius: 10px; box-shadow: 0 0 50px rgba(0,0,0,1); border: 6px solid white; object-fit: contain; }
        .close-lightbox { position: absolute; top: 30px; right: 40px; color: white; font-size: 60px; cursor: pointer; z-index: 1000000; }

        .footer { background: var(--aura-dark); color: #bbb; padding: 60px 0 30px; }
        .total-badge { background: var(--aura-blue); color: white; padding: 15px; border-radius: 12px; font-size: 1.3rem; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-2">
    <div class="container">
        <div class="navbar-brand d-flex align-items-center" style="cursor: pointer;">
            <img src="images/logoIABR.jpg" alt="Island Aura Logo" class="resort-logo-img pop-img" onerror="this.src='https://via.placeholder.com/60?text=LOGO'">
            <span class="fw-bold fs-3 ms-2" style="color: var(--aura-blue); letter-spacing: -1px;">ISLAND AURA</span>
        </div>
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm ms-auto" data-bs-toggle="modal" data-bs-target="#multiBookModal">BOOK NOW</button>
    </div>
</nav>

<div class="hero-container">
    <img src="images/view1.jpg" alt="Resort View" class="hero-bg-img pop-img" onerror="this.src='https://via.placeholder.com/1920x1080?text=Background+Missing'">
    <div class="hero-content text-white">
        <h1>ISLAND AURA</h1>
        <p class="fs-3 mb-5">Experience Paradise in Mati City</p>
        <a href="#rates-section" class="btn btn-aura btn-lg me-2">VIEW RATES</a>
        <a href="#explore-section" class="btn btn-outline-light btn-lg rounded-pill px-4">GALLERY</a>
    </div>
</div>

<section id="rates-section">
    <div class="container text-center">
        <h2 class="fw-bold display-5 mb-2" style="color: var(--aura-blue);">Entrance & Pool Rates</h2>
        <div class="mx-auto mb-5" style="width: 60px; height: 4px; background: var(--aura-orange);"></div>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card price-card">
                    <div class="rate-header">
                        <h3 class="mb-0">Day Tour</h3>
                        <small>8:00 AM - 5:00 PM</small>
                    </div>
                    <ul class="price-list">
                        <li><span>Adults Entrance</span> <strong>₱<?php echo number_format($resort_rates['day_adult'], 2); ?></strong></li>
                        <li><span>Teens Entrance</span> <strong>₱<?php echo number_format($resort_rates['day_teen'], 2); ?></strong></li>
                        <li><span>Kids Entrance</span> <strong>₱<?php echo number_format($resort_rates['day_kid'], 2); ?></strong></li>
                        <li class="mt-2 pt-3 border-top border-primary border-opacity-10 text-primary"><span>Pool Fee (Adult)</span> <strong>₱<?php echo number_format($resort_rates['pool_adult'], 2); ?></strong></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card price-card">
                    <div class="rate-header" style="background: var(--aura-dark);">
                        <h3 class="mb-0">Overnight</h3>
                        <small>6:00 PM - 7:00 AM</small>
                    </div>
                    <ul class="price-list">
                        <li><span>Adults Entrance</span> <strong>₱<?php echo number_format($resort_rates['night_adult'], 2); ?></strong></li>
                        <li><span>Teens Entrance</span> <strong>₱<?php echo number_format($resort_rates['night_teen'], 2); ?></strong></li>
                        <li><span>Kids Entrance</span> <strong>₱<?php echo number_format($resort_rates['night_kid'], 2); ?></strong></li>
                        <li class="mt-2 pt-3 border-top border-primary border-opacity-10 text-primary"><span>Pool Fee (Kids)</span> <strong>₱<?php echo number_format($resort_rates['pool_kid'], 2); ?></strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="explore-section">
    <div class="container text-center">
        <h2 class="fw-bold display-5 mb-2" style="color: var(--aura-blue);">Resort Highlights</h2>
        <div class="mx-auto mb-5" style="width: 60px; height: 4px; background: var(--aura-orange);"></div>
        <div class="row g-4">
            <?php
            $views = $conn->query("SELECT * FROM gallery ORDER BY id DESC LIMIT 6");
            while($v = $views->fetch()){ ?>
                <div class="col-md-4">
                    <div class="view-card">
                        <img src="../admin_system/<?php echo $v['image']; ?>" class="pop-img" alt="Resort View">
                        <div class="view-caption"><?php echo htmlspecialchars($v['caption']); ?></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<section id="contact-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="info-box">
                    <h3 class="fw-bold mb-4" style="color: var(--aura-blue);"><i class="fas fa-id-card me-2"></i>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt me-3 text-primary"></i> Brgy. Dahican, Mati City, Davao Oriental</p>
                    <p><i class="fas fa-phone-alt me-3 text-primary"></i> +63 912 345 6789</p>
                    <p><i class="fas fa-envelope me-3 text-primary"></i> info@islandaura.com</p>
                    <hr>
                    <h5 class="fw-bold mb-3">Follow Us</h5>
                    <a href="#" class="btn btn-outline-primary btn-sm rounded-circle me-2"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-info btn-sm rounded-circle"><i class="fab fa-messenger"></i></a>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <div class="info-box">
                    <h3 class="fw-bold mb-4" style="color: var(--aura-blue);"><i class="fas fa-credit-card me-2"></i>Payment Methods</h3>
                    <div class="d-flex flex-wrap justify-content-center mt-3">
                        <div class="text-center">
                            <img src="images/gcas_logo.png" class="payment-icon" alt="GCash" onerror="this.src='https://via.placeholder.com/60?text=GCash'">
                            <p class="small fw-bold">GCash</p>
                        </div>
                        <div class="text-center">
                            <img src="images/maya_logo.png" class="payment-icon" alt="Maya" onerror="this.src='https://via.placeholder.com/60?text=Maya'">
                            <p class="small fw-bold">Maya</p>
                        </div>
                        <div class="text-center">
                            <img src="images/cash_logo.png" class="payment-icon" alt="Cash" onerror="this.src='https://via.placeholder.com/60?text=Cash'">
                            <p class="small fw-bold">Cash</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="multiBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 text-white p-4" style="background: var(--aura-blue);">
                <h5 class="modal-title fw-bold fs-4"><i class="fas fa-concierge-bell me-2"></i>Reservation Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bookingForm" action="process_booking.php" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">FULL NAME</label>
                            <input type="text" name="guest_name" class="form-control form-control-lg bg-light border-0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">CONTACT NUMBER</label>
                            <input type="text" name="guest_phone" class="form-control form-control-lg bg-light border-0" required>
                        </div>
                    </div>
                    <label class="form-label fw-bold text-primary mb-3">SELECT COTTAGE(S)</label>
                    <div class="cottage-list mb-4 shadow-sm rounded-3" style="max-height: 350px; overflow-y: auto; background: #fff; border: 1px solid #eee;">
                        <?php 
                        // UPDATED: Inalis ang status='Available' para lumabas lahat
                        $rooms_list = $conn->query("SELECT * FROM rooms ORDER BY room_name ASC");
                        while($rl = $rooms_list->fetch()){ 
                            $is_reserved = ($rl['status'] == 'Pending' || $rl['status'] == 'Reserved' || $rl['status'] == 'Paid');
                        ?>
                            <div class="cottage-item <?php echo $is_reserved ? 'is-reserved' : ''; ?>">
                                <input class="form-check-input cottage-checkbox" type="checkbox" 
                                       name="selected_cottages[]" value="<?php echo $rl['room_id']; ?>" 
                                       data-price="<?php echo $rl['price']; ?>" id="rm<?php echo $rl['room_id']; ?>" 
                                       style="width: 25px; height: 25px;"
                                       <?php echo $is_reserved ? 'disabled' : ''; ?>>
                                
                                <img src="../admin_system/<?php echo $rl['image']; ?>" class="cottage-img-thumb pop-img">
                                
                                <label class="flex-grow-1 mb-0 ms-2" for="rm<?php echo $rl['room_id']; ?>">
                                    <div class="fw-bold fs-5">
                                        <?php echo $rl['room_name']; ?>
                                        <?php if($is_reserved): ?>
                                            <span class="reserved-label">RESERVED</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-primary fw-bold">₱<?php echo number_format($rl['price']); ?></div>
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="total-badge mb-4 d-flex justify-content-between align-items-center">
                        <span>Total Payable Amount:</span>
                        <span>₱<span id="display-total">0.00</span></span>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">ARRIVAL DATE</label>
                        <input type="date" name="check_in" class="form-control form-control-lg bg-light border-0" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="submit_booking" class="btn btn-aura w-100 py-3 fs-5 shadow">CONFIRM RESERVATION</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="imageLightbox">
    <span class="close-lightbox" id="btn_close_x">&times;</span>
    <img id="lightboxImg" src="">
</div>

<footer class="footer mt-5">
    <div class="container text-center text-md-start">
        <div class="row gy-4">
            <div class="col-md-4">
                <h4 class="text-white mb-3">Island Aura</h4>
                <p>A serene escape in Mati City. Experience the perfect blend of nature and comfort.</p>
            </div>
            <div class="col-md-4 text-center">
                <p class="small text-muted">&copy; 2026 Island Aura Beach Resort. <br> Built with passion by Krystelle Liray & Team.</p>
            </div>
        </div>
    </div>
</footer>

<script>
    // 1. AUTO TOTAL COMPUTATION
    const checkboxes = document.querySelectorAll('.cottage-checkbox');
    const displayTotal = document.getElementById('display-total');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            let total = 0;
            checkboxes.forEach(c => { if (c.checked) total += parseFloat(c.getAttribute('data-price')); });
            displayTotal.innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2});
        });
    });

    // 2. AJAX BOOKING SUBMISSION (Professional Popup)
    const bForm = document.getElementById('bookingForm');
    bForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const checked = document.querySelectorAll('.cottage-checkbox:checked');
        if(checked.length === 0) {
            Swal.fire('Oops!', 'Please select at least one cottage.', 'warning');
            return;
        }

        const formData = new FormData(this);
        formData.append('submit_booking', 'true');

        Swal.fire({
            title: 'Processing...',
            text: 'Saving your reservation at Island Aura',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch('process_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Booked Successfully!',
                    text: 'Your reservation has been sent to the dashboard.',
                    confirmButtonColor: '#007bff'
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire('Error', data.message || 'Something went wrong.', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Could not connect to the server.', 'error');
        });
    });

    // 3. LIGHTBOX LOGIC
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const closeBtnX = document.getElementById('btn_close_x');
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('pop-img')) {
            e.preventDefault(); e.stopPropagation();
            lightboxImg.src = e.target.src;
            lightbox.style.display = 'flex';
            document.body.style.overflow = 'hidden'; 
        }
    });
    function closeGallery() { lightbox.style.display = 'none'; document.body.style.overflow = 'auto'; }
    closeBtnX.onclick = closeGallery;
    lightbox.onclick = function(e) { if(e.target === lightbox) closeGallery(); };
    document.addEventListener('keydown', function(e) { if (e.key === "Escape") closeGallery(); });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
