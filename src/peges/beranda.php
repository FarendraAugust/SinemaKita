<?php
include 'src/components/navbar.php';
include 'src/components/banner.php';
include 'utils/connection.php'; // Sudah otomatis pakai .env dari index

// =====================
// Ambil semua film dengan episode terbaru
// =====================
$allFilmsResult = mysqli_query($connection, "
    SELECT f.id_film, f.nama_film AS title, f.poster_film AS poster, f.rilis AS year, f.status, f.slug,
           (SELECT t.nama_tag FROM film_tag ft JOIN tag t ON ft.id_tag = t.id_tag 
            WHERE ft.id_film = f.id_film ORDER BY t.klik DESC LIMIT 1) AS category,
           (SELECT ROUND(AVG(r.rating),1) FROM review r WHERE r.id_film = f.id_film) AS rating,
           (SELECT COUNT(*) FROM episode e WHERE e.id_film = f.id_film AND e.nomor_episode != 'Trailer') AS total_episode,
           (SELECT MAX(e.create_at) FROM episode e WHERE e.id_film = f.id_film) AS latest_episode_date
    FROM film f
    ORDER BY latest_episode_date DESC
");

$allFilms = mysqli_fetch_all($allFilmsResult, MYSQLI_ASSOC);

// =====================
// Pagination
// =====================
$perPage = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalFilms = count($allFilms);
$totalPages = ceil($totalFilms / $perPage);
$latestFilms = array_slice($allFilms, ($page - 1) * $perPage, $perPage);

// =====================
// Fungsi ambil film populer/rating tinggi
// =====================
function getFilms($connection, $orderBy = "f.id_film DESC", $limit = 6)
{
  $query = "
      SELECT f.id_film, f.nama_film AS title, f.poster_film AS poster, f.rilis AS year, f.status, f.slug,
      (SELECT t.nama_tag FROM film_tag ft JOIN tag t ON ft.id_tag = t.id_tag 
        WHERE ft.id_film = f.id_film ORDER BY t.klik DESC LIMIT 1) AS category,
      (SELECT ROUND(AVG(r.rating), 1) FROM review r WHERE r.id_film = f.id_film) AS rating,
      (SELECT COUNT(*) FROM episode e WHERE e.id_film = f.id_film AND e.nomor_episode != 'Trailer') AS total_episode
      FROM film f
      ORDER BY $orderBy
      LIMIT $limit
  ";
  $res = mysqli_query($connection, $query);
  return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

$populerFilms = getFilms($connection, "f.klik DESC", 6);
$ratingFilms = getFilms($connection, "rating DESC", 6);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 mt-5">

  <!-- POPULER SEPANJANG WAKTU -->
  <h2 class="text-xl sm:text-2xl font-bold text-white mb-4">Populer Sepanjang Waktu</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6 mb-10">
    <?php foreach ($populerFilms as $film): ?>
      <a href="<?= $_ENV['APP_URL'] ?>/movie/<?= urlencode($film['slug']) ?>" class="block">
        <div class="relative overflow-hidden rounded-lg sm:rounded-xl shadow-lg bg-black hover:scale-[1.03] transition-transform duration-300 aspect-[2/3]">
          <img src="<?= $_ENV['APP_URL'] ?>/assets/film/<?= htmlspecialchars($film['poster']) ?>" 
               alt="<?= htmlspecialchars($film['title']) ?>" 
               class="absolute inset-0 w-full h-full object-cover" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

          <?php if (!empty($film['rating'])): ?>
            <div class="absolute left-2 top-2 bg-black/70 text-yellow-300 text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-md">
              ★ <?= htmlspecialchars($film['rating']) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($film['category'])): ?>
            <div class="absolute right-2 top-2 bg-white/10 text-[10px] sm:text-xs text-white px-2 py-0.5 rounded-md">
              <?= htmlspecialchars($film['category']) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($film['total_episode']) && $film['total_episode'] > 1): ?>
            <div class="absolute left-2 bottom-2 bg-red-600 text-white text-[10px] sm:text-xs px-2 py-0.5 rounded-md">
              <?= htmlspecialchars($film['total_episode']) ?> Ep
            </div>
          <?php endif; ?>

          <div class="absolute right-2 bottom-2 bg-black/60 text-white text-[10px] sm:text-xs px-2 py-0.5 rounded-md">
            <?= htmlspecialchars($film['status']) ?>
          </div>
        </div>
        <h3 class="mt-2 text-white text-xs sm:text-sm font-medium truncate">
          <?= htmlspecialchars($film['title']) ?>
        </h3>
        <p class="text-gray-400 text-[10px] sm:text-xs">
          <?= htmlspecialchars($film['year']) ?>
        </p>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- RATING PALING BAGUS -->
  <h2 class="text-xl sm:text-2xl font-bold text-white mb-4">Rating Paling Bagus</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6 mb-10">
    <?php foreach ($ratingFilms as $film): ?>
      <a href="<?= $_ENV['APP_URL'] ?>/movie/<?= urlencode($film['slug']) ?>" class="block">
        <div class="relative overflow-hidden rounded-lg sm:rounded-xl shadow-lg bg-black hover:scale-[1.03] transition-transform duration-300 aspect-[2/3]">
          <img src="<?= $_ENV['APP_URL'] ?>/assets/film/<?= htmlspecialchars($film['poster']) ?>" 
               alt="<?= htmlspecialchars($film['title']) ?>" 
               class="absolute inset-0 w-full h-full object-cover" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

          <?php if (!empty($film['rating'])): ?>
            <div class="absolute left-2 top-2 bg-black/70 text-yellow-300 text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-md">
              ★ <?= htmlspecialchars($film['rating']) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($film['category'])): ?>
            <div class="absolute right-2 top-2 bg-white/10 text-[10px] sm:text-xs text-white px-2 py-0.5 rounded-md">
              <?= htmlspecialchars($film['category']) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($film['total_episode']) && $film['total_episode'] > 1): ?>
            <div class="absolute left-2 bottom-2 bg-red-600 text-white text-[10px] sm:text-xs px-2 py-0.5 rounded-md">
              <?= htmlspecialchars($film['total_episode']) ?> Ep
            </div>
          <?php endif; ?>

          <div class="absolute right-2 bottom-2 bg-black/60 text-white text-[10px] sm:text-xs px-2 py-0.5 rounded-md">
            <?= htmlspecialchars($film['status']) ?>
          </div>
        </div>
        <h3 class="mt-2 text-white text-xs sm:text-sm font-medium truncate">
          <?= htmlspecialchars($film['title']) ?>
        </h3>
        <p class="text-gray-400 text-[10px] sm:text-xs">
          <?= htmlspecialchars($film['year']) ?>
        </p>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- TERAKHIR DIUNGGAH -->
  <h2 class="text-xl sm:text-2xl font-bold text-white mb-4">Terakhir Diunggah</h2>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6 mb-6">
    <?php if (!empty($latestFilms)): ?>
      <?php foreach ($latestFilms as $film): ?>
        <a href="<?= $_ENV['APP_URL'] ?>/movie/<?= urlencode($film['slug']) ?>" class="block">
          <div class="relative overflow-hidden rounded-lg sm:rounded-xl shadow-lg bg-black hover:scale-[1.03] transition-transform duration-300 aspect-[2/3]">
            <img src="<?= $_ENV['APP_URL'] ?>/assets/film/<?= htmlspecialchars($film['poster']) ?>" 
                 alt="<?= htmlspecialchars($film['title']) ?>" 
                 class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

            <?php if (!empty($film['rating'])): ?>
              <div class="absolute left-2 top-2 bg-black/70 text-yellow-300 text-[10px] sm:text-xs font-semibold px-2 py-0.5 rounded-md">
                ★ <?= htmlspecialchars($film['rating']) ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($film['category'])): ?>
              <div class="absolute right-2 top-2 bg-white/10 text-[10px] sm:text-xs text-white px-2 py-0.5 rounded-md">
                <?= htmlspecialchars($film['category']) ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($film['total_episode']) && $film['total_episode'] > 1): ?>
              <div class="absolute left-2 bottom-2 bg-red-600 text-white text-[10px] sm:text-xs px-2 py-0.5 rounded-md">
                <?= htmlspecialchars($film['total_episode']) ?> Ep
              </div>
            <?php endif; ?>

            <div class="absolute right-2 bottom-2 bg-black/60 text-white text-[10px] sm:text-xs px-2 py-0.5 rounded-md">
              <?= htmlspecialchars($film['status']) ?>
            </div>
          </div>
          <h3 class="mt-2 text-white text-xs sm:text-sm font-medium truncate">
            <?= htmlspecialchars($film['title']) ?>
          </h3>
          <p class="text-gray-400 text-[10px] sm:text-xs">
            <?= htmlspecialchars($film['year']) ?>
          </p>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-gray-400 text-center col-span-2 sm:col-span-6">Belum ada film.</p>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <div class="flex justify-center gap-1 sm:gap-2 mt-4">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <a href="?page=<?= $p ?>" 
         class="px-2 sm:px-3 py-1 text-xs sm:text-sm rounded-md <?= $p == $page ? 'bg-red-600 text-white' : 'bg-gray-700 text-gray-200' ?>">
        <?= $p ?>
      </a>
    <?php endfor; ?>
  </div>
