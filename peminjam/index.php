<?php
require_once '../auth/cek_login.php';
cekPeminjam();
require_once '../database/koneksi.php';

// Stats
$totalPeminjaman = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE user_id = ?");
$totalPeminjaman->execute([$_SESSION['user_id']]);
$total = $totalPeminjaman->fetchColumn();

$menunggu = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE user_id = ? AND status = 'menunggu'");
$menunggu->execute([$_SESSION['user_id']]);
$menungguCount = $menunggu->fetchColumn();

$aktif = $pdo->prepare("SELECT COUNT(*) FROM peminjaman WHERE user_id = ? AND status = 'disetujui'");
$aktif->execute([$_SESSION['user_id']]);
$aktifCount = $aktif->fetchColumn();

// Latest rental
$stmt = $pdo->prepare("
    SELECT p.*, m.nama_motor, m.jenis, n.nomor_pesanan, pt.nama as nama_petugas
    FROM peminjaman p 
    JOIN motor m ON p.motor_id = m.id 
    LEFT JOIN nota n ON p.id = n.peminjaman_id
    LEFT JOIN users pt ON n.acc_by = pt.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$riwayat = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Marvell Rental</title>
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
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
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
                <h1 class="dashboard-title">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</h1>
                <div class="dashboard-user">
                    <div class="dashboard-user-info">
                        <p class="dashboard-user-name">
                            <?= htmlspecialchars($_SESSION['nama']) ?>
                        </p>
                        <p class="dashboard-user-role">Member</p>
                    </div>
                    <div class="dashboard-user-avatar">
                        <?php if (!empty($_SESSION['foto']) && file_exists('../assets/images/profiles/' . $_SESSION['foto'])): ?>
                            <img src="../assets/images/profiles/<?= htmlspecialchars($_SESSION['foto']) ?>" alt="Avatar"
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Action -->
            <div class="card mb-3" style="background: var(--bg-gold); color: var(--text-dark);">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h3 style="font-size: 1.3rem; margin-bottom: 5px;">Mau Sewa Motor?</h3>
                        <p>Pilih motor favorit Anda sekarang!</p>
                    </div>
                    <a href="motor.php" class="btn" style="background: var(--text-dark); color: var(--primary);">
                        <i class="fas fa-motorcycle"></i> Lihat Motor
                    </a>
                </div>
            </div>

            <!-- Recent History (moved up) -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Riwayat Terbaru</h3>
                    <a href="riwayat.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Motor</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($riwayat) > 0): ?>
                            <?php foreach ($riwayat as $r): ?>
                                <tr>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($r['nama_motor']) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($r['tanggal_pinjam'])) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($r['tanggal_kembali'])) ?>
                                    </td>
                                    <td style="color: var(--primary);">
                                        <?= formatRupiah($r['total_harga']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($r['status']) {
                                            'menunggu' => 'badge-warning',
                                            'disetujui' => 'badge-info',
                                            'ditolak' => 'badge-danger',
                                            'selesai' => 'badge-success',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($r['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Belum ada riwayat peminjaman</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick Stats Buttons (moved below riwayat) -->
            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 20px;">
                <a href="riwayat.php" class="btn btn-sm btn-secondary" style="position: relative; display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-size: 0.9rem; text-decoration: none; overflow: visible;">
                    <i class="fas fa-receipt"></i>
                    Total Peminjaman
                    <span style="position: absolute; top: -10px; right: -10px; display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; border-radius: 50%; background: var(--primary); color: #fff; font-size: 0.75rem; font-weight: 700; padding: 0 5px; box-shadow: 0 2px 6px rgba(191,49,49,0.4);"><?= $total ?></span>
                </a>
                <a href="riwayat.php" class="btn btn-sm btn-secondary" style="position: relative; display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-size: 0.9rem; text-decoration: none; overflow: visible;">
                    <i class="fas fa-clock" style="color: var(--accent-orange);"></i>
                    Menunggu ACC
                    <span style="position: absolute; top: -10px; right: -10px; display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; border-radius: 50%; background: var(--accent-orange); color: #fff; font-size: 0.75rem; font-weight: 700; padding: 0 5px; box-shadow: 0 2px 6px rgba(230,81,0,0.4);"><?= $menungguCount ?></span>
                </a>
                <a href="riwayat.php" class="btn btn-sm btn-secondary" style="position: relative; display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-size: 0.9rem; text-decoration: none; overflow: visible;">
                    <i class="fas fa-motorcycle" style="color: var(--accent-green);"></i>
                    Sedang Disewa
                    <span style="position: absolute; top: -10px; right: -10px; display: inline-flex; align-items: center; justify-content: center; min-width: 26px; height: 26px; border-radius: 50%; background: var(--accent-green); color: #fff; font-size: 0.75rem; font-weight: 700; padding: 0 5px; box-shadow: 0 2px 6px rgba(46,125,50,0.4);"><?= $aktifCount ?></span>
                </a>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="../assets/js/main.js"></script>
</body>

</html>