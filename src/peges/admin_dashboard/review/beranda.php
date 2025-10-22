<div class="h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Header -->
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-400 to-pink-500 bg-clip-text text-transparent mb-4">
        Manajemen Review
      </h1>
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

    <!-- Table -->
    <div class="bg-[#18191c] rounded-lg shadow overflow-x-auto p-4">
      <table id="reviewTable" class="w-full text-left border-collapse">
        <thead class="bg-[#202124] text-gray-300 text-sm uppercase tracking-wide">
          <tr>
            <th class="px-6 py-3">No</th>
            <th class="px-6 py-3">Poster Film</th>
            <th class="px-6 py-3">Film</th>
            <th class="px-6 py-3">Jumlah Review</th>
            <th class="px-6 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700 text-white">
          <?php
          $no = 1;
          // Ambil semua film yang punya review (distinct) dan jumlah review
          $films = mysqli_query($connection, "
            SELECT f.id_film, f.nama_film, f.poster_film, COUNT(r.id_review) AS total_review
            FROM review r
            JOIN film f ON r.id_film = f.id_film
            GROUP BY f.id_film
            ORDER BY total_review DESC, f.nama_film ASC
          ");

          while ($film = mysqli_fetch_assoc($films)) {
          ?>
            <tr class="odd:bg-[#1b1c1f] even:bg-[#222325] hover:bg-[#2a2b2e] transition">
              <td class="px-6 py-3"><?= $no++; ?></td>
              <td class="px-6 py-3">
                <?php if ($film['poster_film'] && file_exists("assets/film/" . $film['poster_film'])): ?>
                  <img src="<?= $APP_URL ?>/assets/film/<?= htmlspecialchars($film['poster_film']); ?>" 
                       alt="<?= htmlspecialchars($film['nama_film']); ?>" 
                       class="w-20 h-28 object-cover rounded">
                <?php else: ?>
                  <div class="w-20 h-28 bg-gray-700 flex items-center justify-center rounded text-gray-400 text-xs">
                    Tidak ada poster
                  </div>
                <?php endif; ?>
              </td>
              <td class="px-6 py-3"><?= htmlspecialchars($film['nama_film']); ?></td>
              <td class="px-6 py-3"><?= $film['total_review']; ?></td>
              <td class="px-6 py-3 text-center">
                <button 
                  class="bg-yellow-600 hover:bg-yellow-700 px-3 py-1 rounded text-sm transition"
                  onclick="window.location.href='<?= $APP_URL ?>/admin/review/detail/<?= $film['id_film'] ?>'">
                  📑 Detail
                </button>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
  $(document).ready(function () {
    $('#reviewTable').DataTable({
      pageLength: 5,
      lengthMenu: [5, 10, 25, 50],
      ordering: true,
      dom: `
        <"flex flex-col sm:flex-row justify-between items-center gap-4 mb-4"lf>
        <"overflow-x-auto"t>
        <"flex flex-col sm:flex-row justify-between items-center gap-4 mt-4"ip>
      `,
      language: {
        search: "Cari ",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        paginate: { next: "›", previous: "‹" }
      }
    });
  });
</script>
