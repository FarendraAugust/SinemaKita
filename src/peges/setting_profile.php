<?php
include 'utils/connection.php';

// Ambil nilai dari .env
$APP_URL = rtrim(getenv('APP_URL') ?: $_ENV['APP_URL'] ?? 'http://localhost', '/');
$APP_DIR = parse_url($APP_URL, PHP_URL_PATH) ?: ''; // contoh: "/sinemakita"

// Redirect jika belum login
if (!isset($_SESSION['session_id'])) {
    header("Location: {$APP_URL}/login");
    exit();
}

$id_pengguna = $_SESSION['session_id'];

// Ambil data user
$stmt = $connection->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
$stmt->bind_param("i", $id_pengguna);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Pastikan provider selalu ada
if (empty($user['provider'])) {
    $user['provider'] = 'default';
}

// Tentukan avatar awal
$defaultAvatar = "{$APP_URL}/assets/profile/default-avatar.png";
$profileImg = $defaultAvatar;

if (!empty($user['profile_img'])) {
    if (in_array($user['provider'], ['google', 'facebook'])) {
        // Ambil langsung dari provider (URL eksternal)
        $profileImg = $user['profile_img'];
    } else {
        // File lokal
        $profileImg = "{$APP_URL}/assets/profile/" . htmlspecialchars($user['profile_img']);
    }
}

// Tentukan apakah dari provider (Google/Facebook)
$isProvider = $user['provider'] !== 'default';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['hapus_foto']) && !$isProvider) {
        // Hapus foto lokal
        $fotoPath = $_SERVER['DOCUMENT_ROOT'] . "{$APP_DIR}/assets/profile/" . $user['profile_img'];
        if ($user['profile_img'] && file_exists($fotoPath)) {
            unlink($fotoPath);
        }

        $stmt = $connection->prepare("UPDATE pengguna SET profile_img=NULL WHERE id_pengguna=?");
        $stmt->bind_param("i", $id_pengguna);
        if ($stmt->execute()) {
            $success = "Foto profil berhasil dihapus.";
            $user['profile_img'] = '';
            $profileImg = $defaultAvatar;
        } else {
            $errors[] = "Gagal menghapus foto profil.";
        }
        $stmt->close();
    } elseif (!$isProvider) {
        // Update profil manual/default
        $nama  = trim($_POST['nama_pengguna'] ?? '');
        $email = trim($_POST['email_pengguna'] ?? '');
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $password_konf = $_POST['password_konf'] ?? '';

        if (!$nama) $errors[] = "Nama tidak boleh kosong.";
        if (!$email) $errors[] = "Email tidak boleh kosong.";

        $new_password_hashed = $user['password'];
        if ($password_baru || $password_konf) {
            if (!$password_lama) $errors[] = "Masukkan password lama untuk mengganti password.";
            elseif (!password_verify($password_lama, $user['password'])) $errors[] = "Password lama salah.";
            elseif ($password_baru !== $password_konf) $errors[] = "Password baru dan konfirmasi tidak cocok.";
            else $new_password_hashed = password_hash($password_baru, PASSWORD_DEFAULT);
        }

        $targetFile = $user['profile_img'];
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "{$APP_DIR}/assets/profile/";

        if (!empty($_FILES['profile_img']['name'])) {
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $fileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($_FILES['profile_img']['name']));
            $targetFilePath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFilePath)) {
                $errors[] = "Gagal upload foto profil.";
            } else {
                // Hapus foto lama jika ada
                if ($user['profile_img'] && file_exists($uploadDir . $user['profile_img'])) {
                    unlink($uploadDir . $user['profile_img']);
                }
                $targetFile = $fileName;
                $profileImg = "{$APP_URL}/assets/profile/" . $fileName;
            }
        }

        if (empty($errors)) {
            $stmt = $connection->prepare("UPDATE pengguna SET nama_pengguna=?, email_pengguna=?, password=?, profile_img=? WHERE id_pengguna=?");
            $stmt->bind_param("ssssi", $nama, $email, $new_password_hashed, $targetFile, $id_pengguna);
            if ($stmt->execute()) {
                $_SESSION['session_username'] = $nama;
                $success = "Profil berhasil diperbarui.";
                $user['nama_pengguna'] = $nama;
                $user['email_pengguna'] = $email;
                $user['password'] = $new_password_hashed;
                $user['profile_img'] = $targetFile;
            } else {
                $errors[] = "Gagal memperbarui profil.";
            }
            $stmt->close();
        }
    }
}
?>

