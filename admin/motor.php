<?php
require_once '../auth/cek_login.php';
cekAdmin();
require_once '../database/koneksi.php';

$message = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $nama_motor = trim($_POST['nama_motor'] ?? '');
        $jenis = $_POST['jenis'] ?? '';
        $merk = trim($_POST['merk'] ?? '');
        $tahun = $_POST['tahun'] ?? '';
        $plat_nomor = trim($_POST['plat_nomor'] ?? '');
        $harga_per_hari = $_POST['harga_per_hari'] ?? 0;
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        // Handle image upload
        $gambar = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $gambar = 'motor_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/images/' . $gambar);
            }
        }

        $stok = $_POST['stok'] ?? 1;
        $stmt = $pdo->prepare("INSERT INTO motor (nama_motor, jenis, merk, tahun, plat_nomor, harga_per_hari, gambar, deskripsi, stok) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$nama_motor, $jenis, $merk, $tahun, $plat_nomor, $harga_per_hari, $gambar, $deskripsi, $stok])) {
            logAktivitas($pdo, $_SESSION['user_id'], 'Tambah Motor', "Menambahkan motor: $nama_motor");
            $message = 'Motor berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan motor!';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $nama_motor = trim($_POST['nama_motor'] ?? '');
        $jenis = $_POST['jenis'] ?? '';
        $merk = trim($_POST['merk'] ?? '');
        $tahun = $_POST['tahun'] ?? '';
        $plat_nomor = trim($_POST['plat_nomor'] ?? '');
        $harga_per_hari = $_POST['harga_per_hari'] ?? 0;
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status = $_POST['status'] ?? 'tersedia';

        // Handle image upload
        $gambar = $_POST['gambar_lama'] ?? '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                // Delete old image
                if ($gambar && file_exists('../assets/images/' . $gambar)) {
                    unlink('../assets/images/' . $gambar);
                }
                $gambar = 'motor_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['gambar']['tmp_name'], '../assets/images/' . $gambar);
            }
        }

        $stok = $_POST['stok'] ?? 1;
        $stmt = $pdo->prepare("UPDATE motor SET nama_motor=?, jenis=?, merk=?, tahun=?, plat_nomor=?, harga_per_hari=?, gambar=?, deskripsi=?, status=?, stok=? WHERE id=?");
        if ($stmt->execute([$nama_motor, $jenis, $merk, $tahun, $plat_nomor, $harga_per_hari, $gambar, $deskripsi, $status, $stok, $id])) {
            logAktivitas($pdo, $_SESSION['user_id'], 'Edit Motor', "Mengedit motor: $nama_motor");
            $message = 'Motor berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate motor!';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        // Get motor name for log
        $stmt = $pdo->prepare("SELECT nama_motor, gambar FROM motor WHERE id = ?");
        $stmt->execute([$id]);
        $motor = $stmt->fetch();

        if ($motor) {
            // Delete image
            if ($motor['gambar'] && file_exists('../assets/images/' . $motor['gambar'])) {
                unlink('../assets/images/' . $motor['gambar']);
            }

            $stmt = $pdo->prepare("DELETE FROM motor WHERE id = ?");
            if ($stmt->execute([$id])) {
                logAktivitas($pdo, $_SESSION['user_id'], 'Hapus Motor', "Menghapus motor: " . $motor['nama_motor']);
                $message = 'Motor berhasil dihapus!';
            } else {
                $error = 'Gagal menghapus motor!';
            }
        }
    }
}

