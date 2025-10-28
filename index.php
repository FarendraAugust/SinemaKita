<?php
session_start();
include 'utils/connection.php';
include 'utils/auth_check.php';

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Ambil data pengguna untuk role
$id = $_SESSION['session_id'] ?? 0;
$stmt = $connection->prepare("SELECT is_admin FROM pengguna WHERE id_pengguna = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$dataUser = $stmt->get_result()->fetch_assoc();
$role = (int)($dataUser['is_admin'] ?? 0);
$stmt->close();

// Ambil parameter GET
$pege = $_GET['pege'] ?? 'beranda';
$menu = $_GET['menu'] ?? '';
$action = $_GET['action'] ?? '';
$slug = $_GET['slug'] ?? '';
$tagParam = $_GET['tag'] ?? ''; // gunakan parameter khusus tag
$episode = $_GET['episode'] ?? '';

// Default mapping title
$defaultTitles = [
    'beranda' => 'Beranda - SinemaKita',
    'login' => 'Masuk - SinemaKita',
    'register' => 'Registrasi - SinemaKita',
    'akun' => 'Akun - SinemaKita',
    'akun_setting' => 'Edit Profil - SinemaKita',
    'admin' => 'Dashboard Admin - SinemaKita',
];

// Tentukan title dinamis
$title = $defaultTitles[$pege] ?? ucfirst($pege) . " - SinemaKita";

if ($pege === 'movie' && !empty($slug)) {
    // Ambil data film berdasarkan slug
    $stmt = $connection->prepare("SELECT nama_film FROM film WHERE slug = ? LIMIT 1");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $filmData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $title = $filmData 
        ? htmlspecialchars($filmData['nama_film']) . " - SinemaKita" 
        : "Film Tidak Ditemukan - SinemaKita";

} elseif ($pege === 'tag' && !empty($tagParam)) {
    // Ambil data tag dari database
    $stmt = $connection->prepare("SELECT nama_tag FROM tag WHERE nama_tag = ? LIMIT 1");
    $stmt->bind_param("s", $tagParam);
    $stmt->execute();
    $tagData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $title = $tagData 
        ? htmlspecialchars($tagData['nama_tag']) . " - SinemaKita"
        : "Tag Tidak Ditemukan - SinemaKita";

} elseif ($pege === 'admin') {
    // Jika halaman admin
    $parts = [];
    if (!empty($action)) $parts[] = ucfirst($action);
    if (!empty($menu)) $parts[] = ucfirst($menu);
    $parts[] = "Dashboard Admin";
    $title = implode(' - ', $parts);
}

$APP_URL = $_ENV['APP_URL'];

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>

    <link href="<?= $APP_URL ?>/src/output.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body class="bg-[#0c0d0f]">
    <main>
        <?php
        switch ($pege) {

            // ===========================
            // 🔐 HALAMAN LOGIN & REGISTER
            // ===========================
            case 'login':
                if (!isset($_SESSION['session_username'])) {
                    include 'src/auth/login.php';
                } else {
                    header("Location: index.php?pege=beranda");
                }
                break;

            case 'register':
                include 'src/auth/register.php';
                break;

            // ===========================
            // 👤 HALAMAN AKUN
            // ===========================
            case 'akun':
                include 'src/peges/profile.php';
                break;
            case 'akun_setting':
                include 'src/peges/setting_profile.php';
                break;
            case 'verifikasi':
                include 'src/auth/verify.php';
                break;
            case 'kirim_verifikasi':
                include 'src/auth/send_verification.php';
                break;
            case 'lupa_password':
                include 'src/auth/forgot_password/forgot_password.php';
                break;
            case 'reset_password':
                include 'src/auth/forgot_password/reset_password.php';
                break;
            case 'movie':
                include 'src/peges/movie.php';
                break;
            case 'tag':
                include 'src/peges/tag.php';
                break;

            // ===========================
            // 🏠 BERANDA
            // ===========================
            case 'beranda':
                if (isset($_SESSION['session_username']) && $role === 1) {
                    include 'src/peges/admin_dashboard/beranda.php';
                } else {
                    include 'src/peges/beranda.php';
                }
                break;

            // ===========================
            // 🧭 DASHBOARD ADMIN
            // ===========================
            case 'admin':
                if ($role !== 1) {
                    echo "<div class='text-center text-white mt-20'>❌ Akses ditolak</div>";
                    break;
                }

                switch ($menu) {
                    case 'pengguna':
                        switch ($action) {
                            case 'tambah':
                                include 'src/peges/admin_dashboard/pengguna/tambah.php';
                                break;
                            case 'edit':
                                include 'src/peges/admin_dashboard/pengguna/edit.php';
                                break;
                            default:
                                include 'src/peges/admin_dashboard/pengguna/beranda.php';
                                break;
                        }
                        break;

                    case 'tag':
                        switch ($action) {
                            case 'tambah':
                                include 'src/peges/admin_dashboard/tag/tambah.php';
                                break;
                            case 'edit':
                                include 'src/peges/admin_dashboard/tag/edit.php';
                                break;
                            default:
                                include 'src/peges/admin_dashboard/tag/beranda.php';
                                break;
                        }
                        break;

                    case 'film':
                        switch ($action) {
                            case 'tambah':
                                include 'src/peges/admin_dashboard/film/tambah.php';
                                break;
                            case 'edit':
                                include 'src/peges/admin_dashboard/film/edit.php';
                                break;
                            case 'detail':
                                include 'src/peges/admin_dashboard/film/detail_film.php';
                                break;
                            default:
                                include 'src/peges/admin_dashboard/film/beranda.php';
                                break;
                        }
                        break;

                    case 'review':
                        switch ($action) {
                            case 'detail':
                                include 'src/peges/admin_dashboard/review/detail_review.php';
                                break;
                            default:
                                include 'src/peges/admin_dashboard/review/beranda.php';
                                break;
                        }
                        break;

                    case 'episode':
                        switch ($action) {
                            case 'tambah':
                                include 'src/peges/admin_dashboard/episode/tambah.php';
                                break;
                            case 'edit':
                                include 'src/peges/admin_dashboard/episode/edit.php';
                                break;
                            case 'detail':
                                switch ($episode) {
                                    case 'tambah':
                                        include 'src/peges/admin_dashboard/episode/episode_link/tambah.php';
                                        break;
                                    case 'edit':
                                        include 'src/peges/admin_dashboard/episode/episode_link/edit.php';
                                        break;
                                    default:
                                        include 'src/peges/admin_dashboard/episode/episode_link/beranda.php';
                                        break;
                                }
                                break;
                            default:
                                include 'src/peges/admin_dashboard/episode/beranda.php';
                                break;
                        }
                        break;

                    case 'banner':
                        switch ($action) {
                            case 'tambah':
                                include 'src/peges/admin_dashboard/banner/tambah.php';
                                break;
                            case 'edit':
                                include 'src/peges/admin_dashboard/banner/edit.php';
                                break;
                            default:
                                include 'src/peges/admin_dashboard/banner/beranda.php';
                                break;
                        }
                        break;

                    default:
                        include 'src/peges/admin_dashboard/beranda.php';
                        break;
                }
                break;

            // ===========================
            // 🎬 HALAMAN MOVIE
            // ===========================
            

            // ===========================
            // 🚫 HALAMAN 404
            // ===========================
            default:
                echo "<div class='text-center text-white mt-20'>
                        <h1 class='text-3xl font-bold mb-2'>404 - Halaman Tidak Ditemukan</h1>
                        <p class='text-gray-400'>Halaman yang Anda cari tidak tersedia.</p>
                        <a href='index.php?pege=beranda' class='text-orange-400 hover:underline'>Kembali ke Beranda</a>
                      </div>";
                break;
        }
        ?>
    </main>
</body>
</html>
