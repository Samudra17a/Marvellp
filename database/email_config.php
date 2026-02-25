<?php
// Konfigurasi SMTP Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'siyoshh21@gmail.com');
define('SMTP_PASSWORD', 'yvajfmpvmoomezud'); // Isi dengan App Password Gmail Anda
define('SMTP_FROM_EMAIL', 'siyoshh21@gmail.com');
define('SMTP_FROM_NAME', 'Marvell Rental');

/**
 * Fungsi untuk mengirim email menggunakan PHPMailer
 * @param string $to Email tujuan
 * @param string $subject Subject email
 * @param string $body Isi email (HTML)
 * @return array ['success' => bool, 'message' => string]
 */
function sendEmail($to, $subject, $body) {
    // Cek apakah PHPMailer tersedia
    $phpmailer_path = __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
    
    if (file_exists($phpmailer_path)) {
        // Menggunakan PHPMailer
        require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
            
            $mail->send();
            return ['success' => true, 'message' => 'Email berhasil dikirim'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gagal mengirim email: ' . $mail->ErrorInfo];
        }
        
    } else {
        // Fallback: Menggunakan mail() PHP native
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        
        if (@mail($to, $subject, $body, $headers)) {
            return ['success' => true, 'message' => 'Email berhasil dikirim (native mail)'];
        } else {
            return ['success' => false, 'message' => 'Gagal mengirim email. Pastikan server mail terkonfigurasi.'];
        }
    }
}

/**
 * Fungsi khusus untuk mengirim OTP
 * @param string $email Email tujuan
 * @param string $otp Kode OTP
 * @return array
 */
function sendOTPEmail($email, $otp) {
    $subject = "Kode OTP Registrasi - Marvell Rental";
    
    $body = "
    <html>
    <head>
        <title>Kode Verifikasi OTP</title>
    </head>
    <body style='font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0;'>
        <div style='max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(135deg, #1a1a1a 0%, #333 100%); padding: 30px; text-align: center;'>
                <h1 style='color: #BF3131; margin: 0; font-size: 28px;'>🏍️ Marvell Rental</h1>
                <p style='color: #ffffff; margin: 10px 0 0 0; font-size: 14px;'>Verifikasi Email Anda</p>
            </div>
            
            <div style='padding: 40px 30px;'>
                <p style='color: #333; font-size: 16px; margin: 0 0 20px 0;'>Halo,</p>
                <p style='color: #666; font-size: 14px; line-height: 1.6; margin: 0 0 25px 0;'>
                    Terima kasih telah mendaftar di Marvell Rental. Gunakan kode OTP berikut untuk memverifikasi email Anda:
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
                    Jika Anda tidak melakukan registrasi, abaikan email ini.
                </p>
                <p style='color: #BF3131; font-size: 11px; margin: 10px 0 0 0;'>
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
