<?php
session_start();
require_once '../database/koneksi.php';
require_once '../database/email_config.php';

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';
$step = $_SESSION['reset_step'] ?? 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Step 1: Request OTP
    if ($action === 'request_otp') {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid!';
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, nama FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'Email tidak terdaftar dalam sistem!';
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

                // Send OTP email for password reset
                $emailResult = sendResetPasswordEmail($email, $otp, $user['nama']);
                
                // Store email in session for next step
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_step'] = 2;
                $step = 2;
                
                if ($emailResult['success']) {
                    $success = "Kode OTP telah dikirim ke {$email}. Silakan cek inbox/spam.";
                } else {
                    $success = "Kode OTP telah dikirim ke {$email}. (Debug: {$otp})";
                }
            }
        }
    }

    // Resend OTP
    elseif ($action === 'resend_otp') {
        $email = $_SESSION['reset_email'] ?? '';
        
        if (empty($email)) {
            $error = 'Sesi expired. Silakan ulangi dari awal.';
            $_SESSION['reset_step'] = 1;
            $step = 1;
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id, nama FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate new OTP
                $otp = sprintf("%06d", mt_rand(0, 999999));
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                // Delete old OTPs for this email
                $stmt = $pdo->prepare("DELETE FROM otp_verification WHERE email = ?");
                $stmt->execute([$email]);

                // Insert new OTP
                $stmt = $pdo->prepare("INSERT INTO otp_verification (email, otp_code, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $otp, $expires_at]);

                // Send OTP email
                $emailResult = sendResetPasswordEmail($email, $otp, $user['nama']);
                
                $step = 2;
                if ($emailResult['success']) {
                    $success = "Kode OTP baru telah dikirim ke {$email}. Silakan cek inbox/spam.";
                } else {
                    $success = "Kode OTP baru telah dikirim ke {$email}. (Debug: {$otp})";
                }
            } else {
                $error = 'Email tidak ditemukan.';
            }
        }
    }

    // Step 2: Verify OTP
    elseif ($action === 'verify_otp') {
        $otp_input = trim($_POST['otp'] ?? '');
        $email = $_SESSION['reset_email'] ?? '';

        if (empty($email)) {
            $error = 'Sesi expired. Silakan ulangi dari awal.';
            $_SESSION['reset_step'] = 1;
            $step = 1;
        } else {
            $current_time = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("SELECT * FROM otp_verification WHERE email = ? AND otp_code = ? AND is_used = 0 AND expires_at > ?");
            $stmt->execute([$email, $otp_input, $current_time]);
            $otp_record = $stmt->fetch();

            if ($otp_record) {
                // Mark OTP as used
                $stmt = $pdo->prepare("UPDATE otp_verification SET is_used = 1 WHERE id = ?");
                $stmt->execute([$otp_record['id']]);

                $_SESSION['reset_step'] = 3;
                $_SESSION['reset_verified'] = true;
                $step = 3;
                $success = 'Verifikasi berhasil! Silakan buat password baru.';
            } else {
                // Debugging untuk pesan error yang lebih jelas
                $stmt = $pdo->prepare("SELECT *, expires_at > ? as not_expired FROM otp_verification WHERE email = ? ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([$current_time, $email]);
                $debug = $stmt->fetch();
                
                if (!$debug) {
                    $error = 'Kode OTP tidak ditemukan. Silakan minta OTP baru.';
                } elseif ($debug['otp_code'] !== $otp_input) {
                    $error = 'Kode OTP salah! Pastikan memasukkan kode yang benar.';
                } elseif ($debug['is_used'] == 1) {
                    $error = 'Kode OTP sudah digunakan. Silakan minta OTP baru.';
                } elseif (!$debug['not_expired']) {
                    $error = 'Kode OTP sudah expired. Silakan minta OTP baru.';
                } else {
                    $error = 'Kode OTP tidak valid!';
                }
            }
        }
    }

    // Step 3: Reset Password
    elseif ($action === 'reset_password') {
        $email = $_SESSION['reset_email'] ?? '';
        $verified = $_SESSION['reset_verified'] ?? false;
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($email) || !$verified) {
            $error = 'Sesi expired. Silakan ulangi dari awal.';
            unset($_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_verified']);
            $step = 1;
        } elseif (strlen($password) < 6) {
            $error = 'Password minimal 6 karakter!';
        } elseif ($password !== $confirm_password) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            
            if ($stmt->execute([$hashedPassword, $email])) {
                // Log aktivitas
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user) {
                    logAktivitas($pdo, $user['id'], 'Reset Password', 'User berhasil reset password');
                }
                
                // Clear session
                unset($_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_verified']);
                
                $success = 'Password berhasil diubah! Silakan login dengan password baru.';
                $step = 'done';
            } else {
                $error = 'Gagal mengubah password. Silakan coba lagi.';
            }
        }
    }
}

