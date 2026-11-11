# Dashboard Admin — Kantin Kejujuran

Dashboard admin berbasis web untuk mengelola transaksi, analisis penjualan, katalog barang, stok/restock, laporan keuangan, notifikasi, dan QR akses pelanggan **Kantin Kejujuran**.

Dibangun menggunakan **PHP Native** dengan penyimpanan **flat-file JSON** — tanpa framework, tanpa Composer, dan tanpa database server.

---

## Prasyarat

- **PHP 8.0+** (direkomendasikan PHP 8.1+ atau 8.5+) dengan ekstensi standar (`json`, `session`).
- Tidak memerlukan MySQL/PostgreSQL maupun Node.js/npm build step.

---

## Cara Menjalankan

### Opsi 1: Jalankan dari folder root `WEB/` (Disarankan)

Menjalankan server dari folder root proyek membuat **Dashboard Admin** dan halaman pelanggan **`pwa/`** berjalan di server yang sama, sehingga QR Code akses pelanggan pada menu **QR Kantin** langsung mengarah ke URL yang aktif.

```bash
cd WEB
php -S 0.0.0.0:8000
```

Atau di Windows PowerShell / CMD:

```powershell
cd "d:\RIDHOG\Documents (D)\tugas\Mobile Computing\WEB"
php -S 0.0.0.0:8000
```

Buka di browser:
- **Komputer lokal:** `http://localhost:8000/dashboard/`
- **Perangkat lain (HP/tablet dalam satu jaringan Wi-Fi):** `http://<IP-KOMPUTER>:8000/dashboard/`
- **Halaman pelanggan (`pwa`):** `http://localhost:8000/pwa/index.php`

> Anda bisa mengganti port `8000` dengan port lain yang tersedia (misal `8080`), karena path asset dan redirect sudah dikonfigurasi dinamis melalui `<base href>` di `includes/layout.php` dan `login.php`.

### Opsi 2: Jalankan khusus folder `dashboard/` saja

Jika hanya ingin menjalankan dashboard admin:

```bash
cd dashboard
php -S 0.0.0.0:8000
```

atau dari folder root `WEB/`:

```bash
php -S 0.0.0.0:8000 -t dashboard
```

Buka `http://localhost:8000/`.

> **Catatan:** Pada mode `-t dashboard`, URL `/pwa/index.php` yang dirujuk oleh `qr.php` tidak ikut disajikan karena *document root* server hanya berada di dalam folder `dashboard/`.

### Menjalankan di Apache / Nginx / XAMPP / Laragon

1. Salin atau arahkan web server ke folder proyek (`WEB/` atau `WEB/dashboard/`).
2. Pastikan folder `dashboard/data/` memiliki izin tulis (*writable*) oleh proses PHP/web server (di Linux/macOS: `chmod 775 data`).
3. Untuk deployment produksi, ubah `'data_dir'` pada `includes/config.php` ke direktori di luar *web root* (atau blokir akses HTTP langsung ke `/data/*.json` di konfigurasi server) agar file konfigurasi dan data tidak bisa diakses publik.

---

## Akun Login Default

Saat pertama kali dijalankan, akun admin dibuat otomatis di `data/admin.json`:

- **Username:** `admin`
- **Password:** `admin123`

Password dan profil admin dapat diubah setelah login melalui menu **Pengaturan** (`pengaturan.php`).

---

## Struktur File & Halaman

| File / Folder | Fungsi |
|---|---|
| `login.php` | Halaman login admin (responsif desktop & mobile, dilengkapi CSRF token) |
| `index.php` | Dashboard utama: 4 kartu KPI hari ini, grafik penjualan 7 hari, transaksi terbaru, barang terlaris, dan pantauan stok |
| `transaksi.php` | Daftar transaksi, filter status (`Semua`, `Lunas`, `Menunggu`, `Kedaluwarsa`), live search, dan modal detail transaksi |
| `penjualan.php` | Analisis penjualan per periode (`Hari ini`, `7 Hari`, `30 Hari`, `Bulan ini`), rata-rata keranjang, jam tersibuk, grafik harian, dan ranking kontribusi produk |
| `barang.php` | Katalog produk, filter kategori, pencarian, tambah/edit/hapus produk |
| `stok.php` | Pemantauan status stok (`Aman`, `Menipis`, `Habis`) dan modal restock cepat (`+10`, `+20`, `+50`, `+100`) |
| `laporan.php` | Laporan penjualan per rentang waktu, fitur **Cetak / PDF** (`window.print()`), dan **Export CSV** |
| `pengaturan.php` | Profil admin, ganti password, info kantin, batas stok menipis default, pengaturan merchant QRIS, serta toggle QRIS & notifikasi |
| `qr.php` | Generator QR Code SVG mandiri (tanpa library eksternal) untuk scan akses pelanggan (`/pwa/index.php`), lengkap dengan Cetak Stand QR & Unduh SVG |
| `notifikasi.php` | Daftar notifikasi stok menipis, pembayaran, dan restock |
| `aksi.php` | Handler seluruh request `POST` (login, logout, baca notifikasi, kelola barang, restock, simpan pengaturan) dengan proteksi session & CSRF |
| `includes/` | `config.php` (konfigurasi), `bootstrap.php` (helper & session), `auth.php` (session guard), `data.php` (repository JSON & seeder), `layout.php` (shell desktop/mobile), `icons.php`, `qr.php` |
| `assets/` | CSS (`assets/css/style.css`), JS (`assets/js/admin.js`), dan gambar produk (`assets/img/`) |
| `data/` | Penyimpanan data JSON (`admin.json`, `settings.json`, `products.json`, `orders.json`, `notifications.json`) |
| `NOTES.md` | Catatan arsitektur dan log implementasi batch |

---

## Data Demo & Cara Reset

Jika file di folder `data/` belum ada, `init_admin_data_if_needed()` di `includes/data.php` akan otomatis membuat data awal:

- `data/admin.json` — kredensial & profil admin default
- `data/settings.json` — pengaturan toko, batas stok menipis (`5`), dan info merchant QRIS
- `data/products.json` — 5 produk awal (Es Teh Manis, Mie Instan, Roti Cokelat, Air Mineral, Keripik Singkong)
- `data/orders.json` — transaksi demo hari ini dan riwayat 6 hari sebelumnya (disesuaikan relatif terhadap tanggal saat file dibuat)
- `data/notifications.json` — daftar notifikasi awal

**Untuk mereset data ke kondisi awal:**
- Hapus `data/orders.json` saja jika ingin meregenerasi ulang data transaksi demo agar sesuai dengan tanggal hari ini.
- Atau hapus seluruh file `.json` di `dashboard/data/`, lalu buka kembali `dashboard/` di browser agar seluruh file demo dibuat ulang (termasuk password kembali ke `admin` / `admin123`).

---

## Pemeriksaan Cepat (Opsional)

Untuk mengecek tidak ada error sintaksis PHP setelah melakukan perubahan di `dashboard/`:

**PowerShell (dari dalam folder `dashboard/`):**
```powershell
Get-ChildItem -Filter "*.php" -Recurse | ForEach-Object { php -l $_.FullName }
```

**Bash (dari dalam folder `dashboard/`):**
```bash
find . -name "*.php" -exec php -l {} +
```
