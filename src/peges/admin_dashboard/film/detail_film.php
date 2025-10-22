<?php
$APP_URL = rtrim($_ENV['APP_URL'], '/'); // Ambil base URL dari env
?>

<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <?php
      include 'utils/connection.php';

      $id = (int)$_GET['id'];

      $data = mysqli_query($connection, "
          SELECT f.*, GROUP_CONCAT(t.nama_tag SEPARATOR ', ') AS tags
          FROM film f
          LEFT JOIN film_tag ft ON f.id_film = ft.id_film
          LEFT JOIN tag t ON ft.id_tag = t.id_tag
          WHERE f.id_film = $id
          GROUP BY f.id_film
      ");
      $d = mysqli_fetch_assoc($data);

      // Warna-warna cerah random
      $colors = [
        "bg-pink-600", "bg-purple-600", "bg-indigo-600", "bg-blue-600",
        "bg-cyan-600", "bg-teal-600", "bg-green-600", "bg-lime-600",
        "bg-yellow-600", "bg-orange-600", "bg-red-600", "bg-fuchsia-600"
      ];

      function randomColor($colors) {
        return $colors[array_rand($colors)];
      }
    ?>

    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Detail Film</h1>
      <a href="<?= $APP_URL ?>/admin/film" 
         class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition">
        Kembali
      </a>
    </header>

    <!-- Detail Layout -->
    <div class="w-full h-[calc(100vh-100px)] bg-[#18191c] p-8 rounded-2xl shadow-lg grid grid-cols-1 md:grid-cols-4 gap-8">

      <!-- Poster -->
      <div class="md:col-span-1 flex justify-center items-start">
        <img src="<?= $APP_URL ?>/assets/film/<?= htmlspecialchars($d['poster_film']) ?>" 
             alt="<?= htmlspecialchars($d['nama_film']) ?>" 
             class="rounded-xl shadow-lg w-64 md:w-72 object-cover">
      </div>

      <!-- Info -->
      <div class="md:col-span-3 flex flex-col justify-between">
        <div class="space-y-6 overflow-y-auto pr-3">

          <!-- Judul & Slug -->
          <div>
            <h2 class="text-4xl font-bold leading-tight"><?= htmlspecialchars($d['nama_film']) ?></h2>
            <p class="text-gray-400 italic text-sm"><?= htmlspecialchars($d['slug']) ?></p>
          </div>

          <!-- Badges -->
          <div class="flex flex-wrap gap-2 text-sm">
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Tag: <?= htmlspecialchars($d['tags'] ?? '-') ?></span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">
              Rating: 
              <?php 
                  $query = mysqli_query($connection, "SELECT AVG(rating) AS rata_rating FROM review WHERE id_film = $id");
                  $count  = mysqli_fetch_assoc($query);

                  $rating = $count['rata_rating'];
                  echo ($rating) ? "⭐ " . number_format($rating, 1) : "⭐ -";
              ?>
            </span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Usia: <?= htmlspecialchars($d['usia']) ?>+</span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Negara: <?= htmlspecialchars($d['negara']) ?></span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Status: <?= htmlspecialchars($d['status']) ?></span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Rilis: <?= htmlspecialchars($d['rilis']) ?></span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Klik: <?= htmlspecialchars($d['klik']) ?></span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Dibuat: <?= htmlspecialchars($d['create_at']) ?></span>
            <span class="<?= randomColor($colors) ?> px-3 py-1 rounded-full">Diedit: <?= htmlspecialchars($d['update_at']) ?></span>
          </div>

          <!-- Deskripsi -->
          <div>
            <h3 class="text-2xl font-semibold mb-2 border-b border-gray-700 pb-1">Deskripsi</h3>
            <p class="text-gray-300 leading-relaxed text-base"><?= nl2br(htmlspecialchars($d['deskripsi'])) ?></p>
          </div>
        </div>

        <!-- Footer -->
        <div class="pt-4 border-t border-gray-700 mt-6 text-sm text-gray-400">
          Data terakhir diperbarui otomatis dari database 🎬
        </div>
      </div>
    </div>
  </main>
</div>
