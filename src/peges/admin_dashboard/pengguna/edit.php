<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-green-500 bg-clip-text text-transparent mb-4">
        Edit Pengguna
      </h1>
      <a href="<?= $APP_URL ?>/admin/pengguna" 
         class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition">
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

    include 'utils/connection.php';
    $id = $_GET['id'];
    $data = mysqli_query($connection, "SELECT * FROM pengguna WHERE id_pengguna = '$id'");
    while($d = mysqli_fetch_array($data)) {
      $isGoogle = ($d['provider'] === 'google');
    ?>

    <!-- Form -->
    <div class="w-full bg-[#18191c] p-6 rounded-2xl shadow-lg">
      <form action="<?= $APP_URL ?>/src/peges/admin_dashboard/pengguna/edit_action.php" 
            method="post" enctype="multipart/form-data" class="space-y-5">

        <input type="hidden" name="id_pengguna" value="<?= $d['id_pengguna'] ?>">

        <!-- Nama Pengguna -->
        <div>
          <label for="nama_pengguna" class="block text-lg mb-2">Nama Pengguna</label>
          <input type="text" id="nama_pengguna" name="nama_pengguna"
                 class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3"
                 value="<?= htmlspecialchars($d['nama_pengguna']) ?>" required>
        </div>

        <?php if (!$isGoogle): ?>
          <!-- Email -->
          <div>
            <label for="email_pengguna" class="block text-lg mb-2">Email</label>
            <input type="email" id="email_pengguna" name="email_pengguna"
                   class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3"
                   value="<?= htmlspecialchars($d['email_pengguna']) ?>" required>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-lg mb-2">Kata Sandi</label>
            <input type="password" id="password" name="password"
                   class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3"
                   placeholder="Isi jika ingin ganti password">
          </div>

          <div>
            <label for="confirm_password" class="block text-lg mb-2">Konfirmasi Kata Sandi</label>
            <input type="password" id="confirm_password" name="confirm_password"
                   class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3"
                   placeholder="Konfirmasi password baru">
          </div>

          <!-- Foto Profil -->
          <div>
            <label for="profile_img" class="block text-lg mb-2">Foto Profil (opsional)</label>
            <input type="file" id="profile_img" name="profile_img"
                   class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3">
            <?php if ($d['profile_img']): ?>
              <p class="mt-2 text-sm text-gray-400">
                Foto saat ini: <?= htmlspecialchars($d['profile_img']) ?>
              </p>
            <?php endif; ?>
          </div>

          <!-- Role -->
          <div>
            <label for="role" class="block text-lg mb-2">Role</label>
            <select id="role" name="role"
                    class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
              <option value="admin" <?= ($d['is_admin'] == 1) ? 'selected' : '' ?>>Admin</option>
              <option value="pengguna" <?= ($d['is_admin'] == 0) ? 'selected' : '' ?>>Pengguna</option>
            </select>
          </div>
        <?php else: ?>
          <p class="text-gray-400 text-sm">
            Pengguna ini login via Google. Email, password, dan foto profil tidak bisa diubah.
          </p>
        <?php endif; ?>

        <div>
          <button type="submit"
                  class="w-full bg-blue-600 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
            Edit
          </button>
        </div>
      </form>
    </div>

    <?php } ?>
  </main>
</div>