// Handle reset link
if (isset($_GET['reset'])) {
    unset($_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_verified']);
    header('Location: forgot_password.php');
    exit;
}

/**
 * Fungsi untuk mengirim email reset password
 */
function sendResetPasswordEmail($email, $otp, $nama) {
    $subject = "Reset Password - Marvell Rental";
    
    $body = "
    <html>
    <head>
        <title>Reset Password</title>
    </head>
    <body style='font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0;'>
        <div style='max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); padding: 30px; text-align: center;'>
                <h1 style='color: #C9A100; margin: 0; font-size: 28px;'>🏍️ Marvell Rental</h1>
                <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 14px;'>Reset Password</p>
            </div>
            
            <div style='padding: 40px 30px;'>
                <p style='color: #333; font-size: 16px; margin: 0 0 20px 0;'>Halo <strong>{$nama}</strong>,</p>
                <p style='color: #666; font-size: 14px; line-height: 1.6; margin: 0 0 25px 0;'>
                    Kami menerima permintaan untuk reset password akun Anda. Gunakan kode OTP berikut untuk melanjutkan:
                </p>
                
                <div style='background: linear-gradient(135deg, #f8f8f8 0%, #e8e8e8 100%); border-radius: 10px; padding: 25px; text-align: center; margin: 0 0 25px 0;'>
                    <h2 style='margin: 0; font-size: 36px; letter-spacing: 8px; color: #1a1a1a; font-weight: bold;'>{$otp}</h2>
                </div>
                
                <p style='color: #999; font-size: 13px; margin: 0 0 10px 0;'>
                    ⏰ Kode ini berlaku selama <strong>15 menit</strong>
                </p>
                <p style='color: #999; font-size: 13px; margin: 0;'>
                    🔒 Jangan bagikan kode ini kepada siapapun
                </p>
            </div>
            
            <div style='background: #f5f5f5; padding: 20px; text-align: center; border-top: 1px solid #eee;'>
                <p style='color: #999; font-size: 12px; margin: 0;'>
                    Jika Anda tidak meminta reset password, abaikan email ini.
                </p>
                <p style='color: #C9A100; font-size: 11px; margin: 10px 0 0 0;'>
                    © 2024 Marvell Rental. All Rights Reserved.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $body);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Marvell Rental</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo">MR</div>
                    <h1 class="auth-title">Lupa Password</h1>
                    <p class="auth-subtitle">
                        <?php if ($step == 1): ?>
                            Masukkan email untuk reset password
                        <?php elseif ($step == 2): ?>
                            Masukkan kode OTP
                        <?php elseif ($step == 3): ?>
                            Buat password baru
                        <?php else: ?>
                            Password berhasil diubah!
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
                    <!-- Step 1: Email -->
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="request_otp">

                        <div class="form-group">
                            <label class="form-label">Email Terdaftar *</label>
                            <input type="email" name="email" class="form-control" placeholder="Masukkan email terdaftar"
                                required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
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
                                style="color: var(--primary);"><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>
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
                            <a href="?reset=1" style="color: var(--text-secondary); font-size: 0.9rem;">← Gunakan email lain</a>
                        </p>
                    </form>
                    
                    <form method="POST" action="" style="margin-top: 10px;" id="resendForm">
                        <input type="hidden" name="action" value="resend_otp">
                        <p style="text-align: center;">
                            <button type="submit" id="resendBtn" style="background: none; border: none; color: var(--primary); cursor: pointer; font-size: 0.9rem; text-decoration: underline;">
                                <i class="fas fa-redo"></i> <span id="resendText">Kirim Kode OTP Lagi</span>
                            </button>
                        </p>
                    </form>
                    
                    <script>
                    (function() {
                        const COOLDOWN_SECONDS = 60;
                        const storageKey = 'reset_otp_cooldown';
                        const btn = document.getElementById('resendBtn');
                        const text = document.getElementById('resendText');
                        
                        function startCooldown(seconds) {
                            btn.disabled = true;
                            btn.style.color = 'var(--text-secondary)';
                            btn.style.cursor = 'not-allowed';
                            btn.style.textDecoration = 'none';
                            
                            const endTime = Date.now() + (seconds * 1000);
                            localStorage.setItem(storageKey, endTime);
                            
                            const interval = setInterval(() => {
                                const remaining = Math.ceil((endTime - Date.now()) / 1000);
                                if (remaining <= 0) {
                                    clearInterval(interval);
                                    localStorage.removeItem(storageKey);
                                    btn.disabled = false;
                                    btn.style.color = 'var(--primary)';
                                    btn.style.cursor = 'pointer';
                                    btn.style.textDecoration = 'underline';
                                    text.innerHTML = 'Kirim Kode OTP Lagi';
                                } else {
                                    text.innerHTML = 'Tunggu ' + remaining + ' detik';
                                }
                            }, 1000);
                        }
                        
                        // Check existing cooldown
                        const savedEndTime = localStorage.getItem(storageKey);
                        if (savedEndTime) {
                            const remaining = Math.ceil((parseInt(savedEndTime) - Date.now()) / 1000);
                            if (remaining > 0) {
                                startCooldown(remaining);
                            } else {
                                localStorage.removeItem(storageKey);
                            }
                        }
                        
                        // Start cooldown on form submit
                        document.getElementById('resendForm').addEventListener('submit', function() {
                            startCooldown(COOLDOWN_SECONDS);
                        });
                    })();
                    </script>

                <?php elseif ($step == 3): ?>
                    <!-- Step 3: New Password -->
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="reset_password">

                        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
                            Email: <strong
                                style="color: var(--primary);"><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>
                        </p>

                        <div class="form-group">
                            <label class="form-label">Password Baru *</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter"
                                required minlength="6">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password *</label>
                            <input type="password" name="confirm_password" class="form-control"
                                placeholder="Ulangi password baru" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </form>

                <?php else: ?>
                    <!-- Done -->
                    <div style="text-align: center; padding: 30px 0;">
                        <i class="fas fa-check-circle"
                            style="font-size: 4rem; color: var(--accent-green); margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 10px;">Password Berhasil Diubah!</h3>
                        <p style="color: var(--text-secondary); margin-bottom: 25px;">Silakan login dengan password baru
                            Anda.</p>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login Sekarang
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($step != 'done'): ?>
                    <div class="auth-footer">
                        <p>Sudah ingat password? <a href="login.php">Login Sekarang</a></p>
                        <p style="margin-top: 10px;"><a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke
                                Home</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>
