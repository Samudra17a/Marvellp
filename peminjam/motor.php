<?php
require_once '../auth/cek_login.php';
cekPeminjam();
require_once '../database/koneksi.php';

$message = '';
$error = '';

// Get category filter
$category = $_GET['jenis'] ?? '';

// Handle sewa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motor_id = $_POST['motor_id'] ?? 0;
    $tanggal_pinjam = $_POST['tanggal_pinjam'] ?? '';
    $tanggal_kembali = $_POST['tanggal_kembali'] ?? '';

    // Validate
    if (empty($motor_id) || empty($tanggal_pinjam) || empty($tanggal_kembali)) {
        $error = 'Semua field harus diisi!';
    } elseif (strtotime($tanggal_kembali) < strtotime($tanggal_pinjam)) {
        $error = 'Tanggal kembali tidak valid!';
    } elseif (strtotime($tanggal_kembali) > strtotime($tanggal_pinjam . ' + 30 days')) {
        $error = 'Maksimal durasi sewa adalah 30 hari (1 bulan)!';
    } else {
        // Get motor data
        $stmt = $pdo->prepare("SELECT * FROM motor WHERE id = ? AND status = 'tersedia'");
        $stmt->execute([$motor_id]);
        $motor = $stmt->fetch();

        if (!$motor) {
            $error = 'Motor tidak tersedia!';
        } else {
            // Calculate
            $tglPinjam = new DateTime($tanggal_pinjam);
            $tglKembali = new DateTime($tanggal_kembali);
            $totalHari = max(1, $tglKembali->diff($tglPinjam)->days);
            $totalHarga = $motor['harga_per_hari'] * $totalHari;

            // Insert peminjaman
            $stmt = $pdo->prepare("INSERT INTO peminjaman (user_id, motor_id, tanggal_pinjam, tanggal_kembali, total_hari, total_harga, status) VALUES (?, ?, ?, ?, ?, ?, 'menunggu')");
            if ($stmt->execute([$_SESSION['user_id'], $motor_id, $tanggal_pinjam, $tanggal_kembali, $totalHari, $totalHarga])) {
                $peminjaman_id = $pdo->lastInsertId();
                logAktivitas($pdo, $_SESSION['user_id'], 'Ajukan Sewa', "Mengajukan sewa motor: " . $motor['nama_motor'], 'peminjaman', $peminjaman_id);
                // Redirect ke halaman pembayaran
                header("Location: pembayaran_sewa.php?id=" . $peminjaman_id);
                exit;
            } else {
                $error = 'Gagal mengajukan sewa!';
            }
        }
    }
}

// Build query for motors
$sql = "SELECT * FROM motor WHERE status = 'tersedia'";
$params = [];

if ($category && in_array($category, ['Matic', 'Sport', 'Supermoto'])) {
    $sql .= " AND jenis = ?";
    $params[] = $category;
}

$sql .= " ORDER BY nama_motor";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$motors = $stmt->fetchAll();

