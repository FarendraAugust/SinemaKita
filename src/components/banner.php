<?php

$app_url = rtrim($_ENV['APP_URL'], '/');

// 🔍 Ambil data banner dan film
$query = $connection->query("
  SELECT b.id_banner, b.banner_file, b.url, f.nama_film, f.deskripsi 
  FROM banner b
  LEFT JOIN film f ON f.id_film = b.id_film
  ORDER BY b.id_banner ASC
");
?>

<!-- 🎞️ Banner Slider -->
<div class="relative w-full h-[250px] sm:h-[400px] md:h-[500px] overflow-hidden mt-[56px] sm:mt-0">

  <!-- Wrapper slides -->
  <div id="slider" class="flex w-full h-full transition-transform duration-700 ease-in-out">

    <?php while ($row = $query->fetch_assoc()): ?>
      <div class="relative w-full h-full flex-shrink-0">
        <!-- 🖼️ Gambar -->
        <img 
          src="<?= htmlspecialchars("$app_url/assets/banner/" . $row['banner_file']) ?>" 
          alt="<?= htmlspecialchars($row['nama_film'] ?? 'Banner') ?>" 
          class="w-full h-full object-cover" 
        />

        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/70 to-transparent z-10"></div>

        <!-- Konten -->
        <div class="absolute bottom-4 sm:bottom-12 left-3 sm:left-6 z-20 text-white max-w-[90%] sm:max-w-lg bg-black/40 sm:bg-black/50 backdrop-blur-sm p-3 sm:p-4 rounded-lg">
          <h2 class="text-lg sm:text-2xl font-bold leading-snug">
            <?= htmlspecialchars($row['nama_film'] ?? 'Tanpa Judul') ?>
          </h2>
          <p class="text-xs sm:text-sm mt-1 line-clamp-3 sm:line-clamp-none">
            <?= htmlspecialchars($row['deskripsi'] ?? 'Tidak ada deskripsi...') ?>
          </p>
          <a href="<?= htmlspecialchars($row['url'] ?: "$app_url/film?id=" . $row['id_banner']) ?>"
             class="mt-3 inline-block bg-gradient-to-r from-orange-400 to-yellow-500 text-black text-xs sm:text-sm px-4 sm:px-6 py-1.5 sm:py-2 rounded-full font-semibold shadow-lg transition hover:scale-105">
            🎬 Tonton Sekarang
          </a>
        </div>
      </div>
    <?php endwhile; ?>

  </div>

  <!-- Tombol Navigasi -->
  <button onclick="prevSlide()" 
          class="absolute top-1/2 left-2 sm:left-4 -translate-y-1/2 bg-black/60 text-white text-sm sm:text-base px-2 sm:px-3 py-1 sm:py-2 rounded-full hover:bg-black/80 z-40">
    ‹
  </button>
  <button onclick="nextSlide()" 
          class="absolute top-1/2 right-2 sm:right-4 -translate-y-1/2 bg-black/60 text-white text-sm sm:text-base px-2 sm:px-3 py-1 sm:py-2 rounded-full hover:bg-black/80 z-40">
    ›
  </button>
</div>

<script>
  let currentIndex = 0;
  const slider = document.getElementById("slider");
  const totalSlides = slider.children.length;

  function showSlide(index) {
    slider.style.transform = `translateX(-${index * 100}%)`;
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides;
    showSlide(currentIndex);
  }

  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    showSlide(currentIndex);
  }

  // ⏱️ Auto-slide tiap 8 detik
  setInterval(nextSlide, 8000);
</script>
