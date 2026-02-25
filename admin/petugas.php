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
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $password = $_POST['password'] ?? '';

        // Cek email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, 'petugas')");
            if ($stmt->execute([$nama, $email, $hashedPassword, $no_hp])) {
                logAktivitas($pdo, $_SESSION['user_id'], 'Tambah Petugas', "Menambahkan petugas: $nama");
                $message = 'Petugas berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan petugas!';
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $password = $_POST['password'] ?? '';

        // Cek email (exclude current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $error = 'Email sudah digunakan!';
        } else {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama=?, email=?, password=?, no_hp=? WHERE id=?");
                $stmt->execute([$nama, $email, $hashedPassword, $no_hp, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama=?, email=?, no_hp=? WHERE id=?");
                $stmt->execute([$nama, $email, $no_hp, $id]);
            }
            logAktivitas($pdo, $_SESSION['user_id'], 'Edit Petugas', "Mengedit petugas: $nama");
            $message = 'Petugas berhasil diupdate!';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT nama FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                logAktivitas($pdo, $_SESSION['user_id'], 'Hapus Petugas', "Menghapus petugas: " . $user['nama']);
                $message = 'Petugas berhasil dihapus!';
            } else {
                $error = 'Gagal menghapus petugas!';
            }
        }
    }
}

// Get all petugas
$petugas = $pdo->query("SELECT * FROM users WHERE role = 'petugas' ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas - Admin Marvell Rental</title>
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
                    <li><a href="petugas.php" class="active"><i class="fas fa-user-tie"></i> Data Petugas</a></li>
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
                <h1>Data Petugas</h1>
                <p class="print-date">Dicetak pada: <?= date('d F Y, H:i') ?> WIB</p>
            </div>

            <div class="dashboard-header">
                <h1 class="dashboard-title">Data Petugas</h1>
                <div style="display: flex; gap: 10px; margin-left: auto;">
                    <button class="btn btn-primary" data-modal="addModal">
                        <i class="fas fa-plus"></i> Tambah Petugas
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
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($petugas) > 0): ?>
                            <?php foreach ($petugas as $i => $p): ?>
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
                                        <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary"
                                            onclick="editPetugas(<?= htmlspecialchars(json_encode($p)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;"
                                            onsubmit="return confirm('Yakin ingin menghapus petugas ini?')">
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
                                <td colspan="6" class="text-center">Belum ada data petugas</td>
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
                <h3 class="modal-title">Tambah Petugas</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="tel" name="no_hp" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="add_petugas_pw" class="form-control" required minlength="6" style="padding-right: 45px;">
                        <button type="button" onclick="togglePassword('add_petugas_pw', 'addPetugasToggle')" 
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 5px;">
                            <i class="fas fa-eye" id="addPetugasToggle"></i>
                        </button>
                    </div>
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
                <h3 class="modal-title">Edit Petugas</h3>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="tel" name="no_hp" id="edit_no_hp" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Password (kosongkan jika tidak diubah)</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="edit_petugas_pw" class="form-control" minlength="6" style="padding-right: 45px;">
                        <button type="button" onclick="togglePassword('edit_petugas_pw', 'editPetugasToggle')" 
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 5px;">
                            <i class="fas fa-eye" id="editPetugasToggle"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Update
                </button>
            </form>
        </div>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>

    <script src="../assets/js/main.js"></script>
    <script>
        function editPetugas(petugas) {
            document.getElementById('edit_id').value = petugas.id;
            document.getElementById('edit_nama').value = petugas.nama;
            document.getElementById('edit_email').value = petugas.email;
            document.getElementById('edit_no_hp').value = petugas.no_hp || '';
            document.getElementById('editModal').classList.add('active');
        }
    </script>
</body>

</html>