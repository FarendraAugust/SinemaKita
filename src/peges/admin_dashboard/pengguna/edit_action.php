<?php
session_start();
include '../../../../utils/connection.php'; // sudah berisi koneksi dari .env

// Ambil data dari form
$id = $_POST['id_pengguna'];
$nama_pengguna = $_POST['nama_pengguna'];
$role = $_POST['role'] ?? 'pengguna';
$is_admin = ($role === 'admin') ? 1 : 0;

// Ambil provider dari DB
$userQuery = mysqli_query($connection, "SELECT provider, email_pengguna, profile_img FROM pengguna WHERE id_pengguna = '$id'");
$userData = mysqli_fetch_assoc($userQuery);
$isGoogle = ($userData['provider'] === 'google');

// === Untuk user non-Google ===
if (!$isGoogle) {
    $email_pengguna = $_POST['email_pengguna'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi password
    if (!empty($password) && $password !== $confirm) {
        $_SESSION['error_msg'] = "Konfirmasi password tidak cocok!";
        header("Location: {$APP_URL}/admin/pengguna/edit/$id");
        exit();
    }

    $hashed = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    // === Upload foto profil ===
    $profile_img = $userData['profile_img']; // default: gunakan yang lama
    if (!empty($_FILES['profile_img']['name'])) {
        $targetDir = realpath(__DIR__ . '/../../../../assets/profile') . '/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $img_name = basename($_FILES['profile_img']['name']);
        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $filename_only = pathinfo($img_name, PATHINFO_FILENAME);
        $clean_name = preg_replace("/[^a-zA-Z0-9_-]/", "_", $filename_only);

        $newImgName = date("YmdHis") . "_" . $clean_name . "." . $ext;
        $targetFile = $targetDir . $newImgName;

        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFile)) {
            // hapus foto lama jika ada
            if (!empty($userData['profile_img']) && file_exists($targetDir . $userData['profile_img'])) {
                unlink($targetDir . $userData['profile_img']);
            }
            $profile_img = $newImgName;
        } else {
            $_SESSION['error_msg'] = "Upload foto profil gagal!";
            header("Location: {$APP_URL}/admin/pengguna/edit/$id");
            exit();
        }
    }

    // === Siapkan query UPDATE ===
    $fields = "nama_pengguna = ?, email_pengguna = ?, is_admin = ?, update_at = ?";
    $types = "ssis";
    $params = [$nama_pengguna, $email_pengguna, $is_admin, date("Y-m-d H:i:s")];

    if (!empty($hashed)) {
        $fields .= ", password = ?";
        $types .= "s";
        $params[] = $hashed;
    }

    if ($profile_img) {
        $fields .= ", profile_img = ?";
        $types .= "s";
        $params[] = $profile_img;
    }

    $stmt = $connection->prepare("UPDATE pengguna SET $fields WHERE id_pengguna = ?");
    $types .= "i";
    $params[] = $id;
    $stmt->bind_param($types, ...$params);

} else {
    // === Untuk user Google ===
    $stmt = $connection->prepare("UPDATE pengguna SET nama_pengguna = ?, update_at = ? WHERE id_pengguna = ?");
    $stmt->bind_param("ssi", $nama_pengguna, date("Y-m-d H:i:s"), $id);
}

// === Eksekusi Query ===
try {
    $stmt->execute();
    $_SESSION['success_msg'] = "Data pengguna berhasil diedit!";
    header("Location: {$APP_URL}/admin/pengguna");
    exit();
} catch (mysqli_sql_exception $ex) {
    if (strpos($ex->getMessage(), 'Duplicate entry') !== false) {
        $_SESSION['error_msg'] = "Email sudah terdaftar!";
    } else {
        $_SESSION['error_msg'] = "Kesalahan database: " . $ex->getMessage();
    }
    header("Location: {$APP_URL}/admin/pengguna/edit/$id");
    exit();
}

$stmt->close();
$connection->close();
?>