// Count by category
$countMatic = $pdo->query("SELECT COUNT(*) FROM motor WHERE status = 'tersedia' AND jenis = 'Matic'")->fetchColumn();
$countSport = $pdo->query("SELECT COUNT(*) FROM motor WHERE status = 'tersedia' AND jenis = 'Sport'")->fetchColumn();
$countSupermoto = $pdo->query("SELECT COUNT(*) FROM motor WHERE status = 'tersedia' AND jenis = 'Supermoto'")->fetchColumn();
$countAll = $countMatic + $countSport + $countSupermoto;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Motor - Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr Date Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .flatpickr-calendar {
            background: #1a1a1a !important;
            border: 1px solid rgba(255, 215, 0, 0.3) !important;
        }

        .flatpickr-day {
            color: #fff !important;
        }

        .flatpickr-day.selected {
            background: #FFD700 !important;
            border-color: #FFD700 !important;
            color: #000 !important;
        }

        .flatpickr-day:hover {
            background: rgba(255, 215, 0, 0.2) !important;
        }

        .flatpickr-day.disabled {
            color: #555 !important;
        }

        .category-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .category-tab {
            padding: 12px 25px;
            background: var(--bg-card);
            border: var(--border-light);
            border-radius: 50px;
            color: var(--text-secondary);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-tab:hover,
        .category-tab.active {
            background: var(--bg-gold);
            color: var(--text-dark);
            border-color: var(--primary);
        }

        .category-tab .count {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
        }

        .stock-available {
            background: rgba(40, 167, 69, 0.9);
            color: #fff;
        }

        .stock-empty {
            background: rgba(220, 53, 69, 0.9);
            color: #fff;
        }

        .vehicle-card-image {
            position: relative;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed !important;
        }

        .category-tab.active .count {
            background: rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-logo">MR</div>
                <span class="sidebar-title">Marvell Rental</span>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Menu</p>
                <ul class="sidebar-nav">
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="motor.php" class="active"><i class="fas fa-motorcycle"></i> Sewa Motor</a></li>
                    <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
                    <li><a href="nota.php"><i class="fas fa-receipt"></i> Nota</a></li>
                    <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Pilih Motor</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Category Tabs -->
            <div class="category-tabs">
                <a href="motor.php" class="category-tab <?= empty($category) ? 'active' : '' ?>">
                    <i class="fas fa-motorcycle"></i> Semua
                    <span class="count"><?= $countAll ?></span>
                </a>
                <a href="motor.php?jenis=Matic" class="category-tab <?= $category == 'Matic' ? 'active' : '' ?>">
                    <i class="fas fa-bolt"></i> Matic
                    <span class="count"><?= $countMatic ?></span>
                </a>
                <a href="motor.php?jenis=Sport" class="category-tab <?= $category == 'Sport' ? 'active' : '' ?>">
                    <i class="fas fa-flag-checkered"></i> Sport
                    <span class="count"><?= $countSport ?></span>
                </a>
                <a href="motor.php?jenis=Supermoto"
                    class="category-tab <?= $category == 'Supermoto' ? 'active' : '' ?>">
                    <i class="fas fa-mountain"></i> Supermoto
                    <span class="count"><?= $countSupermoto ?></span>
                </a>
            </div>

            <!-- Motor Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px;">
                <?php if (count($motors) > 0): ?>
                    <?php foreach ($motors as $motor): ?>
                        <?php $stok = $motor['stok'] ?? 1; ?>
                        <div class="vehicle-card" style="flex: none;">
                            <div class="vehicle-card-image">
                                <?php if ($stok > 0): ?>
                                    <span class="stock-badge stock-available"><i class="fas fa-check-circle"></i> Stok: <?= $stok ?></span>
                                <?php else: ?>
                                    <span class="stock-badge stock-empty"><i class="fas fa-times-circle"></i> Habis</span>
                                <?php endif; ?>
                                <?php if ($motor['gambar'] && file_exists('../assets/images/' . $motor['gambar'])): ?>
                                    <img src="../assets/images/<?= htmlspecialchars($motor['gambar']) ?>"
                                        alt="<?= htmlspecialchars($motor['nama_motor']) ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/200x120/1a1a1a/FFD700?text=<?= urlencode($motor['nama_motor']) ?>"
                                        alt="<?= htmlspecialchars($motor['nama_motor']) ?>">
                                <?php endif; ?>
                            </div>
                            <span class="vehicle-card-type"><?= htmlspecialchars($motor['jenis']) ?></span>
                            <h3 class="vehicle-card-name"><?= htmlspecialchars($motor['nama_motor']) ?></h3>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 10px;">
                                <?= htmlspecialchars($motor['merk']) ?> | <?= $motor['tahun'] ?>
                            </p>
                            <p class="vehicle-card-price">
                                <?= formatRupiah($motor['harga_per_hari']) ?>
                                <span>/hari</span>
                            </p>
                            <?php if ($stok > 0): ?>
                                <button class="btn btn-primary btn-block"
                                    onclick="openSewa(<?= htmlspecialchars(json_encode($motor)) ?>)">
                                    <i class="fas fa-motorcycle"></i> Sewa Sekarang
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-block btn-disabled"
                                    onclick="showNotAvailable('<?= htmlspecialchars($motor['nama_motor']) ?>')">
                                    <i class="fas fa-ban"></i> Tidak Tersedia
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="card" style="grid-column: 1/-1; text-align: center; padding: 50px;">
                        <i class="fas fa-motorcycle"
                            style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 15px;"></i>
                        <p style="color: var(--text-secondary);">Tidak ada motor
                            <?= $category ? 'kategori ' . $category : '' ?> yang tersedia saat ini
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Sewa Modal -->
    <div class="modal-overlay" id="sewaModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Form Penyewaan</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="motor_id" id="sewa_motor_id">

                <div id="motorInfo"
                    style="background: var(--bg-light); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <!-- Filled by JS -->
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Pinjam</label>
                    <input type="text" name="tanggal_pinjam" id="tanggal_pinjam" class="form-control datepicker"
                        placeholder="Pilih tanggal" required readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Kembali</label>
                    <input type="text" name="tanggal_kembali" id="tanggal_kembali" class="form-control datepicker"
                        placeholder="Pilih tanggal" required readonly>
                </div>

                <div
                    style="background: rgba(255, 215, 0, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--text-secondary);">Harga per hari</span>
                        <span id="display_harga" style="color: var(--primary);">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: var(--text-secondary);">Durasi</span>
                        <span id="total_hari">0 hari</span>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; font-weight: 600; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                        <span>Total</span>
                        <span id="total_harga" style="color: var(--primary); font-size: 1.2rem;">Rp 0</span>
                    </div>
                </div>
                <input type="hidden" id="harga_per_hari" value="0">

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Ajukan Sewa
                </button>
            </form>
        </div>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Initialize Flatpickr date pickers
        const today = new Date();

        const fpPinjam = flatpickr("#tanggal_pinjam", {
            locale: "id",
            dateFormat: "Y-m-d",
            minDate: "today",
            altInput: true,
            altFormat: "j F Y",
            onChange: function (selectedDates, dateStr) {
                // Set min date for return date
                fpKembali.set('minDate', dateStr);
                // Set max date to 30 days from start date
                const maxDate = new Date(selectedDates[0]);
                maxDate.setDate(maxDate.getDate() + 30);
                fpKembali.set('maxDate', maxDate);
                updateTotal();
            }
        });

        const fpKembali = flatpickr("#tanggal_kembali", {
            locale: "id",
            dateFormat: "Y-m-d",
            minDate: "today",
            altInput: true,
            altFormat: "j F Y",
            onChange: function () {
                updateTotal();
            }
        });

        function showNotAvailable(motorName) {
            Swal.fire({
                icon: 'warning',
                title: 'Motor Tidak Tersedia',
                html: `<p>Maaf, <strong>${motorName}</strong> sedang tidak tersedia.</p><p>Stok motor habis, silakan pilih motor lain atau coba lagi nanti.</p>`,
                confirmButtonText: 'OK',
                confirmButtonColor: '#FFD700',
                background: '#1a1a1a',
                color: '#fff'
            });
        }

        function openSewa(motor) {
            // Check stock before opening modal
            if ((motor.stok ?? 1) <= 0) {
                showNotAvailable(motor.nama_motor);
                return;
            }

            document.getElementById('sewa_motor_id').value = motor.id;
            document.getElementById('harga_per_hari').value = motor.harga_per_hari;
            document.getElementById('display_harga').textContent = formatRupiah(motor.harga_per_hari);

            document.getElementById('motorInfo').innerHTML = `
                <p style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">${motor.nama_motor}</p>
                <p style="color: var(--text-secondary);">${motor.merk} | ${motor.jenis} | ${motor.plat_nomor}</p>
                <p style="color: var(--accent-green); font-size: 0.85rem; margin-top: 5px;"><i class="fas fa-check-circle"></i> Stok tersedia: ${motor.stok ?? 1} unit</p>
            `;

            // Reset dates
            fpPinjam.clear();
            fpKembali.clear();
            document.getElementById('total_hari').textContent = '0 hari';
            document.getElementById('total_harga').textContent = 'Rp 0';

            document.getElementById('sewaModal').classList.add('active');
        }

        function updateTotal() {
            const start = document.getElementById('tanggal_pinjam').value;
            const end = document.getElementById('tanggal_kembali').value;
            const harga = parseInt(document.getElementById('harga_per_hari').value) || 0;

            if (start && end) {
                const startDate = new Date(start);
                const endDate = new Date(end);
                const diffTime = endDate - startDate;
                const days = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
                const total = harga * days;

                document.getElementById('total_hari').textContent = days + ' hari';
                document.getElementById('total_harga').textContent = formatRupiah(total);
            }
        }

        function formatRupiah(num) {
            return 'Rp ' + parseInt(num).toLocaleString('id-ID');
        }
    </script>
</body>

</html>