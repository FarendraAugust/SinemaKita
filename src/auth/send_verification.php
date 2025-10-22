<?php

$APP_URL  = rtrim($env['APP_URL'] ?? '/sinemakita', '/');
$APP_NAME = $env['APP_NAME'] ?? 'SinemaKita';

include __DIR__ . '/../../utils/connection.php';
include __DIR__ . '/../../utils/verify_email.php';

// ✅ Pastikan pengguna login
if (!isset($_SESSION['session_id'])) {
    header("Location: {$APP_URL}/login");
    exit();
}

$user_id = $_SESSION['session_id'];

// 🔍 Ambil data pengguna
$query = $connection->prepare("
    SELECT nama_pengguna, email_pengguna, verified 
    FROM pengguna 
    WHERE id_pengguna = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$user = $query->get_result()->fetch_assoc();

if (!$user) {
    die("❌ Pengguna tidak ditemukan.");
}

$email    = $user['email_pengguna'];
$name     = $user['nama_pengguna'];
$verified = (bool) $user['verified'];

// 🕒 Cek cooldown (60 detik)
$lastKey  = 'last_verify_' . $user_id;
$cooldown = 0;
if (isset($_SESSION[$lastKey])) {
    $elapsed = time() - $_SESSION[$lastKey];
    if ($elapsed < 60) {
        $cooldown = 60 - $elapsed;
    }
}

// 🚀 Kirim ulang email verifikasi jika tombol ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$cooldown && !$verified) {
    $token  = bin2hex(random_bytes(32));
    $expire = date('Y-m-d H:i:s', strtotime('+7 days'));

    $stmt = $connection->prepare("
        UPDATE pengguna 
        SET verify_token = ?, verify_expire = ? 
        WHERE id_pengguna = ?
    ");
    $stmt->bind_param("ssi", $token, $expire, $user_id);
    $stmt->execute();

    if (sendVerificationEmail($email, $name, $token)) {
        $_SESSION[$lastKey] = time();
        $msg = "✅ Email verifikasi telah dikirim ke <b>$email</b>. Periksa inbox Anda!";
    } else {
        $msg = "❌ Gagal mengirim email verifikasi. Silakan coba lagi nanti.";
    }
}
?>

<!-- 🌙 Tampilan halaman -->
<section class="min-h-screen bg-gradient-to-b from-[#0c0d0f] via-[#0e0f11] to-[#1a1b1f] flex items-center justify-center px-6 py-12">
  <div class="relative w-full max-w-md bg-[#18191c]/80 border border-gray-800 rounded-2xl shadow-[0_0_30px_rgba(255,255,255,0.05)] p-8 backdrop-blur-sm overflow-hidden">

    <!-- Efek cahaya latar belakang -->
    <div class="absolute -top-24 -left-16 w-72 h-72 bg-orange-600/30 blur-3xl rounded-full"></div>
    <div class="absolute -bottom-24 -right-16 w-72 h-72 bg-red-600/30 blur-3xl rounded-full"></div>

    <div class="relative z-10 text-center">
      <a href="<?= $APP_URL ?>">
        <img src="<?= $APP_URL ?>/img/favicon.png" alt="logo" class="w-24 mx-auto mb-3 animate-pulse">
      </a>
      <h1 class="text-3xl font-extrabold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
        Verifikasi Akun
      </h1>
      <p class="text-gray-400 text-sm mt-2 mb-8">
        Pastikan email kamu sudah terverifikasi agar dapat menikmati seluruh fitur.
      </p>

      <!-- 📧 Form kirim ulang verifikasi -->
      <form method="POST" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-300 mb-1 text-left">Email Kamu</label>
          <input 
            type="text" 
            value="<?= htmlspecialchars($email); ?>" 
            readonly
            class="w-full px-4 py-2 bg-[#101113] border border-gray-700 text-gray-300 rounded-lg text-center focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
          >
        </div>

        <button 
          type="submit"
          class="w-full py-3 font-semibold text-white rounded-lg transition-transform transform hover:scale-[1.02] shadow-lg
          <?= $verified 
              ? 'bg-green-600 cursor-not-allowed' 
              : ($cooldown 
                  ? 'bg-gray-700 cursor-wait' 
                  : 'bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400'); ?>"

          <?= ($cooldown || $verified) ? 'disabled' : ''; ?>
        >
          <?= $verified 
              ? '✅ Sudah Diverifikasi' 
              : ($cooldown 
                  ? "⏳ Tunggu {$cooldown} detik" 
                  : '📩 Kirim Ulang Verifikasi'); ?>
        </button>
      </form>

      <!-- 🔔 Pesan notifikasi -->
      <?php if (isset($msg)): ?>
        <div class="p-3 mt-6 text-sm text-orange-300 border border-orange-600 rounded-lg bg-orange-900/20">
          <?= $msg ?>
        </div>
      <?php endif; ?>

      <!-- 💚 Pesan sukses -->
      <?php if ($verified): ?>
        <div class="mt-6 bg-green-900/20 text-green-300 px-4 py-3 rounded-lg border border-green-700">
          Akun kamu sudah terverifikasi.<br>
          Terima kasih telah menggunakan <b><?= htmlspecialchars($APP_NAME) ?></b>!
        </div>
      <?php endif; ?>

      <p class="text-xs text-gray-500 mt-8">
        Jika tidak menerima email, periksa folder <b>Spam</b> atau kirim ulang setelah 1 menit.
      </p>
    </div>
  </div>
</section>
