<?php
include 'utils/connection.php';

// Ambil APP_URL dari .env
$app_url = rtrim(getenv('APP_URL') ?: '/sinemakita', '/');

// Ambil total masing-masing data
$q_users = mysqli_query($connection, "SELECT COUNT(*) as total FROM pengguna");
$total_user = mysqli_fetch_assoc($q_users)['total'];

$q_film = mysqli_query($connection, "SELECT COUNT(*) as total FROM film");
$total_film = mysqli_fetch_assoc($q_film)['total'];

$q_review = mysqli_query($connection, "SELECT COUNT(*) as total FROM review");
$total_review = mysqli_fetch_assoc($q_review)['total'];

$q_tag = mysqli_query($connection, "SELECT COUNT(*) as total FROM tag");
$total_tag = mysqli_fetch_assoc($q_tag)['total'];

$q_banner = mysqli_query($connection, "SELECT COUNT(*) as total FROM banner");
$total_banner = mysqli_fetch_assoc($q_banner)['total'];

$q_episode = mysqli_query($connection, "SELECT COUNT(*) as total FROM episode");
$total_episode = mysqli_fetch_assoc($q_episode)['total'];

$total_server = 1; // sementara 1 server aktif
?>

<div class="h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-8 overflow-y-auto">
    <!-- Header -->
    <header class="flex justify-between items-center mb-8">
      <h1 class="text-4xl font-extrabold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent mb-6">
        Dashboard
      </h1>
    </header>

    <!-- Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">

      <?php
      $cards = [
        ['label' => 'Total Pengguna', 'count' => $total_user, 'icon' => 'fa-users', 'color' => 'from-purple-600 to-indigo-500'],
        ['label' => 'Total Film', 'count' => $total_film, 'icon' => 'fa-film', 'color' => 'from-green-500 to-emerald-400'],
        ['label' => 'Total Review', 'count' => $total_review, 'icon' => 'fa-star', 'color' => 'from-yellow-400 to-orange-500'],
        ['label' => 'Total Tag', 'count' => $total_tag, 'icon' => 'fa-tag', 'color' => 'from-pink-500 to-purple-500'],
        ['label' => 'Total Banner', 'count' => $total_banner, 'icon' => 'fa-image', 'color' => 'from-blue-500 to-cyan-400'],
        ['label' => 'Total Episode', 'count' => $total_episode, 'icon' => 'fa-film', 'color' => 'from-indigo-500 to-purple-400'],
        ['label' => 'Server Aktif', 'count' => $total_server, 'icon' => 'fa-server', 'color' => 'from-red-500 to-yellow-500'],
      ];

      foreach ($cards as $c):
      ?>
        <div class="bg-[#1b1c1f] p-8 rounded-2xl shadow-xl hover:shadow-2xl transition transform hover:scale-105 border border-[#2a2b2f]">
          <div class="flex items-center space-x-6">
            <div class="p-5 rounded-full bg-gradient-to-r <?= $c['color']; ?> text-white shadow-lg">
              <i class="fas <?= $c['icon']; ?> text-3xl"></i>
            </div>
            <div>
              <p class="text-gray-400 text-sm uppercase tracking-wide"><?= $c['label']; ?></p>
              <h2 class="text-3xl font-bold mt-1"><?= $c['count']; ?></h2>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

    </div>

    <!-- Table Users -->
    <div class="bg-[#1b1c1f] rounded-2xl shadow-xl overflow-hidden border border-[#2a2b2f] mt-6">
      <table class="w-full text-left border-collapse">
        <thead class="bg-[#2a2b2f]">
          <tr>
            <th class="px-6 py-3 text-gray-400 uppercase text-xs tracking-wider">No</th>
            <th class="px-6 py-3 text-gray-400 uppercase text-xs tracking-wider">Nama</th>
            <th class="px-6 py-3 text-gray-400 uppercase text-xs tracking-wider">Email</th>
            <th class="px-6 py-3 text-gray-400 uppercase text-xs tracking-wider">Role</th>
          </tr>
        </thead>
        <tbody>
          <?php 
            $i = 1;
            $q_users_list = mysqli_query($connection, "SELECT * FROM pengguna ORDER BY id_pengguna DESC LIMIT 5");
            while ($user = mysqli_fetch_assoc($q_users_list)): 
          ?>
          <tr class="hover:bg-[#2f3034] transition">
            <td class="px-6 py-3"><?= $i ?></td>
            <td class="px-6 py-3"><?= htmlspecialchars($user['nama_pengguna']); ?></td>
            <td class="px-6 py-3"><?= htmlspecialchars($user['email_pengguna']); ?></td>
            <td class="px-6 py-3">
              <?php if ($user['is_admin'] == 1): ?>
                <span class="px-3 py-1 bg-green-600 rounded-full text-sm">Admin</span>
              <?php else: ?>
                <span class="px-3 py-1 bg-blue-600 rounded-full text-sm">User</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php $i++; endwhile; ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<!-- FontAwesome -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
