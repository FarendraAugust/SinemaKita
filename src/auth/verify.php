<?php
include 'utils/connection.php';

$app_url = rtrim($_ENV['APP_URL'], '/');

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
?>

<div class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-b from-[#0c0d0f] to-[#1a1b1f] text-center text-white font-sans px-4">
  <div class="bg-[#18191d]/90 backdrop-blur-xl rounded-2xl shadow-2xl p-10 w-full max-w-md border border-gray-800 animate-fadeIn">

    <!-- Logo / Judul -->
    <h1 class="text-3xl font-bold mb-6 text-[#e50914] flex items-center justify-center gap-2">
      <span class="text-4xl">🎬</span> <span>SinemaKita</span>
    </h1>

    <?php
    if (empty($email) || empty($token)) {
        echo "
        <div class='bg-red-900/40 border border-red-700 rounded-xl p-6'>
          <h2 class='text-xl font-semibold mb-2 text-red-400'>❌ Link Tidak Valid</h2>
          <p class='text-gray-300 text-sm'>Link verifikasi tidak ditemukan atau rusak.</p>
        </div>
        ";
        exit();
    }

    $stmt = $connection->prepare("
        SELECT id_pengguna, verify_expire 
        FROM pengguna 
        WHERE email_pengguna = ? AND verify_token = ? AND verified = 0
    ");
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtotime($row['verify_expire']) < time()) {
            echo "
            <div class='bg-yellow-900/40 border border-yellow-700 rounded-xl p-6'>
              <h2 class='text-xl font-semibold mb-2 text-yellow-400'>⚠️ Link Kadaluarsa</h2>
              <p class='text-gray-300 text-sm'>Maaf, link verifikasi Anda sudah <b>kadaluarsa</b>.</p>
              <p class='mt-2 text-gray-400 text-sm'>Silakan daftar kembali untuk mendapatkan link baru.</p>
            </div>
            <a href='{$app_url}/register' class='mt-6 inline-block bg-[#e50914] hover:bg-[#f6121d] transition px-6 py-2 rounded-lg font-semibold text-white shadow-md hover:shadow-red-500/30'>Daftar Ulang</a>
            ";
        } else {
            $update = $connection->prepare('UPDATE pengguna SET verified = 1, verify_token = NULL, verify_expire = NULL WHERE email_pengguna = ?');
            $update->bind_param('s', $email);
            $update->execute();

            echo "
            <div class='bg-green-900/40 border border-green-700 rounded-xl p-6'>
              <h2 class='text-xl font-semibold mb-2 text-green-400'>✅ Akun Berhasil Diverifikasi!</h2>
              <p class='text-gray-300 text-sm'>Selamat! Akun Anda kini aktif dan siap untuk menikmati ribuan film di <b>SinemaKita</b>.</p>
            </div>
            <a href='{$app_url}/login' class='mt-6 inline-block bg-[#e50914] hover:bg-[#f6121d] transition px-6 py-2 rounded-lg font-semibold text-white shadow-md hover:shadow-red-500/30'>Login Sekarang</a>
            ";
        }
    } else {
        echo "
        <div class='bg-red-900/40 border border-red-700 rounded-xl p-6'>
          <h2 class='text-xl font-semibold mb-2 text-red-400'>❌ Verifikasi Gagal</h2>
          <p class='text-gray-300 text-sm'>Link salah, sudah digunakan, atau akun telah diverifikasi sebelumnya.</p>
        </div>
        <a href='{$app_url}/login' class='mt-6 inline-block bg-[#e50914] hover:bg-[#f6121d] transition px-6 py-2 rounded-lg font-semibold text-white shadow-md hover:shadow-red-500/30'>Login</a>
        ";
    }
    ?>

    <p class="text-gray-500 text-xs mt-10">
      © <?= date('Y') ?> <span class="text-[#e50914] font-semibold">SinemaKita</span>. Semua hak dilindungi.
    </p>
  </div>
</div>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
  animation: fadeIn 0.7s ease-out forwards;
}
</style>
