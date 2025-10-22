<?php
include 'utils/connection.php';

// Ambil APP_URL dari .env
$app_url = rtrim(getenv('APP_URL') ?: '/sinemakita', '/');
?>

<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent mb-4">
        Tambah Tag
      </h1>
      <a href="<?= $app_url ?>/admin/tag" 
         class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition">
        Batal
      </a>
    </header>

    <!-- Alert -->
    <?php if (isset($_SESSION['error_msg'])): ?>
      <div class="p-3 mb-3 text-white bg-red-900 rounded">
        <?= $_SESSION['error_msg']; ?>
      </div>
      <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_msg'])): ?>
      <div class="p-3 mb-3 text-white bg-green-900 rounded">
        <?= $_SESSION['success_msg']; ?>
      </div>
      <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- Form -->
    <div class="w-full bg-[#18191c] p-6 rounded-2xl shadow-lg">
      <form action="<?= $app_url ?>/src/peges/admin_dashboard/tag/tambah_action.php" method="post" class="space-y-5">
        <div>
          <label for="nama_tag" class="block text-lg mb-2">Nama Tag</label>
          <input 
            type="text" 
            id="nama_tag" 
            name="nama_tag" 
            class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" 
            placeholder="Masukkan nama tag..." 
            required>
        </div>

        <div>
          <button 
            type="submit" 
            class="w-full bg-blue-600 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
            Tambahkan
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
