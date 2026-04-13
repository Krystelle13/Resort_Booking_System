<?php require_once 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Island Aura Beach Resort - Mati City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .view-card { border-radius: 20px; overflow: hidden; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(0,0,0,0.1); height: 300px; }
        .view-card:hover { transform: scale(1.03); }
        .view-card img { width: 100%; height: 100%; object-fit: cover; }

        /* RESERVATION MODAL */
        .cottage-item { background: #fff; border-radius: 12px; margin-bottom: 12px; transition: 0.2s; border: 1px solid #eee; display: flex; align-items: center; padding: 15px; }
        .cottage-item:hover { border-color: var(--aura-blue); background: #f0f7ff; }
        .cottage-img-thumb { width: 100px; height: 70px; object-fit: cover; border-radius: 8px; cursor: pointer; margin: 0 15px; }
        
        /* LIGHTBOX POPUP FIX */
        #imageLightbox {
            display: none; position: fixed; z-index: 999999; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95); align-items: center; justify-content: center;
            backdrop-filter: blur(5px);
        }
        #imageLightbox img { 
            max-width: 85%; 
            max-height: 85%; 
            border-radius: 10px; 
            box-shadow: 0 0 50px rgba(0,0,0,1); 
            border: 6px solid white;
            object-fit: contain;
        }
        .close-lightbox { 
            position: absolute; top: 30px; right: 40px; color: white; font-size: 60px; 
            cursor: pointer; font-weight: bold; line-height: 1; z-index: 1000000;
        }

        /* FOOTER */
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
    <div class="hero-content">
        <h1>ISLAND AURA</h1>
        <p class="fs-3 mb-5">Experience Paradise in Mati City</p>
        <a href="#explore-section" class="btn btn-aura btn-lg">EXPLORE VIEWS</a>
    </div>
</div>

<section id="explore-section">
    <div class="container text-center">
        <h2 class="fw-bold display-5 mb-2" style="color: var(--aura-blue);">Resort Highlights</h2>
        <div class="mx-auto mb-5" style="width: 60px; height: 4px; background: var(--aura-orange);"></div>
        
        <div class="row g-4">
            <?php
            $views = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC LIMIT 6");
            while($v = $views->fetch()){ ?>
                <div class="col-md-4">
                    <div class="view-card">
                        <img src="../admin_system/<?php echo $v['image']; ?>" class="pop-img" alt="Resort View">
                    </div>
                </div>
            <?php } ?>
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
            <form action="process_booking.php" method="POST">
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
                        $rooms_list = $conn->query("SELECT * FROM rooms WHERE status = 'Available'");
                        while($rl = $rooms_list->fetch()){ ?>
                            <div class="cottage-item">
                                <input class="form-check-input cottage-checkbox" type="checkbox" name="selected_cottages[]" value="<?php echo $rl['room_id']; ?>" data-price="<?php echo $rl['price']; ?>" id="rm<?php echo $rl['room_id']; ?>" style="width: 25px; height: 25px;">
                                <img src="../admin_system/<?php echo $rl['image']; ?>" class="cottage-img-thumb pop-img" title="Click to enlarge">
                                <label class="flex-grow-1 mb-0 ms-2" for="rm<?php echo $rl['room_id']; ?>">
                                    <div class="fw-bold fs-5"><?php echo $rl['room_name']; ?></div>
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
                        <input type="date" name="check_in" class="form-control form-control-lg bg-light border-0" required>
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
    <img id="lightboxImg" src="" onclick="event.stopPropagation();">
</div>

<footer class="footer">
    <div class="container text-center text-md-start">
        <div class="row gy-4">
            <div class="col-md-4">
                <h4 class="text-white mb-3">Island Aura</h4>
                <p>A serene escape in Mati City. Experience the perfect blend of nature and comfort.</p>
            </div>
            <div class="col-md-4 text-center">
                <p class="small text-muted">&copy; 2026 Island Aura Beach Resort. <br> Built with passion by Krystelle Liray.</p>
            </div>
        </div>
    </div>
</footer>

<script>
    // AUTO TOTAL COMPUTATION
    const checkboxes = document.querySelectorAll('.cottage-checkbox');
    const displayTotal = document.getElementById('display-total');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => {
            let total = 0;
            checkboxes.forEach(c => { if (c.checked) total += parseFloat(c.getAttribute('data-price')); });
            displayTotal.innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2});
        });
    });

    // ULTIMATE LIGHTBOX LOGIC
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImg');
    const closeBtnX = document.getElementById('btn_close_x');

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('pop-img')) {
            e.preventDefault(); // Stop any refreshing/jumping
            e.stopPropagation();
            
            lightboxImg.src = e.target.src;
            lightbox.style.display = 'flex';
            document.body.style.overflow = 'hidden'; 
        }
    });

    // Close logic
    function closeGallery() {
        lightbox.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    closeBtnX.onclick = closeGallery;
    lightbox.onclick = function(e) {
        if(e.target === lightbox) {
            closeGallery();
        }
    };

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === "Escape") closeGallery();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>