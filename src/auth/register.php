<?php

// ✅ Load .env langsung di sini
$env = parse_ini_file(__DIR__ . '/../../.env', true);
$APP_URL = rtrim($env['APP_URL'] ?? '/sinemakita', '/');
$APP_NAME = $env['APP_NAME'] ?? 'SinemaKita';

// ✅ Ambil pesan session
$errorMsg = $_SESSION['register_error'] ?? '';
$sccMessage = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_error'], $_SESSION['register_success']);
?>

<section class="min-h-screen bg-gradient-to-b from-[#0c0d0f] via-[#0e0f11] to-[#1a1b1f] flex items-center justify-center px-6 py-12">
  <div class="relative w-full max-w-md bg-[#18191c]/80 border border-gray-800 rounded-2xl shadow-[0_0_30px_rgba(255,255,255,0.05)] p-8 backdrop-blur-sm overflow-hidden">

    <!-- Efek cahaya latar belakang -->
    <div class="absolute -top-24 -left-16 w-72 h-72 bg-orange-600/30 blur-3xl rounded-full"></div>
    <div class="absolute -bottom-24 -right-16 w-72 h-72 bg-red-600/30 blur-3xl rounded-full"></div>

    <div class="relative z-10">
      <div class="flex flex-col items-center mb-8">
        <a href="<?= $APP_URL ?>">
          <img src="<?= $APP_URL ?>/img/favicon.png" alt="logo" class="w-24 mb-3 animate-pulse">
        </a>
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
          Daftar Akun Baru
        </h1>
        <p class="text-gray-400 text-sm mt-2">Bergabung dan nikmati dunia hiburan <?= htmlspecialchars($APP_NAME) ?> 🎬</p>
      </div>

      <?php if ($errorMsg): ?>
        <div class="p-3 mb-5 text-sm text-red-400 border border-red-600 rounded-lg bg-red-900/20">
          <?= $errorMsg ?>
        </div>
      <?php endif; ?>

      <?php if ($sccMessage): ?>
        <div class="p-3 mb-5 text-sm text-green-400 border border-green-600 rounded-lg bg-green-900/20">
          <?= $sccMessage ?>
        </div>
      <?php endif; ?>

      <form action="<?= $APP_URL ?>/src/auth/register_action.php" method="POST" class="space-y-6">
        <div>
          <label for="username" class="block text-sm font-medium text-gray-300 mb-1">Nama Pengguna</label>
          <input 
            type="text" 
            name="username" 
            id="username" 
            placeholder="Nama Kamu"
            class="w-full px-4 py-2 bg-[#101113] border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
            required
          >
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
          <input 
            type="email" 
            name="email" 
            id="email" 
            placeholder="emailkamu@email.com"
            class="w-full px-4 py-2 bg-[#101113] border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
            required
          >
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Kata Sandi</label>
          <input 
            type="password" 
            name="password" 
            id="password" 
            placeholder="••••••••"
            class="w-full px-4 py-2 bg-[#101113] border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
            required
          >
        </div>

        <div>
          <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Konfirmasi Kata Sandi</label>
          <input 
            type="password" 
            name="confirm_password" 
            id="confirm_password" 
            placeholder="••••••••"
            class="w-full px-4 py-2 bg-[#101113] border border-gray-700 text-white rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
            required
          >
        </div>

        <button 
          type="submit" 
          class="w-full py-3 font-semibold text-white rounded-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 transition-transform transform hover:scale-[1.02] shadow-lg">
          Buat Akun Sekarang
        </button>

        <p class="text-sm text-center text-gray-400">
          Sudah punya akun?
          <a href="<?= $APP_URL ?>/login" class="text-orange-400 hover:text-orange-300 font-medium transition">Masuk Sekarang</a>
        </p>
      </form>
    </div>
  </div>
</section>
