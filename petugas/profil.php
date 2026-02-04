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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <!-- Profile Photo Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Foto Profil</h3>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_photo">

                        <!-- Profile Photo -->
                        <div style="text-align: center; margin-bottom: 25px;">
                            <div
                                style="width: 150px; height: 150px; border-radius: 50%; margin: 0 auto 20px; overflow: hidden; border: 4px solid var(--primary);">
                                <?php if ($user['foto'] && file_exists('../assets/images/profiles/' . $user['foto'])): ?>
                                    <img src="../assets/images/profiles/<?= htmlspecialchars($user['foto']) ?>"
                                        alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; background: var(--bg-gold); display: flex; align-items: center; justify-content: center; font-size: 4rem; font-weight: 700; color: var(--text-dark);">
                                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <input type="file" name="foto" accept="image/*" class="form-control" id="fotoInput">
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Upload Foto Baru
                            </button>
                        </div>
                    </form>

                    <!-- Account Info -->
                    <div style="margin-top: 30px; padding-top: 20px; border-top: var(--border-light);">
                        <h4 style="font-size: 1rem; margin-bottom: 15px; color: var(--text-secondary);">Informasi Akun</h4>
                        <p style="font-size: 0.95rem; color: var(--text-primary); margin-bottom: 10px;">
                            <i class="fas fa-user" style="color: var(--primary); margin-right: 10px; width: 20px;"></i>
                            <?= htmlspecialchars($user['nama']) ?>
                        </p>
                        <p style="font-size: 0.95rem; color: var(--text-primary); margin-bottom: 10px;">
                            <i class="fas fa-envelope" style="color: var(--primary); margin-right: 10px; width: 20px;"></i>
                            <?= htmlspecialchars($user['email']) ?>
                        </p>
                        <p style="font-size: 0.95rem; color: var(--text-primary); margin-bottom: 10px;">
                            <i class="fas fa-user-shield" style="color: var(--primary); margin-right: 10px; width: 20px;"></i>
                            Role: <?= ucfirst($user['role']) ?>
                        </p>
                        <p style="font-size: 0.9rem; color: var(--text-secondary);">
                            <i class="fas fa-calendar" style="color: var(--primary); margin-right: 10px; width: 20px;"></i>
                            Terdaftar: <?= date('d F Y', strtotime($user['created_at'])) ?>
                        </p>
                    </div>
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

                    <!-- Logout Button -->
                    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <a href="../auth/logout.php" class="btn btn-block" style="background: var(--accent-red); color: #fff;" 
                           onclick="return confirm('Yakin ingin logout?')">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>

                    <div style="margin-top: 30px; padding: 20px; background: rgba(255, 215, 0, 0.1); border-radius: 10px;">
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
    <script src="../assets/js/main.js"></script>
</body>

</html>
