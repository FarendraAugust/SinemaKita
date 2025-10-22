<?php

// Pastikan user sudah login
if (!isset($_SESSION['session_username'])) {
  header('Location: ' . $_ENV['APP_URL'] . '/login');
  exit();
}

// Ambil data pengguna dari database
$id = $_SESSION['session_id'];

$stmt = $connection->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Hitung total review pengguna
$stmt = $connection->prepare("SELECT COUNT(*) AS total_review FROM review WHERE id_pengguna = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc();
$totalReview = $total['total_review'] ?? 0;
$stmt->close();

// Hitung rata-rata rating pengguna
$stmt = $connection->prepare("SELECT AVG(rating) AS avg_rating FROM review WHERE id_pengguna = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$average = $stmt->get_result()->fetch_assoc();
$avgRating = round($average['avg_rating'] ?? 0, 2);
$stmt->close();
?>

<div class="max-w-3xl mx-auto mt-24 px-6 py-10 bg-gradient-to-b from-[#1a1b1e] to-[#0f1012] rounded-2xl shadow-[0_0_30px_rgba(255,255,255,0.05)] text-white relative overflow-hidden">

  <!-- Elemen dekorasi latar belakang -->
  <div class="absolute inset-0 pointer-events-none opacity-20">
    <div class="w-72 h-72 bg-orange-500/30 rounded-full blur-3xl absolute top-0 -left-16"></div>
    <div class="w-72 h-72 bg-red-500/30 rounded-full blur-3xl absolute bottom-0 right-0"></div>
  </div>

  <div class="relative z-10">
    <h2 class="text-3xl font-extrabold mb-8 bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent text-center">
      Profil Akun
    </h2>

    <!-- Profil Card -->
    <div class="flex flex-col md:flex-row items-center gap-6 md:gap-8 bg-[#131417]/60 rounded-xl p-6 border border-gray-800 shadow-inner">
      <div class="relative group">

        <?php
        // Default avatar
        $profileImg = $_ENV['APP_URL'] . "/assets/profile/default-avatar.png";

        if (!empty($user['profile_img'])) {
          if (!empty($user['provider']) && in_array($user['provider'], ['google', 'facebook'])) {
            // URL langsung dari provider (Google / Facebook)
            $profileImg = $user['profile_img'];
          } else {
            // Avatar lokal
            $profileImg = $_ENV['APP_URL'] . "/assets/profile/" . htmlspecialchars($user['profile_img']);
          }
        }
        ?>

        <img 
          src="<?= $profileImg ?>" 
          alt="Foto Profil" 
          class="w-28 h-28 rounded-full object-cover border-2 border-gray-700 group-hover:scale-105 transition-transform duration-300"
        >
        <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
          <ion-icon name="camera-outline" class="text-xl text-gray-300"></ion-icon>
        </div>
      </div>

      <div class="text-center md:text-left">
        <div class="flex items-center justify-center md:justify-start gap-2">
          <h3 class="text-2xl font-bold">
            <?= htmlspecialchars($user['nama_pengguna']); ?>
          </h3>

          <?php if ($user['verified'] == 1): ?>
            <span class="flex items-center justify-center bg-blue-600 text-white rounded-full w-5 h-5">
              <ion-icon name="checkmark-outline" class="text-xs"></ion-icon>
            </span>
          <?php else: ?>
            <span class="flex items-center justify-center bg-red-600 text-white rounded-full w-5 h-5">
              <ion-icon name="close-outline" class="text-xs"></ion-icon>
            </span>
          <?php endif; ?>
        </div>

        <p class="text-gray-400 mt-1"><?= htmlspecialchars($user['email_pengguna']); ?></p>

        <?php if ($user['verified'] == 1): ?>
          <p class="text-blue-400 text-sm font-medium mt-1 flex items-center justify-center md:justify-start gap-1">
            <ion-icon name="shield-checkmark-outline" class="text-base"></ion-icon>
            Terverifikasi
          </p>
        <?php else: ?>
          <p class="text-red-400 text-sm font-medium mt-1 flex items-center justify-center md:justify-start gap-1">
            <ion-icon name="shield-outline" class="text-base"></ion-icon>
            Belum Terverifikasi
          </p>
        <?php endif; ?>

        <div class="flex justify-center md:justify-start mt-4 gap-3">
          <a href="<?= $_ENV['APP_URL'] ?>/akun/setting" 
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 font-medium transition">
            <ion-icon name="settings-outline"></ion-icon> Edit Profil
          </a>
          <a href="<?= $_ENV['APP_URL'] ?>/src/auth/logout_action.php" 
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-[#2a2b2e] hover:bg-red-600 transition font-medium">
            <ion-icon name="log-out-outline"></ion-icon> Logout
          </a>
        </div>
      </div>
    </div>

    <!-- Statistik / Info Tambahan -->
    <div class="mt-10 grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div class="bg-[#1e1f22] p-4 rounded-lg border border-gray-800 text-center hover:bg-[#242529] transition">
        <ion-icon name="film-outline" class="text-3xl text-orange-400 mb-2"></ion-icon>
        <p class="text-sm text-gray-400">Total Review</p>
        <h4 class="text-xl font-semibold"><?= $totalReview ?></h4>
      </div>

      <div class="bg-[#1e1f22] p-4 rounded-lg border border-gray-800 text-center hover:bg-[#242529] transition">
        <ion-icon name="star-outline" class="text-3xl text-yellow-400 mb-2"></ion-icon>
        <p class="text-sm text-gray-400">Rata-rata Rating</p>
        <h4 class="text-xl font-semibold"><?= $avgRating ?></h4>
      </div>

      <div class="bg-[#1e1f22] p-4 rounded-lg border border-gray-800 text-center hover:bg-[#242529] transition">
        <ion-icon name="calendar-outline" class="text-3xl text-green-400 mb-2"></ion-icon>
        <p class="text-sm text-gray-400">Bergabung Sejak</p>
        <h4 class="text-xl font-semibold">
          <?= date('d M Y', strtotime($user['created_at'] ?? 'now')); ?>
        </h4>
      </div>
    </div>

    <!-- Tombol Kembali ke Beranda -->
    <div class="mt-10 text-center">
      <a href="<?= $_ENV['APP_URL'] ?>" 
        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 text-white font-semibold transition">
        <ion-icon name="home-outline" class="text-xl"></ion-icon>
        Kembali ke Beranda
      </a>
    </div>
  </div>
</div>
