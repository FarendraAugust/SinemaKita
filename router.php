<?php
// router.php — untuk meniru .htaccess pada PHP built-in server

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = __DIR__;

// Jika file/folder benar-benar ada, langsung tampilkan (misal: file CSS, JS, gambar, dll)
if ($uri !== '/' && file_exists($root . $uri)) {
    return false;
}

// Bersihkan slash awal dan akhir
$path = trim($uri, '/');

// Logika rewrite utama
switch (true) {
    // === Admin Dashboard ===
    case $path === 'admin':
        $_GET['pege'] = 'admin';
        break;

    // --- Admin menu ---
    case preg_match('#^admin/(pengguna|tag|film|review|episode|banner)$#', $path, $m):
        $_GET['pege'] = 'admin';
        $_GET['menu'] = $m[1];
        break;

    // --- Admin menu + action ---
    case preg_match('#^admin/(pengguna|tag|film|review|episode|banner)/(tambah|edit|detail)$#', $path, $m):
        $_GET['pege'] = 'admin';
        $_GET['menu'] = $m[1];
        $_GET['action'] = $m[2];
        break;

    // --- Admin menu + action + id ---
    case preg_match('#^admin/(pengguna|tag|film|review|episode|banner)/(edit|detail)/([0-9]+)$#', $path, $m):
        $_GET['pege'] = 'admin';
        $_GET['menu'] = $m[1];
        $_GET['action'] = $m[2];
        $_GET['id'] = $m[3];
        break;

    // --- Login/Register/Akun dll ---
    case in_array($path, ['login', 'register', 'akun', 'verifikasi', 'kirim-verifikasi']):
        $_GET['pege'] = str_replace('-', '_', $path);
        break;

    // --- Akun Setting ---
    case $path === 'akun/setting':
        $_GET['pege'] = 'akun_setting';
        break;

    // --- Movie detail ---
    case preg_match('#^movie/([^/]+)/?$#', $path, $m):
        $_GET['pege'] = 'movie';
        $_GET['slug'] = $m[1];
        break;

    // --- Movie + episode ---
    case preg_match('#^movie/([^/]+)/([^/]+)/?$#', $path, $m):
        $_GET['pege'] = 'movie';
        $_GET['slug'] = $m[1];
        $_GET['episode'] = $m[2];
        break;

    // --- Tag page ---
    case preg_match('#^tag/([^/]+)/?$#', $path, $m):
        $_GET['pege'] = 'tag';
        $_GET['tag'] = $m[1];
        break;
    case $path === 'tag':
        $_GET['pege'] = 'tag';
        break;

    // --- Lupa Password / Reset Password ---
    case $path === 'lupa-password':
        $_GET['pege'] = 'lupa_password';
        break;
    case $path === 'reset-password':
        $_GET['pege'] = 'reset_password';
        break;

    // --- Logout ---
    case $path === 'logout':
        require $root . '/src/auth/logout_action.php';
        exit;

    // --- Default (misal beranda) ---
    case $path === '' || $path === 'beranda':
        $_GET['pege'] = 'beranda';
        break;

    default:
        // Kalau URL tidak dikenali → 404
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "URL: $path";
        exit;
}

// Jalankan index.php
require $root . '/index.php';
