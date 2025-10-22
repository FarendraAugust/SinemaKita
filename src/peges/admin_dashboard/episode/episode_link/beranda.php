<?php $APP_URL = rtrim($_ENV['APP_URL'], '/'); ?>

<div class="h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>
  <?php include 'utils/connection.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Header -->
    <header class="flex justify-between items-center mb-6">
      <?php 
      $id = (int)$_GET['id'];
      $head = mysqli_fetch_assoc(mysqli_query($connection, "SELECT nama_film FROM film WHERE id_film = '$id'")); 
      ?>
      <h1 class="text-3xl font-bold">Manajemen <span class="text-yellow-600"><?= $head['nama_film'] ?></span></h1>
      <div class="flex gap-2">
        <button 
          class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition cursor-pointer" 
          onclick="window.location.href='<?= $APP_URL ?>/admin/episode'">
          Kembali
        </button>
        <button 
          class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded font-medium transition cursor-pointer" 
          onclick="window.location.href='<?= $APP_URL ?>/admin/episode/detail/tambah/<?= $id ?>'">
          Tambah Episode
        </button>
      </div>
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

    // Ambil data episode + film
    $data = mysqli_query($connection, "
      SELECT e.*, f.nama_film 
      FROM episode e
      JOIN film f ON e.id_film = f.id_film
      WHERE e.id_film = '$id'
      ORDER BY e.id_episode DESC
    ");
    ?>
    
    <!-- Table -->
    <div class="bg-[#18191c] rounded-lg shadow overflow-x-auto p-4">
      <table id="episodeLinkTable" class="w-full text-left border-collapse">
        <thead class="bg-[#202124] text-gray-300 text-sm uppercase tracking-wide">
          <tr>
            <th class="px-6 py-3">No</th>
            <th class="px-6 py-3">Nama Film</th>
            <th class="px-6 py-3">Nomor Episode</th>
            <th class="px-6 py-3">Dibuat</th>
            <th class="px-6 py-3">Diedit</th>
            <th class="px-6 py-3">Download</th>
            <th class="px-6 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700 text-white">
          <?php
          $no = 1;
          while ($d = mysqli_fetch_assoc($data)) { ?>
            <tr class="odd:bg-[#1b1c1f] even:bg-[#222325] hover:bg-[#2a2b2e] transition">
              <td class="px-6 py-3"><?= $no++; ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['nama_film']); ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['nomor_episode']); ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['create_at']); ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['update_at']); ?></td>
              <td class="px-6 py-3">
                <?php if (!empty($d['download_url'])): ?>
                  <a href="<?= htmlspecialchars($d['download_url'], ENT_QUOTES); ?>" 
                     download
                     class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-sm transition inline-block">
                    ⬇️ Download
                  </a>
                <?php else: ?>
                  <span class="text-gray-500 italic text-sm">Belum ada</span>
                <?php endif; ?>
              </td>
              <td class="px-6 py-3 text-center space-x-2 flex">
                <button 
                  class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm transition" 
                  onclick="window.location.href='<?= $APP_URL ?>/admin/episode/detail/edit/<?= $d['id_episode'] ?>'">
                  ✏️
                </button>

                <button 
                  class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm transition btn-hapus"
                  data-id="<?= htmlspecialchars($d['id_episode']); ?>"
                  data-nama="<?= htmlspecialchars($d['nomor_episode']); ?>">
                  🗑️
                </button>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

  </main>
</div>

<!-- Overlay -->
<div id="modalHapus"
    class="hidden fixed inset-0 z-50 flex items-center justify-center
            bg-black/50 backdrop-blur-sm">
  <div class="bg-[#18191c] text-white rounded-lg shadow-lg w-full max-w-md p-6">
    <h2 class="text-xl font-bold mb-4">Konfirmasi Hapus</h2>
    <p class="mb-4">
      Apakah Anda yakin ingin menghapus episode 
      <span id="noEpisode" class="font-semibold text-red-400"></span>?
    </p>
    <form id="formHapus" method="POST" 
          action="<?= $APP_URL ?>/src/peges/admin_dashboard/episode/episode_link/hapus_action.php" 
          class="mt-6 flex justify-end gap-3">
      <input type="hidden" name="id_episode" id="idEpisode">
      <input type="hidden" name="id_film" value="<?= $id ?>">
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

<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
  $(document).ready(function () {
    $('#episodeLinkTable').DataTable({
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

    // event tombol hapus
    $('#episodeLinkTable').on('click', '.btn-hapus', function () {
      const id = $(this).data('id');
      const nama = $(this).data('nama');
      $('#idEpisode').val(id);
      $('#noEpisode').text(nama);
      $('#modalHapus').removeClass('hidden');
    });

    // batal
    $('#btnBatal').on('click', function () {
      $('#modalHapus').addClass('hidden');
    });
  });
</script>
