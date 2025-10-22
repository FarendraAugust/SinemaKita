<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// === LOAD .env TANPA FILE TERPISAH ===
$envPath = __DIR__ . '/../../.env';
if (file_exists($envPath)) {
  foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#') === 0 || !str_contains($line, '=')) continue;
    [$name, $value] = array_map('trim', explode('=', $line, 2));
    $_ENV[$name] = trim($value, "\"'");
  }
}

include __DIR__ . '/../../utils/connection.php';
?>

<nav class="fixed w-full z-50 top-0 bg-[#18191c]/95 backdrop-blur-md border-b border-gray-800 shadow-lg">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16 items-center">

      <!-- Logo -->
      <a href="<?= $_ENV['APP_URL'] ?? '/sinemakita' ?>" class="shrink-0 flex items-center gap-2">
        <img src="<?= ($_ENV['APP_URL'] ?? '/sinemakita') ?>/img/favicon.png" alt="logo" class="w-9 h-9 animate-pulse">
        <span class="text-xl font-extrabold bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">
          <?= $_ENV['APP_NAME'] ?? 'SinemaKita' ?>
        </span>
      </a>

      <!-- Menu Desktop -->
      <div class="hidden md:flex gap-6 text-md items-center">
        <?php
          $tag_query = mysqli_query($connection, "SELECT id_tag, nama_tag, klik FROM tag ORDER BY klik DESC");
          $tags = [];
          while ($row = mysqli_fetch_assoc($tag_query)) {
            $tags[] = $row;
          }
          $utama = array_slice($tags, 0, 3);
          $lainnya = array_slice($tags, 3);
        ?>

        <?php foreach ($utama as $tag): ?>
          <a href="<?= ($_ENV['APP_URL'] ?? '/sinemakita') ?>/tag/<?= urlencode(strtolower($tag['nama_tag'])) ?>" 
             class="relative pb-1 text-white group hover:text-orange-400 transition-colors">
            <?= htmlspecialchars($tag['nama_tag']) ?>
            <span class="absolute left-0 bottom-0 h-[2px] w-full origin-left scale-x-0 transform bg-gradient-to-r from-orange-400 to-red-500 transition-transform duration-300 group-hover:scale-x-100"></span>
          </a>
        <?php endforeach; ?>

        <?php if (!empty($lainnya)): ?>
          <div class="relative group">
            <a href="#" class="relative pb-1 text-white flex items-center gap-1 hover:text-orange-400 transition-colors">
              Lainnya <ion-icon name="chevron-down-outline"></ion-icon>
            </a>
            <div class="absolute left-0 mt-2 w-40 bg-[#18191c] rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 border border-gray-700">
              <?php foreach ($lainnya as $tag): ?>
                <a href="<?= ($_ENV['APP_URL'] ?? '/sinemakita') ?>/tag/<?= urlencode(strtolower($tag['nama_tag'])) ?>" 
                   class="block px-4 py-2 text-sm text-white hover:bg-[#2a2b2e] hover:text-orange-400 transition">
                  <?= htmlspecialchars($tag['nama_tag']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Search + User (Desktop) -->
      <div class="hidden md:flex items-center gap-4">
        <!-- Search Desktop -->
        <div class="relative">
          <input 
            type="text" id="search"
            placeholder="Cari film... (Ctrl + K)"
            class="w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-700 bg-[#101113] text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
            autocomplete="off"
          >
          <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
            <ion-icon name="search-outline"></ion-icon>
          </span>
          <div id="search-results" class="absolute mt-2 w-80 bg-[#18191c] border border-gray-700 rounded-lg shadow-lg hidden max-h-80 overflow-y-auto z-50"></div>
        </div>

        <!-- User -->
        <?php 
        if (!isset($_SESSION['session_username'])) {
          echo '<a href="' . ($_ENV['APP_URL'] ?? '/sinemakita') . '/login" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-400 hover:to-red-400 text-white font-medium transition shadow-md">
                  <ion-icon name="person-circle-outline" class="text-xl"></ion-icon>
                  <span>Login</span>
                </a>';
        } else {
          $stmt = $connection->prepare("SELECT profile_img, provider FROM pengguna WHERE id_pengguna = ?");
          $stmt->bind_param("i", $_SESSION['session_id']);
          $stmt->execute();
          $userData = $stmt->get_result()->fetch_assoc();
          $stmt->close();

          $profileImg = ($_ENV['APP_URL'] ?? '/sinemakita') . "/assets/profile/default-avatar.png";
          if (!empty($userData['profile_img'])) {
            if (!empty($userData['provider']) && in_array($userData['provider'], ['google', 'facebook'])) {
              $profileImg = $userData['profile_img'];
            } else {
              $profileImg = ($_ENV['APP_URL'] ?? '/sinemakita') . "/assets/profile/" . htmlspecialchars($userData['profile_img']);
            }
          }

          echo '<a href="' . ($_ENV['APP_URL'] ?? '/sinemakita') . '/akun" class="flex items-center gap-2 px-3 py-1 rounded-lg bg-[#101113] hover:bg-[#2a2b2e] text-white font-medium transition shadow-md">
                  <img src="' . $profileImg . '" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-gray-700">
                  <span class="hidden sm:inline">' . htmlspecialchars($_SESSION['session_username']) . '</span>
                </a>';
        }
        ?>
      </div>

      <!-- Tombol mobile -->
      <div class="flex md:hidden items-center gap-3">
        <button id="mobile-search-toggle" class="text-gray-300 text-xl hover:text-orange-400">
          <ion-icon name="search-outline"></ion-icon>
        </button>
        <button id="mobile-toggle" class="text-gray-300 text-2xl hover:text-orange-400 transition">
          <ion-icon name="menu-outline"></ion-icon>
        </button>
      </div>
    </div>
  </div>

  <!-- Search Mobile -->
  <div id="mobile-search" class="hidden md:hidden border-t border-gray-800 bg-[#101113] p-4 animate-slideDown">
    <div class="relative">
      <input 
        type="text" id="search-mobile"
        placeholder="Cari film... (Ctrl + K)"
        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-700 bg-[#1b1c1f] text-white placeholder-gray-400 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
        autocomplete="off"
      >
      <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
        <ion-icon name="search-outline"></ion-icon>
      </span>
      <div id="search-results-mobile" class="absolute mt-2 w-full bg-[#18191c] border border-gray-700 rounded-lg shadow-lg hidden max-h-80 overflow-y-auto z-50"></div>
    </div>
  </div>

  <!-- Menu Mobile -->
  <div id="mobile-menu" class="hidden md:hidden border-t border-gray-800 bg-[#18191c] p-4 space-y-3 animate-slideDown">
    <div class="flex overflow-x-auto gap-2 scrollbar-hide pb-2">
      <?php foreach ($tags as $tag): ?>
        <a href="<?= ($_ENV['APP_URL'] ?? '/sinemakita') ?>/tag/<?= urlencode(strtolower($tag['nama_tag'])) ?>" 
           class="px-3 py-1 text-sm rounded-full bg-gradient-to-r from-orange-500/20 to-red-500/20 text-gray-300 hover:text-orange-400 hover:bg-[#2a2b2e] transition whitespace-nowrap">
          <?= htmlspecialchars($tag['nama_tag']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="pt-2 border-t border-gray-700 flex justify-end">
      <?php 
      if (!isset($_SESSION['session_username'])) {
        echo '<a href="' . ($_ENV['APP_URL'] ?? '/sinemakita') . '/login" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 text-white font-medium transition shadow-md">
                <ion-icon name="person-circle-outline" class="text-xl"></ion-icon>
                <span>Login</span>
              </a>';
      } else {
        echo '<a href="' . ($_ENV['APP_URL'] ?? '/sinemakita') . '/akun" class="flex items-center gap-2 text-white hover:text-orange-400">
                <ion-icon name="person-outline" class="text-xl"></ion-icon>
                <span>Akun Saya</span>
              </a>';
      }
      ?>
    </div>
  </div>
</nav>

<script>
  // Animasi dan toggle
  const style = document.createElement('style');
  style.innerHTML = `
    @keyframes slideDown {
      0% { opacity: 0; transform: translateY(-10px); }
      100% { opacity: 1; transform: translateY(0); }
    }
    .animate-slideDown { animation: slideDown 0.25s ease forwards; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
  `;
  document.head.appendChild(style);

  const menuBtn = document.getElementById('mobile-toggle');
  const menu = document.getElementById('mobile-menu');
  const searchBtn = document.getElementById('mobile-search-toggle');
  const searchBox = document.getElementById('mobile-search');
  const desktopSearch = document.getElementById('search');
  const mobileSearch = document.getElementById('search-mobile');

  menuBtn.addEventListener('click', () => {
    menu.classList.toggle('hidden');
    searchBox.classList.add('hidden');
  });

  searchBtn.addEventListener('click', () => {
    searchBox.classList.toggle('hidden');
    menu.classList.add('hidden');
  });

  // Shortcut Ctrl + K / Cmd + K
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      if (window.innerWidth >= 768) {
        desktopSearch.focus();
      } else {
        searchBox.classList.remove('hidden');
        mobileSearch.focus();
      }
    }
  });

  // Search AJAX
  const setupSearch = (inputId, resultId) => {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultId);
    if (!input) return;
    let timeout = null;

    input.addEventListener("input", function() {
      clearTimeout(timeout);
      const q = this.value.trim();
      if (q.length < 2) { results.classList.add("hidden"); results.innerHTML = ""; return; }
      timeout = setTimeout(() => {
        fetch(`<?= ($_ENV['APP_URL'] ?? '/sinemakita') ?>/utils/search_handler.php?q=${encodeURIComponent(q)}`)
          .then(res => res.json())
          .then(data => {
            results.innerHTML = data.length === 0
              ? `<p class='p-3 text-gray-400 text-sm'>Tidak ada hasil untuk "<b>${q}</b>"</p>`
              : data.map(f => `
                <a href="<?= ($_ENV['APP_URL'] ?? '/sinemakita') ?>/movie/${f.slug}" class="flex items-center gap-3 p-2 hover:bg-[#2a2b2e] rounded transition">
                  <img src="<?= ($_ENV['APP_URL'] ?? '/sinemakita') ?>/assets/film/${f.poster_film}" alt="${f.nama_film}" class="w-12 h-16 object-cover rounded">
                  <div><p class="text-white font-medium text-sm">${f.nama_film}</p><p class="text-gray-400 text-xs">${f.rilis}</p></div>
                </a>`).join('');
            results.classList.remove("hidden");
          });
      }, 300);
    });

    document.addEventListener("click", (e) => {
      if (!results.contains(e.target) && e.target !== input) results.classList.add("hidden");
    });
  };

  setupSearch("search", "search-results");
  setupSearch("search-mobile", "search-results-mobile");
</script>
