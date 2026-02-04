<?php
session_start();

// Cek jika sudah login, redirect sesuai role
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/index.php');
            break;
        case 'petugas':
            header('Location: petugas/index.php');
            break;
        case 'peminjam':
            header('Location: peminjam/index.php');
            break;
    }
    exit;
}

require_once 'database/koneksi.php';

// Ambil data motor untuk ditampilkan
$stmt = $pdo->query("SELECT * FROM motor WHERE status = 'tersedia' ORDER BY id DESC LIMIT 8");
$motors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Marvell Rental - Solusi rental motor harian hingga mingguan. Tersedia motor Matic, Supermoto, dan Sport sesuai kebutuhan perjalanan Anda.">
    <title>Marvell Rental - Rental Sepeda Motor Terpercaya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">
                <div class="navbar-logo">MR</div>
                <span class="navbar-title">Marvell<span>Rental</span></span>
            </a>

            <div class="navbar-menu">
                <a href="#home" class="active">Home</a>
                <a href="#motor">Daftar Motor</a>
                <a href="#cara-sewa">Cara Sewa</a>
                <a href="auth/login.php">Login</a>
            </div>

            <a href="tel:+6281234567890" class="navbar-phone">
                <i class="fas fa-phone"></i>
                0812 3456 7890
            </a>

            <div class="navbar-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge">🏍️ Rental Motor Terpercaya</span>
                <h1 class="hero-title">
                    Sewa Motor Mudah
                    <span>Perjalanan Nyaman</span>
                </h1>
                <p class="hero-desc">
                    Solusi rental motor harian hingga mingguan.
                    Tersedia motor Matic, Supermoto, dan Sport sesuai kebutuhan perjalanan Anda.
                </p>
                <div class="hero-buttons">
                    <a href="#motor" class="btn btn-primary">
                        <i class="fas fa-motorcycle"></i>
                        Lihat Motor
                    </a>
                    <a href="auth/register.php" class="btn btn-outline">
                        <i class="fas fa-user-plus"></i>
                        Daftar Sekarang
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/yamaha_nmax.png" alt="Yamaha NMAX"
                    onerror="this.src='https://via.placeholder.com/500x300/1a1a1a/FFD700?text=Motor+Sport'">
            </div>
        </div>
    </section>

    <!-- CTA Banner -->
    <section class="cta-banner">
        <div class="container">
            <a href="#motor" class="cta-button">
                Lihat Armada Lainnya
            </a>
        </div>
    </section>

    <!-- Vehicle Section -->
    <section id="motor" class="section section-gold">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title" style="color: #000;">OUR VEHICLE</h2>
                <p class="section-subtitle" style="color: #333;">Pilihan motor terbaik untuk perjalanan Anda</p>
            </div>

            <!-- Category Tabs -->
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <button class="category-filter active" onclick="filterMotor('')"
                    style="padding: 10px 25px; background: #000; color: #FFD700; border: none; border-radius: 50px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-motorcycle"></i> Semua
                </button>
                <button class="category-filter" onclick="filterMotor('Matic')"
                    style="padding: 10px 25px; background: rgba(0,0,0,0.2); color: #000; border: none; border-radius: 50px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-bolt"></i> Matic
                </button>
                <button class="category-filter" onclick="filterMotor('Sport')"
                    style="padding: 10px 25px; background: rgba(0,0,0,0.2); color: #000; border: none; border-radius: 50px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-flag-checkered"></i> Sport
                </button>
                <button class="category-filter" onclick="filterMotor('Supermoto')"
                    style="padding: 10px 25px; background: rgba(0,0,0,0.2); color: #000; border: none; border-radius: 50px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-mountain"></i> Supermoto
                </button>
            </div>
        </div>
    </section>

    <section class="vehicle-section">
        <div class="container">
            <div class="vehicle-carousel">
                <button class="carousel-nav carousel-prev">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="vehicle-track">
                    <?php if (count($motors) > 0): ?>
                        <?php foreach ($motors as $motor): ?>
                            <div class="vehicle-card">
                                <div class="vehicle-card-image">
                                    <?php if ($motor['gambar'] && file_exists('assets/images/' . $motor['gambar'])): ?>
                                        <img src="assets/images/<?= htmlspecialchars($motor['gambar']) ?>"
                                            alt="<?= htmlspecialchars($motor['nama_motor']) ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/200x120/1a1a1a/FFD700?text=<?= urlencode($motor['nama_motor']) ?>"
                                            alt="<?= htmlspecialchars($motor['nama_motor']) ?>">
                                    <?php endif; ?>
                                </div>
                                <span class="vehicle-card-type">
                                    <?= htmlspecialchars($motor['jenis']) ?>
                                </span>
                                <h3 class="vehicle-card-name">
                                    <?= htmlspecialchars($motor['nama_motor']) ?>
                                </h3>
                                <p class="vehicle-card-price">
                                    <?= formatRupiah($motor['harga_per_hari']) ?>
                                    <span>/hari</span>
                                </p>
                                <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">
                                    Sewa Sekarang
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Sample Motor Cards if database is empty -->
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="assets/images/honda_beat.png" alt="Honda Beat">
                            </div>
                            <span class="vehicle-card-type">Matic</span>
                            <h3 class="vehicle-card-name">Honda Beat</h3>
                            <p class="vehicle-card-price">Rp 75.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="assets/images/honda_vario.png" alt="Honda Vario">
                            </div>
                            <span class="vehicle-card-type">Matic</span>
                            <h3 class="vehicle-card-name">Honda Vario 160</h3>
                            <p class="vehicle-card-price">Rp 100.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="assets/images/yamaha_nmax.png" alt="Yamaha NMAX">
                            </div>
                            <span class="vehicle-card-type">Matic</span>
                            <h3 class="vehicle-card-name">Yamaha NMAX</h3>
                            <p class="vehicle-card-price">Rp 150.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="assets/images/honda_pcx.png" alt="Honda PCX">
                            </div>
                            <span class="vehicle-card-type">Matic</span>
                            <h3 class="vehicle-card-name">Honda PCX 160</h3>
                            <p class="vehicle-card-price">Rp 175.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="assets/images/yamaha_r15.png" alt="Yamaha R15">
                            </div>
                            <span class="vehicle-card-type">Sport</span>
                            <h3 class="vehicle-card-name">Yamaha R15</h3>
                            <p class="vehicle-card-price">Rp 200.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="https://www.honda.co.id/uploads/product/gallery/cbr150r/CBR150R-Racing-Red.png"
                                    alt="CBR"
                                    onerror="this.src='https://via.placeholder.com/200x120/1a1a1a/FFD700?text=Honda+CBR'">
                            </div>
                            <span class="vehicle-card-type">Sport</span>
                            <h3 class="vehicle-card-name">Honda CBR 150R</h3>
                            <p class="vehicle-card-price">Rp 200.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="https://www.kawasaki.co.id/images/product/klx150-2023.png" alt="KLX"
                                    onerror="this.src='https://via.placeholder.com/200x120/1a1a1a/FFD700?text=Kawasaki+KLX'">
                            </div>
                            <span class="vehicle-card-type">Supermoto</span>
                            <h3 class="vehicle-card-name">Kawasaki KLX 150</h3>
                            <p class="vehicle-card-price">Rp 250.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                        <div class="vehicle-card">
                            <div class="vehicle-card-image">
                                <img src="https://www.honda.co.id/uploads/product/gallery/crf150l/CRF150L-Extreme-Red.png"
                                    alt="CRF"
                                    onerror="this.src='https://via.placeholder.com/200x120/1a1a1a/FFD700?text=Honda+CRF'">
                            </div>
                            <span class="vehicle-card-type">Supermoto</span>
                            <h3 class="vehicle-card-name">Honda CRF 150L</h3>
                            <p class="vehicle-card-price">Rp 275.000 <span>/hari</span></p>
                            <a href="auth/login.php" class="btn btn-primary btn-sm btn-block">Sewa Sekarang</a>
                        </div>
                    <?php endif; ?>
                </div>

                <button class="carousel-nav carousel-next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-header">
                <div class="about-line"></div>
                <h2 class="about-title">TENTANG MARVELL RENTAL</h2>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <p>
                        <strong>Marvell Rental</strong> adalah layanan rental sepeda motor terpercaya yang berkomitmen
                        memberikan pengalaman berkendara terbaik bagi pelanggan. Dengan armada motor yang terawat
                        dan harga kompetitif, kami siap menemani perjalanan Anda.
                    </p>
                    <p>
                        Kami menyediakan berbagai pilihan motor mulai dari Matic untuk mobilitas harian,
                        Sport untuk sensasi berkendara yang berbeda, hingga Supermoto untuk petualangan off-road.
                    </p>
                    <a href="auth/register.php" class="btn btn-primary" style="background: #C9A100; margin-top: 20px;">
                        <i class="fas fa-arrow-right"></i>
                        Mulai Sewa Sekarang
                    </a>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=500" alt="Rental Motor"
                        style="border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);"
                        onerror="this.src='https://via.placeholder.com/500x300/C9A100/000?text=Marvell+Rental'">
                </div>
            </div>
        </div>
    </section>

    <!-- How to Rent Section -->
    <section id="cara-sewa" class="section" style="background: var(--bg-darker);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">CARA SEWA MOTOR</h2>
                <p class="section-subtitle">4 langkah mudah untuk menyewa motor di Marvell Rental</p>
            </div>

            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3 class="step-title">Daftar Akun</h3>
                    <p class="step-desc">Buat akun dengan mengisi data diri Anda secara lengkap</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3 class="step-title">Pilih Motor</h3>
                    <p class="step-desc">Pilih motor sesuai kebutuhan dan tentukan tanggal sewa</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3 class="step-title">Konfirmasi</h3>
                    <p class="step-desc">Tunggu konfirmasi dari petugas kami via WhatsApp/telepon</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3 class="step-title">Ambil Motor</h3>
                    <p class="step-desc">Datang ke lokasi dengan KTP/SIM untuk mengambil motor</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <div class="footer-logo-icon">MR</div>
                        <span class="footer-logo-text">MarvellRental</span>
                    </div>
                    <p class="footer-desc">
                        Layanan rental sepeda motor terpercaya dengan harga terjangkau dan armada terawat.
                        Siap menemani perjalanan Anda kapanpun dan kemanapun.
                    </p>
                </div>

                <div class="footer-links-wrapper">
                    <h4 class="footer-title">Menu</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#motor">Daftar Motor</a></li>
                        <li><a href="#cara-sewa">Cara Sewa</a></li>
                        <li><a href="auth/login.php">Login</a></li>
                    </ul>
                </div>

                <div class="footer-links-wrapper">
                    <h4 class="footer-title">Layanan</h4>
                    <ul class="footer-links">
                        <li><a href="#">Sewa Harian</a></li>
                        <li><a href="#">Sewa Mingguan</a></li>
                        <li><a href="#">Sewa Bulanan</a></li>
                        <li><a href="#">Antar Jemput</a></li>
                    </ul>
                </div>

                <div class="footer-contact-wrapper">
                    <h4 class="footer-title">Kontak</h4>
                    <ul class="footer-contact">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jl. Contoh No. 123, Kota</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>0812 3456 7890</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>info@marvellrental.com</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Senin - Minggu: 08:00 - 22:00</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy;
                    <?= date('Y') ?> Marvell Rental. All Rights Reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Float Button -->
    <a href="https://wa.me/6281234567890" target="_blank" class="whatsapp-float">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="assets/js/main.js"></script>
</body>

</html>