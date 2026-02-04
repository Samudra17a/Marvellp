<?php
require_once '../auth/cek_login.php';
cekAdmin();
require_once '../database/koneksi.php';

// Filter by date
$startDate = $_GET['start'] ?? '';
$endDate = $_GET['end'] ?? '';

// Build query with optional date filter
$sql = "
    SELECT p.*, u.nama as nama_peminjam, u.no_hp, m.nama_motor, m.jenis, m.plat_nomor,
           n.nomor_pesanan, n.acc_by,
           pt.nama as nama_petugas
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN motor m ON p.motor_id = m.id 
    LEFT JOIN nota n ON p.id = n.peminjaman_id
    LEFT JOIN users pt ON n.acc_by = pt.id
";

$params = [];
if ($startDate && $endDate) {
    $sql .= " WHERE p.tanggal_pinjam BETWEEN ? AND ?";
    $params = [$startDate, $endDate];
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi - Admin Marvell Rental</title>
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="motor.php"><i class="fas fa-motorcycle"></i> Data Motor</a></li>
                    <li><a href="petugas.php"><i class="fas fa-user-tie"></i> Data Petugas</a></li>
                    <li><a href="peminjam.php"><i class="fas fa-users"></i> Data Peminjam</a></li>
                </ul>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Transaksi</p>
                <ul class="sidebar-nav">
                    <li><a href="transaksi.php" class="active"><i class="fas fa-exchange-alt"></i> Semua Transaksi</a>
                    </li>
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
                <h1 class="dashboard-title">Semua Transaksi</h1>
            </div>

            <!-- Filter -->
            <div class="card mb-3">
                <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start" class="form-control" value="<?= $startDate ?>" readonly onclick="this.showPicker()" style="cursor: pointer;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end" class="form-control" value="<?= $endDate ?>" readonly onclick="this.showPicker()" style="cursor: pointer;">
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <?php if ($startDate && $endDate): ?>
                        <a href="transaksi.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Pesanan</th>
                            <th>Peminjam</th>
                            <th>Motor</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>ACC By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transaksi) > 0): ?>
                            <?php foreach ($transaksi as $t): ?>
                                <tr>
                                    <td style="color: var(--primary);">
                                        <?= htmlspecialchars($t['nomor_pesanan'] ?: '-') ?>
                                    </td>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($t['nama_peminjam']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($t['nama_motor']) ?> <br><small
                                            style="color: var(--text-secondary);">
                                            <?= $t['plat_nomor'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($t['tanggal_pinjam'])) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($t['tanggal_kembali'])) ?>
                                    </td>
                                    <td style="color: var(--primary);">
                                        <?= formatRupiah($t['total_harga']) ?>
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
                                    <td>
                                        <?= htmlspecialchars($t['nama_petugas'] ?: '-') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Belum ada transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="../assets/js/main.js"></script>
</body>

</html>