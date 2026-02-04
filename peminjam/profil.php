<?php
require_once '../auth/cek_login.php';
cekPeminjam();
require_once '../database/koneksi.php';

$message = '';
$error = '';

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');

        // Handle photo upload
        $foto = $user['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                // Delete old photo
                if ($foto && file_exists('../assets/images/profiles/' . $foto)) {
                    unlink('../assets/images/profiles/' . $foto);
                }

                // Create profiles directory if not exists
                if (!is_dir('../assets/images/profiles')) {
                    mkdir('../assets/images/profiles', 0777, true);
                }

                $foto = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/images/profiles/' . $foto);
            } else {
                $error = 'Format foto tidak valid! Gunakan JPG, PNG, atau WEBP.';
            }
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("UPDATE users SET nama=?, no_hp=?, alamat=?, foto=? WHERE id=?");
            if ($stmt->execute([$nama, $no_hp, $alamat, $foto, $_SESSION['user_id']])) {
                $_SESSION['nama'] = $nama;
                $_SESSION['foto'] = $foto;
                logAktivitas($pdo, $_SESSION['user_id'], 'Update Profil', 'Mengupdate data profil');
                $message = 'Profil berhasil diupdate!';

                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
            } else {
                $error = 'Gagal mengupdate profil!';
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!password_verify($current_password, $user['password'])) {
            $error = 'Password lama salah!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
            if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                logAktivitas($pdo, $_SESSION['user_id'], 'Ganti Password', 'Mengganti password akun');
                $message = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Marvell Rental</title>
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="motor.php"><i class="fas fa-motorcycle"></i> Sewa Motor</a></li>
                    <li><a href="riwayat.php"><i class="fas fa-history"></i> Riwayat</a></li>
                    <li><a href="nota.php"><i class="fas fa-receipt"></i> Nota</a></li>
                    <li><a href="profil.php" class="active"><i class="fas fa-user"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Profil Saya</h1>
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <!-- Profile Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Profil</h3>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_profile">

                        <!-- Profile Photo -->
                        <div style="text-align: center; margin-bottom: 25px;">
                            <div
                                style="width: 120px; height: 120px; border-radius: 50%; margin: 0 auto 15px; overflow: hidden; border: 3px solid var(--primary);">
                                <?php if ($user['foto'] && file_exists('../assets/images/profiles/' . $user['foto'])): ?>
                                    <img src="../assets/images/profiles/<?= htmlspecialchars($user['foto']) ?>"
                                        alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; background: var(--bg-gold); display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; color: var(--text-dark);">
                                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <label class="btn btn-sm btn-secondary" style="cursor: pointer;">
                                <i class="fas fa-camera"></i> Ubah Foto
                                <input type="file" name="foto" accept="image/*" style="display: none;"
                                    onchange="this.form.submit()">
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control"
                                value="<?= htmlspecialchars($user['nama']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"
                                disabled>
                            <small style="color: var(--text-secondary);">Email tidak dapat diubah</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">No. HP</label>
                            <input type="tel" name="no_hp" class="form-control"
                                value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control"
                                rows="3"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>

                <!-- Change Password Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ganti Password</h3>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label class="form-label">Password Lama</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                            <small style="color: var(--text-secondary);">Minimal 6 karakter</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-secondary btn-block">
                            <i class="fas fa-key"></i> Ganti Password
                        </button>
                    </form>

                    <!-- Account Info -->
                    <div style="margin-top: 30px; padding-top: 20px; border-top: var(--border-light);">
                        <h4 style="font-size: 1rem; margin-bottom: 15px; color: var(--text-secondary);">Informasi Akun
                        </h4>
                        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 10px;">
                            <i class="fas fa-calendar" style="color: var(--primary); margin-right: 10px;"></i>
                            Terdaftar:
                            <?= date('d F Y', strtotime($user['created_at'])) ?>
                        </p>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">
                            <i class="fas fa-user-tag" style="color: var(--primary); margin-right: 10px;"></i>
                            Role:
                            <?= ucfirst($user['role']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Logout Button -->
            <div style="margin-top: 25px;">
                <a href="../auth/logout.php" class="btn btn-block"
                    style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: #fff; padding: 15px; font-size: 1rem;">
                    <i class="fas fa-sign-out-alt"></i> Logout dari Akun
                </a>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="../assets/js/main.js"></script>
</body>

</html>