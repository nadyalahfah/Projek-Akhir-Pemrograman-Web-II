# 🎮 NexusTopup v3.0 — MySQL + Digiflazz Edition

---

## 📁 Struktur File

```
nexustopup_mysql/
├── index.php              ← Halaman utama
├── config.php             ← ⚙️ KONFIGURASI UTAMA (DB + Digiflazz)
├── webhook.php            ← Endpoint webhook Digiflazz
├── nexustopup.sql         ← Import database ini ke phpMyAdmin
│
├── includes/
│   ├── db.php             ← Database PDO MySQL
│   ├── api.php            ← API user (AJAX)
│   └── digiflazz.php      ← Integrasi Digiflazz API
│
├── admin/
│   ├── login.php          ← Login admin
│   ├── index.php          ← Dashboard admin
│   ├── api.php            ← API admin
│   ├── auth.php           ← Auth helper
│   └── logout.php
│
├── assets/                ← CSS, JS, Gambar
└── logs/                  ← Log webhook (auto-dibuat)
```

---

## 🚀 TAHAPAN SETUP

### LANGKAH 1 — Import Database

1. Buka `http://localhost/phpmyadmin`
2. Klik database **nexustopup** di sidebar kiri
3. Klik tab **Import** di atas
4. Klik **Choose File** → pilih file `nexustopup.sql`
5. Klik **Go / Import**
6. Seharusnya muncul pesan hijau "Import berhasil"

---

### LANGKAH 2 — Copy Project ke XAMPP

Salin folder `nexustopup_mysql` ke:
```
C:\xampp\htdocs\nexustopup_mysql
```

---

### LANGKAH 3 — Edit config.php

Buka file `config.php` dan sesuaikan:

```php
// Database (XAMPP default)
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexustopup');
define('DB_USER', 'root');
define('DB_PASS', '');   // kosong untuk XAMPP default

// Digiflazz (isi setelah dapat API Key)
define('DIGIFLAZZ_USERNAME', 'username_digiflazz_kamu');
define('DIGIFLAZZ_API_KEY_DEV',  'api_key_development');
define('DIGIFLAZZ_API_KEY_PROD', 'api_key_production');
define('DIGIFLAZZ_MODE', 'dev'); // ganti 'prod' saat go live
```

---

### LANGKAH 4 — Buka Web

```
http://localhost/nexustopup_mysql/
```

Admin Panel:
```
http://localhost/nexustopup_mysql/admin/login.php
```
Login: `admin` / `admin123`

---

### LANGKAH 5 — Setup Digiflazz

1. Login Digiflazz → **Pengaturan → API**
2. Salin **Username** dan **API Key Development**
3. Isi di `config.php`
4. Buka Admin Panel → **Digiflazz API** → klik **Cek Saldo**
5. Kalau muncul saldo → koneksi berhasil! ✅

---

### LANGKAH 6 — Isi SKU Digiflazz ke Produk

1. Di Admin Panel → **Digiflazz API** → bagian "Produk Tanpa SKU"
2. Klik **Set SKU** di setiap produk
3. Isi kode SKU dari price list Digiflazz
   - Contoh ML 86 Diamond → `game-ml-86-diamond` (lihat di price list)
4. Simpan

---

### LANGKAH 7 — Daftarkan Webhook (saat hosting)

Di panel Digiflazz → Pengaturan → Webhook URL, isi:
```
https://domainmu.com/webhook.php
```

---

## 🔑 Admin Default

| Username | Password |
|----------|----------|
| `admin`  | `admin123` |

**Ganti password setelah login pertama!**

---

## ⚠️ Troubleshooting

| Error | Solusi |
|-------|--------|
| Koneksi DB gagal | Cek DB_HOST, DB_NAME, DB_USER, DB_PASS di config.php |
| Tabel tidak ada | Import nexustopup.sql ke phpMyAdmin |
| Cek saldo gagal | Isi DIGIFLAZZ_USERNAME dan API Key di config.php |
| Game tidak muncul | Cek tabel games di phpMyAdmin |