</div>

<!-- Footer -->
<footer class="bg-[#0c0d0f] border-t border-gray-800 mt-10 py-6 text-center">
  <p class="text-gray-400 text-sm">
    © <?= date('Y') ?> <span class="text-red-500 font-semibold"><?= $_ENV['APP_NAME'] ?></span>. Semua Hak Dilindungi.
  </p>
</footer>

<!-- Tombol Verifikasi Email -->
<?php if (isset($_SESSION['session_id'])): ?>
  <?php
  $id = $_SESSION['session_id'];
  $cekEmail = mysqli_query($connection, "SELECT verified FROM pengguna WHERE id_pengguna='$id' LIMIT 1");
  $cek = mysqli_fetch_assoc($cekEmail);
  $verified = (int)($cek['verified'] ?? 0);
  ?>
  <?php if ($verified === 0): ?>
    <a href="<?= $_ENV['APP_URL'] ?>/kirim-verifikasi"
       class="fixed bottom-5 right-5 z-50 bg-gradient-to-r from-red-600 via-pink-500 to-purple-600 text-white font-semibold px-5 py-3 rounded-full shadow-lg shadow-red-600/40 hover:scale-105 hover:shadow-red-400/50 transition-all duration-300">
      🔒 Verifikasi Email
    </a>
  <?php endif; ?>
<?php endif; ?>
