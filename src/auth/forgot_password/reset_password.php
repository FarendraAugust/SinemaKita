<?php

// === LOAD ENV ===
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

include 'utils/connection.php';

$token = $_GET['token'] ?? '';

$stmt = $connection->prepare("SELECT id_pengguna FROM pengguna WHERE reset_token=? AND reset_expire>NOW() LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<div style='color:red;font-weight:bold;'>Token tidak valid atau sudah kadaluarsa.</div>";
    exit();
}

$errorMsg = $_SESSION['forgot_error'] ?? '';
unset($_SESSION['forgot_error']);

// Ambil URL dari .env
$APP_URL = rtrim($_ENV['APP_URL'], '/');
?>
<section class="min-h-screen bg-gradient-to-b from-[#0c0d0f] via-[#0e0f11] to-[#1a1b1f] flex items-center justify-center px-6 py-12">
  <div class="relative w-full max-w-md bg-[#18191c]/80 border border-gray-800 rounded-2xl shadow-[0_0_30px_rgba(255,255,255,0.05)] p-8 backdrop-blur-sm overflow-hidden">
    <div class="relative z-10">
      <div class="flex flex-col items-center mb-6">
        <img src="<?= $APP_URL ?>/img/favicon.png" alt="logo" class="w-24 mb-3 animate-pulse">
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
          Reset Password
        </h1>
        <p class="text-gray-400 text-sm mt-2">Masukkan password baru 🎬</p>
      </div>

      <?php if ($errorMsg): ?>
        <div class="p-3 mb-4 text-sm text-red-400 border border-red-600 rounded-lg bg-red-900/20">
          <?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <form action="<?= $APP_URL ?>/src/auth/forgot_password/reset_password_action.php" method="post" class="space-y-4">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        
        <div>
          <label class="block text-sm text-gray-300 mb-1">Password Baru</label>
          <input type="password" name="password" required class="w-full px-4 py-2 bg-[#101113] border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
        </div>
        
        <div>
          <label class="block text-sm text-gray-300 mb-1">Konfirmasi Password</label>
          <input type="password" name="confirm_password" required class="w-full px-4 py-2 bg-[#101113] border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
        </div>

        <button type="submit" class="w-full py-3 font-semibold text-white rounded-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 transition-transform transform hover:scale-[1.02] shadow-lg">
          Reset Password
        </button>
      </form>
    </div>
  </div>
</section>
