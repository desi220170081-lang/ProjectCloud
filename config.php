<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'otpdes';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ Koneksi database gagal: " . htmlspecialchars($conn->connect_error));
}
$conn->set_charset("utf8mb4");

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("❌ File vendor/autoload.php tidak ditemukan. Jalankan 'composer install' terlebih dahulu.");
}
require $autoloadPath;

// Email
define('MAIL_FROM', 'desi.220170081@mhs.unimal.ac.id');
define('MAIL_FROM_NAME', 'Sistem Login OTP');
define('MAIL_PASS', 'luxcqomuuaiwabrm'); // App password Gmail (bukan password asli)

// OTP
function sendOTP($emailTujuan, $kodeOTP)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_FROM;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPDebug  = 0;

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($emailTujuan);
        $mail->isHTML(true);
        $mail->Subject = 'Kode OTP Login Anda';
        $verifyLink = "http://localhost/mfa_crud/verify_otp.php?email=" . urlencode($emailTujuan);
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; background-color:#f6f7fb; padding:20px;'>
            <div style='max-width:500px;margin:auto;background:#fff;border-radius:8px;
                        padding:20px;box-shadow:0 3px 10px rgba(0,0,0,0.1)'>
                <h2 style='color:#1a73e8;margin-bottom:10px;'>Verifikasi OTP Anda</h2>
                <p>Kode OTP Anda adalah:</p>
                <div style='font-size:26px;font-weight:bold;color:#1a73e8;text-align:center;margin:10px 0;'>$kodeOTP</div>
                <p>Berlaku selama <b>5 menit</b>.</p>
                <a href='$verifyLink' 
                    style='display:inline-block;background:#1a73e8;color:#fff;text-decoration:none;
                    padding:10px 20px;border-radius:6px;margin-top:10px;font-weight:bold;'>
                    Verifikasi Sekarang
                </a>
                <p style='font-size:12px;color:#888;margin-top:25px;text-align:center;'>
                    Email ini dikirim otomatis oleh sistem MFA (Multi-Factor Authentication).
                </p>
            </div>
        </body>
        </html>";
        $mail->AltBody = "Kode OTP Anda: $kodeOTP\nVerifikasi di: $verifyLink";
        $mail->send();
        return true;
    } catch (Exception $e) {
        $errorInfo = $mail->ErrorInfo ?: $e->getMessage();
        error_log("❌ Gagal kirim OTP ke $emailTujuan: $errorInfo");
        return "Gagal mengirim email: " . $errorInfo;
    }
}
?>
