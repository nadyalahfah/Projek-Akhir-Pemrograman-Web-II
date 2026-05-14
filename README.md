# 🎮 NexusTopup — Web Top Up Game Modern

Platform top up game berbasis **PHP + MySQL** dengan sistem Device ID otomatis, panel admin lengkap, dan desain dark modern.

---

## 📁 Struktur File

```
nexustopup_mysql/
├── index.php              ← Halaman utama (storefront)
├── config.php             ← ⚙️ Konfigurasi database (JANGAN di-push ke GitHub)
├── nexustopup.sql         ← File SQL untuk import database
│
├── includes/
│   ├── db.php             ← Database helper (PDO MySQL)
│   └── api.php            ← API endpoint untuk user (AJAX)
│
├── admin/
│   ├── login.php          ← Halaman login admin
│   ├── index.php          ← Dashboard admin
│   ├── api.php            ← API endpoint untuk admin
│   ├── auth.php           ← Helper autentikasi
│   └── logout.php
│
├── assets/
│   ├── css/style.css      ← Stylesheet utama
│   ├── js/app.js          ← JavaScript storefront
│   └── images/            ← Gambar game
│
└── logs/                  ← Log sistem (auto-dibuat)
```

---

## 🚀 Cara Setup

### Langkah 1 — Import Database

1. Buka `http://localhost/phpmyadmin`
2. Klik database **nexustopup** di sidebar kiri
3. Klik tab **Import**
4. Pilih file `nexustopup.sql` → klik **Go**

### Langkah 2 — Copy ke XAMPP

Salin folder `nexustopup_mysql` ke:
```
C:\xampp\htdocs\nexustopup_mysql
```

### Langkah 3 — Edit config.php

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexustopup');
define('DB_USER', 'root');
define('DB_PASS', '');   // kosong = default XAMPP
```

### Langkah 4 — Buka di Browser

```
http://localhost/nexustopup_mysql/
```

Admin panel:
```
http://localhost/nexustopup_mysql/admin/login.php
```

---

## 🔑 Login Admin Default

| Username | Password  |
|----------|-----------|
| `admin`  | `admin123`|

> Ganti password setelah login pertama di menu **Pengaturan**!

---

## 📱 Sistem Device ID

Setiap pengguna otomatis mendapat **Device ID unik** saat pertama buka web — tanpa perlu daftar akun. Riwayat transaksi tersimpan per perangkat dan bisa dilihat di tab **Transaksi**.

---

## ⚙️ Fitur Admin Panel

| Fitur | Keterangan |
|-------|-----------|
| Dashboard | Statistik transaksi, revenue, device unik |
| Game & Produk | Tambah, edit, hapus game + upload gambar |
| Semua Transaksi | Lihat, filter, update status transaksi |
| Pengaturan | Ganti password admin |

---

## ⚠️ Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Game tidak muncul | Jalankan `debug.php` untuk cek koneksi DB |
| Koneksi DB gagal | Cek `config.php` dan pastikan MySQL XAMPP sudah START |
| Tabel tidak ada | Import ulang `nexustopup.sql` ke phpMyAdmin |
| Gambar tidak muncul | Pastikan folder `assets/images/` ada dan bisa ditulis |

---

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL (via PDO)
- **Frontend**: HTML, CSS, Vanilla JavaScript
- **Tools**: XAMPP / Laragon

---

> Dibuat sebagai Projek Akhir Pemrograman Web II
