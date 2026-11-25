# Kantin Kejujuran — Admin Dashboard (`Dashboard New`)

Implementasi antarmuka (frontend / PHP view templates + mock JSON data) Admin Dashboard **Kantin Kejujuran** berdasarkan desain terbaru di `mockup/mockup new/` dan `SPESIFIKASI.md`.

> **Catatan:** Versi ini belum menggunakan database maupun API backend (`sessionStorage` digunakan di browser untuk simulasi perubahan data antar-halaman secara interaktif).

## Menjalankan Preview Lokal

Pastikan PHP terpasang (`php -v`), lalu jalankan server built-in PHP dari root workspace atau direktori `Dashboard New`:

```powershell
cd "d:\RIDHOG\Documents (D)\tugas\Mobile Computing\WEB\Dashboard New"
php -S localhost:8080
```

Buka di browser:
- Login Admin (Halaman Awal): `http://localhost:8080/index.php` (Demo kredensial: `admin` / `admin123`)
- Dashboard Utama: `http://localhost:8080/dashboard.php`

## Daftar Halaman
| File | Halaman | Keterangan |
|---|---|---|
| `index.php` | Login Admin | Split-screen desktop & mobile gradient card (halaman masuk utama) |
| `dashboard.php` | Dashboard | 4 KPI, Grafik 7 hari (3 metrik), Barang Terlaris, Transaksi Terbaru, Stok Barang + Popover Notifikasi |
| `transaksi.php` | Transaksi | Filter status/pencarian/tanggal, detail panel desktop (`Menunggu`/`Lunas`/`Batal`) & view detail mobile |
| `barang.php` | Barang | Daftar barang, filter kategori, panel Tambah/Edit + kalkulasi komisi penitip, modal Nonaktifkan |
| `stok.php` | Stok | Monitor stok & level bar, aksi Restock dan Koreksi Stok |
| `penitip.php` | Penitip | Daftar penitip, panel/halaman detail (tab Barang/Penjualan/Setoran), Tambah/Edit Penitip, Catat Setoran |
| `laporan.php` | Laporan | Filter periode (`Hari ini`, `7 Hari`, `30 Hari`, `Bulan ini`), Grafik Pendapatan, Rekap Barang & Penitip |
| `notifikasi.php` | Notifikasi | Daftar notifikasi paginasi, filter `Semua`/`Stok`/`Pembayaran`, hapus satuan & modal Hapus Semua |
| `pengaturan.php` | Pengaturan | Stepper Batas Stok Menipis, toggle Notifikasi, Merchant QRIS, tautan QR Kantin |
| `qr.php` | QR Kantin | Tampilan kartu QR akses pelanggan (`Unduh PNG` via canvas & `Cetak`) |

## Simulasi Empty State (Preview UI)
Tambahkan `?empty=1` pada URL `transaksi.php`, `barang.php`, `penitip.php`, atau `notifikasi.php` untuk langsung melihat tampilan kondisi kosong (*empty state*).
