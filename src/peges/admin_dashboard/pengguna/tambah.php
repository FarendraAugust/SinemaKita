<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-green-500 bg-clip-text text-transparent mb-4">Tambah Pengguna</h1>
      <a href="/sinemakita/admin/pengguna" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition">
        Batal
      </a>
    </header>

    <!-- Alert -->
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

    <!-- Form -->
    <div class="w-full bg-[#18191c] p-6 rounded-2xl shadow-lg">
      <form action="/sinemakita/src/peges/admin_dashboard/pengguna/tambah_action.php" method="post" enctype="multipart/form-data" class="space-y-5">
        <div>
          <label for="profile_img" class="block text-lg mb-2">Foto Profil (opsional)</label>
          <input type="file" id="profile_img" name="profile_img" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3">
        </div>
      
        <div>
          <label for="nama_pengguna" class="block text-lg mb-2">Nama Pengguna</label>
          <input type="text" id="nama_pengguna" name="nama_pengguna" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
        </div>

        <div>
          <label for="email_pengguna" class="block text-lg mb-2">Email</label>
          <input type="email" id="email_pengguna" name="email_pengguna" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
        </div>

        <div>
          <label for="password" class="block text-lg mb-2">Kata Sandi</label>
          <input type="password" id="password" name="password" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
        </div>

        <div>
          <label for="confirm_password" class="block text-lg mb-2">Konfirmasi Kata Sandi</label>
          <input type="password" id="confirm_password" name="confirm_password" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
        </div>

        <div>
          <label for="role" class="block text-lg mb-2">Role</label>
          <select id="role" name="role" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
            <option value="pengguna">Pengguna</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div>
          <button type="submit" class="w-full bg-blue-600 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
            Tambahkan
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
