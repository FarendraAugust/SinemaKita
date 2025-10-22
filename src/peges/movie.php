<div class="min-h-screen w-full bg-[#0c0d0f] text-white font-sans">

  <?php include 'src/components/navbar.php'; ?>

  <!-- Main Content -->
  <main class="p-8 pt-24 max-w-7xl mx-auto">
    <?php
      include 'utils/connection.php';
      $APP_URL = $_ENV['APP_URL'] ?? 'http://localhost/SinemaKita';

      $slug = $_GET['slug'] ?? '';

      if (empty($slug)) {
        echo "<p class='text-center text-gray-400 mt-20'>Slug film tidak ditemukan.</p>";
        exit;
      }

      $data = mysqli_query($connection, "SELECT * FROM film WHERE slug = '" . mysqli_real_escape_string($connection, $slug) . "' LIMIT 1");

      if (!$data || mysqli_num_rows($data) === 0) {
        echo "<p class='text-center text-gray-400 mt-20'>Film dengan slug <b>$slug</b> tidak ditemukan.</p>";
        exit;
      }

      $d = mysqli_fetch_assoc($data);
      $id_film = $d['id_film'];

      $episode_query = mysqli_query($connection, "SELECT id_episode FROM episode WHERE id_film = $id_film");
      $e = mysqli_fetch_assoc($episode_query);
      $id_episode = $e['id_episode'];

      // Tambahkan 1 klik pada film
      mysqli_query($connection, "UPDATE film SET klik = klik + 1 WHERE id_film = $id_film");

      // Tambahkan 1 klik pada semua tag yang terkait film ini
      mysqli_query($connection, "
          UPDATE tag
          JOIN film_tag ON tag.id_tag = film_tag.id_tag
          SET tag.klik = tag.klik + 1
          WHERE film_tag.id_film = $id_film
      ");
    ?>

    <!-- Header Judul -->
    <section class="mb-8 border-b border-gray-800 pb-5">
      <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
        <?php echo $d['nama_film'] . " (" .  $d['rilis'] . ")"; ?>
      </h1>

      <div class="flex items-center gap-2 text-sm text-gray-400">
        <span><?= $d['create_at']; ?> </span>
        <span>•</span>
        <span><?= htmlspecialchars($d['negara']); ?></span>
        <span>•</span>
        <span><?= $d['status']; ?></span>
      </div>

      <div class="mt-3 flex flex-wrap gap-2 text-xs">
        <span class="bg-red-600/80 px-2 py-1 rounded"><?= $d['negara'] ?></span>
        <?php
          $query = mysqli_query($connection, "SELECT tag.nama_tag, tag.klik FROM film_tag JOIN tag ON film_tag.id_tag = tag.id_tag WHERE film_tag.id_film = $id_film");
          if (mysqli_num_rows($query) > 0) {
            while ($tag = mysqli_fetch_assoc($query)) {
              echo '
                <span class="bg-gray-700/70 px-2 py-1 rounded cursor-pointer hover:bg-red-600 transition"
                      title="Tag ini sudah diklik ' . htmlspecialchars($tag['klik']) . ' kali">
                  ' . htmlspecialchars($tag['nama_tag']) . '
                </span>
              ';
            }
          } else {
            echo '<span class="text-gray-500 italic">Tidak ada tag</span>';
          }
        ?>
      </div>
    </section>

    <!-- Deskripsi -->
    <section class="mb-8 leading-relaxed">
      <h2 class="text-2xl font-bold mb-2 bg-gradient-to-l from-orange-400 to-red-500 bg-clip-text text-transparent">Deskripsi</h2>
      <p class="text-gray-300"><?= $d['deskripsi']; ?></p>
    </section>

    <!-- EPISODE LIST -->
    <section class="mb-8">
      <h2 class="text-lg font-semibold mb-3 border-b border-gray-800 pb-2">📺 Daftar Episode</h2>
      <div class="flex flex-wrap gap-3">
        <?php
          $episode_query = mysqli_query($connection, "SELECT id_episode, nomor_episode FROM episode WHERE id_film = $id_film ORDER BY CASE WHEN LOWER(nomor_episode) = 'trailer' THEN 0 ELSE 1 END,CAST(nomor_episode AS UNSIGNED),nomor_episode ASC");
          if (mysqli_num_rows($episode_query) > 0) {
            while ($ep = mysqli_fetch_assoc($episode_query)) {
              echo '
              <a href="' . $APP_URL . '/movie/' . $slug . '/' . $ep['nomor_episode'] . '"
                 class="bg-[#1a1b1e] hover:bg-red-600 transition px-4 py-2 rounded-lg text-sm font-medium">
                Episode ' . htmlspecialchars($ep['nomor_episode']) . '
              </a>';
            }
          } else {
            echo '<p class="text-gray-500 italic text-sm">Belum ada episode tersedia.</p>';
          }
        ?>
      </div>
    </section>

    <?php
// Ambil data user jika login
$id_pengguna = $_SESSION['session_id'] ?? null;
$user = null;
if ($id_pengguna) {
    $user_result = mysqli_query($connection, "SELECT verified FROM pengguna WHERE id_pengguna = $id_pengguna LIMIT 1");
    $user = mysqli_fetch_assoc($user_result);
}

$default_url = '';

// Ambil nomor episode dari URL path (contoh: /movie/slug/2)
$path = $_SERVER['REQUEST_URI'];
preg_match('#/movie/[^/]+/([^/?]+)#', $path, $match);
$nomor_episode = $match[1] ?? ($_GET['ep'] ?? 'Trailer');
$nomor_episode = mysqli_real_escape_string($connection, urldecode($nomor_episode));

// Ambil id_episode berdasarkan nomor episode
$ep_row = mysqli_query($connection, "
  SELECT id_episode 
  FROM episode 
  WHERE id_film = $id_film 
    AND nomor_episode = '$nomor_episode'
  LIMIT 1
");
$ep_data = mysqli_fetch_assoc($ep_row);
$id_episode = $ep_data['id_episode'] ?? null;

if ($id_episode) {
    // Ambil link video pertama
    $link_row = mysqli_query($connection, "
      SELECT url_video 
      FROM episode_link 
      WHERE id_episode = $id_episode 
      ORDER BY id_link ASC 
      LIMIT 1
    ");
    $link_data = mysqli_fetch_assoc($link_row);
    $default_url = $link_data['url_video'] ?? '';
}
?>

<!-- Video Player -->
<div class="relative w-full rounded-2xl overflow-hidden shadow-xl aspect-video mb-10">
<?php if (!$id_pengguna): ?>
    <div class="flex items-center justify-center h-full bg-[#1a1b1e] text-gray-400 text-center p-4">
        ⚠️ Silakan <a href="<?= $APP_URL ?>/login" class="text-red-500 hover:underline">login</a> terlebih dahulu untuk menonton video.
    </div>
<?php elseif ($user && $user['verified'] == 0): ?>
    <div class="flex items-center justify-center h-full bg-[#1a1b1e] text-gray-400 text-center p-4">
        ⚠️ Akun Anda belum terverifikasi. Silakan cek email untuk verifikasi sebelum menonton video.
    </div>
<?php elseif (!empty($default_url)): ?>
    <iframe
        id="player"
        class="absolute top-0 left-0 w-full h-full"
        src="<?= htmlspecialchars($default_url); ?>"
        frameborder="0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen>
    </iframe>
<?php else: ?>
    <div class="flex items-center justify-center h-full bg-[#1a1b1e] text-gray-500 italic">
        Trailer belum tersedia.
    </div>
<?php endif; ?>
</div>


    <!-- Tombol Server -->
    <section class="mt-6 mb-10">
      <h2 class="text-lg font-semibold mb-3">Pilih Server Streaming</h2>
      <div class="flex flex-wrap gap-3">
        <?php
          if ($id_episode) {
            $server_query = mysqli_query($connection, "
              SELECT nama_server, url_video 
              FROM episode_link 
              WHERE id_episode = $id_episode 
              ORDER BY id_link ASC
            ");
            if (mysqli_num_rows($server_query) > 0) {
              while ($server = mysqli_fetch_assoc($server_query)) {
                $url = htmlspecialchars($server['url_video'], ENT_QUOTES);
                $name = htmlspecialchars($server['nama_server']);
                echo "<button data-url=\"{$url}\" onclick=\"gantiServer(this.dataset.url, this)\" class=\"server-btn bg-gray-700 hover:bg-red-600 px-4 py-2 rounded-lg text-sm font-medium transition\">🎬 {$name}</button>";
              }
            } else {
              echo '<p class="text-gray-500 italic text-sm">Belum ada server untuk episode ini.</p>';
            }
          } else {
            echo '<p class="text-gray-500 italic text-sm">Episode tidak ditemukan.</p>';
          }
        ?>
      </div>
    </section>

    <?php
// Ambil URL download untuk episode saat ini
$download_url = '';
if ($id_episode) {
    $dl_query = mysqli_query($connection, "
        SELECT download_url 
        FROM episode 
        WHERE id_episode = $id_episode
        LIMIT 1
    ");
    $dl_data = mysqli_fetch_assoc($dl_query);
    $download_url = $dl_data['download_url'] ?? '';
}
?>

<?php if (!empty($download_url)): ?>
<section class="mb-8">
    <a href="<?= htmlspecialchars($download_url) ?>" target="_blank"
       class="inline-block w-full text-center bg-gradient-to-r from-orange-400 to-red-500
              text-white font-semibold px-6 py-3 rounded-lg shadow-lg hover:scale-[1.02]
              transition-all duration-300">
        Download Episode <?= htmlspecialchars($nomor_episode) ?>
    </a>
</section>
<?php endif; ?>


    <!-- POPULER SEPANJANG WAKTU -->
     <?php
      function getFilms($connection, $orderBy = "f.id_film DESC", $limit = 6) {
        $query = "
          SELECT f.id_film, f.nama_film AS title, f.poster_film AS poster, f.rilis AS year, f.status, f.slug,
          (SELECT t.nama_tag FROM film_tag ft 
            JOIN tag t ON ft.id_tag = t.id_tag 
            WHERE ft.id_film = f.id_film 
            ORDER BY t.klik DESC LIMIT 1) AS category,
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
      ?>

      <section class="mt-12 mb-10">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-6">🔥 Populer Sepanjang Waktu</h2>

        <!-- Grid responsif -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-5 sm:gap-6">
          <?php foreach ($populerFilms as $film): ?>
            <a href="<?= $APP_URL ?>/movie/<?= urlencode($film['slug']) ?>" 
              class="group block bg-[#0c0d0f] rounded-2xl overflow-hidden shadow-md hover:shadow-xl hover:scale-[1.03] transition-all duration-300">
              
              <!-- Poster -->
              <div class="relative w-full aspect-[2/3] overflow-hidden">
                <img src="<?= $APP_URL ?>/assets/film/<?= htmlspecialchars($film['poster']) ?>" 
                    alt="<?= htmlspecialchars($film['title']) ?>" 
                    class="w-full h-full object-cover group-hover:brightness-110 transition" />

                <!-- Overlay gradient -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>

                <!-- Info badge (rating, kategori, status, episode) -->
                <?php if (!empty($film['rating'])): ?>
                  <div class="absolute left-2 top-2 bg-black/70 text-yellow-300 text-[11px] font-semibold px-2 py-0.5 rounded-md">
                    ★ <?= htmlspecialchars($film['rating']) ?>
                  </div>
                <?php endif; ?>

                <?php if (!empty($film['category'])): ?>
                  <div class="absolute right-2 top-2 bg-white/10 text-white text-[11px] px-2 py-0.5 rounded-md">
                    <?= htmlspecialchars($film['category']) ?>
                  </div>
                <?php endif; ?>

                <?php if (!empty($film['total_episode']) && $film['total_episode'] > 1): ?>
                  <div class="absolute left-2 bottom-2 bg-red-600 text-white text-[11px] px-2 py-0.5 rounded-md">
                    <?= htmlspecialchars($film['total_episode']) ?> Ep
                  </div>
                <?php endif; ?>

                <div class="absolute right-2 bottom-2 bg-black/70 text-white text-[11px] px-2 py-0.5 rounded-md">
                  <?= htmlspecialchars($film['status']) ?>
                </div>
              </div>

              <!-- Judul & Tahun -->
              <div class="p-2">
                <h3 class="text-white text-sm sm:text-base font-semibold truncate group-hover:text-orange-400 transition">
                  <?= htmlspecialchars($film['title']) ?>
                </h3>
                <p class="text-gray-400 text-xs"><?= htmlspecialchars($film['year']) ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </section>

    <!-- REVIEW -->
    <section class="mt-16 text-center">
      <h2 class="text-lg font-semibold mb-6 border-b border-gray-800 pb-3 inline-block">💬 Ulasan Penonton</h2>

      <?php
        $id_pengguna = $_SESSION['session_id'] ?? null;
        $existing_review = null;
        $action_url = $APP_URL . '/src/peges/review/tambah_review.php'; // default

        if ($id_pengguna) {
          $cek = mysqli_query($connection, "
            SELECT * FROM review 
            WHERE id_film = $id_film AND id_pengguna = $id_pengguna
            LIMIT 1
          ");
          if (mysqli_num_rows($cek) > 0) {
            $existing_review = mysqli_fetch_assoc($cek);
            $action_url = $APP_URL . '/src/peges/review/update_review.php';
          }
        }
      ?>

      <!-- Jika user sudah login -->
      <?php if ($id_pengguna): ?>
        <form action="<?= $action_url ?>" method="POST" class="mb-10 max-w-lg mx-auto">
          <input type="hidden" name="id_film" value="<?= $id_film; ?>">
          <?php if ($existing_review): ?>
            <input type="hidden" name="id_review" value="<?= $existing_review['id_review']; ?>">
          <?php endif; ?>
          <input type="hidden" name="rating" id="ratingInput" value="<?= $existing_review['rating'] ?? 0; ?>">

          <!-- Bintang rating -->
          <div class="flex justify-center mb-4">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <?php $filled = ($existing_review && $i <= $existing_review['rating']) ? 'text-yellow-400' : 'text-gray-500'; ?>
              <svg onclick="setRating(<?= $i ?>)" id="star<?= $i ?>" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke="currentColor"
                class="w-10 h-10 mx-1 cursor-pointer <?= $filled ?> hover:text-yellow-400 transition">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 
                                18.18 21.02 12 17.77 
                                5.82 21.02 7 14.14 
                                2 9.27 8.91 8.26 12 2" />
              </svg>
            <?php endfor; ?>
          </div>

          <!-- Textarea komentar -->
          <textarea name="komentar" rows="3" placeholder="Tulis pendapatmu tentang film ini..."
            class="w-full bg-[#1a1b1e] text-gray-200 rounded-lg border border-gray-700 p-3 text-sm focus:outline-none focus:border-red-600 resize-none"
            required><?= htmlspecialchars($existing_review['komentar'] ?? '') ?></textarea>

          <!-- Tombol kirim -->
          <button type="submit"
            class="mt-4 <?= $existing_review ? 'bg-yellow-600 hover:bg-yellow-700' : 'bg-red-600 hover:bg-red-700' ?> px-6 py-2 rounded-lg text-sm font-medium transition">
            <?= $existing_review ? 'Update Review' : 'Kirim Review' ?>
          </button>
        </form>

      <!-- Jika user belum login -->
      <?php else: ?>
        <div class="bg-[#1a1b1e] border border-gray-800 rounded-lg p-6 max-w-md mx-auto mb-10">
          <p class="text-gray-400 mb-3 text-sm">
            Silakan <a href="<?= $APP_URL ?>/login" class="text-red-500 hover:underline">login</a> terlebih dahulu untuk memberikan review.
          </p>
          <a href="<?= $APP_URL ?>/login" class="inline-block bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition">Login Sekarang</a>
        </div>
      <?php endif; ?>

      <!-- Daftar review -->
      <div class="space-y-4 max-h-80 overflow-y-auto pr-2 text-left max-w-3xl mx-auto">
      <?php 
        $review_query = mysqli_query($connection, "
          SELECT pengguna.id_pengguna, pengguna.nama_pengguna, pengguna.profile_img, pengguna.provider, review.id_review, review.komentar, review.create_at, review.rating
          FROM review
          JOIN pengguna ON review.id_pengguna = pengguna.id_pengguna
          WHERE review.id_film = $id_film
          ORDER BY 
            CASE WHEN review.id_pengguna = '$id_pengguna' THEN 0 ELSE 1 END,
            review.create_at DESC
        ");

        if (mysqli_num_rows($review_query) > 0) {
          while ($r = mysqli_fetch_assoc($review_query)) {
            $is_self = ($r['id_pengguna'] == $id_pengguna);

            // LOGIKA AVATAR
            $profileImg = $APP_URL . "/assets/profile/default-avatar.png"; // default
            if (!empty($r['profile_img'])) {
                if (!empty($r['provider']) && in_array($r['provider'], ['google','facebook'])) {
                    $profileImg = $r['profile_img']; // ambil URL provider
                } else {
                    $profileImg = $APP_URL . "/assets/profile/" . htmlspecialchars($r['profile_img']); // lokal
                }
            }

            echo '
            <div class="bg-[#1a1b1e] border ' . ($is_self ? 'border-yellow-500' : 'border-gray-800') . ' rounded-lg p-3 relative flex gap-3 items-start">
              <img src="'.$profileImg.'" alt="Profil" class="w-10 h-10 rounded-full object-cover border border-gray-700 flex-shrink-0">
              <div class="flex-1">
                <div class="flex justify-between items-center mb-1">
                  <span class="font-semibold text-sm ' . ($is_self ? 'text-yellow-400' : 'text-red-400') . '">'.
                    htmlspecialchars($r['nama_pengguna']) . ($is_self ? ' (Kamu)' : '') .
                  '</span>
                  <span class="text-xs text-gray-500">'.date("d M Y", strtotime($r['create_at'])).'</span>
                </div>
                <div class="flex mb-1">';
            for ($i = 1; $i <= 5; $i++) {
              $color = ($i <= $r['rating']) ? 'text-yellow-400' : 'text-gray-600';
              echo '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 '.$color.'" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.57L24 9.748l-6 5.847L19.335 24 12 19.897 4.665 24 6 15.595 0 9.748l8.332-1.591z"/></svg>';
            }
            echo '</div>
                <p class="text-gray-300 text-sm">'.nl2br(htmlspecialchars($r['komentar'])).'</p>';

            if ($is_self) {
              echo '<button 
                type="button"
                class="absolute bottom-2 right-3 text-gray-500 hover:text-red-500 transition btn-hapus flex items-center gap-1 text-xs cursor-pointer"
                title="Hapus review"
                data-id="'.$r['id_review'].'"
                data-nama="'.htmlspecialchars($r['komentar']).'">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="w-4 h-4">
                  <polyline points="3 6 5 6 21 6" />
                  <path d="M19 6l-1 14H6L5 6m5 0V4h4v2" />
                  <line x1="10" y1="11" x2="10" y2="17" />
                  <line x1="14" y1="11" x2="14" y2="17" />
                </svg>
                <span>Hapus</span>
              </button>';
            }

            echo '</div></div>';
          }
        } else {
          echo '<p class="text-gray-500 italic text-sm">Belum ada ulasan. Jadilah yang pertama!</p>';
        }
      ?>
      </div>

    </section>

    <!-- Modal Konfirmasi Hapus Review -->
    <div id="modalHapusReview"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-[#18191c] text-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-xl font-bold mb-4">Konfirmasi Hapus</h2>
        <p class="mb-4">
          Apakah kamu yakin ingin menghapus review ini?
        </p>
        <form id="formHapusReview" method="POST" action="<?= $APP_URL ?>/src/peges/review/hapus_review.php" class="mt-6 flex justify-end gap-3">
          <input type="hidden" name="id_review" id="idReviewHapus">
          <button type="button" id="btnBatalReview" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded transition">
            Batal
          </button>
          <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded transition">
            Ya, Hapus
          </button>
        </form>
      </div>
    </div>
  </main>
</div>

<script>
  function gantiServer(url, btn) {
    if (!url) return;
    var player = document.getElementById('player') || document.querySelector('iframe');
    if (!player) return;
    player.src = url;

    // highlight active button
    document.querySelectorAll('.server-btn').forEach(b => {
      b.classList.remove('bg-red-600', 'text-white');
    });
    if (btn) btn.classList.add('bg-red-600', 'text-white');
  }

  function setRating(value) {
    document.getElementById('ratingInput').value = value;
    for (let i = 1; i <= 5; i++) {
      const star = document.getElementById('star' + i);
      if (!star) continue;
      star.classList.remove('text-yellow-400', 'text-gray-500');
      star.classList.add(i <= value ? 'text-yellow-400' : 'text-gray-500');
    }
  }

  function setRating(value) {
  document.getElementById('ratingInput').value = value;
  for (let i = 1; i <= 5; i++) {
    document.getElementById('star' + i).classList.toggle('text-yellow-400', i <= value);
    document.getElementById('star' + i).classList.toggle('text-gray-500', i > value);
  }
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalHapusReview');
    const idInput = document.getElementById('idReviewHapus');
    const btnBatal = document.getElementById('btnBatalReview');

    document.querySelectorAll('.btn-hapus').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        idInput.value = id;
        modal.classList.remove('hidden');
      });
    });

    btnBatal.addEventListener('click', () => {
      modal.classList.add('hidden');
    });
  });
</script>

