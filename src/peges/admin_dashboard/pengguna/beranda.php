<?php
include 'utils/connection.php';
?>

<div class="h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Header -->
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-green-500 bg-clip-text text-transparent mb-4">
        Manajemen Pengguna
      </h1>
      <button 
        class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded font-medium transition cursor-pointer"
        onclick="window.location.href='<?= $_ENV['APP_URL'] ?>/admin/pengguna/tambah'">
        Tambah Pengguna
      </button>
    </header>

    <!-- Pesan Sukses / Error -->
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
      <table id="usersTable" class="w-full text-left border-collapse">
        <thead class="bg-[#202124] text-gray-300 text-sm uppercase tracking-wide">
          <tr>
            <th class="px-6 py-3">No</th>
            <th class="px-6 py-3">Profil</th>
            <th class="px-6 py-3">Nama</th>
            <th class="px-6 py-3">Email</th>
            <th class="px-6 py-3">Role</th>
            <th class="px-6 py-3">Verifikasi</th>
            <th class="px-6 py-3">Dibuat</th>
            <th class="px-6 py-3">Diedit</th>
            <th class="px-6 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-700 text-white">
          <?php
          $no = 1;
          $data = mysqli_query($connection, "SELECT * FROM pengguna ORDER BY id_pengguna DESC");
          while ($d = mysqli_fetch_assoc($data)) { ?>
            <tr class="odd:bg-[#1b1c1f] even:bg-[#222325] hover:bg-[#2a2b2e] transition">
              <td class="px-6 py-3"><?= $no++; ?></td>

              <!-- Profile -->
              <td class="px-6 py-3">
                <?php 
                if (!empty($d['profile_img'])): 
                    // Jika dari Google, gunakan URL penuh
                    if (!empty($d['provider']) && $d['provider'] === 'google') {
                        $imgSrc = htmlspecialchars($d['profile_img']);
                    } else {
                        // Path lokal, ambil dari env
                        $imgSrc = $_ENV['APP_URL'] . '/assets/profile/' . htmlspecialchars($d['profile_img']);
                    }
                ?>
                    <img src="<?= $imgSrc ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover border border-gray-600">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center text-gray-300 font-bold">
                        <?= strtoupper(substr($d['nama_pengguna'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
              </td>

              <!-- Data -->
              <td class="px-6 py-3"><?= htmlspecialchars($d['nama_pengguna']); ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['email_pengguna']); ?></td>
              <td class="px-6 py-3">
                <?= $d['is_admin'] == 1 
                      ? '<span class="text-blue-400 font-semibold">Admin</span>' 
                      : '<span class="text-gray-300">User</span>'; ?>
              </td>

              <td class="px-6 py-3">
                <?php if ($d['verified'] == 1): ?>
                  <span class="text-green-400 font-medium">✔️ Terverifikasi</span>
                <?php else: ?>
                  <span class="text-red-400 font-medium">❌ Belum</span>
                <?php endif; ?>
              </td>

              <td class="px-6 py-3"><?= htmlspecialchars($d['create_at']); ?></td>
              <td class="px-6 py-3"><?= htmlspecialchars($d['update_at']); ?></td>

              <td class="px-6 py-3 text-center flex gap-2 justify-center">
                <!-- Tombol Edit -->
                <button 
                  class="bg-blue-600 hover:bg-blue-700 px-3 py-1 rounded text-sm transition"
                  onclick="window.location.href='<?= $_ENV['APP_URL'] ?>/admin/pengguna/edit/<?= $d['id_pengguna'] ?>'">
                  ✏️
                </button>

                <!-- Tombol Hapus -->
                <button 
                  class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm transition btn-hapus"
                  data-id="<?= htmlspecialchars($d['id_pengguna']); ?>"
                  data-nama="<?= htmlspecialchars($d['nama_pengguna']); ?>">
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

<!-- Overlay Modal Hapus -->
<div id="modalHapus"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
  
  <div class="bg-[#18191c] text-white rounded-lg shadow-lg w-full max-w-md p-6">
    <h2 class="text-xl font-bold mb-4">Konfirmasi Hapus</h2>
    <p class="mb-4">
      Apakah Anda yakin ingin menghapus pengguna 
      <span id="namaUser" class="font-semibold text-red-400"></span>?
    </p>

    <form id="formHapus" method="POST" 
          action="<?= $_ENV['APP_URL'] ?>/src/peges/admin_dashboard/pengguna/hapus_action.php" 
          class="mt-6 flex justify-end gap-3">
      <input type="hidden" name="id_pengguna" id="idUser">
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
    $('#usersTable').DataTable({
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

    // Tombol hapus
    $('#usersTable').on('click', '.btn-hapus', function () {
      const id = $(this).data('id');
      const nama = $(this).data('nama');
      $('#idUser').val(id);
      $('#namaUser').text(nama);
      $('#modalHapus').removeClass('hidden');
    });

    // Batal hapus
    $('#btnBatal').on('click', function () {
      $('#modalHapus').addClass('hidden');
    });
  });
</script>
