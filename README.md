# 🎮 NexusTopup v2.0 — Web Top Up Game Modern

Platform top up game dengan sistem **Device ID** otomatis, **panel admin** lengkap, dan database **JSON file** (tanpa MySQL).

---

## 📁 Struktur File

```
topup_game/
├── index.php                  ← Halaman utama (storefront)
├── .htaccess                  ← Keamanan Apache
│
├── includes/
│   ├── db.php                 ← Database helper (baca/tulis JSON)
│   └── api.php                ← API endpoint untuk user (AJAX)
│
├── admin/
│   ├── login.php              ← Halaman login admin
│   ├── index.php              ← Dashboard admin
│   ├── api.php                ← API endpoint untuk admin
│   ├── auth.php               ← Helper autentikasi admin
│   └── logout.php             ← Logout admin
│
├── assets/
│   ├── css/style.css          ← Stylesheet utama
│   ├── js/app.js              ← JavaScript storefront
│   └── images/                ← Gambar game
│
└── data/                      ← Database JSON (dilindungi .htaccess)
    ├── games.json             ← Data game & produk
    ├── transactions.json      ← Semua transaksi
    ├── admin.json             ← Kredensial admin
    └── .htaccess              ← Blokir akses langsung ke JSON
```

---

## 🚀 CARA MENJALANKAN

### ✅ Opsi 1: XAMPP (Paling Mudah — Windows/Mac/Linux)

**Langkah 1 — Install XAMPP**
- Download di: https://www.apachefriends.org/
- Install dan buka **XAMPP Control Panel**

**Langkah 2 — Salin Project**
- Ekstrak ZIP ini
- Salin folder `topup_game` ke:
  - Windows: `C:\xampp\htdocs\topup_game`
  - Mac: `/Applications/XAMPP/htdocs/topup_game`
  - Linux: `/opt/lampp/htdocs/topup_game`

**Langkah 3 — Atur Permission Folder data/**
- Windows: Klik kanan folder `data/` → Properties → pastikan tidak Read-only
- Mac/Linux:
  ```bash
  chmod 755 data/
  chmod 644 data/*.json
  ```

**Langkah 4 — Start Apache**
- Di XAMPP Control Panel, klik **Start** di baris Apache

**Langkah 5 — Buka di Browser**
```
http://localhost/topup_game/
```

Admin Panel:
```
http://localhost/topup_game/admin/login.php
```
Login: `admin` / `admin123`

---

### ✅ Opsi 2: PHP Built-in Server (Tanpa XAMPP)

Syarat: PHP 7.4+ sudah terinstall

```bash
# Masuk ke folder project
cd /path/ke/topup_game

# Jalankan server PHP
php -S localhost:8080

# Buka di browser
# http://localhost:8080
# http://localhost:8080/admin/login.php
```

---

### ✅ Opsi 3: Laragon (Windows — Alternatif XAMPP)

1. Install Laragon: https://laragon.org/
2. Salin folder `topup_game` ke `C:\laragon\www\`
3. Buka: `http://topup_game.test/`

---

## 🔑 Akun Admin Default

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

> ⚠️ **Ganti password segera setelah login pertama!**
> Panel Admin → Pengaturan → Ganti Password

---

## 📱 Sistem Device ID

- Setiap perangkat (browser) **otomatis mendapat Device ID unik** saat pertama kali membuka web
- Device ID disimpan di `localStorage` browser
- Riwayat transaksi **terikat ke Device ID**, bukan akun
- User tidak perlu registrasi/login untuk melihat riwayat mereka
- Device ID ditampilkan di halaman Riwayat Transaksi dan bisa disalin

---

## ⚙️ Fitur Admin Panel

Akses: `http://localhost/topup_game/admin/`

| Fitur                    | Keterangan                                     |
|--------------------------|------------------------------------------------|
| Dashboard                | Statistik real-time: transaksi, revenue, device |
| Tambah Game              | Nama, kategori, mata uang, gambar, server ID   |
| Edit / Nonaktifkan Game  | Toggle aktif/nonaktif tanpa hapus data         |
| Tambah Produk Top Up     | Jumlah, bonus, harga, diskon, terlaris         |
| Edit / Hapus Produk      | Update harga dan status kapan saja             |
| Lihat Semua Transaksi    | Filter: game, status, cari ID/user/device      |
| Update Status Transaksi  | Ubah status: Berhasil / Pending / Gagal        |
| Ganti Password Admin     | Dari menu Pengaturan                           |

---

## 🛠️ Konfigurasi Tambahan

### Menambah Admin Baru
Edit file `data/admin.json`:
```json
{
    "username": "admin_baru",
    "password": "passwordbaru",
    "name": "Nama Admin",
    "email": "email@domain.com",
    "last_login": null
}
```
Password akan otomatis di-hash saat login pertama.

### Menambah Metode Pembayaran
Edit bagian `$payment_methods` di `index.php` dan array `$payments` di `includes/api.php`.

---

## ⚠️ Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Halaman kosong / error 500 | Pastikan PHP 7.4+ dan extension `json` aktif |
| Game tidak muncul | Cek permission folder `data/` dan file `games.json` |
| Admin tidak bisa login | Pastikan `data/admin.json` bisa dibaca & ditulis PHP |
| Gambar tidak muncul | Pastikan folder `assets/images/` ada dan berisi file gambar |
| Transaksi tidak tersimpan | Pastikan `data/transactions.json` bisa ditulis (chmod 644) |

---

Made with ❤️ — NexusTopup v2.0.0 | PHP + JSON | No Database Required
