<?php
require_once '../auth/cek_login.php';
cekAdmin();
require_once '../database/koneksi.php';

$message = '';
$error = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT nama FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                logAktivitas($pdo, $_SESSION['user_id'], 'Hapus Peminjam', "Menghapus peminjam: " . $user['nama']);
                $message = 'Peminjam berhasil dihapus!';
            } else {
                $error = 'Gagal menghapus peminjam!';
            }
        }
    }
}

// Get all peminjam
$peminjam = $pdo->query("SELECT * FROM users WHERE role = 'peminjam' ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peminjam - Admin Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <li><a href="peminjam.php" class="active"><i class="fas fa-users"></i> Data Peminjam</a></li>
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
            <!-- Print Header -->
            <div class="print-header">
                <div class="company-name">MARVELL RENTAL</div>
                <h1>Data Peminjam</h1>
                <p class="print-date">Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
            </div>

            <div class="dashboard-header">
                <h1 class="dashboard-title">Data Peminjam</h1>
                <button class="btn btn-secondary" onclick="window.print()" style="margin-left: auto;">
                    <i class="fas fa-print"></i> Cetak PDF
                </button>
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

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Alamat</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($peminjam) > 0): ?>
                            <?php foreach ($peminjam as $i => $p): ?>
                                <tr>
                                    <td style="color: var(--text-primary);">
                                        <?= $i + 1 ?>
                                    </td>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($p['nama']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['email']) ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['no_hp'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['alamat'] ? substr($p['alamat'], 0, 30) . '...' : '-') ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary"
                                            onclick="viewPeminjam(<?= htmlspecialchars(json_encode($p)) ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Yakin ingin menghapus peminjam ini?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm"
                                                style="background: var(--accent-red); color: #fff;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data peminjam</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- View Modal -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Detail Peminjam</h3>
                <button class="modal-close">&times;</button>
            </div>
            <div id="peminjamDetail">
                <!-- Content loaded by JS -->
            </div>
        </div>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>

    <script src="../assets/js/main.js"></script>
    <script>
        function viewPeminjam(p) {
            document.getElementById('peminjamDetail').innerHTML = `
                <table style="width: 100%;">
                    <tr><td style="padding: 10px 0; color: var(--text-secondary);">Nama</td><td style="padding: 10px 0; color: var(--text-primary);">${p.nama}</td></tr>
                    <tr><td style="padding: 10px 0; color: var(--text-secondary);">Email</td><td style="padding: 10px 0;">${p.email}</td></tr>
                    <tr><td style="padding: 10px 0; color: var(--text-secondary);">No. HP</td><td style="padding: 10px 0;">${p.no_hp || '-'}</td></tr>
                    <tr><td style="padding: 10px 0; color: var(--text-secondary);">Alamat</td><td style="padding: 10px 0;">${p.alamat || '-'}</td></tr>
                    <tr><td style="padding: 10px 0; color: var(--text-secondary);">Terdaftar</td><td style="padding: 10px 0;">${new Date(p.created_at).toLocaleDateString('id-ID')}</td></tr>
                </table>
            `;
            document.getElementById('viewModal').classList.add('active');
        }
    </script>
</body>

</html>