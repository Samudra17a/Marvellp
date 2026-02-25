<?php
require_once '../auth/cek_login.php';
cekPetugas();
require_once '../database/koneksi.php';

// Stats
$menunggu = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'menunggu'")->fetchColumn();
$disetujui = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'disetujui'")->fetchColumn();
$selesai = $pdo->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'selesai'")->fetchColumn();

// Pengajuan terbaru
$stmt = $pdo->query("
    SELECT p.*, u.nama as nama_peminjam, u.no_hp, m.nama_motor, m.jenis
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN motor m ON p.motor_id = m.id 
    WHERE p.status = 'menunggu'
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$pengajuan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Marvell Rental</title>
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
                <p class="sidebar-menu-title">Menu Utama</p>
                <ul class="sidebar-nav">
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="peminjaman.php"><i class="fas fa-clipboard-list"></i> Pengajuan Peminjaman</a></li>
                    <li><a href="pengembalian.php"><i class="fas fa-undo"></i> Pengembalian</a></li>
                    <li><a href="profil.php"><i class="fas fa-user-cog"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard Petugas</h1>
                <div class="dashboard-user">
                    <div class="dashboard-user-info">
                        <p class="dashboard-user-name">
                            <?= htmlspecialchars($_SESSION['nama']) ?>
                        </p>
                        <p class="dashboard-user-role">Petugas</p>
                    </div>
                    <div class="dashboard-user-avatar">
                        <?php if (!empty($_SESSION['foto']) && file_exists('../assets/images/profiles/' . $_SESSION['foto'])): ?>
                            <img src="../assets/images/profiles/<?= htmlspecialchars($_SESSION['foto']) ?>" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-orange);"><i class="fas fa-clock"></i></div>
                    <p class="stat-value">
                        <?= $menunggu ?>
                    </p>
                    <p class="stat-label">Menunggu ACC</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--primary);"><i class="fas fa-check-circle"></i></div>
                    <p class="stat-value">
                        <?= $disetujui ?>
                    </p>
                    <p class="stat-label">Sedang Disewa</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="color: var(--accent-green);"><i class="fas fa-flag-checkered"></i>
                    </div>
                    <p class="stat-value">
                        <?= $selesai ?>
                    </p>
                    <p class="stat-label">Selesai</p>
                </div>
            </div>

            <!-- Pengajuan Terbaru -->
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Pengajuan Terbaru</h3>
                    <a href="peminjaman.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Peminjam</th>
                            <th>Motor</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pengajuan) > 0): ?>
                            <?php foreach ($pengajuan as $p): ?>
                                <tr>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($p['nama_peminjam']) ?>
                                        <br><small style="color: var(--text-secondary);">
                                            <?= $p['no_hp'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['nama_motor']) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($p['tanggal_pinjam'])) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($p['tanggal_kembali'])) ?>
                                    </td>
                                    <td style="color: var(--primary);">
                                        <?= formatRupiah($p['total_harga']) ?>
                                    </td>
                                    <td>
                                        <a href="peminjaman.php" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada pengajuan baru</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sweetalert-theme.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>