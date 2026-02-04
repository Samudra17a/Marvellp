<?php
require_once '../auth/cek_login.php';
cekPetugas();
require_once '../database/koneksi.php';

// Filter
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-t');

// Get report
$stmt = $pdo->prepare("
    SELECT p.*, u.nama as nama_peminjam, m.nama_motor,
           pg.denda, n.acc_by, pt.nama as nama_petugas
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN motor m ON p.motor_id = m.id 
    LEFT JOIN pengembalian pg ON p.id = pg.peminjaman_id
    LEFT JOIN nota n ON p.id = n.peminjaman_id
    LEFT JOIN users pt ON n.acc_by = pt.id
    WHERE p.status = 'selesai' AND p.tanggal_pinjam BETWEEN ? AND ?
    ORDER BY p.tanggal_pinjam DESC
");
$stmt->execute([$startDate, $endDate]);
$laporan = $stmt->fetchAll();

$total = 0;
$totalDenda = 0;
foreach ($laporan as $l) {
    $total += $l['total_harga'];
    $totalDenda += $l['denda'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Petugas Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {

            .sidebar,
            .dashboard-header,
            .no-print {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 20px !important;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-logo">MR</div>
                <span class="sidebar-title">Marvell Rental</span>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Menu Utama</p>
                <ul class="sidebar-nav">
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="peminjaman.php"><i class="fas fa-clipboard-list"></i> Pengajuan Peminjaman</a></li>
                    <li><a href="pengembalian.php"><i class="fas fa-undo"></i> Pengembalian</a></li>
                    <li><a href="laporan.php" class="active"><i class="fas fa-file-alt"></i> Laporan</a></li>
                </ul>
            </div>

            <div class="sidebar-menu">
                <p class="sidebar-menu-title">Akun</p>
                <ul class="sidebar-nav">
                    <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </div>
        </aside>

        <main class="main-content">
            <div class="dashboard-header no-print">
                <h1 class="dashboard-title">Laporan Transaksi</h1>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>

            <div class="card mb-3 no-print">
                <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Dari</label>
                        <input type="date" name="start" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Sampai</label>
                        <input type="date" name="end" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
                </form>
            </div>

            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 25px;">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <p class="stat-value">
                        <?= count($laporan) ?>
                    </p>
                    <p class="stat-label">Total Transaksi</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <p class="stat-value" style="font-size: 1.2rem;">
                        <?= formatRupiah($total) ?>
                    </p>
                    <p class="stat-label">Total Pendapatan</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-red);"><i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <p class="stat-value" style="font-size: 1.2rem;">
                        <?= formatRupiah($totalDenda) ?>
                    </p>
                    <p class="stat-label">Total Denda</p>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Motor</th>
                            <th>Tgl Pinjam</th>
                            <th>Total</th>
                            <th>Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan as $i => $l): ?>
                            <tr>
                                <td style="color: var(--text-primary);">
                                    <?= $i + 1 ?>
                                </td>
                                <td style="color: var(--text-primary);">
                                    <?= htmlspecialchars($l['nama_peminjam']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($l['nama_motor']) ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($l['tanggal_pinjam'])) ?>
                                </td>
                                <td style="color: var(--primary);">
                                    <?= formatRupiah($l['total_harga']) ?>
                                </td>
                                <td style="color: var(--accent-red);">
                                    <?= formatRupiah($l['denda'] ?? 0) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(255, 215, 0, 0.1);">
                            <td colspan="4" style="text-align: right; font-weight: 600; color: var(--text-primary);">
                                TOTAL</td>
                            <td style="color: var(--primary); font-weight: 700;">
                                <?= formatRupiah($total) ?>
                            </td>
                            <td style="color: var(--accent-red); font-weight: 700;">
                                <?= formatRupiah($totalDenda) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn no-print"><i class="fas fa-bars"></i></button>
    <script src="../assets/js/main.js"></script>
</body>

</html>