// Get all motors
$motors = $pdo->query("SELECT * FROM motor ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Motor - Admin Marvell Rental</title>
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
                    <li><a href="motor.php" class="active"><i class="fas fa-motorcycle"></i> Data Motor</a></li>
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
            <!-- Print Header -->
            <div class="print-header">
                <div class="company-name">MARVELL RENTAL</div>
                <h1>Data Motor</h1>
                <p class="print-date">Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
            </div>

            <div class="dashboard-header">
                <h1 class="dashboard-title">Data Motor</h1>
                <div style="display: flex; gap: 10px; margin-left: auto;">
                    <button class="btn btn-primary" data-modal="addModal">
                        <i class="fas fa-plus"></i> Tambah Motor
                    </button>
                    <button class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Cetak PDF
                    </button>
                </div>
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
                            <th>Gambar</th>
                            <th>Nama Motor</th>
                            <th>Jenis</th>
                            <th>Plat Nomor</th>
                            <th>Harga/Hari</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($motors) > 0): ?>
                            <?php foreach ($motors as $i => $motor): ?>
                                <tr>
                                    <td style="color: var(--text-primary);">
                                        <?= $i + 1 ?>
                                    </td>
                                    <td>
                                        <?php if ($motor['gambar'] && file_exists('../assets/images/' . $motor['gambar'])): ?>
                                            <img src="../assets/images/<?= htmlspecialchars($motor['gambar']) ?>" alt=""
                                                style="width: 60px; height: 40px; object-fit: cover; border-radius: 5px;">
                                        <?php else: ?>
                                            <div
                                                style="width: 60px; height: 40px; background: var(--bg-light); border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-motorcycle" style="color: var(--text-secondary);"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: var(--text-primary);">
                                        <?= htmlspecialchars($motor['nama_motor']) ?>
                                    </td>
                                    <td><span class="badge badge-info">
                                            <?= htmlspecialchars($motor['jenis']) ?>
                                        </span></td>
                                    <td>
                                        <?= htmlspecialchars($motor['plat_nomor']) ?>
                                    </td>
                                    <td style="color: var(--primary);">
                                        <?= formatRupiah($motor['harga_per_hari']) ?>
                                    </td>
                                    <td style="color: var(--primary);">
                                        <?= $motor['stok'] ?? 1 ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($motor['status']) {
                                            'tersedia' => 'badge-success',
                                            'disewa' => 'badge-warning',
                                            'maintenance' => 'badge-danger',
                                            default => 'badge-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst($motor['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary"
                                            onclick="editMotor(<?= htmlspecialchars(json_encode($motor)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Yakin ingin menghapus motor ini?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $motor['id'] ?>">
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
                                <td colspan="8" class="text-center">Belum ada data motor</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Motor</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label class="form-label">Nama Motor *</label>
                    <input type="text" name="nama_motor" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Jenis *</label>
                        <select name="jenis" class="form-control" required>
                            <option value="Matic">Matic</option>
                            <option value="Sport">Sport</option>
                            <option value="Supermoto">Supermoto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Merk *</label>
                        <input type="text" name="merk" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Tahun</label>
                        <input type="number" name="tahun" class="form-control" min="2000" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plat Nomor *</label>
                        <input type="text" name="plat_nomor" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Harga per Hari (Rp) *</label>
                    <input type="number" name="harga_per_hari" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Stok *</label>
                    <input type="number" name="stok" class="form-control" value="1" min="0" required>
                    <small style="color: var(--text-secondary);">Jumlah unit motor yang tersedia</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Gambar</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Motor</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="gambar_lama" id="edit_gambar_lama">

                <div class="form-group">
                    <label class="form-label">Nama Motor *</label>
                    <input type="text" name="nama_motor" id="edit_nama_motor" class="form-control" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Jenis *</label>
                        <select name="jenis" id="edit_jenis" class="form-control" required>
                            <option value="Matic">Matic</option>
                            <option value="Sport">Sport</option>
                            <option value="Supermoto">Supermoto</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Merk *</label>
                        <input type="text" name="merk" id="edit_merk" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Tahun</label>
                        <input type="number" name="tahun" id="edit_tahun" class="form-control" min="2000" max="2030">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Plat Nomor *</label>
                        <input type="text" name="plat_nomor" id="edit_plat_nomor" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Harga per Hari (Rp) *</label>
                    <input type="number" name="harga_per_hari" id="edit_harga_per_hari" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" id="edit_status" class="form-control" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="disewa">Disewa</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Stok *</label>
                    <input type="number" name="stok" id="edit_stok" class="form-control" min="0" required>
                    <small style="color: var(--text-secondary);">Jumlah unit motor yang tersedia</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Gambar (kosongkan jika tidak diubah)</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Update
                </button>
            </form>
        </div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn">
        <i class="fas fa-bars"></i>
    </button>

    <script src="../assets/js/main.js"></script>
    <script>
        function editMotor(motor) {
            document.getElementById('edit_id').value = motor.id;
            document.getElementById('edit_nama_motor').value = motor.nama_motor;
            document.getElementById('edit_jenis').value = motor.jenis;
            document.getElementById('edit_merk').value = motor.merk;
            document.getElementById('edit_tahun').value = motor.tahun;
            document.getElementById('edit_plat_nomor').value = motor.plat_nomor;
            document.getElementById('edit_harga_per_hari').value = motor.harga_per_hari;
            document.getElementById('edit_status').value = motor.status;
            document.getElementById('edit_stok').value = motor.stok || 1;
            document.getElementById('edit_deskripsi').value = motor.deskripsi;
            document.getElementById('edit_gambar_lama').value = motor.gambar;

            document.getElementById('editModal').classList.add('active');
        }
    </script>
</body>

</html>