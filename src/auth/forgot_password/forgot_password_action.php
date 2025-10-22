<?php
session_start();

date_default_timezone_set('Asia/Jakarta'); // ganti sesuai lokasi

include '../../../utils/connection.php';
require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv; // tambahkan ini

// 🔹 Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

$email = $_POST['email'];

// Cek apakah email ada
$stmt = $connection->prepare("SELECT id_pengguna FROM pengguna WHERE email_pengguna=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['forgot_error'] = "Email tidak terdaftar!";
    header("Location: /sinemakita?pege=lupa_password");
    exit();
}

// Buat token reset
$token = bin2hex(random_bytes(16));
$expire = date("Y-m-d H:i:s", strtotime("+1 hour")); // optional

// Simpan token & expire ke database
$update = $connection->prepare("UPDATE pengguna SET reset_token=?, reset_expire=? WHERE id_pengguna=?");
$update->bind_param("ssi", $token, $expire, $user['id_pengguna']);
$update->execute();

// Kirim email reset
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = ($_ENV['MAIL_ENCRYPTION'] === 'ssl')
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['MAIL_PORT'];

    $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    $mail->addAddress($email);
    $mail->addReplyTo('no-reply@sinemakita.id', 'No Reply');

    $mail->isHTML(true);
    $mail->Subject = 'Reset Password Anda';

    // Link reset
    $link = $_ENV['APP_URL'] . "/reset-password?token=$token";

    // Body email dengan tema
    $mail->Body = "
    <div style='
        background-color:#0c0d0f;
        font-family:Segoe UI,Arial,sans-serif;
        color:#f1f1f1;
        margin:0;
        padding:40px 0;
    '>
        <div style='
            max-width:600px;
            background:#18191c;
            margin:auto;
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 0 30px rgba(0,0,0,0.6);
        '>

            <div style='background:linear-gradient(90deg, #ff7e5f, #e50914); padding:25px; text-align:center;'>
                <h1 style='margin:0;color:white;font-size:26px; letter-spacing:1px;'>🎬 SinemaKita</h1>
            </div>

            <div style='padding:40px;'>
                <h2 style='margin-top:0;color:#fff;'>Hai, $name 👋</h2>
                <p style='color:#ccc;font-size:15px;line-height:1.6;margin-bottom:30px;'>
                    Kamu meminta reset password untuk akun <b>SinemaKita</b>.<br>
                    Klik tombol di bawah untuk mengatur password baru.
                </p>

                <div style='text-align:center;margin:35px 0;'>
                    <a href='$link' 
                    style='
                        background: linear-gradient(90deg, #ff7e5f, #e50914);
                        color:white;
                        padding:16px 30px;
                        text-decoration:none;
                        font-weight:bold;
                        font-size:16px;
                        border-radius:10px;
                        box-shadow:0 5px 15px rgba(229,9,20,0.4);
                        display:inline-block;
                    '>
                    🔑 Reset Password
                    </a>
                </div>

                <p style='font-size:14px;color:#999;line-height:1.6; text-align:center;'>
                    Jika tombol di atas tidak berfungsi, salin dan buka tautan ini di browser:
                </p>
                <p style='color:#ddd;font-size:13px;word-break:break-all;background:#111;padding:12px;border-radius:8px;text-align:center;'>
                    $link
                </p>

                <p style='font-size:13px;color:#777;margin-top:30px;text-align:center;'>
                    Link ini berlaku selama <b>1 jam</b>.<br>
                    Jika kamu tidak merasa meminta reset password, abaikan email ini.
                </p>
            </div>

            <div style='background:#111;padding:15px;text-align:center;color:#666;font-size:12px;'>
                © " . date('Y') . " SinemaKita — Dunia Streaming Film Favoritmu
            </div>
        </div>
    </div>
    ";

    $mail->send();
    $_SESSION['forgot_success'] = "Link reset password telah dikirim ke email Anda!";
    header("Location: /sinemakita?pege=lupa_password");
    exit();
} catch (Exception $e) {
    $_SESSION['forgot_error'] = "Gagal mengirim email: " . $mail->ErrorInfo;
    header("Location: /sinemakita?pege=lupa_password");
    exit();
}
?>
