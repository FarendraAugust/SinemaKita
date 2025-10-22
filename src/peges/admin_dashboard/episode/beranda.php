<?php
$APP_URL = rtrim($_ENV['APP_URL'], '/'); // Ambil dari env
?>

<div class="h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Header -->
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-600 bg-clip-text text-transparent mb-4">Manajemen Episode</h1>
    </header>
    
    <?php
    if (isset($_SESSION['error_msg'])) {
        echo '<div class="p-3 mb-3 text-white bg-red-900 rounded">' . $_SESSION['error_msg'] . '</div>';
        unset($_SESSION['error_msg']);
    }

    if (isset($_SESSION['success_msg'])) {
        echo '<div class="p-3 mb-3 text-white bg-green-900 rounded">' . $_SESSION['success_msg'] . '</div>';
        unset($_SESSION['success_msg']);
    }
    ?>

    <!-- Table -->
    <div class="bg-[#18191c] rounded-lg shadow overflow-x-auto p-4">
      <table id="episodeTable" class="w-full text-left border-collapse">
        <thead class="bg-[#202124] text-gray-300 text-sm uppercase tracking-wide">
          <tr>
            <th class="px-6 py-3">No</th>
            <th class="px-6 py-3">Nama Film</th>
            <th class="px-6 py-3">Poster</th>
            <th class="px-6 py-3">Kategori</th>
            <th class="px-6 py-3">Rating</th>
            <th class="px-6 py-3">Status</th>
            <th class="px-6 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700 text-white">
          <?php
          $no = 1;
          $data = mysqli_query($connection, "
            SELECT f.*, GROUP_CONCAT(t.nama_tag SEPARATOR ', ') AS tags
            FROM film f
            LEFT JOIN film_tag ft ON f.id_film = ft.id_film
            LEFT JOIN tag t ON ft.id_tag = t.id_tag
            GROUP BY f.id_film
          ");
          while ($d = mysqli_fetch_array($data)) { ?>
            <tr class="odd:bg-[#1b1c1f] even:bg-[#222325] hover:bg-[#2a2b2e] transition">
              <td class="px-6 py-3"><?= $no++; ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['nama_film']); ?></td>
              <td class="px-6 py-3"><img src="<?= $APP_URL ?>/assets/film/<?= htmlspecialchars($d['poster_film']); ?>" alt="" class="w-32 rounded"></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['tags'] ?? '-'); ?></td>
              <td class="px-6 py-3">
                <?php 
                  $id_film = $d['id_film'];
                  $query = mysqli_query($connection, "SELECT AVG(rating) AS rata_rating FROM review WHERE id_film = '$id_film'");
                  $count  = mysqli_fetch_assoc($query);
                  echo number_format($count['rata_rating'], 1);
                ?>
              </td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['status']); ?></td>
              <td class="px-6 py-3 text-center flex gap-2">
                <button class="bg-yellow-600 hover:bg-yellow-700 px-3 py-1 rounded text-sm transition" 
                        onclick="window.location.href='<?= $APP_URL ?>/admin/episode/detail/<?= $d['id_film'] ?>'">
                  Atur Episode
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
    $('#episodeTable').DataTable({
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
        paginate: {
          next: "›",
          previous: "‹"
        }
      }
    });
  });
</script>
