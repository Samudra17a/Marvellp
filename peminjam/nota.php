<?php
require_once '../auth/cek_login.php';
cekPeminjam();
require_once '../database/koneksi.php';

// Get specific nota or latest
$notaId = $_GET['id'] ?? null;

if ($notaId) {
    $stmt = $pdo->prepare("
        SELECT p.*, u.nama as nama_peminjam, u.no_hp, u.alamat,
               m.nama_motor, m.jenis, m.plat_nomor, m.harga_per_hari,
               n.nomor_pesanan, n.tanggal_acc, pt.nama as nama_petugas,
               pg.denda, pg.keterangan_denda
        FROM peminjaman p 
        JOIN users u ON p.user_id = u.id 
        JOIN motor m ON p.motor_id = m.id 
        LEFT JOIN nota n ON p.id = n.peminjaman_id
        LEFT JOIN users pt ON n.acc_by = pt.id
        LEFT JOIN pengembalian pg ON p.id = pg.peminjaman_id
        WHERE p.id = ? AND p.user_id = ? AND p.status IN ('disetujui', 'selesai')
    ");
    $stmt->execute([$notaId, $_SESSION['user_id']]);
    $nota = $stmt->fetch();
} else {
    // Get all approved rentals with nota
    $stmt = $pdo->prepare("
        SELECT p.*, m.nama_motor, n.nomor_pesanan
        FROM peminjaman p 
        JOIN motor m ON p.motor_id = m.id 
        LEFT JOIN nota n ON p.id = n.peminjaman_id
        WHERE p.user_id = ? AND p.status IN ('disetujui', 'selesai') AND n.id IS NOT NULL
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notas = $stmt->fetchAll();
    $nota = null;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Peminjaman - Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        @media print {
            body {
                background: #fff !important;
            }

            .sidebar,
            .dashboard-header,
            .no-print,
            .mobile-menu-btn {
                display: none !important;
            }

            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .nota {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
            }
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
                    <li><a href="motor.php"><i class="fas fa-motorcycle"></i> Sewa Motor</a></li>
                    <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
                    <li><a href="nota.php" class="active"><i class="fas fa-receipt"></i> Nota</a></li>
                    <li><a href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($nota): ?>
                <!-- Show specific nota -->
                <div class="dashboard-header no-print">
                    <h1 class="dashboard-title">Nota Peminjaman</h1>
                    <div>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                        <a href="nota.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="nota" id="notaPrint">
                    <div class="nota-header">
                        <div class="nota-logo">MARVELL RENTAL</div>
                        <h3 class="nota-title">NOTA PEMINJAMAN</h3>
                        <p class="nota-number">
                            <?= htmlspecialchars($nota['nomor_pesanan']) ?>
                        </p>
                    </div>

                    <div class="nota-body">
                        <div class="nota-row">
                            <span class="nota-label">Nama Peminjam</span>
                            <span class="nota-value">
                                <?= htmlspecialchars($nota['nama_peminjam']) ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">No. HP</span>
                            <span class="nota-value">
                                <?= htmlspecialchars($nota['no_hp'] ?: '-') ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Motor</span>
                            <span class="nota-value">
                                <?= htmlspecialchars($nota['nama_motor']) ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Jenis Motor</span>
                            <span class="nota-value">
                                <?= htmlspecialchars($nota['jenis']) ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Plat Nomor</span>
                            <span class="nota-value">
                                <?= htmlspecialchars($nota['plat_nomor']) ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Tanggal Pinjam</span>
                            <span class="nota-value">
                                <?= formatTanggal($nota['tanggal_pinjam']) ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Tanggal Kembali</span>
                            <span class="nota-value">
                                <?= formatTanggal($nota['tanggal_kembali']) ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Durasi</span>
                            <span class="nota-value">
                                <?= $nota['total_hari'] ?> hari
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Harga / Hari</span>
                            <span class="nota-value">
                                <?= formatRupiah($nota['harga_per_hari']) ?>
                            </span>
                        </div>

                        <?php if ($nota['denda'] && $nota['denda'] > 0): ?>
                            <div class="nota-row" style="color: #e74c3c;">
                                <span class="nota-label">Denda</span>
                                <span class="nota-value">
                                    <?= formatRupiah($nota['denda']) ?>
                                </span>
                            </div>
                            <?php if ($nota['keterangan_denda']): ?>
                                <div class="nota-row">
                                    <span class="nota-label">Ket. Denda</span>
                                    <span class="nota-value">
                                        <?= htmlspecialchars($nota['keterangan_denda']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="nota-total">
                            <div class="nota-row">
                                <span class="nota-label" style="font-weight: 600;">TOTAL BAYAR</span>
                                <span class="nota-value">
                                    <?= formatRupiah($nota['total_harga'] + ($nota['denda'] ?? 0)) ?>
                                </span>
                            </div>
                        </div>

                        <div class="nota-row" style="margin-top: 20px;">
                            <span class="nota-label">Disetujui Oleh</span>
                            <span class="nota-value">
                                <?= htmlspecialchars($nota['nama_petugas'] ?: '-') ?>
                            </span>
                        </div>
                        <div class="nota-row">
                            <span class="nota-label">Tanggal ACC</span>
                            <span class="nota-value">
                                <?= $nota['tanggal_acc'] ? date('d/m/Y H:i', strtotime($nota['tanggal_acc'])) : '-' ?>
                            </span>
                        </div>
                    </div>

                    <div class="nota-footer">
                        <p>Terima kasih telah menggunakan jasa kami!</p>
                        <p style="margin-top: 10px;">Marvell Rental - Rental Motor Terpercaya</p>
                        <p>Telp: 0812 3456 7890</p>
                    </div>
                </div>

            <?php else: ?>
                <!-- Show list of notas -->
                <div class="dashboard-header">
                    <h1 class="dashboard-title">Daftar Nota</h1>
                </div>

                <?php if (isset($notas) && count($notas) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Motor</th>
                                    <th>Tgl Pinjam</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas as $n): ?>
                                    <tr>
                                        <td style="color: var(--primary);">
                                            <?= htmlspecialchars($n['nomor_pesanan']) ?>
                                        </td>
                                        <td style="color: var(--text-primary);">
                                            <?= htmlspecialchars($n['nama_motor']) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($n['tanggal_pinjam'])) ?>
                                        </td>
                                        <td style="color: var(--primary);">
                                            <?= formatRupiah($n['total_harga']) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = $n['status'] === 'selesai' ? 'badge-success' : 'badge-info';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= ucfirst($n['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="nota.php?id=<?= $n['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Lihat
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card" style="text-align: center; padding: 50px;">
                        <i class="fas fa-receipt"
                            style="font-size: 3rem; color: var(--text-secondary); margin-bottom: 15px;"></i>
                        <p style="color: var(--text-secondary);">Belum ada nota peminjaman</p>
                        <a href="motor.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-motorcycle"></i> Sewa Motor Sekarang
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <button class="mobile-menu-btn no-print"><i class="fas fa-bars"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sweetalert-theme.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>