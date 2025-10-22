<h1 align="center">🎬 SinemaKita</h1>

<p align="center">
  <i>Platform streaming film modern berbasis PHP & MySQL dengan integrasi Google OAuth dan sistem notifikasi email.</i><br>
  Dibangun dengan ❤️ menggunakan <b>Composer</b> untuk backend dan <b>npm</b> untuk frontend.
</p>

<p align="center">
  <a href="https://github.com/FarendraAugust/SinemaKita/stargazers">
    <img src="https://img.shields.io/github/stars/FarendraAugust/SinemaKita?color=yellow&style=flat-square">
  </a>
  <a href="https://github.com/FarendraAugust/SinemaKita/issues">
    <img src="https://img.shields.io/github/issues/FarendraAugust/SinemaKita?style=flat-square">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/Build-Passing-brightgreen?style=flat-square&logo=githubactions&logoColor=white">
  </a>
  <a href="#">
    <img src="https://img.shields.io/badge/Made%20with-PHP%20%26%20TailwindCSS-blue?style=flat-square">
  </a>
</p>

---

## ✨ Tentang SinemaKita

**SinemaKita** adalah platform streaming film sederhana yang dikembangkan untuk memberikan pengalaman menonton yang mudah, cepat, dan interaktif.  
Aplikasi ini memiliki sistem login multiuser (Admin dan Pengguna) serta dukungan autentikasi Google dan verifikasi akun melalui email.

---

**SinemaKita** adalah platform streaming film modern yang dirancang untuk memberikan pengalaman menonton yang mudah, cepat, dan interaktif.  
Aplikasi ini dilengkapi dengan sistem **login multiuser (Admin & Pengguna)**, **autentikasi Google OAuth**, serta **verifikasi akun melalui email Gmail SMTP**.

---

## 📦 Fitur Utama

- 🔐 **Login Multiuser:** Sistem login terpisah untuk *Admin* dan *Pengguna* (dengan dukungan Google OAuth).
- 🎞️ **Streaming Film:** Tonton film langsung melalui tampilan antarmuka yang elegan dan modern.
- 🗂️ **Manajemen Film (Admin):** Tambah, ubah, hapus, dan kelola daftar film dengan mudah.
- 💬 **Ulasan & Rating:** Pengguna dapat memberikan komentar dan penilaian terhadap film.
- 🔍 **Pencarian Real-Time:** Temukan film favoritmu secara instan.
- 📧 **Email Notification:** Kirim email verifikasi dan reset password melalui Gmail SMTP.
- ⚙️ **Konfigurasi Mudah:** Semua pengaturan disimpan dalam file `.env`.

---

## 🧩 Persyaratan Sistem

> Pastikan perangkatmu telah memiliki software berikut sebelum memulai instalasi 👇

=======
| 🧰 Komponen | 💡 Versi Disarankan | 🔗 Keterangan |
|--------------|--------------------|---------------|
| 🐘 **PHP** | ≥ 8.1 | Backend utama proyek |
| 🎼 **Composer** | 2.x | Manajer dependensi PHP |
| 🟢 **Node.js & npm** | Node 18+ / npm 9+ | Untuk dependensi frontend |
| 🗄️ **MySQL** | 5.7+ | Basis data utama |
| 🔧 **Git** | Terpasang di sistem | Untuk clone & version control |

---

## 🚀 Instalasi Lengkap (Copy-Paste Friendly)


Berikut tutorial lengkap agar pengguna lain bisa langsung menjalankan **SinemaKita** di komputer mereka 👇  

---

### 1 Clone Repository

```bash
# Clone repository dari GitHub
git clone https://github.com/FarendraAugust/SinemaKita.git

# Masuk ke folder project
cd SinemaKita
```
### 2 Instal Dependensi PHP (Backend)

```bash
# Pastikan sudah menginstal Composer terlebih dahulu.
# Unduh di: https://getcomposer.org/download/

composer install
```
### 3 Instal Dependensi Frontend (npm)

```bash
# Pastikan Node.js dan npm sudah terpasang.
# Unduh di: https://nodejs.org/

npm install
```

### 4 Konfigurasi File .env

```bash
cp .env.example .env
```

### 6 Jalankan Aplikasi (PHP Built In Server)

```bash
php -S localhost:8000
```

### 7 Jalankan Frondted

```bash
npm run dev
npm run build
=======
### 7️⃣ Jalankan Frontend

```bash
npm run dev
```

---

## 🧠 Tips Tambahan

* ✅ Gunakan **Laragon** agar lebih mudah mengelola PHP, MySQL, dan Node.js.
* 🔐 Pastikan file `.env` berada di root folder.
* 🔄 Jalankan `npm run build` setiap kali melakukan perubahan besar di frontend.

---

## 🗂️ Struktur Folder

```
SinemaKita/
├── assets/
├── src/
│   ├── components/
│   └── pages/
├── admin/
├── auth/
├── utils/
├── vendor/
├── package.json
├── composer.json
├── .env.example
└── index.php
```

---

## ⚙️ Teknologi yang Digunakan

| Kategori        | Teknologi                                  |
| --------------- | ------------------------------------------ |
| **Backend**     | PHP 8.1+, MySQL, Composer                  |
| **Frontend**    | HTML, CSS, JavaScript (Vite / TailwindCSS) |
| **Autentikasi** | Google OAuth, PHP Sessions                 |
| **Email**       | Gmail SMTP via PHPMailer                   |
| **Tools**       | Node.js, npm, Git                          |

---

## 📸 Preview UI

<p align="center">
  <img src="assets/preview/homepage.png" alt="Preview Home" width="80%">
  <br>
  <em>Tampilan beranda SinemaKita</em>
</p>

<p align="center">
  <img src="assets/preview/admin-dashboard.png" alt="Preview Admin" width="80%">
  <br>
  <em>Dashboard Admin untuk manajemen film</em>
</p>

---

## 🪪 Lisensi

Proyek ini dirilis di bawah lisensi **MIT** — silakan gunakan, modifikasi, dan kembangkan dengan bebas, selama tetap mencantumkan atribusi kepada pengembang asli.

---

## 🌟 Kontribusi

Kontribusi terbuka untuk siapa pun!
Kamu bisa membantu dengan cara:

* Menemukan dan memperbaiki bug 🐞
* Menambahkan fitur baru 🚀
* Meningkatkan dokumentasi 📖

Silakan ajukan *pull request* di GitHub:
👉 [github.com/FarendraAugust/SinemaKita](https://github.com/FarendraAugust/SinemaKita)

---

### 💬 Dukungan

Jika kamu menyukai proyek ini, jangan lupa beri ⭐ di repositori!
Dukunganmu membantu proyek ini terus berkembang ❤️

---

**Dibuat dengan ❤️ oleh [Farendra August](https://github.com/FarendraAugust) & [Esa Farellio](https://github.com/EsaFrllio)**
