<?php
require 'utils/connection.php'; // sudah otomatis load .env

$id = (int)($_GET['id'] ?? 0);

// Cek validitas ID
if ($id <= 0) {
  $_SESSION['error_msg'] = "ID banner tidak valid!";
  header("Location: {$APP_URL}/admin/banner");
  exit();
}

// Ambil data banner
$banner = mysqli_query($connection, "SELECT * FROM banner WHERE id_banner = $id");
$data = mysqli_fetch_assoc($banner);

if (!$data) {
  $_SESSION['error_msg'] = "Data banner tidak ditemukan!";
  header("Location: {$APP_URL}/admin/banner");
  exit();
}
?>

<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold">Edit Banner</h1>
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
      <form action="<?= $APP_URL ?>/src/peges/admin_dashboard/banner/edit_action.php" 
            method="post" 
            enctype="multipart/form-data" 
            class="space-y-5">

        <input type="hidden" name="id_banner" value="<?= htmlspecialchars($data['id_banner']); ?>">

        <!-- File Banner -->
        <div>
          <label for="banner_file" class="block text-lg mb-2">File Banner</label>
          <input type="file" id="banner_file" name="banner_file" accept="image/*"
                 class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3">
          <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti</p>
          <div class="mt-2">
            <img src="<?= $APP_URL ?>/assets/banner/<?= htmlspecialchars($data['banner_file']); ?>" 
                 alt="Banner Lama" 
                 class="w-64 rounded-lg border border-gray-600">
          </div>
        </div>

        <!-- URL Tujuan -->
        <div>
          <label for="url" class="block text-lg mb-2">URL Tujuan</label>
          <input type="text" id="url" name="url" value="<?= htmlspecialchars($data['url']); ?>"
                 class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required>
        </div>

        <!-- Pilih Film -->
        <div>
          <label class="block text-lg mb-2">Pilih Film (Opsional)</label>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <?php
              $films = mysqli_query($connection, "SELECT id_film, nama_film FROM film ORDER BY nama_film ASC");
              while ($f = mysqli_fetch_assoc($films)) {
                $checked = ($data['id_film'] == $f['id_film']) ? "checked" : "";
                ?>
                <label class='flex items-center gap-2 cursor-pointer bg-[#0c0d0f] border border-gray-600 px-3 py-2 rounded-lg hover:bg-[#1a1b1f] transition'>
                  <input 
                    type='radio' 
                    name='id_film' 
                    value='<?= $f['id_film']; ?>' 
                    class='w-4 h-4 text-blue-600 border-gray-600 focus:ring-blue-500' <?= $checked; ?>>
                  <span class='text-sm'><?= htmlspecialchars($f['nama_film']); ?></span>
                </label>
                <?php
              }
            ?>
          </div>
          <p class="text-xs text-gray-400 mt-2">Hanya bisa pilih satu film</p>
        </div>

        <!-- Tombol -->
        <div>
          <button type="submit" 
                  class="w-full bg-blue-600 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
            Update
          </button>
        </div>

      </form>
    </div>
  </main>
</div>
