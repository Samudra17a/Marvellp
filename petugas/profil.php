<?php
require_once '../auth/cek_login.php';
cekPetugas();
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

    if ($action === 'update_photo') {
        // Handle photo upload only
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

                $foto = 'petugas_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/images/profiles/' . $foto);
                
                $stmt = $pdo->prepare("UPDATE users SET foto=? WHERE id=?");
                if ($stmt->execute([$foto, $_SESSION['user_id']])) {
                    $_SESSION['foto'] = $foto;
                    logAktivitas($pdo, $_SESSION['user_id'], 'Update Foto Profil', 'Mengupdate foto profil petugas');
                    $message = 'Foto profil berhasil diupdate!';
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                } else {
                    $error = 'Gagal mengupdate foto!';
                }
            } else {
                $error = 'Format foto tidak valid! Gunakan JPG, PNG, atau WEBP.';
            }
        } else {
            $error = 'Pilih foto terlebih dahulu!';
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
                logAktivitas($pdo, $_SESSION['user_id'], 'Ganti Password', 'Mengganti password akun petugas');
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
    <title>Profil Petugas - Marvell Rental</title>
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
                    <li><a href="peminjaman.php"><i class="fas fa-clipboard-list"></i> Pengajuan Peminjaman</a></li>
                    <li><a href="pengembalian.php"><i class="fas fa-undo"></i> Pengembalian</a></li>
                    <li><a href="profil.php" class="active"><i class="fas fa-user-cog"></i> Profil</a></li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Profil Petugas</h1>
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

            <div style="display: flex; gap: 25px; align-items: flex-start;">
                <!-- Left Sidebar - Photo & Menu -->
                <div style="width: 280px; flex-shrink: 0;">
                    <div class="card" style="text-align: center; padding: 30px 20px;">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_photo">
                            <div style="width: 130px; height: 130px; border-radius: 50%; margin: 0 auto 15px; overflow: hidden; border: 3px solid var(--primary);">
                                <?php if ($user['foto'] && file_exists('../assets/images/profiles/' . $user['foto'])): ?>
                                    <img src="../assets/images/profiles/<?= htmlspecialchars($user['foto']) ?>"
                                        alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; background: var(--bg-gold); display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 700; color: var(--text-dark);">
                                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p style="font-weight: 600; font-size: 1.1rem; margin-bottom: 3px;"><?= htmlspecialchars($user['nama']) ?></p>
                            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 15px;"><?= ucfirst($user['role']) ?></p>
                            <label class="btn btn-sm btn-secondary" style="cursor: pointer; display: inline-block; width: 100%;">
                                <i class="fas fa-camera"></i> Pilih Foto
                                <input type="file" name="foto" accept="image/*" style="display: none;" onchange="this.form.submit()">
                            </label>
                            <p style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 10px;">Maks 10 MB. Format: JPG, JPEG, PNG</p>
                        </form>
                    </div>
                    <div class="card" style="margin-top: 15px; padding: 0;">
                        <a href="#passwordSection" onclick="document.getElementById('passwordSection').scrollIntoView({behavior:'smooth'})"
                            style="display: flex; align-items: center; justify-content: space-between; padding: 15px 20px; border-bottom: var(--border-light); color: var(--text-primary); text-decoration: none;">
                            <span><i class="fas fa-key" style="color: var(--primary); margin-right: 10px;"></i> Ubah Kata Sandi</span>
                            <i class="fas fa-chevron-down" style="color: var(--text-secondary); font-size: 0.8rem;"></i>
                        </a>
                        <a href="../auth/logout.php" style="display: flex; align-items: center; padding: 15px 20px; color: var(--accent-red); text-decoration: none;" onclick="return confirm('Yakin ingin logout?')">
                            <i class="fas fa-sign-out-alt" style="margin-right: 10px;"></i> Logout
                        </a>
                    </div>
                </div>

                <!-- Right Content -->
                <div style="flex: 1;">
                    <div class="card">
                        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 25px;">Informasi Akun</h3>
                        <div style="display: flex; align-items: center; padding: 15px 0; border-bottom: var(--border-light);">
                            <span style="width: 140px; color: var(--text-secondary); font-size: 0.9rem; flex-shrink: 0;">Nama</span>
                            <span style="font-size: 0.95rem;"><?= htmlspecialchars($user['nama']) ?></span>
                        </div>
                        <div style="display: flex; align-items: center; padding: 15px 0; border-bottom: var(--border-light);">
                            <span style="width: 140px; color: var(--text-secondary); font-size: 0.9rem; flex-shrink: 0;">Role</span>
                            <span style="font-size: 0.95rem;"><?= ucfirst($user['role']) ?></span>
                        </div>
                    </div>

                    <div class="card" style="margin-top: 20px;">
                        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 25px;">Ubah Kontak</h3>
                        <div style="display: flex; align-items: center; padding: 15px 0; border-bottom: var(--border-light);">
                            <span style="width: 140px; color: var(--text-secondary); font-size: 0.9rem; flex-shrink: 0;">Email</span>
                            <span style="font-size: 0.95rem; margin-right: 12px;"><?= htmlspecialchars($user['email']) ?></span>
                            <span style="background: var(--accent-green); color: #fff; padding: 3px 10px; border-radius: 5px; font-size: 0.75rem; font-weight: 600;">terverifikasi</span>
                        </div>
                        <div style="display: flex; align-items: center; padding: 15px 0;">
                            <span style="width: 140px; color: var(--text-secondary); font-size: 0.9rem; flex-shrink: 0;">Terdaftar</span>
                            <span style="font-size: 0.95rem;"><?= date('d F Y', strtotime($user['created_at'])) ?></span>
                        </div>
                    </div>

                    <div class="card" style="margin-top: 20px;" id="passwordSection">
                        <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 25px;"><i class="fas fa-key" style="color: var(--primary); margin-right: 8px;"></i> Ubah Kata Sandi</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label class="form-label">Password Lama</label>
                                <div style="position: relative;">
                                    <input type="password" name="current_password" id="petugas_current_pw" class="form-control" required style="padding-right: 45px;">
                                    <button type="button" onclick="togglePassword('petugas_current_pw', 'petugasToggle1')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 5px;">
                                        <i class="fas fa-eye" id="petugasToggle1"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Password Baru</label>
                                <div style="position: relative;">
                                    <input type="password" name="new_password" id="petugas_new_pw" class="form-control" required minlength="6" style="padding-right: 45px;">
                                    <button type="button" onclick="togglePassword('petugas_new_pw', 'petugasToggle2')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 5px;">
                                        <i class="fas fa-eye" id="petugasToggle2"></i>
                                    </button>
                                </div>
                                <small style="color: var(--text-secondary);">Minimal 6 karakter</small>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <div style="position: relative;">
                                    <input type="password" name="confirm_password" id="petugas_confirm_pw" class="form-control" required style="padding-right: 45px;">
                                    <button type="button" onclick="togglePassword('petugas_confirm_pw', 'petugasToggle3')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-secondary); cursor: pointer; padding: 5px;">
                                        <i class="fas fa-eye" id="petugasToggle3"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Ganti Password</button>
                        </form>
                    </div>

                    <div style="margin-top: 20px; padding: 20px; background: rgba(255, 215, 0, 0.1); border-radius: 10px;">
                        <h4 style="font-size: 0.95rem; color: var(--primary); margin-bottom: 10px;">
                            <i class="fas fa-info-circle"></i> Catatan
                        </h4>
                        <p style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6;">
                            Sebagai petugas, Anda hanya dapat mengubah foto profil dan password. Untuk mengubah data lainnya, silakan hubungi admin.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <button class="mobile-menu-btn"><i class="fas fa-bars"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/sweetalert-theme.js"></script>
    <script src="../assets/js/main.js"></script>
</body>

</html>
