# CRUD Inventaris - Tugas Rutin 8

Aplikasi manajemen inventaris berbasis **PHP (PDO) + MySQL/MariaDB**.

## 1. Kebutuhan
- PHP 8.0 atau lebih baru (ekstensi `pdo_mysql`; `mbstring` disarankan)
- MySQL 5.7+ / MariaDB 10.3+
- Cara termudah: **XAMPP** (sudah berisi Apache, PHP, MySQL, phpMyAdmin)

## 2. Cara menjalankan (XAMPP / Windows)
1. Ekstrak folder `inventaris` ke `C:\xampp\htdocs\` sehingga menjadi `C:\xampp\htdocs\inventaris\`.
2. Buka **XAMPP Control Panel**, klik **Start** pada **Apache** dan **MySQL**.
3. Buka `http://localhost/phpmyadmin`, klik tab **Import**, pilih file `database.sql`, lalu klik **Import**.
   Database `inventaris_db` beserta tabel dan data contoh akan terbentuk otomatis.
4. Cek `config/config.php`. Bawaan XAMPP: user `root`, password kosong (tidak perlu diubah).
   Jika MySQL Anda memakai port/password lain, ubah di file itu.
5. Buka `http://localhost/inventaris/` di browser.

## 3. Cara menjalankan tanpa XAMPP (Linux / macOS / terminal)
```bash
mysql -u root -p < database.sql        # import database
php -S localhost:8000                  # jalankan dari dalam folder inventaris
```
Lalu buka `http://localhost:8000`. Jika password MySQL bukan kosong, isi di `config/config.php`
atau jalankan dengan variabel lingkungan: `DB_USER=root DB_PASS=rahasia php -S localhost:8000`.

## 4. Cara memakai aplikasi
| Menu / tombol | Fungsi |
|---|---|
| **Daftar Produk** | Tabel produk (data dari JOIN `produk` + `kategori` + `supplier`), 10 baris per halaman |
| Kotak **Cari** + filter kategori | Cari berdasarkan nama, kode, atau nama supplier; bisa digabung filter kategori |
| **Tambah Produk** | Form dengan dropdown Kategori dan Supplier |
| **Ubah** | Form sudah terisi data produk; simpan untuk memperbarui |
| **Hapus** | Muncul jendela konfirmasi; produk dihapus dan tercatat di log |
| **Export laporan CSV** | Unduh laporan sesuai pencarian/filter yang aktif (buka di Excel) |
| **Riwayat Aktivitas** | Log tambah / ubah / hapus produk |

Catatan export: file memakai pemisah titik koma (`;`) dan UTF-8 BOM agar terbaca benar di Excel berlokal Indonesia.

## 5. Struktur folder
```
inventaris/
├── database.sql          # skema + data seed
├── config/config.php     # pengaturan koneksi
├── src/
│   ├── Database.php      # Singleton PDO
│   ├── bootstrap.php     # session, error handler, autoload sederhana
│   ├── helpers.php       # e() = htmlspecialchars, flash, CSRF, pagination
│   └── produk_repo.php   # query (semua prepared statement) + validasi
├── partials/             # header, footer, form bersama create/edit
├── assets/               # style.css, app.js
├── index.php             # list + JOIN + cari + pagination
├── create.php  edit.php  delete.php
├── logs.php              # riwayat aktivitas
└── export.php            # export CSV
```

## 6. Checklist requirements
| # | Requirement | Di mana |
|---|---|---|
| 1 | Database `inventaris_db`, 3 tabel + FK | `database.sql`: `kategori`, `supplier`, `produk` (2 FK) |
| 2 | Minimal 5 data seed per tabel | `database.sql`: 6 kategori, 6 supplier, 15 produk |
| 3 | Koneksi PDO Singleton | `src/Database.php` |
| 4 | List produk dengan JOIN | `src/produk_repo.php` (`ambil_produk`), tampil di `index.php` |
| 5 | Create dengan dropdown kategori & supplier | `create.php` + `partials/_form.php` |
| 6 | Update (form pre-filled) & delete (konfirmasi) | `edit.php`, `delete.php` + dialog di `index.php` |
| 7 | Semua query input pakai prepared statement | seluruh query di `src/` dan halaman |
| 8 | Output HTML pakai `htmlspecialchars()` | fungsi `e()` di `src/helpers.php` |
| 9 | Flash message sukses/gagal (redirect pattern) | `flash_set()` / `flash_get()`, semua aksi berakhir dengan redirect |
| 10 | UI rapi | `assets/style.css` (responsif) |
| Bonus | Transaction pada delete + log aktivitas | `delete.php` (BEGIN, log, DELETE, COMMIT/ROLLBACK), tabel `log_aktivitas` |
| Bonus | Pencarian | `index.php` |
| Bonus | Pagination | `index.php`, `logs.php` |
| Bonus | Export laporan | `export.php` |

Catatan: tabel `log_aktivitas` adalah tabel tambahan untuk bonus (tanpa FK agar riwayat tetap ada
setelah produk dihapus). Tiga tabel inti tetap `kategori`, `supplier`, `produk`.

## 7. Keamanan tambahan
- Token **CSRF** pada semua form POST (tambah, ubah, hapus)
- Hapus hanya lewat **POST**, bukan link GET langsung
- Validasi di server (format kode, angka, kategori/supplier harus ada, kode unik)
- Export CSV dilindungi dari formula injection

## 8. Troubleshooting
| Masalah | Solusi |
|---|---|
| "Koneksi database gagal" | Pastikan MySQL sudah Start, `database.sql` sudah diimport, user/password di `config/config.php` benar |
| Halaman kosong / error | Pastikan PHP 8.0+ dan ekstensi `pdo_mysql` aktif |
| Tampilan berantakan | Pastikan folder `assets/` ikut terunggah bersama file PHP |
| Ingin mengembalikan data awal | Import ulang `database.sql` (tabel lama di-reset otomatis) |
| Port 3306 berbeda | Ubah `port` di `config/config.php` |
