<?php
include 'utils/connection.php';
include 'src/components/navbar.php';

// Pastikan APP_URL dari .env sudah dimuat
$APP_URL = rtrim($_ENV['APP_URL'], '/');

// Ambil semua tag
$tagResult = mysqli_query($connection, "SELECT * FROM tag ORDER BY klik DESC");
$tags = [];
if ($tagResult && mysqli_num_rows($tagResult) > 0) {
    while ($t = mysqli_fetch_assoc($tagResult)) {
        $tags[] = $t;
    }
}

// Cek tag yang dipilih
$selectedTag = isset($_GET['tag']) ? $_GET['tag'] : '';

// Update klik tag jika ada
if ($selectedTag) {
    $safeTag = mysqli_real_escape_string($connection, $selectedTag);
    mysqli_query($connection, "UPDATE tag SET klik = klik + 1 WHERE nama_tag = '$safeTag'");
}

// Query film berdasarkan tag jika ada
$query = "
    SELECT 
        f.id_film,
        f.nama_film AS title,
        f.poster_film AS poster,
        f.rilis AS year,
        f.status,
        f.slug,
        f.usia,
        f.negara,
        f.deskripsi,
        (
            SELECT t.nama_tag
            FROM film_tag ft
            JOIN tag t ON ft.id_tag = t.id_tag
            WHERE ft.id_film = f.id_film
            ORDER BY t.klik DESC
            LIMIT 1
        ) AS category,
        (
            SELECT ROUND(AVG(r.rating), 1)
            FROM review r
            WHERE r.id_film = f.id_film
        ) AS rating,
        (
            SELECT COUNT(*) 
            FROM episode e
            WHERE e.id_film = f.id_film
            AND e.nomor_episode != 'Trailer'
        ) AS total_episode
    FROM film f
";

if ($selectedTag) {
    $query .= "
        JOIN film_tag ft2 ON ft2.id_film = f.id_film
        JOIN tag t2 ON ft2.id_tag = t2.id_tag
        WHERE t2.nama_tag = '$safeTag'
    ";
}

$query .= " ORDER BY f.id_film DESC";

$result = mysqli_query($connection, $query);
$films = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $films[] = $row;
    }
}
?>

<div class="w-full flex justify-center mt-20">
  <div class="max-w-7xl w-full px-6">

    <!-- Menu Tag -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="<?= $APP_URL ?>/tag" 
           class="px-3 py-1 rounded-full <?= ($selectedTag == '') ? 'bg-orange-500' : 'bg-gray-700' ?> text-white hover:bg-gray-600 transition">
            Semua
        </a>
        <?php foreach ($tags as $tag): ?>
            <a href="<?= $APP_URL ?>/tag/<?= urlencode($tag['nama_tag']) ?>" 
               class="px-3 py-1 rounded-full <?= ($selectedTag == $tag['nama_tag']) ? 'bg-orange-500' : 'bg-gray-700' ?> text-white hover:bg-gray-600 transition">
                <?= htmlspecialchars($tag['nama_tag']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Grid Film -->
    <h2 class="text-2xl font-bold mb-4 text-white">
        <?= $selectedTag ? 'Film Kategori: ' . htmlspecialchars($selectedTag) : 'Semua Film' ?>
    </h2>

    <div class="grid grid-cols-6 gap-6">
        <?php if (!empty($films)): ?>
            <?php foreach ($films as $film): ?>
                <a href="<?= $APP_URL ?>/movie/<?= urlencode($film['slug']) ?>">
                  <div class="relative overflow-hidden rounded-xl shadow-lg bg-black hover:scale-[1.03] transition-transform duration-300">
                    <img src="<?= $APP_URL ?>/assets/film/<?= htmlspecialchars($film['poster']) ?>" 
                         alt="<?= htmlspecialchars($film['title']) ?>" 
                         class="w-full h-[240px] object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

                    <?php if (!empty($film['rating'])): ?>
                    <div class="absolute left-2 top-2 bg-black/70 text-yellow-300 text-[10px] font-semibold px-2 py-0.5 rounded-md">
                      ★ <?= htmlspecialchars($film['rating']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($film['category'])): ?>
                    <div class="absolute right-2 top-2 bg-white/10 text-[10px] text-white px-2 py-0.5 rounded-md">
                      <?= htmlspecialchars($film['category']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($film['total_episode']) && $film['total_episode'] > 1): ?>
                    <div class="absolute left-2 bottom-2 bg-red-600 text-white text-[10px] px-2 py-0.5 rounded-md">
                      <?= htmlspecialchars($film['total_episode']) ?> Episode
                    </div>
                    <?php endif; ?>

                    <div class="absolute right-2 bottom-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded-md">
                      <?= htmlspecialchars($film['status']) ?>
                    </div>
                  </div>

                  <h3 class="mt-2 text-white text-sm font-medium truncate">
                    <?= htmlspecialchars($film['title']) ?>
                  </h3>
                  <p class="text-gray-400 text-xs">
                    <?= htmlspecialchars($film['year']) ?>
                  </p>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-400 text-center col-span-6">Belum ada film di kategori ini.</p>
        <?php endif; ?>
    </div>
  </div>
</div>
