<?php
require_once '../auth/cek_login.php';
cekAdmin();
require_once '../database/koneksi.php';

// Ambil statistik
$totalMotor = $pdo->query("SELECT COUNT(*) FROM motor")->fetchColumn();
$motorTersedia = $pdo->query("SELECT COUNT(*) FROM motor WHERE status = 'tersedia'")->fetchColumn();
$totalTransaksi = $pdo->query("SELECT COUNT(*) FROM peminjaman")->fetchColumn();
$transaksiAktif = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'disetujui'")->fetchColumn();
$totalPendapatan = $pdo->query("SELECT COALESCE(SUM(total_harga), 0) FROM peminjaman WHERE status = 'selesai'")->fetchColumn();
$totalPetugas = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'petugas'")->fetchColumn();
$totalPeminjam = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'peminjam'")->fetchColumn();

// Transaksi terbaru
$stmt = $pdo->query("
    SELECT p.*, u.nama as nama_peminjam, m.nama_motor 
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN motor m ON p.motor_id = m.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$transaksiTerbaru = $stmt->fetchAll();

// Log aktivitas terbaru
$stmt = $pdo->query("
    SELECT l.*, u.nama, u.role 
    FROM log_aktivitas l 
    LEFT JOIN users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC 
    LIMIT 10
");
$logAktivitas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Marvell Rental</title>
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
                <span class="sidebar-title">Admin Panel</span>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Menu Utama</p>
                <ul class="sidebar-nav">
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="motor.php"><i class="fas fa-motorcycle"></i> Data Motor</a></li>
                    <li><a href="petugas.php"><i class="fas fa-user-tie"></i> Data Petugas</a></li>
                    <li><a href="peminjam.php"><i class="fas fa-users"></i> Data Peminjam</a></li>
                </ul>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Transaksi</p>
                <ul class="sidebar-nav">
                    <li><a href="transaksi.php"><i class="fas fa-exchange-alt"></i> Semua Transaksi</a></li>
                    <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
                </ul>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Akun</p>
                <ul class="sidebar-nav">
                    <li><a href="profil.php"><i class="fas fa-user-cog"></i> Profil</a></li>
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard Admin</h1>
                <div class="dashboard-user">
                    <div class="dashboard-user-info">
                        <p class="dashboard-user-name">
                            <?= htmlspecialchars($_SESSION['nama']) ?>
                        </p>
                        <p class="dashboard-user-role">Administrator</p>
                    </div>
                    <div class="dashboard-user-avatar">
                        <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-motorcycle"></i></div>
                    <p class="stat-value">
                        <?= $totalMotor ?>
                    </p>
                    <p class="stat-label">Total Motor</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-green);"><i class="fas fa-check-circle"></i></div>
                    <p class="stat-value">
                        <?= $motorTersedia ?>
                    </p>
                    <p class="stat-label">Motor Tersedia</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-orange);"><i class="fas fa-exchange-alt"></i>
                    </div>
                    <p class="stat-value">
                        <?= $totalTransaksi ?>
                    </p>
                    <p class="stat-label">Total Transaksi</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <p class="stat-value" style="font-size: 1.3rem;">
                        <?= formatRupiah($totalPendapatan) ?>
                    </p>
                    <p class="stat-label">Total Pendapatan</p>
                </div>
            </div>

            <!-- Quick Stats Row 2 -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                    <p class="stat-value">
                        <?= $totalPetugas ?>
                    </p>
                    <p class="stat-label">Total Petugas</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <p class="stat-value">
                        <?= $totalPeminjam ?>
                    </p>
                    <p class="stat-label">Total Peminjam</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-green);"><i class="fas fa-spinner"></i></div>
                    <p class="stat-value">
                        <?= $transaksiAktif ?>
                    </p>
                    <p class="stat-label">Transaksi Aktif</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
                <!-- Transaksi Terbaru -->
                <div class="table-container">
                    <div class="table-header">
                        <h3 class="table-title">Transaksi Terbaru</h3>
                        <a href="transaksi.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Motor</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($transaksiTerbaru) > 0): ?>
                                <?php foreach ($transaksiTerbaru as $t): ?>
                                    <tr>
                                        <td style="color: var(--text-primary);">
                                            <?= htmlspecialchars($t['nama_peminjam']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($t['nama_motor']) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($t['tanggal_pinjam'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($t['status']) {
                                                'menunggu' => 'badge-warning',
                                                'disetujui' => 'badge-info',
                                                'ditolak' => 'badge-danger',
                                                'selesai' => 'badge-success',
                                                default => 'badge-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= ucfirst($t['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada transaksi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Log Aktivitas -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Log Aktivitas</h3>
                    </div>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php if (count($logAktivitas) > 0): ?>
                            <?php foreach ($logAktivitas as $log): ?>
                                <div style="padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                    <p style="font-size: 0.9rem; margin-bottom: 5px;">
                                        <strong style="color: var(--primary);">
                                            <?= htmlspecialchars($log['nama'] ?? 'System') ?>
                                        </strong>
                                        <span class="badge badge-info" style="font-size: 0.7rem; margin-left: 5px;">
                                            <?= ucfirst($log['role'] ?? '-') ?>
                                        </span>
                                    </p>
                                    <p style="font-size: 0.85rem; color: var(--text-secondary);">
                                        <?= htmlspecialchars($log['aksi']) ?>
                                    </p>
                                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 5px;">
                                        <i class="fas fa-clock"></i>
                                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted" style="padding: 20px;">Belum ada aktivitas</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn no-print">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/main.js"></script>
</body>

</html>