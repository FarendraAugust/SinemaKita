<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent mb-4">
        Edit Tag
      </h1>
      <a href="<?= getenv('APP_URL') ?: '/sinemakita' ?>/admin/tag" 
         class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition">
        Batal
      </a>
    </header>

    <!-- Alert -->
    <?php
    if (isset($_SESSION['error_msg'])) {
        echo '<div class="p-3 mb-3 text-white bg-red-900 rounded">' . htmlspecialchars($_SESSION['error_msg']) . '</div>';
        unset($_SESSION['error_msg']);
    }

    if (isset($_SESSION['success_msg'])) {
        echo '<div class="p-3 mb-3 text-white bg-green-900 rounded">' . htmlspecialchars($_SESSION['success_msg']) . '</div>';
        unset($_SESSION['success_msg']);
    }

    include 'utils/connection.php';

    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo '<div class="p-3 mb-3 text-white bg-red-900 rounded">ID tag tidak ditemukan!</div>';
    } else {
        $stmt = $connection->prepare("SELECT * FROM tag WHERE id_tag = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo '<div class="p-3 mb-3 text-white bg-red-900 rounded">Data tag tidak ditemukan!</div>';
        } else {
            $d = $result->fetch_assoc();
    ?>

    <!-- Form -->
    <div class="w-full bg-[#18191c] p-6 rounded-2xl shadow-lg">
      <form action="<?= getenv('APP_URL') ?: '/sinemakita' ?>/src/peges/admin_dashboard/tag/edit_action.php" 
            method="post" class="space-y-5">

        <input type="hidden" name="id" value="<?= htmlspecialchars($d['id_tag']) ?>">

        <div>
          <label for="nama_tag" class="block text-lg mb-2">Nama Tag</label>
          <input 
            type="text" 
            id="nama_tag" 
            name="nama_tag" 
            value="<?= htmlspecialchars($d['nama_tag']) ?>" 
            class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" 
            required>
        </div>

        <div>
          <button type="submit" 
                  class="w-full bg-blue-600 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>

    <?php 
        } // end if data ada
        $stmt->close();
    } // end if id
    $connection->close();
    ?>
  </main>
</div>
