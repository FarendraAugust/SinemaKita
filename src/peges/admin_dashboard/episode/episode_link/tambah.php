<?php
$APP_URL = rtrim($_ENV['APP_URL'], '/'); // Ambil dari env
?>

<div class="min-h-screen flex bg-[#0c0d0f] text-white font-sans ml-64">

  <?php 
  include 'src/components/sidebar.php';
  include 'utils/connection.php'; 
  $id = $_GET['id'];

  $no_episode = mysqli_query($connection, "
      SELECT MAX(CAST(nomor_episode AS UNSIGNED)) AS last_episode
      FROM episode
      WHERE id_film = '$id'
  ");
  $row = mysqli_fetch_assoc($no_episode);
  $next_episode = $row['last_episode'] ? $row['last_episode'] + 1 : 1;
  ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-600 bg-clip-text text-transparent mb-4">Tambah Episode</h1>
      <a href="<?= $APP_URL ?>/admin/episode/detail/<?= $id ?>" 
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
      <form action="<?= $APP_URL ?>/src/peges/admin_dashboard/episode/episode_link/tambah_action.php" 
            method="post" class="space-y-5">
        <input type="hidden" name="id_film" value="<?= $id ?>">
        <!-- hidden final nomor_episode -->
        <input type="hidden" name="nomor_episode" id="nomor_episode" value="<?= $next_episode ?>">

        <div>
          <label class="block text-lg mb-2">Nomor Episode</label>

          <div class="flex gap-4">
            <!-- Opsi otomatis -->
            <label class="flex-1 cursor-pointer">
              <input type="radio" name="mode_episode" value="auto" checked class="hidden peer">
              <input type="text" id="no_episode_auto"
                    value="<?= $next_episode ?>"
                    readonly
                    class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3 text-gray-400 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-600 transition">
              <p class="mt-1 text-sm text-gray-400">Otomatis</p>
            </label>

            <!-- Opsi manual -->
            <label class="flex-1 cursor-pointer">
              <input type="radio" name="mode_episode" value="manual" class="hidden peer">
              <input type="text" id="no_episode_manual"
                    placeholder="Masukkan episode"
                    class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-600 transition">
              <p class="mt-1 text-sm text-gray-400">Manual</p>
            </label>
          </div>
        </div>

        <div>
          <label class="block text-lg mb-2">Server & URL Video</label>
          
          <div id="server-list" class="space-y-3">
            <!-- Group server+url pertama -->
            <div class="flex gap-3 items-center bg-[#141518] p-3 rounded-xl shadow">
              <input type="text" name="nama_server[]" placeholder="Nama Server"
                     class="flex-1 rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-2 
                            focus:border-blue-600 focus:ring-2 focus:ring-blue-600 transition" required>
              <input type="url" name="url[]" placeholder="URL Video"
                     class="flex-1 rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-2 
                            focus:border-blue-600 focus:ring-2 focus:ring-blue-600 transition" required>
              <button type="button" onclick="removeServer(this)" 
                      class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm font-medium shadow transition">
                ✕
              </button>
            </div>
          </div>

          
          <button type="button" onclick="addServer()" 
          class="mt-3 ml-3 bg-green-900 cursor-pointer px-4 py-2 rounded-lg font-medium shadow transition">
          + Tambah Server
        </button>

        <div>
          <label class="block text-lg mb-2 mt-2">URL Download Episode (opsional)</label>
          <input type="url" name="download_url" placeholder="Masukkan URL download episode"
                class="w-full rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-3 
                        focus:border-green-600 focus:ring-2 focus:ring-green-600 transition">
        </div>
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

<script>
  const radios = document.querySelectorAll('input[name="mode_episode"]');
  const inputAuto = document.getElementById("no_episode_auto");
  const inputManual = document.getElementById("no_episode_manual");
  const hiddenEpisode = document.getElementById("nomor_episode");

  // Default: auto aktif
  hiddenEpisode.value = inputAuto.value;

  // Klik auto
  radios[0].addEventListener("change", () => {
    hiddenEpisode.value = inputAuto.value;
    inputManual.value = "";
    inputManual.readOnly = true;
  });

  // Klik manual
  radios[1].addEventListener("change", () => {
    inputManual.readOnly = false;
    inputManual.focus();
  });

  // Update hidden saat manual diisi
  inputManual.addEventListener("input", () => {
    hiddenEpisode.value = inputManual.value;
  });

  function addServer() {
    const list = document.getElementById("server-list");

    const div = document.createElement("div");
    div.className = "flex gap-3 items-center bg-[#141518] p-3 rounded-xl shadow";

    div.innerHTML = `
      <input 
        type="text" 
        name="nama_server[]" 
        placeholder="Nama Server"
        class="flex-1 rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-2 
               focus:border-blue-600 focus:ring-2 focus:ring-blue-600 transition" 
        required
      >
      <input 
        type="url" 
        name="url[]" 
        placeholder="URL Video"
        class="flex-1 rounded-lg border border-gray-600 bg-[#0c0d0f] px-4 py-2 
               focus:border-blue-600 focus:ring-2 focus:ring-blue-600 transition" 
        required
      >
      <button 
        type="button" 
        onclick="removeServer(this)" 
        class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm font-medium shadow transition"
      >
        ✕
      </button>
    `;

    list.appendChild(div);
  }

  function removeServer(button) {
    button.parentElement.remove();
  }
</script>
