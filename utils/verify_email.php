<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . '/vendor/autoload.php';

function sendVerificationEmail($email, $name, $token) {
    echo "<!-- verify_email.php loaded -->";

    $mail = new PHPMailer(true);

    try {
        // Konfigurasi SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = $_ENV['DB_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD']; // Gmail App Password
        $mail->SMTPSecure = ($_ENV['MAIL_ENCRYPTION'] === 'ssl')
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $_ENV['MAIL_PORT'];

        // SSL opsional untuk Laragon
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Pengirim & penerima
        $mail->setFrom($_ENV['MAIL_USERNAME'], $_ENV['APP_NAME']);
        $mail->addAddress($email, $name);
        $mail->addReplyTo($_ENV['MAIL_USERNAME'], 'No Reply');

        // Link verifikasi
        $verifyLink = $_ENV['APP_URL'] . "verifikasi?email=" . urlencode($email) . "&token=" . urlencode($token);

        // Subjek
        $mail->Subject = 'Verifikasi Akun Anda di ' . $_ENV['APP_NAME'];

        // Body HTML — versi dark elegan
        $mail->isHTML(true);
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

                <!-- HEADER -->
                <div style='background:linear-gradient(90deg, #ff7e5f, #e50914); padding:25px; text-align:center;'>
                    <h1 style='margin:0;color:white;font-size:26px; letter-spacing:1px;'>🎬 SinemaKita</h1>
                </div>

                <!-- BODY -->
                <div style='padding:40px;'>
                    <h2 style='margin-top:0;color:#fff;'>Hai, $name 👋</h2>
                    <p style='color:#ccc;font-size:15px;line-height:1.6;margin-bottom:30px;'>
                        Terima kasih telah bergabung di <b>SinemaKita</b>!  
                        Sebelum mulai menonton ribuan film favoritmu, yuk verifikasi akun kamu dulu.
                    </p>

                    <div style='text-align:center;margin:35px 0;'>
                        <a href='$verifyLink' 
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
                        ✅ Verifikasi Sekarang
                        </a>
                    </div>

                    <p style='font-size:14px;color:#999;line-height:1.6; text-align:center;'>
                        Jika tombol di atas tidak berfungsi, salin dan buka tautan ini di browser Anda:
                    </p>
                    <p style='color:#ddd;font-size:13px;word-break:break-all;background:#111;padding:12px;border-radius:8px;text-align:center;'>
                        $verifyLink
                    </p>

                    <p style='font-size:13px;color:#777;margin-top:30px;text-align:center;'>
                        Link ini berlaku selama <b>7 hari</b>.<br>
                        Jika Anda tidak merasa mendaftar, abaikan email ini.
                    </p>
                </div>

                <!-- FOOTER -->
                <div style='background:#111;padding:15px;text-align:center;color:#666;font-size:12px;'>
                    © " . date('Y') . " SinemaKita — Dunia Streaming Film Favoritmu
                </div>

            </div>
        </div>
        ";

        $mail->AltBody = "Halo $name,\n\nKlik link berikut untuk verifikasi akun Anda:\n$verifyLink\n\nLink berlaku selama 7 hari.";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Email verification failed: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
