# SPK SAW — Sistem Pendukung Keputusan Pemilihan Desain & Bahan Kain
## Studi Kasus: Rumah Jahit Eti, Tasikmalaya

Aplikasi web berbasis PHP native + MySQL untuk membantu pemilik usaha jahit menentukan
kombinasi desain dan bahan kain terbaik menggunakan metode **Simple Additive Weighting (SAW)**.

---

## 🚀 Cara Instalasi (XAMPP Lokal)

### 1. Persiapan
- Pastikan XAMPP sudah terinstall dan Apache + MySQL sudah berjalan.
- PHP 8.x (sudah tersedia di XAMPP terbaru).

### 2. Clone / Copy Project
Letakkan folder `spk-saw-kain` di:
```
C:\xampp\htdocs\spk-saw-kain\
```

### 3. Buat Database
Buka phpMyAdmin (`http://localhost/phpmyadmin`) atau MySQL CLI, lalu:

```sql
-- Jalankan schema dulu:
SOURCE C:/xampp/htdocs/spk-saw-kain/database/schema.sql;

-- Lalu seed data awal:
SOURCE C:/xampp/htdocs/spk-saw-kain/database/seed.sql;
```

Atau lewat phpMyAdmin → Import → pilih `database/schema.sql` → Import.
Ulangi untuk `database/seed.sql`.

### 4. Update Password Seed (WAJIB)
Seed.sql menggunakan password hash Laravel default. Untuk mendapatkan hash bcrypt yang benar:

1. Buka `http://localhost/spk-saw-kain/generate_hash.php`
2. Copy hash untuk `admin123` dan `user123`
3. Jalankan di phpMyAdmin:
   ```sql
   USE spk_saw_kain;
   UPDATE pengguna SET password = '[HASH_ADMIN123]' WHERE username = 'admin';
   UPDATE pengguna SET password = '[HASH_USER123]'  WHERE username = 'user';
   ```
4. **Hapus** file `generate_hash.php` dari server!

### 5. Konfigurasi Database
Edit `src/Config/Database.php` jika konfigurasi MySQL berbeda:
```php
private static string $host     = 'localhost';
private static string $dbname   = 'spk_saw_kain';
private static string $username = 'root';      // ganti jika berbeda
private static string $password = '';          // ganti jika ada password
```

### 6. Akses Aplikasi
```
http://localhost/spk-saw-kain/public/
```

---

## 👤 Akun Default

| Role     | Username | Password  |
|----------|----------|-----------|
| Admin    | `admin`  | `admin123` |
| Pengguna | `user`   | `user123`  |

> ⚠️ **Ganti password segera setelah login pertama!**

---

## 📁 Struktur Folder

```
spk-saw-kain/
├── database/
│   ├── schema.sql          # DDL (buat tabel)
│   └── seed.sql            # Data awal
├── public/                 # Document root
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── desain/             # CRUD Desain
│   ├── bahan-kain/         # CRUD Bahan Kain
│   ├── kriteria/           # CRUD Kriteria
│   ├── rekomendasi/        # Form + Hasil SAW
│   ├── partials/           # Header, Sidebar, Navbar
│   └── assets/             # CSS, JS, Img
├── src/
│   ├── Config/Database.php
│   ├── Middleware/AuthGuard.php
│   ├── Helpers/Validator.php
│   ├── Models/             # 7 model
│   ├── Services/SAWService.php
│   └── autoload.php
├── generate_hash.php       # HAPUS setelah dipakai!
└── README.md
```

---

## ⚙️ Cara Kerja SAW

1. **Input**: Jenis Pakaian, Tingkat Kenyamanan, Aktivitas
2. **Ambil data**: kriteria (bobot, atribut) + nilai bahan kain dari DB
3. **Override Ketebalan**: Olahraga/Santai/Cuaca Panas → Cost; lainnya → Benefit
4. **Normalisasi**: Benefit: `r = x/max`, Cost: `r = min/x`
5. **Hitung Vi**: `Vi = Σ(Wj × rij)` untuk setiap bahan
6. **Ranking**: Urutkan DESC berdasarkan Vi
7. **Simpan**: Ke tabel `rekomendasi` + `detail_rekomendasi`

---

## 🛡️ Keamanan

- ✅ Prepared statements (PDO) — tidak ada raw SQL concat
- ✅ Password bcrypt (`password_hash` / `password_verify`)
- ✅ Session-based auth dengan session regeneration
- ✅ CSRF token pada semua form POST
- ✅ Input validation & sanitasi server-side
- ✅ AuthGuard di setiap halaman terproteksi
- ✅ Role check untuk halaman admin

---

## 📋 Fitur

| Fitur | Admin | Pengguna |
|-------|-------|---------|
| Login/Logout | ✅ | ✅ |
| Dashboard | ✅ | ✅ |
| CRUD Data Desain | ✅ | ❌ |
| CRUD Data Bahan Kain | ✅ | ❌ |
| CRUD Data Kriteria | ✅ | ❌ |
| Form Rekomendasi | ✅ | ✅ |
| Hasil Rekomendasi (milik sendiri) | ✅ | ✅ |
| Lihat semua riwayat | ✅ | ❌ |

---

*Dibuat untuk keperluan skripsi — Sistem Pendukung Keputusan menggunakan metode SAW.*