<div class="max-w-3xl mx-auto mt-24 px-8 py-12 bg-gradient-to-b from-[#1a1b1e] to-[#0f1012] rounded-3xl shadow-xl text-white relative overflow-hidden">
    <h2 class="text-3xl font-extrabold mb-8 text-center bg-gradient-to-r from-orange-400 to-red-500 bg-clip-text text-transparent">Edit Profil</h2>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-700/50 p-4 rounded mb-6">
            <?php foreach ($errors as $err): ?>
                <p class="text-sm"><?= htmlspecialchars($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-700/50 p-4 rounded mb-6 text-center text-sm">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">

        <!-- Foto Profil -->
        <div class="flex flex-col items-center">
            <div class="relative group w-32 h-32">
                <img src="<?= $profileImg ?>" class="w-32 h-32 rounded-full object-cover border-2 border-gray-700 group-hover:scale-105 transition duration-300" id="profilePreview">
                <?php if (!$isProvider): ?>
                    <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                        <ion-icon name="camera-outline" class="text-3xl text-gray-300"></ion-icon>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!$isProvider): ?>
                <input type="file" name="profile_img" accept="image/*" onchange="previewProfile(this)" class="mt-4 text-sm text-gray-400 bg-[#0c0d0f] border border-gray-700 rounded-lg p-2 w-full max-w-xs">
                <button type="submit" name="hapus_foto" value="1" class="mt-3 py-2 px-4 bg-red-600 hover:bg-red-500 rounded-lg font-semibold transition text-white">🗑️ Hapus Foto Profil</button>
            <?php else: ?>
                <p class="text-gray-400 mt-2 text-sm">Avatar dari <?= ucfirst($user['provider']) ?>, tidak bisa diubah di sini.</p>
            <?php endif; ?>
        </div>

        <!-- Nama & Email -->
        <div class="space-y-3">
            <div class="relative">
                <label>Nama Pengguna</label>
                <div class="flex items-center gap-2">
                    <input type="text"
                        name="nama_pengguna"
                        value="<?= htmlspecialchars($user['nama_pengguna']) ?>"
                        required
                        class="w-full p-3 rounded-lg bg-[#0c0d0f] border border-gray-700"
                    >
                    <?php if ($user['verified'] == 1): ?>
                        <span class="flex items-center text-green-400" title="Terverifikasi">
                            <ion-icon name="checkmark-circle" class="text-2xl"></ion-icon>
                        </span>
                    <?php else: ?>
                        <span class="flex items-center text-red-500" title="Belum Terverifikasi">
                            <ion-icon name="close-circle" class="text-2xl"></ion-icon>
                        </span>
                    <?php endif; ?>
                </div>

                <p class="text-sm mt-1 <?= $user['verified'] ? 'text-green-400' : 'text-red-400' ?>">
                    <?= $user['verified'] ? '✔ Akun Anda sudah terverifikasi' : '✖ Akun Anda belum terverifikasi' ?>
                </p>
            </div>
            <div>
                <label>Email</label>
                <input type="email"
                    name="email_pengguna"
                    value="<?= htmlspecialchars($user['email_pengguna']) ?>"
                    <?= $isProvider ? 'readonly class="w-full p-3 rounded-lg bg-gray-700 border border-gray-500 cursor-not-allowed"' : 'class="w-full p-3 rounded-lg bg-[#0c0d0f] border border-gray-700"' ?>>
            </div>
        </div>

        <!-- Ganti Password -->
        <?php if (!$isProvider): ?>
        <div class="space-y-3">
            <div>
                <label>Password Lama</label>
                <input type="password" name="password_lama" placeholder="Masukkan password lama" class="w-full p-3 rounded-lg bg-[#0c0d0f] border border-gray-700">
            </div>
            <div>
                <label>Password Baru</label>
                <input type="password" name="password_baru" placeholder="Masukkan password baru" class="w-full p-3 rounded-lg bg-[#0c0d0f] border border-gray-700">
            </div>
            <div>
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="password_konf" placeholder="Konfirmasi password baru" class="w-full p-3 rounded-lg bg-[#0c0d0f] border border-gray-700">
            </div>
        </div>
        <?php endif; ?>

        <div class="flex gap-3">
            <?php if (!$isProvider): ?>
                <button type="submit" class="flex-1 py-3 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg font-semibold hover:from-orange-400 hover:to-red-400 transition">Simpan Perubahan</button>
            <?php endif; ?>
            <a href="<?= $APP_URL ?>/akun" class="flex-1 py-3 rounded-lg font-semibold bg-[#2a2b2e] hover:bg-[#3a3b3e] text-center transition">Kembali</a>
        </div>
    </form>
</div>

<script>
function previewProfile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('profilePreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
