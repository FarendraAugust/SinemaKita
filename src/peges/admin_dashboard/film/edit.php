<?php
$APP_URL = rtrim($_ENV['APP_URL'], '/'); // Base URL dari .env

include 'utils/connection.php';

$id = (int)$_GET['id'];
$data = mysqli_query($connection, "SELECT * FROM film WHERE id_film = $id");
$d = mysqli_fetch_assoc($data);

// Ambil relasi tag film
$film_tags = [];
$qTag = mysqli_query($connection, "SELECT id_tag FROM film_tag WHERE id_film = $id");
while ($row = mysqli_fetch_assoc($qTag)) {
    $film_tags[] = $row['id_tag'];
}
?>

<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php include 'src/components/sidebar.php'; ?>

  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-pink-400 to-purple-500 bg-clip-text text-transparent mb-4">Edit Film</h1>
      <a href="<?= $APP_URL ?>/admin/film" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded font-medium transition">Batal</a>
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
      <form action="<?= $APP_URL ?>/src/peges/admin_dashboard/film/edit_action.php" method="post" enctype="multipart/form-data" class="space-y-5">
        <input type="hidden" name="id_film" value="<?= $d['id_film'] ?>">

        <!-- Nama Film -->
        <div>
          <label for="nama_film" class="block text-lg mb-2">Nama Film</label>
          <input type="text" id="nama_film" name="nama_film" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" value="<?= htmlspecialchars($d['nama_film']) ?>" required>
        </div>

        <!-- Poster Film -->
        <div class="space-y-2">
          <label for="poster_film" class="block text-lg font-medium">Poster Film</label>
          <div class="flex items-center rounded-lg border border-gray-600 bg-[#0c0d0f] overflow-hidden">
            <label for="poster_film" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 cursor-pointer text-white font-medium">
              Pilih File
            </label>
            <span class="px-3 border-l border-gray-600 flex-1 text-sm text-gray-400 truncate" id="file-name">
              <?= !empty($d['poster_film']) ? htmlspecialchars($d['poster_film']) : 'Belum ada file' ?>
            </span>
            <input type="file" id="poster_film" name="poster_film" accept="image/*" class="hidden"
              onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Belum ada file'">
          </div>
        </div>

        <!-- Deskripsi -->
        <div>
          <label for="deskripsi" class="block text-lg mb-2">Deskripsi</label>
          <textarea id="deskripsi" name="deskripsi" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" required><?= htmlspecialchars($d['deskripsi']) ?></textarea>
        </div>

        <!-- Slug -->
        <div>
          <label for="slug" class="block text-lg mb-2">Slug</label>
          <input type="text" id="slug" name="slug" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3 text-gray-400" value="<?= htmlspecialchars($d['slug']) ?>" readonly required>
          <p class="text-xs text-gray-400 mt-1">Slug dibuat otomatis dari nama film</p>
        </div>

        <!-- Tag -->
        <div>
          <label for="tag" class="block text-lg mb-2">Tag</label>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <?php
            $query = mysqli_query($connection, "SELECT * FROM tag ORDER BY nama_tag ASC");
            while ($row = mysqli_fetch_assoc($query)) {
                $checked = in_array($row['id_tag'], $film_tags) ? "checked" : "";
                echo "
                <label class='flex items-center gap-2 cursor-pointer bg-[#0c0d0f] border border-gray-600 px-3 py-2 rounded-lg hover:bg-[#1a1b1f] transition'>
                  <input type='checkbox' name='tag[]' value='{$row['id_tag']}' class='w-4 h-4 text-blue-600 border-gray-600 rounded focus:ring-blue-500' $checked>
                  <span class='text-sm'>{$row['nama_tag']}</span>
                </label>";
            }
            ?>
          </div>
        </div>

        <!-- Usia -->
        <div>
          <label for="usia" class="block text-lg mb-2">Usia</label>
          <div class="flex flex-wrap gap-3">
            <?php
            $usia_opsi = ["SU" => "Semua Umur", "13+" => "13+", "17+" => "17+", "21+" => "21+"];
            foreach ($usia_opsi as $val => $label) {
                $checked = ($d['usia'] == $val) ? "checked" : "";
                echo "
                <label class='flex items-center gap-2 cursor-pointer bg-[#0c0d0f] border border-gray-600 px-3 py-2 rounded-lg hover:bg-[#1a1b1f] transition'>
                  <input type='radio' name='usia' value='$val' $checked required>
                  <span class='text-sm'>$label</span>
                </label>";
            }
            ?>
          </div>
        </div>

        <!-- Negara -->
        <div>
          <label for="negara" class="block text-lg mb-2">Negara</label>
          <input type="text" id="negara" name="negara" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3" value="<?= htmlspecialchars($d['negara']) ?>" required>
        </div>

        <!-- Status Film -->
        <div>
          <label for="status" class="block text-lg mb-2">Status Film</label>
          <div class="flex flex-wrap gap-3">
            <?php
            $status_opsi = ["Sedang Tayang", "Tamat"];
            foreach ($status_opsi as $val) {
                $checked = ($d['status'] == $val) ? "checked" : "";
                echo "
                <label class='flex items-center gap-2 cursor-pointer bg-[#0c0d0f] border border-gray-600 px-3 py-2 rounded-lg hover:bg-[#1a1b1f] transition'>
                  <input type='radio' name='status' value='$val' $checked required>
                  <span class='text-sm'>$val</span>
                </label>";
            }
            ?>
          </div>
        </div>

        <!-- Tahun Rilis -->
        <div>
          <label for="rilis" class="block text-lg mb-2">Tahun Rilis</label>
          <input type="number" id="rilis" name="rilis" min="1900" max="<?= date('Y'); ?>" value="<?= htmlspecialchars($d['rilis']) ?>" class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div>
          <button type="submit" class="w-full bg-blue-600 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
            Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
  document.getElementById('nama_film').addEventListener('input', function () {
    let slug = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
    document.getElementById('slug').value = slug;
  });
</script>
