<?php
// === ENV LOADER LANGSUNG DI SINI ===
$env_path = __DIR__ . '/../../.env';
if (file_exists($env_path)) {
    $env_lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($env_lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $env[$key] = trim($value, "\"'");
    }
} else {
    die("❌ File .env tidak ditemukan di: $env_path");
}

$APP_URL = $env['APP_URL'] ?? '/sinemakita';

// === SESSION & LOGIN VALIDATION ===
$menu = $_GET['menu'] ?? 'dashboard';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['session_id'])) {
    header("Location: {$APP_URL}/login");
    exit();
}

// === DATABASE CONNECTION ===
$connection = mysqli_connect(
    $env['DB_HOST'] ?? 'localhost',
    $env['DB_USER'] ?? 'root',
    $env['DB_PASS'] ?? '',
    $env['DB_NAME'] ?? 'sinemakita'
);

if (mysqli_connect_errno()) {
    die("❌ Koneksi database gagal: " . mysqli_connect_error());
}

// === MENU ADMIN ===
$menus = [
    'dashboard' => 'Dashboard',
    'pengguna'  => 'Pengguna',
    'tag'       => 'Tag',
    'film'      => 'Film',
    'review'    => 'Review',
    'episode'   => 'Episode',
    'banner'    => 'Banner'
];

// === USER INFO ===
$id_pengguna = $_SESSION['session_id'];
$user = null;
if ($id_pengguna) {
    $stmt = $connection->prepare("SELECT nama_pengguna, email_pengguna, profile_img, provider FROM pengguna WHERE id_pengguna = ?");
    $stmt->bind_param("i", $id_pengguna);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!-- Sidebar -->
<aside class="w-64 flex-none h-screen bg-[#18191c] border-r border-gray-800 shadow-lg flex flex-col fixed left-0 top-0 justify-between">
  
  <!-- Atas: Logo + Menu -->
  <div>
    <!-- Logo -->
    <div class="border-b border-gray-700 relative">
      <div class="absolute -top-12 -left-8 w-40 h-40 bg-orange-600/30 blur-3xl rounded-full"></div>
      <div class="absolute -bottom-12 -right-8 w-40 h-40 bg-red-600/30 blur-3xl rounded-full"></div>

      <a href="#" class="relative flex items-center gap-3 font-bold px-4 py-4 z-10">
        <img src="<?= $APP_URL ?>/img/favicon.png" alt="logo" class="w-12 h-12 rounded animate-pulse">
        <span class="text-lg bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">Admin Dashboard</span>
      </a>
    </div>

    <!-- Menu -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto relative z-10">
      <?php foreach ($menus as $key => $label): ?>
        <a 
          href="<?= $APP_URL ?><?= $key === 'dashboard' ? '/admin' : '/admin/' . $key ?>" 
          class="block px-4 py-2 rounded font-medium transition
            <?= ($menu === $key ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white' : 'text-gray-300 hover:bg-[#2a2b2e] hover:text-white') ?>">
          <?= htmlspecialchars($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </div>

  <!-- Bawah: Profil + Logout -->
  <?php if ($user): ?>
  <div class="border-t border-gray-700 p-4 flex items-center gap-3 mb-5">
    
    <!-- Foto profil -->
    <?php if ($user['provider'] === 'google' && filter_var($user['profile_img'], FILTER_VALIDATE_URL)): ?>
      <img src="<?= htmlspecialchars($user['profile_img']) ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover border border-gray-600 flex-shrink-0">
    <?php elseif ($user['provider'] === 'default' && !empty($user['profile_img'])): ?>
      <img src="<?= $APP_URL ?>/assets/profile/<?= htmlspecialchars($user['profile_img']) ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover border border-gray-600 flex-shrink-0">
    <?php else: ?>
      <div class="w-12 h-12 rounded-full bg-gray-700 flex items-center justify-center text-gray-300 font-bold flex-shrink-0">
        <?= strtoupper(substr($user['nama_pengguna'], 0, 1)); ?>
      </div>
    <?php endif; ?>

    <!-- Info user -->
    <div class="flex-1 min-w-0">
      <p class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($user['nama_pengguna']); ?></p>
      <a href="<?= $APP_URL ?>/src/auth/logout_action.php" class="text-red-400 hover:text-red-300 text-xs mt-1 inline-block">Logout</a>
    </div>

  </div>
  <?php endif; ?>
</aside>
