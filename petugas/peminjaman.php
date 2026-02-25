<?php
require_once '../auth/cek_login.php';
cekPetugas();
require_once '../database/koneksi.php';

$message = '';
$error = '';

// Handle ACC / Tolak
if (isset($_GET['action'])) {
    $id = $_GET['id'] ?? 0;
    $action = $_GET['action'];

    if ($action === 'acc') {
        // Get peminjaman data
        $stmt = $pdo->prepare("SELECT p.*, m.nama_motor FROM peminjaman p JOIN motor m ON p.motor_id = m.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $peminjaman = $stmt->fetch();

        if ($peminjaman && $peminjaman['status'] === 'menunggu') {
            // Update status and processed_by
            $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'disetujui', processed_by = ? WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $id]);

            // Update motor status
            $stmt = $pdo->prepare("UPDATE motor SET status = 'disewa' WHERE id = ?");
            $stmt->execute([$peminjaman['motor_id']]);

            // Generate nota
            $nomorPesanan = generateNomorPesanan();
            $stmt = $pdo->prepare("INSERT INTO nota (peminjaman_id, nomor_pesanan, acc_by) VALUES (?, ?, ?)");
            $stmt->execute([$id, $nomorPesanan, $_SESSION['user_id']]);

            logAktivitas($pdo, $_SESSION['user_id'], 'ACC Peminjaman', "Menyetujui peminjaman: " . $peminjaman['nama_motor']);
            $message = 'Peminjaman berhasil disetujui! Nota: ' . $nomorPesanan;
        }
    } elseif ($action === 'tolak' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $alasan = $_POST['alasan_tolak'] ?? '';
        if (empty(trim($alasan))) {
            $error = 'Alasan penolakan wajib diisi!';
        } else {
            $stmt = $pdo->prepare("UPDATE peminjaman SET status = 'ditolak', alasan_tolak = ?, processed_by = ? WHERE id = ? AND status = 'menunggu'");
            if ($stmt->execute([$alasan, $_SESSION['user_id'], $id])) {
                logAktivitas($pdo, $_SESSION['user_id'], 'Tolak Peminjaman', "Menolak peminjaman ID: $id. Alasan: $alasan");
                $message = 'Peminjaman ditolak!';
            }
        }
    }
}

// Get all pengajuan
$filter = $_GET['filter'] ?? 'menunggu';
$stmt = $pdo->prepare("
    SELECT p.*, u.nama as nama_peminjam, u.no_hp, u.alamat, m.nama_motor, m.jenis, m.plat_nomor, m.harga_per_hari,
           n.nomor_pesanan
    FROM peminjaman p 
    JOIN users u ON p.user_id = u.id 
    JOIN motor m ON p.motor_id = m.id 
    LEFT JOIN nota n ON p.id = n.peminjaman_id
    WHERE p.status = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$filter]);
$pengajuan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Peminjaman - Petugas Marvell Rental</title>
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="peminjaman.php" class="active"><i class="fas fa-clipboard-list"></i> Pengajuan
                            Peminjaman</a></li>
                    <li><a href="pengembalian.php"><i class="fas fa-undo"></i> Pengembalian</a></li>
                    <li><a href="profil.php"><i class="fas fa-user-cog"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Pengajuan Peminjaman</h1>
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

            <!-- Filter Tabs -->
            <div style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap;">
                <a href="?filter=menunggu" class="btn <?= $filter === 'menunggu' ? 'btn-primary' : 'btn-secondary' ?>">
                    <i class="fas fa-clock"></i> Menunggu
                </a>
                <a href="?filter=disetujui"
                    class="btn <?= $filter === 'disetujui' ? 'btn-primary' : 'btn-secondary' ?>">
                    <i class="fas fa-check"></i> Disetujui
                </a>
                <a href="?filter=ditolak" class="btn <?= $filter === 'ditolak' ? 'btn-primary' : 'btn-secondary' ?>">
                    <i class="fas fa-times"></i> Ditolak
                </a>
                <a href="?filter=selesai" class="btn <?= $filter === 'selesai' ? 'btn-primary' : 'btn-secondary' ?>">
                    <i class="fas fa-flag-checkered"></i> Selesai
                </a>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Peminjam</th>
                            <th>Motor</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Total</th>
                            <th>Status</th>
                            <?php if ($filter === 'menunggu'): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pengajuan) > 0): ?>
                            <?php foreach ($pengajuan as $i => $p): ?>
                                <tr>
                                    <td style="color: var(--text-primary);">
                                        <?= $i + 1 ?>
                                    </td>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($p['nama_peminjam']) ?>
                                        <br><small style="color: var(--text-secondary);">
                                            <?= $p['no_hp'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($p['nama_motor']) ?>
                                        <br><small style="color: var(--text-secondary);">
                                            <?= $p['plat_nomor'] ?>
                                        </small>
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
                                        <?php
                                        $badgeClass = match ($p['status']) {
                                            'menunggu' => 'badge-warning',
                                            'disetujui' => 'badge-info',
                                            'ditolak' => 'badge-danger',
                                            'selesai' => 'badge-success',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($p['status']) ?>
                                        </span>
                                    </td>
                                    <?php if ($filter === 'menunggu'): ?>
                                        <td>
                                            <a href="?action=acc&id=<?= $p['id'] ?>&filter=menunggu" class="btn btn-sm btn-primary"
                                                onclick="return confirm('ACC peminjaman ini?')">
                                                <i class="fas fa-check"></i> ACC
                                            </a>
                                            <button type="button" class="btn btn-sm"
                                                style="background: var(--accent-red); color: #fff;"
                                                onclick="openRejectModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_peminjam']) ?>', '<?= htmlspecialchars($p['nama_motor']) ?>')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?= $filter === 'menunggu' ? 8 : 7 ?>" class="text-center">Tidak ada data</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Rejection Modal -->
    <div class="modal-overlay" id="rejectModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Tolak Pengajuan</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" action="" id="rejectForm">
                <input type="hidden" name="action" value="tolak">
                
                <div id="rejectInfo" style="background: var(--bg-light); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <!-- Filled by JS -->
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan Penolakan <span style="color: var(--accent-red);">*</span></label>
                    <textarea name="alasan_tolak" class="form-control" rows="4" required
                        placeholder="Masukkan alasan mengapa pengajuan ini ditolak..."></textarea>
                    <small style="color: var(--text-secondary);">Alasan ini akan ditampilkan kepada peminjam</small>
                </div>

                <button type="submit" class="btn btn-block" style="background: var(--accent-red); color: #fff;">
                    <i class="fas fa-times"></i> Tolak Pengajuan
                </button>
            </form>
        </div>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sweetalert-theme.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function openRejectModal(id, peminjam, motor) {
            document.getElementById('rejectForm').action = `?action=tolak&id=${id}&filter=menunggu`;
            document.getElementById('rejectInfo').innerHTML = `
                <p style="margin-bottom: 8px;"><strong>Peminjam:</strong> ${peminjam}</p>
                <p><strong>Motor:</strong> ${motor}</p>
            `;
            document.getElementById('rejectModal').classList.add('active');
        }
    </script>
</body>

</html>