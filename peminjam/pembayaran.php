<?php
require_once '../auth/cek_login.php';
cekPeminjam();
require_once '../database/koneksi.php';

$message = '';
$error = '';

// Get peminjaman_id from URL
$peminjaman_id = $_GET['id'] ?? 0;

// Get peminjaman data
$stmt = $pdo->prepare("
    SELECT p.*, m.nama_motor, m.jenis, m.plat_nomor, m.harga_per_hari,
           n.nomor_pesanan
    FROM peminjaman p 
    JOIN motor m ON p.motor_id = m.id 
    LEFT JOIN nota n ON p.id = n.peminjaman_id
    WHERE p.id = ? AND p.user_id = ? AND p.status = 'disetujui'
");
$stmt->execute([$peminjaman_id, $_SESSION['user_id']]);
$peminjaman = $stmt->fetch();

if (!$peminjaman) {
    header('Location: riwayat.php');
    exit;
}

// Get bank info
$banks = $pdo->query("SELECT * FROM bank_info WHERE is_active = 1")->fetchAll();

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode'] ?? '';
    $jumlah = $peminjaman['total_harga'];

    // Handle bukti transfer upload
    $bukti = null;
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if (!is_dir('../assets/images/payments')) {
                mkdir('../assets/images/payments', 0777, true);
            }
            $bukti = 'payment_' . $peminjaman_id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['bukti']['tmp_name'], '../assets/images/payments/' . $bukti);
        }
    }

    if (empty($metode)) {
        $error = 'Pilih metode pembayaran!';
    } elseif ($metode != 'tunai' && !$bukti) {
        $error = 'Upload bukti transfer!';
    } else {
        // Insert payment
        $stmt = $pdo->prepare("INSERT INTO pembayaran (peminjaman_id, metode, jumlah, bukti_transfer) VALUES (?, ?, ?, ?)");
        $stmt->execute([$peminjaman_id, $metode, $jumlah, $bukti]);

        // Update peminjaman
        $status_bayar = $metode == 'tunai' ? 'belum_bayar' : 'menunggu_verifikasi';
        $stmt = $pdo->prepare("UPDATE peminjaman SET metode_pembayaran = ?, status_bayar = ?, bukti_bayar = ? WHERE id = ?");
        $stmt->execute([$metode, $status_bayar, $bukti, $peminjaman_id]);

        logAktivitas($pdo, $_SESSION['user_id'], 'Upload Pembayaran', "Upload bukti pembayaran untuk pesanan " . $peminjaman['nomor_pesanan'], 'pembayaran', $peminjaman_id);

        $message = $metode == 'tunai' ? 'Metode pembayaran tunai dipilih. Bayar saat mengambil motor.' : 'Bukti pembayaran berhasil diupload! Menunggu verifikasi.';

        // Refresh data
        $stmt = $pdo->prepare("SELECT p.*, m.nama_motor, m.jenis, m.plat_nomor FROM peminjaman p JOIN motor m ON p.motor_id = m.id WHERE p.id = ?");
        $stmt->execute([$peminjaman_id]);
        $peminjaman = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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
                    <li><a href="motor.php"><i class="fas fa-motorcycle"></i> Sewa Motor</a></li>
                    <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
                    <li><a href="nota.php"><i class="fas fa-receipt"></i> Nota</a></li>
                    <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Pembayaran</h1>
                <a href="riwayat.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detail Pesanan</h3>
                    </div>

                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 10px 0; color: var(--text-secondary);">Motor</td>
                            <td style="padding: 10px 0; color: var(--text-primary); text-align: right;">
                                <?= htmlspecialchars($peminjaman['nama_motor']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: var(--text-secondary);">Plat Nomor</td>
                            <td style="padding: 10px 0; text-align: right;">
                                <?= htmlspecialchars($peminjaman['plat_nomor']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: var(--text-secondary);">Tgl Pinjam</td>
                            <td style="padding: 10px 0; text-align: right;">
                                <?= formatTanggal($peminjaman['tanggal_pinjam']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: var(--text-secondary);">Tgl Kembali</td>
                            <td style="padding: 10px 0; text-align: right;">
                                <?= formatTanggal($peminjaman['tanggal_kembali']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; color: var(--text-secondary);">Durasi</td>
                            <td style="padding: 10px 0; text-align: right;">
                                <?= $peminjaman['total_hari'] ?> hari
                            </td>
                        </tr>
                        <tr style="border-top: var(--border-light);">
                            <td style="padding: 15px 0; font-weight: 600; color: var(--text-primary);">Total Bayar</td>
                            <td
                                style="padding: 15px 0; font-size: 1.3rem; font-weight: 700; color: var(--primary); text-align: right;">
                                <?= formatRupiah($peminjaman['total_harga']) ?>
                            </td>
                        </tr>
                    </table>

                    <?php if ($peminjaman['status_bayar'] == 'menunggu_verifikasi'): ?>
                        <div
                            style="background: rgba(255, 152, 0, 0.1); border: 1px solid var(--accent-orange); padding: 15px; border-radius: 10px; margin-top: 20px;">
                            <p style="color: var(--accent-orange);"><i class="fas fa-clock"></i> Pembayaran sedang
                                diverifikasi</p>
                        </div>
                    <?php elseif ($peminjaman['status_bayar'] == 'lunas'): ?>
                        <div
                            style="background: rgba(0, 200, 83, 0.1); border: 1px solid var(--accent-green); padding: 15px; border-radius: 10px; margin-top: 20px;">
                            <p style="color: var(--accent-green);"><i class="fas fa-check-circle"></i> Pembayaran sudah
                                lunas</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Payment Methods -->
                <?php if ($peminjaman['status_bayar'] == 'belum_bayar'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pilih Metode Pembayaran</h3>
                        </div>

                        <form method="POST" enctype="multipart/form-data">
                            <!-- QRIS -->
                            <div style="margin-bottom: 20px;">
                                <label
                                    style="display: flex; align-items: center; gap: 15px; padding: 15px; background: var(--bg-light); border-radius: 10px; cursor: pointer; border: 2px solid transparent;"
                                    class="payment-option" onclick="selectPayment(this, 'qris')">
                                    <input type="radio" name="metode" value="qris" style="display: none;">
                                    <div
                                        style="width: 50px; height: 50px; background: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <img src="https://seeklogo.com/images/Q/quick-response-code-indonesia-standard-qris-logo-F300D5EB32-seeklogo.com.png"
                                            alt="QRIS" style="height: 35px;"
                                            onerror="this.parentElement.innerHTML='<i class=\'fas fa-qrcode\' style=\'color: #000; font-size: 20px;\'></i>'">
                                    </div>
                                    <div>
                                        <p style="font-weight: 600; color: var(--text-primary);">QRIS</p>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary);">Scan menggunakan
                                            e-wallet atau m-banking</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Bank Transfer -->
                            <?php foreach ($banks as $bank): ?>
                                <div style="margin-bottom: 15px;">
                                    <label
                                        style="display: flex; align-items: center; gap: 15px; padding: 15px; background: var(--bg-light); border-radius: 10px; cursor: pointer; border: 2px solid transparent;"
                                        class="payment-option"
                                        onclick="selectPayment(this, 'bank_<?= strtolower($bank['nama_bank']) ?>')">
                                        <input type="radio" name="metode" value="bank_<?= strtolower($bank['nama_bank']) ?>"
                                            style="display: none;">
                                        <div
                                            style="width: 50px; height: 50px; background: #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                            <span style="font-weight: 700; color: #000; font-size: 12px;">
                                                <?= $bank['nama_bank'] ?>
                                            </span>
                                        </div>
                                        <div style="flex: 1;">
                                            <p style="font-weight: 600; color: var(--text-primary);">Bank
                                                <?= $bank['nama_bank'] ?>
                                            </p>
                                            <p style="font-size: 0.85rem; color: var(--text-secondary);">
                                                <?= $bank['nomor_rekening'] ?> a.n.
                                                <?= $bank['atas_nama'] ?>
                                            </p>
                                        </div>
                                        <button type="button" onclick="copyToClipboard('<?= $bank['nomor_rekening'] ?>')"
                                            class="btn btn-sm btn-secondary" style="padding: 5px 10px;">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <!-- Cash -->
                            <div style="margin-bottom: 20px;">
                                <label
                                    style="display: flex; align-items: center; gap: 15px; padding: 15px; background: var(--bg-light); border-radius: 10px; cursor: pointer; border: 2px solid transparent;"
                                    class="payment-option" onclick="selectPayment(this, 'tunai')">
                                    <input type="radio" name="metode" value="tunai" style="display: none;">
                                    <div
                                        style="width: 50px; height: 50px; background: var(--bg-gold); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-money-bill-wave"
                                            style="font-size: 20px; color: var(--text-dark);"></i>
                                    </div>
                                    <div>
                                        <p style="font-weight: 600; color: var(--text-primary);">Bayar Tunai</p>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary);">Bayar saat mengambil
                                            motor</p>
                                    </div>
                                </label>
                            </div>

                            <!-- Upload Bukti -->
                            <div id="uploadSection" style="display: none; margin-bottom: 20px;">
                                <div class="form-group">
                                    <label class="form-label">Upload Bukti Transfer</label>
                                    <input type="file" name="bukti" class="form-control" accept="image/*">
                                    <small style="color: var(--text-secondary);">Format: JPG, PNG, WEBP (Max 2MB)</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-check"></i> Konfirmasi Pembayaran
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Status Pembayaran</h3>
                        </div>

                        <div style="text-align: center; padding: 30px;">
                            <?php if ($peminjaman['status_bayar'] == 'menunggu_verifikasi'): ?>
                                <i class="fas fa-clock"
                                    style="font-size: 4rem; color: var(--accent-orange); margin-bottom: 20px;"></i>
                                <h3 style="margin-bottom: 10px;">Menunggu Verifikasi</h3>
                                <p style="color: var(--text-secondary);">Pembayaran Anda sedang diverifikasi oleh petugas.</p>
                            <?php elseif ($peminjaman['status_bayar'] == 'lunas'): ?>
                                <i class="fas fa-check-circle"
                                    style="font-size: 4rem; color: var(--accent-green); margin-bottom: 20px;"></i>
                                <h3 style="margin-bottom: 10px;">Pembayaran Lunas</h3>
                                <p style="color: var(--text-secondary);">Silakan ambil motor di lokasi rental.</p>
                            <?php endif; ?>

                            <?php if ($peminjaman['bukti_bayar']): ?>
                                <div style="margin-top: 20px;">
                                    <p style="color: var(--text-secondary); margin-bottom: 10px;">Bukti Transfer:</p>
                                    <img src="../assets/images/payments/<?= htmlspecialchars($peminjaman['bukti_bayar']) ?>"
                                        alt="Bukti" style="max-width: 200px; border-radius: 10px; border: var(--border-light);">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>

    <script src="../assets/js/main.js"></script>
    <script>
        function selectPayment(element, method) {
            // Remove active from all
            document.querySelectorAll('.payment-option').forEach(el => {
                el.style.borderColor = 'transparent';
            });
            // Set active
            element.style.borderColor = 'var(--primary)';
            element.querySelector('input').checked = true;

            // Show/hide upload section
            const uploadSection = document.getElementById('uploadSection');
            if (method !== 'tunai') {
                uploadSection.style.display = 'block';
            } else {
                uploadSection.style.display = 'none';
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            alert('Nomor rekening disalin!');
        }
    </script>
</body>

</html>