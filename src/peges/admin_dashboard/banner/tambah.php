<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>
  <?php include 'utils/connection.php'; ?>
  <?php $APP_URL = rtrim($_ENV['APP_URL'], '/'); ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Tambah Banner</h1>
      <a href="<?= $APP_URL ?>/admin/banner" 
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
    ?>

    <!-- Form -->
    <div class="w-full bg-[#18191c] p-6 rounded-2xl shadow-lg">
      <form action="<?= $APP_URL ?>/src/peges/admin_dashboard/banner/tambah_action.php" 
            method="post" 
            enctype="multipart/form-data" 
            class="space-y-5">

        <!-- File Banner -->
        <div>
          <label for="banner_file" class="block text-lg mb-2">File Banner</label>
          <input type="file" id="banner_file" name="banner_file" accept="image/*"
                 class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
        </div>

        <!-- URL Tujuan -->
        <div>
          <label for="url" class="block text-lg mb-2">URL Tujuan</label>
          <input type="text" id="url" name="url" placeholder="contoh: index.php?pege=detail&id=1"
                 class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
        </div>

        <!-- Pilih Film -->
        <div>
          <label class="block text-lg mb-2">Pilih Film (Opsional)</label>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <?php
              $films = mysqli_query($connection, "SELECT id_film, nama_film FROM film ORDER BY nama_film ASC");
              while ($f = mysqli_fetch_assoc($films)) {
                echo "
                <label class='flex items-center gap-2 cursor-pointer bg-[#0c0d0f] border border-gray-600 px-3 py-2 rounded-lg hover:bg-[#1a1b1f] transition'>
                  <input 
                    type='radio' 
                    name='id_film' 
                    value='{$f['id_film']}' 
                    class='w-4 h-4 text-blue-600 border-gray-600 focus:ring-blue-500'>
                  <span class='text-sm'>{$f['nama_film']}</span>
                </label>";
              }
            ?>
          </div>
          <p class="text-xs text-gray-400 mt-2">Hanya bisa pilih satu film</p>
        </div>

        <!-- Tombol -->
        <div>
          <button type="submit" 
                  class="w-full bg-blue-600 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
            Tambahkan
          </button>
        </div>

      </form>
    </div>
  </main>
</div>
