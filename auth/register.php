<?php
session_start();
require_once '../database/koneksi.php';

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: ../admin/index.php');
            break;
        case 'petugas':
            header('Location: ../petugas/index.php');
            break;
        case 'peminjam':
            header('Location: ../peminjam/index.php');
            break;
    }
    exit;
}

// reCAPTCHA Site Key (ganti dengan key Anda sendiri)
$recaptcha_site_key = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'; // Test key

$error = '';
$success = '';
$step = $_SESSION['register_step'] ?? 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Step 1: Send OTP
    if ($action === 'send_otp') {
        $email = trim($_POST['email'] ?? '');

        // Verify reCAPTCHA
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        $recaptcha_secret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'; // Test secret

        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
        $captcha_result = json_decode($verify);

        if (!$captcha_result->success) {
            $error = 'Verifikasi CAPTCHA gagal! Silakan coba lagi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid!';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar!';
            } else {
                // Generate OTP
                $otp = sprintf("%06d", mt_rand(0, 999999));
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                // Delete old OTPs for this email
                $stmt = $pdo->prepare("DELETE FROM otp_verification WHERE email = ?");
                $stmt->execute([$email]);

                // Insert new OTP
                $stmt = $pdo->prepare("INSERT INTO otp_verification (email, otp_code, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $otp, $expires_at]);

                // Send email (using PHP mail function - configure SMTP for production)
                $to = $email;
                $subject = "Kode OTP Marvell Rental";
                $message = "
                <html>
                <head>
                    <title>Kode Verifikasi OTP</title>
                </head>
                <body style='font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;'>
                    <div style='max-width: 500px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 30px;'>
                        <h2 style='color: #C9A100; text-align: center;'>Marvell Rental</h2>
                        <p>Halo,</p>
                        <p>Kode OTP Anda untuk verifikasi email adalah:</p>
                        <h1 style='text-align: center; font-size: 40px; letter-spacing: 10px; color: #333; background: #f0f0f0; padding: 20px; border-radius: 10px;'>{$otp}</h1>
                        <p style='color: #666;'>Kode ini berlaku selama 15 menit.</p>
                        <p style='color: #999; font-size: 12px;'>Jika Anda tidak melakukan registrasi, abaikan email ini.</p>
                    </div>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: Marvell Rental <noreply@marvellrental.com>\r\n";

                // Try to send email (may fail on localhost without SMTP)
                @mail($to, $subject, $message, $headers);

                // Store email in session for next step
                $_SESSION['register_email'] = $email;
                $_SESSION['register_step'] = 2;
                $step = 2;
                $success = "Kode OTP telah dikirim ke {$email}. (Untuk testing: {$otp})";
            }
        }
    }

    // Step 2: Verify OTP
    elseif ($action === 'verify_otp') {
        $otp_input = trim($_POST['otp'] ?? '');
        $email = $_SESSION['register_email'] ?? '';

        if (empty($email)) {
            $error = 'Sesi expired. Silakan ulangi dari awal.';
            $_SESSION['register_step'] = 1;
            $step = 1;
        } else {
            $stmt = $pdo->prepare("SELECT * FROM otp_verification WHERE email = ? AND otp_code = ? AND is_used = 0 AND expires_at > NOW()");
            $stmt->execute([$email, $otp_input]);
            $otp_record = $stmt->fetch();

            if ($otp_record) {
                // Mark OTP as used
                $stmt = $pdo->prepare("UPDATE otp_verification SET is_used = 1 WHERE id = ?");
                $stmt->execute([$otp_record['id']]);

                $_SESSION['register_step'] = 3;
                $step = 3;
                $success = 'Email berhasil diverifikasi! Silakan lengkapi data.';
            } else {
                $error = 'Kode OTP tidak valid atau sudah expired!';
            }
        }
    }

    // Step 3: Complete Registration
    elseif ($action === 'complete_register') {
        $email = $_SESSION['register_email'] ?? '';
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($email)) {
            $error = 'Sesi expired. Silakan ulangi dari awal.';
            $_SESSION['register_step'] = 1;
            $step = 1;
        } elseif (empty($nama) || empty($password)) {
            $error = 'Nama dan password harus diisi!';
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } elseif ($password !== $confirm_password) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, role, email_verified) VALUES (?, ?, ?, ?, ?, 'peminjam', 1)");

            if ($stmt->execute([$nama, $email, $hashedPassword, $no_hp, $alamat])) {
                // Clear session
                unset($_SESSION['register_email']);
                unset($_SESSION['register_step']);

                $success = 'Registrasi berhasil! Silakan login.';
                $step = 'done';
            } else {
                $error = 'Gagal menyimpan data. Silakan coba lagi.';
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
    <title>Daftar - Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">MR</div>
                    <h1 class="auth-title">Daftar Akun</h1>
                    <p class="auth-subtitle">
                        <?php if ($step == 1): ?>
                            Masukkan email untuk memulai
                        <?php elseif ($step == 2): ?>
                            Masukkan kode OTP
                        <?php elseif ($step == 3): ?>
                            Lengkapi data diri
                        <?php else: ?>
                            Registrasi berhasil!
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Progress Steps -->
                <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 25px;">
                    <div
                        style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; <?= $step >= 1 ? 'background: var(--bg-gold); color: var(--text-dark);' : 'background: var(--bg-light); color: var(--text-secondary);' ?>">
                        1</div>
                    <div
                        style="flex: 1; max-width: 40px; height: 2px; background: <?= $step >= 2 ? 'var(--primary)' : 'var(--bg-light)' ?>; align-self: center;">
                    </div>
                    <div
                        style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; <?= $step >= 2 ? 'background: var(--bg-gold); color: var(--text-dark);' : 'background: var(--bg-light); color: var(--text-secondary);' ?>">
                        2</div>
                    <div
                        style="flex: 1; max-width: 40px; height: 2px; background: <?= $step >= 3 ? 'var(--primary)' : 'var(--bg-light)' ?>; align-self: center;">
                    </div>
                    <div
                        style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; <?= $step >= 3 || $step == 'done' ? 'background: var(--bg-gold); color: var(--text-dark);' : 'background: var(--bg-light); color: var(--text-secondary);' ?>">
                        3</div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($step == 1): ?>
                    <!-- Step 1: Email + CAPTCHA -->
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="send_otp">

                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" placeholder="Masukkan email aktif"
                                required>
                        </div>

                        <div class="form-group" style="display: flex; justify-content: center;">
                            <div class="g-recaptcha" data-sitekey="<?= $recaptcha_site_key ?>" data-theme="dark"></div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Kirim Kode OTP
                        </button>
                    </form>

                <?php elseif ($step == 2): ?>
                    <!-- Step 2: OTP Verification -->
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="verify_otp">

                        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
                            Kode OTP telah dikirim ke:<br>
                            <strong
                                style="color: var(--primary);"><?= htmlspecialchars($_SESSION['register_email'] ?? '') ?></strong>
                        </p>

                        <div class="form-group">
                            <label class="form-label">Kode OTP (6 digit)</label>
                            <input type="text" name="otp" class="form-control" placeholder="000000" maxlength="6"
                                pattern="[0-9]{6}" required
                                style="text-align: center; font-size: 1.5rem; letter-spacing: 10px;">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-check"></i> Verifikasi
                        </button>

                        <p style="text-align: center; margin-top: 15px;">
                            <a href="?reset=1" style="color: var(--text-secondary); font-size: 0.9rem;">← Gunakan email
                                lain</a>
                        </p>
                    </form>

                <?php elseif ($step == 3): ?>
                    <!-- Step 3: Complete Registration -->
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="complete_register">

                        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
                            Email: <strong
                                style="color: var(--primary);"><?= htmlspecialchars($_SESSION['register_email'] ?? '') ?></strong>
                        </p>

                        <div class="form-group">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">No. HP</label>
                            <input type="tel" name="no_hp" class="form-control" placeholder="081234567890">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter"
                                required minlength="6">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password *</label>
                            <input type="password" name="confirm_password" class="form-control"
                                placeholder="Ulangi password" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </button>
                    </form>

                <?php else: ?>
                    <!-- Done -->
                    <div style="text-align: center; padding: 30px 0;">
                        <i class="fas fa-check-circle"
                            style="font-size: 4rem; color: var(--accent-green); margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 10px;">Registrasi Berhasil!</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 25px;">Akun Anda telah dibuat. Silakan login.
                        </p>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login Sekarang
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($step != 'done'): ?>
                    <div class="auth-footer">
                        <p>Sudah punya akun? <a href="login.php">Login Sekarang</a></p>
                        <p style="margin-top: 10px;"><a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke
                                Home</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (isset($_GET['reset'])):
        unset($_SESSION['register_email']);
        unset($_SESSION['register_step']);
        header('Location: register.php');
        exit;
    endif; ?>

    <script src="../assets/js/main.js"></script>
</body>

</html>