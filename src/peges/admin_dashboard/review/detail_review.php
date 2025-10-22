<?php
// Ambil ID film dari URL
$id_film = (int)($_GET['id'] ?? 0);

if ($id_film <= 0) {
    $_SESSION['error_msg'] = "ID film tidak valid!";
    header("Location: {$APP_URL}/admin/film");
    exit();
}

// Ambil info film
$film_q = mysqli_query($connection, "SELECT nama_film FROM film WHERE id_film = $id_film");
$film = mysqli_fetch_assoc($film_q);

// Ambil semua review untuk film ini beserta nama pengguna
$reviews = mysqli_query($connection, "
    SELECT r.*, u.nama_pengguna
    FROM review r
    LEFT JOIN pengguna u ON r.id_pengguna = u.id_pengguna
    WHERE r.id_film = $id_film
    ORDER BY r.create_at DESC
");
?>

<div class="h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">
  <?php include 'src/components/sidebar.php'; ?>

  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-400 to-pink-500 bg-clip-text text-transparent">
        Review Film: <?= htmlspecialchars($film['nama_film'] ?? '-') ?>
      </h1>
      <a href="<?= $APP_URL ?>/admin/review" 
         class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition">
        Kembali
      </a>
    </header>

    <!-- Alert -->
    <?php if (isset($_SESSION['error_msg'])): ?>
      <div class="p-3 mb-3 text-white bg-red-900 rounded">
        <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success_msg'])): ?>
      <div class="p-3 mb-3 text-white bg-green-900 rounded">
        <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
      </div>
    <?php endif; ?>

    <!-- Daftar Review -->
    <div class="max-w-4xl mx-auto space-y-4">
      <?php if (mysqli_num_rows($reviews) > 0): ?>
        <?php while ($rev = mysqli_fetch_assoc($reviews)): ?>
          <div class="p-4 bg-[#18191c] rounded-xl border border-gray-700 shadow-sm hover:shadow-md transition">
            
            <!-- Nama & Rating & Hapus -->
            <div class="flex justify-between items-center mb-2">
              <div class="flex items-center gap-4">
                <span class="font-medium"><?= htmlspecialchars($rev['nama_pengguna'] ?? 'Anonim') ?></span>
                <span class="text-yellow-400 flex items-center gap-1 text-sm">
                  ⭐
                  <span class="text-gray-300 font-normal">(<?= $rev['rating'] ?>/5)</span>
                </span>
              </div>
              
              <!-- Tombol hapus modal -->
              <button 
                type="button"
                class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm transition btn-hapus"
                data-id="<?= htmlspecialchars($rev['id_review']); ?>"
                data-nama="<?= htmlspecialchars($rev['nama_pengguna'] ?? 'Anonim'); ?>">
                🗑️
              </button>
            </div>

            <!-- Komentar -->
            <p class="text-gray-300 leading-relaxed mb-2"><?= nl2br(htmlspecialchars($rev['komentar'])) ?></p>

            <!-- Waktu -->
            <div class="text-xs text-gray-500">
              Dibuat: <?= date("d M Y H:i", strtotime($rev['create_at'])) ?>
              <?php if ($rev['update_at'] && $rev['update_at'] != $rev['create_at']): ?>
                | Diedit: <?= date("d M Y H:i", strtotime($rev['update_at'])) ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-gray-400">Belum ada review untuk film ini.</p>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- Modal Hapus -->
<div id="modalHapus"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
  <div class="bg-[#18191c] text-white rounded-lg shadow-lg w-full max-w-md p-6">
    <h2 class="text-xl font-bold mb-4">Konfirmasi Hapus</h2>
    <p class="mb-4">
      Apakah Anda yakin ingin menghapus review dari:
      <span id="namaReviewHapus" class="font-semibold text-red-400"></span>?
    </p>

    <form id="formHapus" method="POST"
          action="<?= $APP_URL ?>/src/peges/admin_dashboard/review/hapus_action.php"
          class="mt-6 flex justify-end gap-3">
      <input type="hidden" name="id_review" id="idReview">
      <button type="button" id="btnBatal"
              class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded transition">
        Batal
      </button>
      <button type="submit"
              class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded transition">
        Ya, Hapus
      </button>
    </form>
  </div>
</div>

<!-- jQuery untuk modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $(document).ready(function () {
    // Event tombol hapus
    $('.btn-hapus').on('click', function () {
      const id = $(this).data('id');
      const nama = $(this).data('nama');
      $('#idReview').val(id);
      $('#namaReviewHapus').text(nama);
      $('#modalHapus').removeClass('hidden');
    });

    // Tombol batal
    $('#btnBatal').on('click', function () {
      $('#modalHapus').addClass('hidden');
    });
  });
</script>
