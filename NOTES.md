# Dashboard New — Architectural Notes & Mockup Resolutions

## Ringkasan Proyek
Direktori `Dashboard New/` berisi implementasi ulang **Admin Dashboard Kantin Kejujuran** yang merujuk pada:
1. Desain visual baru di `mockup/mockup new/` (`Admin Desktop` 21 SVG + `Admin Mobile` 27 SVG)
2. Spesifikasi bisnis dan resolusi konflik di `SPESIFIKASI.md`
3. Model relasi data di `diagram/kantin-kejujuran-ERD.drawio` dan `diagram/kantin-kejujuran-UseCase.drawio`
4. Token warna dan konvensi komponen yang sudah ada di `dashboard/assets/css/style.css` dan `pwa/`

> **Status Backend:** Sesuai instruksi pengguna (*"don't work on bakcend yet"*), seluruh fitur di `Dashboard New/` berjalan pada lapisan frontend/view (PHP Native UI templates + mock JSON seed data read-only + simulasi state interaktif di browser menggunakan `sessionStorage`), **belum terhubung ke database SQL, webhook QRIS, maupun endpoint API backend**.

---

## Keputusan Arsitektur & Teknologi
- **Stack:** PHP Native UI Templates + Mock JSON Data (`Dashboard New/data/*.json`), mengikuti struktur folder `dashboard/`.
- **Cakupan:** Admin Dashboard (desktop `>= 1280px`, tablet `768px`, mobile `360px`), terpisah dari `pwa/` maupun `dashboard/` lama (keduanya tidak disentuh).
- **Navigasi Baru:**
  - **Desktop Sidebar (`298px`):** `Dashboard`, `Transaksi`, `Barang`, `Stok`, `Penitip`, `Laporan`, `Pengaturan`, garis pemisah, `Keluar`, versi aplikasi. (`Penjualan` dihapus dan digabung ke `Laporan`; `Tentang` dihapus).
  - **Desktop Topbar (`60px`):** Ikon Lonceng notifikasi + dot merah unread (membuka *Notification Popover* `420px`) + pembatas + avatar ikon + teks `Admin` (tanpa dropdown profil).
  - **Mobile Bottom Nav (`64px`):** Hanya 4 menu (`Dashboard`, `Transaksi`, `Barang`, `Laporan`); disembunyikan di halaman detail/form/subhalaman (`Notifikasi`, `QR Kantin`, detail/form transaksi, barang, stok, penitip).
  - **Mobile Side Drawer (`280px`):** Header gradien biru (`Kantin Kejujuran` / `Ambil, Bayar, Jujur`), 7 menu utama, pembatas, `Keluar` merah, footer versi.

---

## Pemetaan Data Mockup (`data/*.json` ↔ ERD & Mockup SVGs)
Struktur JSON di `Dashboard New/data/` dipetakan ke entitas pada `diagram/kantin-kejujuran-ERD.drawio`:
- `kategori.json` (`kategori`)
- `penitip.json` (`penitip`)
- `barang.json` (`barang`)
- `pesanan.json` (`pesanan` + `item_pesanan` tersemat)
- `mutasi_stok.json` (`mutasi_stok`)
- `setoran_penitip.json` (`setoran_penitip`)
- `notifikasi.json` (`notifikasi`)
- `pengaturan.json` (`pengaturan`)

### Catatan Resolusi Detail & Konsistensi Angka
1. **Sinkronisasi Matematis Harian & 7-Hari (`pesanan.json`):**
   - `24 Nov 2026` (hari demo): 38 transaksi (`#TRX041` Menunggu `Rp 7.000`, `#TRX039` Batal `Rp 3.000`, serta 36 transaksi Lunas = `Rp 428.000`, `126 item` terdiri dari Air Mineral 40, Mie Instan 36, Keripik Singkong 24, Roti Cokelat 16, Es Teh Manis 10).
   - 7 Hari (`18–24 Nov 2026`): 173 transaksi lunas + 2 non-lunas dengan Omzet `Rp 2.363.000`, Hak Penitip `Rp 670.000`, Pendapatan Kantin `Rp 1.693.000`, Barang Terjual `696 item`. Total catatan historis di `pesanan.json` adalah `180` transaksi (untuk mendukung paginasi Dashboard `Menampilkan 1–5 dari 180`).
2. **Perbedaan Angka di Mockup `12 Penitip` vs `16 Laporan`:**
   - Pada SVG `16 Laporan` (7 hari), item titipan terjual tercatat `120 Keripik` (Bu Sari) & `100 Roti` (Pak Joko).
   - Pada SVG `12 Penitip - detail`, siklus penagihan aktif menampilkan `40 item` (`Hak Rp 140.000`, `Dibayar Rp 100.000`, `Sisa Rp 40.000`) untuk Bu Sari dan `30 item` (`Hak Rp 75.000`, `Dibayar Rp 50.000`, `Sisa Rp 25.000`) untuk Pak Joko.
   - **Resolusi:** Field `terjual_siklus` dan `hak_siklus` disimpan eksplisit pada `penitip.json` agar angka layar `Penitip` identik dengan mockup `12 Penitip` sekaligus mendukung penambahan setoran interaktif (`Catat Setoran` mengurangi `Sisa` secara langsung), sementara `Laporan` menghitung agregasi langsung dari `pesanan.json` sesuai periode aktif. Nantinya saat backend SQL diimplementasikan, keduanya cukup memakai jendela `siklus_penitip_mulai` atau tabel ledger settlement.
3. **Nama `Admin` & Auth Tanpa Backend:**
   - `index.php` (halaman masuk / Login Admin) memvalidasi kredensial demo (`admin` / `admin123`) di sisi klien dan mengarahkan ke `dashboard.php` sambil menyimpan flag demonstrasi di `sessionStorage`. Halaman dashboard tetap dapat diakses langsung saat preview URL agar peninjauan UI per halaman mudah dilakukan sebelum integrasi session backend dibangun.
4. **Fitur yang Sengaja Dikecualikan (sesuai `SPESIFIKASI.md` Bagian 8):**
   - Tidak ada halaman `Penjualan`, `Tentang`, Export CSV/PDF, Cetak Struk Transaksi, Ubah Password/Profil Admin, Lupa Password, Toggle Aktif/Nonaktif QRIS, maupun harga beli modal pada Restock.
   - Menghapus barang memakai aksi **Nonaktifkan** (status nonaktif) dengan dialog konfirmasi agar riwayat transaksi aman.
5. **Aksesibilitas (WCAG 2.2 AA):**
   - Warna teks muted disesuaikan dari `#8192A9` menjadi `--grey: #5E718B` untuk teks kecil agar rasio kontras pada latar `#FFFFFF` dan `#F5F9FD` mencapai `>= 4.5:1`, sementara `#8192A9` tetap dipakai untuk ikon dekoratif/placeholder non-kritis.
   - Seluruh form memakai `<label>`, `aria-describedby`, status `role="alert"` / `aria-live`, serta dukungan navigasi keyboard (`Escape` menutup modal, popover, dan drawer).
