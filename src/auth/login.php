<?php

// === Load ENV langsung di sini ===
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$APP_URL = rtrim($_ENV['APP_URL'], '/');

$remember = '';
$errorMsg = '';
if (isset($_SESSION['login_error'])) {
    $errorMsg = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}
?>

<section class="min-h-screen bg-gradient-to-b from-[#0c0d0f] via-[#0e0f11] to-[#1a1b1f] flex items-center justify-center px-6 py-12">
  <div class="relative w-full max-w-md bg-[#18191c]/80 border border-gray-800 rounded-2xl shadow-[0_0_30px_rgba(255,255,255,0.05)] p-8 backdrop-blur-sm overflow-hidden">

    <!-- Efek cahaya latar belakang -->
    <div class="absolute -top-24 -left-16 w-72 h-72 bg-orange-600/30 blur-3xl rounded-full"></div>
    <div class="absolute -bottom-24 -right-16 w-72 h-72 bg-red-600/30 blur-3xl rounded-full"></div>

    <div class="relative z-10">
      <div class="flex flex-col items-center mb-6">
        <a href="<?= $APP_URL ?>">
          <img src="<?= $APP_URL ?>/img/favicon.png" alt="logo" class="w-24 mb-3 animate-pulse">
        </a>
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
          Masuk ke <?= htmlspecialchars($_ENV['APP_NAME']) ?>
        </h1>
        <p class="text-gray-400 text-sm mt-2">Nikmati hiburan tanpa batas 🎬</p>
      </div>

      <?php if ($errorMsg): ?>
        <div class="p-3 mb-5 text-sm text-red-400 border border-red-600 rounded-lg bg-red-900/20">
          <?= $errorMsg ?>
        </div>
      <?php endif; ?>

      <!-- Form Login Email/Password -->
      <form action="<?= $APP_URL ?>/src/auth/login_action.php" method="POST" class="space-y-6">
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

        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2 text-gray-400">
            <input 
              type="checkbox" 
              name="remember" 
              value="1" 
              class="accent-orange-500 w-4 h-4 rounded focus:ring-orange-400"
              <?php if ($remember == '1') echo "checked"?>>
            Ingat Saya
          </label>
          <a href="<?= $APP_URL ?>/lupa-password" class="text-orange-400 hover:text-orange-300 transition">Lupa Password?</a>
        </div>

        <button 
          type="submit" 
          class="w-full py-3 font-semibold text-white rounded-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 transition-transform transform hover:scale-[1.02] shadow-lg">
          Masuk Sekarang
        </button>
      </form>

      <!-- Login dengan Google -->
      <div class="mt-6 space-y-3">
        <a href="<?= $APP_URL ?>/utils/google/redirect.php" class="w-full flex items-center justify-center gap-3 py-3 rounded-lg bg-white text-gray-900 font-medium hover:shadow-lg transition">
          <img src="<?= $APP_URL ?>/img/google-icon.png" alt="Google" class="w-5 h-5">
          Masuk dengan Google
        </a>

        <p class="text-sm text-center text-gray-400 mt-4">
          Belum punya akun?
          <a href="<?= $APP_URL ?>/register" class="text-orange-400 hover:text-orange-300 font-medium transition">Daftar Sekarang</a>
        </p>
      </div>
    </div>
  </div>
</section>
