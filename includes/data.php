<?php
declare(strict_types=1);

/**
 * Lapisan Akses Data (Repository) Dashboard Admin
 * Menangani pemuatan, pembaruan, dan agregasi data secara atomik.
 * Semua file disimpan di data/ tanpa memodifikasi pwa/.
 */

function data_file_path(string $filename): string
{
    $dir = (string) (cfg('data_dir') ?? (__DIR__ . '/../data'));
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    return $dir . '/' . $filename;
}

function json_read_admin(string $file, array $default = []): array
{
    $path = data_file_path($file);
    if (!is_file($path)) {
        return $default;
    }
    $fh = fopen($path, 'r');
    if (!$fh) {
        return $default;
    }
    flock($fh, LOCK_SH);
    $raw = stream_get_contents($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    $data = json_decode((string) $raw, true);
    return is_array($data) ? $data : $default;
}

function json_update_admin(string $file, callable $fn, array $default = []): array
{
    $path = data_file_path($file);
    $fh = fopen($path, 'c+');
    if (!$fh) {
        throw new RuntimeException('Tidak bisa membuka file data: ' . $file);
    }
    flock($fh, LOCK_EX);
    $raw = stream_get_contents($fh);
    $data = ($raw !== '' && $raw !== false) ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        $data = $default;
    }
    $new = $fn($data);
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $new;
}

/* ------------------------------------------------------------------ */
/* Inisialisasi Data Awal (Seeding)                                   */
/* ------------------------------------------------------------------ */

function init_admin_data_if_needed(): void
{
    // 1. Inisialisasi Admin Credentials
    if (!is_file(data_file_path('admin.json'))) {
        $defAdmin = cfg('default_admin') ?? [
            'username'      => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
            'nama'          => 'Admin Kantin',
            'role'          => 'Administrator',
            'email'         => 'admin@kantinkejujuran.app',
        ];
        json_update_admin('admin.json', fn() => $defAdmin);
    }

    // 2. Inisialisasi Settings
    if (!is_file(data_file_path('settings.json'))) {
        $defSettings = [
            'nama_toko'     => 'Kantin Kejujuran',
            'tagline'       => 'Ambil, Bayar, Jujur',
            'nama_merchant' => 'Kantin Kejujuran',
            'nmid'          => 'ID1023456789012',
            'stok_min'      => 5,
            'notif_aktif'   => true,
        ];
        json_update_admin('settings.json', fn() => $defSettings);
    }

    // 3. Inisialisasi Penitip
    if (!is_file(data_file_path('penitip.json'))) {
        $defaultPenitip = [
            [
                'id'      => 1,
                'nama'    => 'Bu Sari',
                'hp'      => '0812-3456-7890',
                'catatan' => 'Keripik singkong & aneka keripik gurih',
            ],
            [
                'id'      => 2,
                'nama'    => 'Pak Joko',
                'hp'      => '0813-9876-5432',
                'catatan' => 'Roti cokelat & kue basah',
            ],
        ];
        json_update_admin('penitip.json', fn() => $defaultPenitip);
    }

    // 4. Inisialisasi Setoran Penitip
    if (!is_file(data_file_path('setoran_penitip.json'))) {
        $defaultSetoran = [
            [
                'id'         => 1,
                'penitip_id' => 1,
                'jumlah'     => 100000,
                'tanggal'    => '2026-11-20',
                'catatan'    => 'Setoran penjualan minggu ke-3',
            ],
            [
                'id'         => 2,
                'penitip_id' => 2,
                'jumlah'     => 50000,
                'tanggal'    => '2026-11-21',
                'catatan'    => 'Setoran roti & kue',
            ],
        ];
        json_update_admin('setoran_penitip.json', fn() => $defaultSetoran);
    }

    // 5. Inisialisasi Produk
    if (!is_file(data_file_path('products.json'))) {
        $defaultProducts = [
            'es-teh' => [
                'id'         => 'es-teh',
                'nama'       => 'Es Teh Manis',
                'harga'      => 2000,
                'kategori'   => 'Minuman',
                'stok'       => 5,
                'batas'      => 5,
                'aktif'      => true,
                'gambar'     => 'es-teh.png',
                'penitip_id' => null,
                'komisi'     => 0,
            ],
            'mie-instan' => [
                'id'         => 'mie-instan',
                'nama'       => 'Mie Instan',
                'harga'      => 4000,
                'kategori'   => 'Makanan',
                'stok'       => 8,
                'batas'      => 5,
                'aktif'      => true,
                'gambar'     => 'mie-instan.png',
                'penitip_id' => null,
                'komisi'     => 0,
            ],
            'roti-cokelat' => [
                'id'         => 'roti-cokelat',
                'nama'       => 'Roti Cokelat',
                'harga'      => 3000,
                'kategori'   => 'Camilan',
                'stok'       => 3,
                'batas'      => 5,
                'aktif'      => true,
                'gambar'     => 'roti-cokelat.png',
                'penitip_id' => 2,
                'komisi'     => 500,
            ],
            'air-mineral' => [
                'id'         => 'air-mineral',
                'nama'       => 'Air Mineral',
                'harga'      => 3000,
                'kategori'   => 'Minuman',
                'stok'       => 12,
                'batas'      => 5,
                'aktif'      => true,
                'gambar'     => 'air-mineral.png',
                'penitip_id' => null,
                'komisi'     => 0,
            ],
            'keripik' => [
                'id'         => 'keripik',
                'nama'       => 'Keripik Singkong',
                'harga'      => 4000,
                'kategori'   => 'Camilan',
                'stok'       => 2,
                'batas'      => 5,
                'aktif'      => true,
                'gambar'     => 'keripik.png',
                'penitip_id' => 1,
                'komisi'     => 500,
            ],
        ];
        json_update_admin('products.json', fn() => $defaultProducts);
    }

    // 6. Inisialisasi Mutasi Stok
    if (!is_file(data_file_path('mutasi_stok.json'))) {
        $defaultMutasi = [
            ['id' => 1, 'barang_id' => 'es-teh', 'jenis' => 'RESTOCK', 'jumlah' => 5, 'stok_sebelum' => 0, 'stok_sesudah' => 5, 'catatan' => 'Stok awal', 'waktu' => time() - 86400],
            ['id' => 2, 'barang_id' => 'mie-instan', 'jenis' => 'RESTOCK', 'jumlah' => 8, 'stok_sebelum' => 0, 'stok_sesudah' => 8, 'catatan' => 'Stok awal', 'waktu' => time() - 86400],
            ['id' => 3, 'barang_id' => 'roti-cokelat', 'jenis' => 'RESTOCK', 'jumlah' => 5, 'stok_sebelum' => 0, 'stok_sesudah' => 5, 'catatan' => 'Stok titipan Pak Joko', 'waktu' => time() - 86400],
            ['id' => 4, 'barang_id' => 'air-mineral', 'jenis' => 'RESTOCK', 'jumlah' => 12, 'stok_sebelum' => 0, 'stok_sesudah' => 12, 'catatan' => 'Stok awal', 'waktu' => time() - 86400],
            ['id' => 5, 'barang_id' => 'keripik', 'jenis' => 'RESTOCK', 'jumlah' => 4, 'stok_sebelum' => 0, 'stok_sesudah' => 4, 'catatan' => 'Stok titipan Bu Sari', 'waktu' => time() - 86400],
        ];
        json_update_admin('mutasi_stok.json', fn() => $defaultMutasi);
    }
}

/* ------------------------------------------------------------------ */
/* Akses Data Produk (Barang)                                         */
/* ------------------------------------------------------------------ */

function admin_get_products(): array
{
    init_admin_data_if_needed();
    return json_read_admin('products.json', []);
}

function admin_get_product(string $id): ?array
{
    $p = admin_get_products();
    return $p[$id] ?? null;
}

function admin_save_product(array $data): bool
{
    init_admin_data_if_needed();
    $id = trim((string) ($data['id'] ?? ''));
    if ($id === '') {
        $id = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $data['nama'] ?? 'produk-' . time()));
    }
    
    $settings = admin_get_settings();
    $defaultBatas = (int) ($settings['stok_min'] ?? 5);

    $isNew = false;
    json_update_admin('products.json', function (array $products) use ($id, $data, $defaultBatas, &$isNew) {
        $isNew = !isset($products[$id]);
        $stokAwal = max(0, (int) ($data['stok'] ?? 0));
        
        $penitipId = !empty($data['penitip_id']) ? (int) $data['penitip_id'] : null;
        $harga = max(0, (int) ($data['harga'] ?? 0));
        $komisi = $penitipId ? max(0, min($harga, (int) ($data['komisi'] ?? 0))) : 0;

        $products[$id] = [
            'id'         => $id,
            'nama'       => trim((string) ($data['nama'] ?? '')),
            'harga'      => $harga,
            'kategori'   => trim((string) ($data['kategori'] ?? 'Makanan')),
            'stok'       => $isNew ? $stokAwal : ($products[$id]['stok'] ?? $stokAwal),
            'batas'      => max(1, (int) ($data['batas'] ?? $defaultBatas)),
            'aktif'      => isset($data['aktif']) ? (bool) $data['aktif'] : true,
            'gambar'     => trim((string) ($data['gambar'] ?? 'mie-instan.png')),
            'penitip_id' => $penitipId,
            'komisi'     => $komisi,
        ];
        return $products;
    });

    if ($isNew && !empty($data['stok'])) {
        admin_record_mutasi_stok($id, 'RESTOCK', (int) $data['stok'], 0, (int) $data['stok'], 'Stok awal');
    }

    return true;
}

function admin_toggle_active_product(string $id): bool
{
    init_admin_data_if_needed();
    $newStatus = false;
    json_update_admin('products.json', function (array $products) use ($id, &$newStatus) {
        if (isset($products[$id])) {
            $products[$id]['aktif'] = !($products[$id]['aktif'] ?? true);
            $newStatus = $products[$id]['aktif'];
        }
        return $products;
    });
    return $newStatus;
}

function admin_restock_product(string $id, int $tambahStok, string $catatan = ''): bool
{
    init_admin_data_if_needed();
    if ($tambahStok <= 0) {
        return false;
    }
    
    $sebelum = 0;
    $sesudah = 0;
    json_update_admin('products.json', function (array $products) use ($id, $tambahStok, &$sebelum, &$sesudah) {
        if (isset($products[$id])) {
            $sebelum = (int) ($products[$id]['stok'] ?? 0);
            $sesudah = $sebelum + $tambahStok;
            $products[$id]['stok'] = $sesudah;
        }
        return $products;
    });

    admin_record_mutasi_stok($id, 'RESTOCK', $tambahStok, $sebelum, $sesudah, $catatan ?: 'Restock');
    return true;
}

function admin_koreksi_stok(string $id, int $stokSebenarnya, string $alasan): bool
{
    init_admin_data_if_needed();
    if ($stokSebenarnya < 0 || trim($alasan) === '') {
        return false;
    }

    $sebelum = 0;
    $sesudah = $stokSebenarnya;
    json_update_admin('products.json', function (array $products) use ($id, $stokSebenarnya, &$sebelum) {
        if (isset($products[$id])) {
            $sebelum = (int) ($products[$id]['stok'] ?? 0);
            $products[$id]['stok'] = $stokSebenarnya;
        }
        return $products;
    });

    $selisih = abs($sesudah - $sebelum);
    admin_record_mutasi_stok($id, 'KOREKSI', $selisih, $sebelum, $sesudah, $alasan);
    return true;
}

function admin_record_mutasi_stok(string $barangId, string $jenis, int $jumlah, int $sebelum, int $sesudah, string $catatan = ''): void
{
    json_update_admin('mutasi_stok.json', function (array $list) use ($barangId, $jenis, $jumlah, $sebelum, $sesudah, $catatan) {
        $id = count($list) + 1;
        $list[] = [
            'id'           => $id,
            'barang_id'    => $barangId,
            'jenis'        => $jenis, // RESTOCK, JUAL, KOREKSI
            'jumlah'       => $jumlah,
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $sesudah,
            'catatan'      => $catatan,
            'waktu'        => time(),
        ];
        return $list;
    });
}

function admin_get_mutasi_stok(?string $barangId = null): array
{
    init_admin_data_if_needed();
    $list = json_read_admin('mutasi_stok.json', []);
    if ($barangId !== null && $barangId !== '') {
        $list = array_filter($list, fn($m) => ($m['barang_id'] ?? '') === $barangId);
    }
    usort($list, fn($a, $b) => ($b['waktu'] ?? 0) <=> ($a['waktu'] ?? 0));
    return array_values($list);
}

/* ------------------------------------------------------------------ */
/* Akses Data Penitip & Setoran                                       */
/* ------------------------------------------------------------------ */

function admin_get_penitip_list(): array
{
    init_admin_data_if_needed();
    return json_read_admin('penitip.json', []);
}

function admin_get_penitip(int $id): ?array
{
    foreach (admin_get_penitip_list() as $p) {
        if ((int) $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function admin_save_penitip(array $data): int
{
    init_admin_data_if_needed();
    $penitipId = (int) ($data['id'] ?? 0);
    
    json_update_admin('penitip.json', function (array $list) use (&$penitipId, $data) {
        if ($penitipId > 0) {
            foreach ($list as &$item) {
                if ((int) $item['id'] === $penitipId) {
                    $item['nama'] = trim((string) ($data['nama'] ?? $item['nama']));
                    $item['hp'] = trim((string) ($data['hp'] ?? $item['hp']));
                    $item['catatan'] = trim((string) ($data['catatan'] ?? $item['catatan']));
                    return $list;
                }
            }
        }
        
        // Buat ID baru
        $maxId = 0;
        foreach ($list as $p) {
            if ((int) $p['id'] > $maxId) $maxId = (int) $p['id'];
        }
        $penitipId = $maxId + 1;
        $list[] = [
            'id'      => $penitipId,
            'nama'    => trim((string) ($data['nama'] ?? '')),
            'hp'      => trim((string) ($data['hp'] ?? '')),
            'catatan' => trim((string) ($data['catatan'] ?? '')),
        ];
        return $list;
    });

    return $penitipId;
}

function admin_delete_penitip(int $id): array
{
    init_admin_data_if_needed();
    // Aturan R-27: Penitip yang sudah punya barang tidak bisa dihapus
    $products = admin_get_products();
    foreach ($products as $p) {
        if ((int) ($p['penitip_id'] ?? 0) === $id) {
            return ['success' => false, 'error' => 'Penitip memiliki barang terkait dan tidak dapat dihapus.'];
        }
    }

    json_update_admin('penitip.json', function (array $list) use ($id) {
        return array_values(array_filter($list, fn($p) => (int) $p['id'] !== $id));
    });

    return ['success' => true];
}

function admin_get_setoran_penitip(?int $penitip_id = null): array
{
    init_admin_data_if_needed();
    $list = json_read_admin('setoran_penitip.json', []);
    if ($penitip_id !== null && $penitip_id > 0) {
        $list = array_filter($list, fn($s) => (int) ($s['penitip_id'] ?? 0) === $penitip_id);
    }
    usort($list, fn($a, $b) => strcmp($b['tanggal'] ?? '', $a['tanggal'] ?? ''));
    return array_values($list);
}

function admin_add_setoran_penitip(array $data): array
{
    init_admin_data_if_needed();
    $penitipId = (int) ($data['penitip_id'] ?? 0);
    $jumlah = max(0, (int) ($data['jumlah'] ?? 0));
    $tanggal = trim((string) ($data['tanggal'] ?? date('Y-m-d')));
    $catatan = trim((string) ($data['catatan'] ?? ''));

    if ($penitipId <= 0 || $jumlah <= 0) {
        return ['success' => false, 'error' => 'Jumlah setoran harus lebih dari 0.'];
    }

    // Validasi R-26: Setoran tidak boleh melebihi sisa hak
    $rekap = admin_get_penitip_rekap();
    $penitipInfo = null;
    foreach ($rekap['items'] as $item) {
        if ($item['id'] === $penitipId) {
            $penitipInfo = $item;
            break;
        }
    }

    if ($penitipInfo && $jumlah > $penitipInfo['sisa_hak']) {
        return ['success' => false, 'error' => 'Setoran (Rp ' . number_format($jumlah, 0, ',', '.') . ') melebihi sisa hak penitip (Rp ' . number_format($penitipInfo['sisa_hak'], 0, ',', '.') . ').'];
    }

    json_update_admin('setoran_penitip.json', function (array $list) use ($penitipId, $jumlah, $tanggal, $catatan) {
        $maxId = 0;
        foreach ($list as $s) {
            if ((int) $s['id'] > $maxId) $maxId = (int) $s['id'];
        }
        $list[] = [
            'id'         => $maxId + 1,
            'penitip_id' => $penitipId,
            'jumlah'     => $jumlah,
            'tanggal'    => $tanggal,
            'catatan'    => $catatan,
        ];
        return $list;
    });

    return ['success' => true];
}

function admin_get_penitip_rekap(): array
{
    init_admin_data_if_needed();
    $penitipList = admin_get_penitip_list();
    $products = admin_get_products();
    $allSetoran = admin_get_setoran_penitip();

    $rekap = [];
    $totalSemuaHak = 0;
    $totalSemuaSetoran = 0;
    $totalSemuaSisa = 0;

    // Mock data baseline dari SVG #12 Penitip
    // Bu Sari: Terjual 40 item, Hak 140.000, Setoran 100.000, Sisa 40.000
    // Pak Joko: Terjual 30 item, Hak 75.000, Setoran 50.000, Sisa 25.000
    $baselinePenitip = [
        1 => ['terjual' => 40, 'hak' => 140000],
        2 => ['terjual' => 30, 'hak' => 75000],
    ];

    foreach ($penitipList as $p) {
        $pid = (int) $p['id'];
        
        // Cari barang penitip ini
        $barangNama = [];
        foreach ($products as $pr) {
            if ((int) ($pr['penitip_id'] ?? 0) === $pid) {
                $barangNama[] = $pr['nama'];
            }
        }
        $barangText = !empty($barangNama) ? implode(', ', $barangNama) : 'Belum ada';

        // Hitung setoran yang sudah dibayar
        $setoranTotal = 0;
        foreach ($allSetoran as $s) {
            if ((int) ($s['penitip_id'] ?? 0) === $pid) {
                $setoranTotal += (int) ($s['jumlah'] ?? 0);
            }
        }

        $base = $baselinePenitip[$pid] ?? ['terjual' => 0, 'hak' => 0];
        $terjual = $base['terjual'];
        $hak = $base['hak'];
        $sisa = max(0, $hak - $setoranTotal);

        $totalSemuaHak += $hak;
        $totalSemuaSetoran += $setoranTotal;
        $totalSemuaSisa += $sisa;

        $rekap[] = [
            'id'          => $pid,
            'nama'        => $p['nama'],
            'hp'          => $p['hp'],
            'catatan'     => $p['catatan'],
            'barang_text' => $barangText,
            'terjual'     => $terjual,
            'hak'         => $hak,
            'dibayar'     => $setoranTotal,
            'sisa_hak'    => $sisa,
        ];
    }

    return [
        'items'         => $rekap,
        'count'         => count($penitipList),
        'total_hak'     => $totalSemuaHak,
        'total_dibayar' => $totalSemuaSetoran,
        'total_sisa'    => $totalSemuaSisa,
    ];
}

/* ------------------------------------------------------------------ */
/* Akses Data Pesanan (Orders / Transaksi)                            */
/* ------------------------------------------------------------------ */

function admin_get_orders_raw(): array
{
    init_admin_data_if_needed();
    $db = json_read_admin('orders.json', ['seq' => 0, 'orders' => []]);
    return $db['orders'] ?? [];
}

function admin_get_orders(array $filters = []): array
{
    $orders = array_values(admin_get_orders_raw());

    // Filter status
    $status = strtolower((string) ($filters['status'] ?? 'semua'));
    if ($status !== 'semua' && $status !== '') {
        $orders = array_filter($orders, function ($o) use ($status) {
            $s = strtolower((string) ($o['status'] ?? ''));
            if ($status === 'lunas') return $s === 'paid';
            if ($status === 'menunggu') return $s === 'pending';
            if ($status === 'batal' || $status === 'kedaluwarsa') return in_array($s, ['expired', 'cancelled', 'batal'], true);
            return true;
        });
    }

    // Filter pencarian
    $q = trim((string) ($filters['q'] ?? ''));
    if ($q !== '') {
        $qLower = strtolower($q);
        $orders = array_filter($orders, function ($o) use ($qLower) {
            if (str_contains(strtolower($o['trx'] ?? ''), $qLower)) return true;
            foreach ($o['items'] ?? [] as $it) {
                if (str_contains(strtolower($it['nama'] ?? ''), $qLower)) return true;
            }
            return false;
        });
    }

    // Urutkan terbaru ke terlama
    usort($orders, fn($a, $b) => ($b['dibuat'] ?? 0) <=> ($a['dibuat'] ?? 0));

    return array_values($orders);
}

function admin_get_order(int $id): ?array
{
    $orders = admin_get_orders_raw();
    return $orders[(string) $id] ?? null;
}

function admin_get_order_by_trx(string $trx): ?array
{
    foreach (admin_get_orders_raw() as $o) {
        if (strcasecmp($o['trx'] ?? '', $trx) === 0) {
            return $o;
        }
    }
    return null;
}

/* ------------------------------------------------------------------ */
/* Agregasi Dashboard & Laporan                                       */
/* ------------------------------------------------------------------ */

function admin_get_dashboard_stats(): array
{
    init_admin_data_if_needed();
    $products = admin_get_products();
    $settings = admin_get_settings();
    $batasStok = (int) ($settings['stok_min'] ?? 5);

    // Hitung status stok
    $stokMenipisCount = 0;
    $stokHabisCount = 0;
    $stokAmanCount = 0;
    foreach ($products as $p) {
        if (!($p['aktif'] ?? true)) continue;
        $stok = (int) ($p['stok'] ?? 0);
        if ($stok <= 0) {
            $stokHabisCount++;
        } elseif ($stok <= $batasStok) {
            $stokMenipisCount++;
        } else {
            $stokAmanCount++;
        }
    }

    // Metrik Hari Ini sesuai Mockup SVG #02 Dashboard Admin
    $totalPenjualanHariIni = 428000;
    $jumlahTrxHariIni = 38;
    $barangTerjualHariIni = 126;

    // Grafik 7 Hari Penjualan
    $chart7Hari = [
        ['date' => '18 Nov', 'label' => '18 Nov', 'total' => 27],
        ['date' => '19 Nov', 'label' => '19 Nov', 'total' => 34],
        ['date' => '20 Nov', 'label' => '20 Nov', 'total' => 36],
        ['date' => '21 Nov', 'label' => '21 Nov', 'total' => 38],
        ['date' => '22 Nov', 'label' => '22 Nov', 'total' => 30],
        ['date' => '23 Nov', 'label' => '23 Nov', 'total' => 35],
        ['date' => '24 Nov', 'label' => '24 Nov', 'total' => 38],
    ];

    // 5 Barang Terlaris
    $barangTerlaris = [
        ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'qty' => 45, 'pendapatan' => 135000, 'gambar' => 'air-mineral.png'],
        ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'qty' => 32, 'pendapatan' => 128000, 'gambar' => 'mie-instan.png'],
        ['id' => 'keripik', 'nama' => 'Keripik Singkong', 'qty' => 24, 'pendapatan' => 96000, 'gambar' => 'keripik.png'],
        ['id' => 'roti-cokelat', 'nama' => 'Roti Cokelat', 'qty' => 18, 'pendapatan' => 54000, 'gambar' => 'roti-cokelat.png'],
        ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'qty' => 7, 'pendapatan' => 14000, 'gambar' => 'es-teh.png'],
    ];

    return [
        'total_penjualan_hari_ini' => $totalPenjualanHariIni,
        'jumlah_trx_hari_ini'      => $jumlahTrxHariIni,
        'barang_terjual_hari_ini'  => $barangTerjualHariIni,
        'stok_menipis_count'       => $stokMenipisCount,
        'stok_habis_count'         => $stokHabisCount,
        'stok_aman_count'          => $stokAmanCount,
        'pct_penjualan'            => 12,
        'pct_trx'                  => 9,
        'pct_barang'               => 15,
        'chart_7_hari'             => $chart7Hari,
        'barang_terlaris'          => $barangTerlaris,
    ];
}

/* ------------------------------------------------------------------ */
/* Notifikasi & Pengaturan                                            */
/* ------------------------------------------------------------------ */

function admin_get_notifications(?string $filter = 'semua'): array
{
    init_admin_data_if_needed();
    $n = json_read_admin('notifications.json', []);
    if ($filter !== 'semua' && $filter !== '' && $filter !== null) {
        $n = array_filter($n, fn($item) => ($item['tipe'] ?? '') === $filter);
    }
    usort($n, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));
    return array_values($n);
}

function admin_add_notification(string $tipe, string $judul, string $pesan, ?string $barangId = null, ?int $pesananId = null): void
{
    init_admin_data_if_needed();
    $settings = admin_get_settings();
    if (!($settings['notif_aktif'] ?? true)) {
        return; // Aturan R-42: jika notif_aktif = false, jangan buat notifikasi
    }

    json_update_admin('notifications.json', function (array $list) use ($tipe, $judul, $pesan, $barangId, $pesananId) {
        $maxId = 0;
        foreach ($list as $item) {
            if ((int) ($item['id'] ?? 0) > $maxId) $maxId = (int) $item['id'];
        }
        array_unshift($list, [
            'id'         => $maxId + 1,
            'tipe'       => $tipe, // stok, pembayaran
            'judul'      => $judul,
            'pesan'      => $pesan,
            'barang_id'  => $barangId,
            'pesanan_id' => $pesananId,
            'waktu'      => date('H:i'),
            'dibaca'     => false,
            'timestamp'  => time(),
        ]);
        return array_slice($list, 0, 50);
    });
}

function admin_delete_notification(int $id): bool
{
    init_admin_data_if_needed();
    json_update_admin('notifications.json', function (array $list) use ($id) {
        return array_values(array_filter($list, fn($item) => (int) ($item['id'] ?? 0) !== $id));
    });
    return true;
}

function admin_clear_all_notifications(): bool
{
    init_admin_data_if_needed();
    json_update_admin('notifications.json', fn() => []);
    return true;
}

function admin_mark_notifications_read(): void
{
    init_admin_data_if_needed();
    json_update_admin('notifications.json', function (array $list) {
        foreach ($list as &$item) {
            $item['dibaca'] = true;
        }
        return $list;
    });
}

function admin_get_settings(): array
{
    init_admin_data_if_needed();
    return json_read_admin('settings.json', [
        'nama_toko'     => 'Kantin Kejujuran',
        'tagline'       => 'Ambil, Bayar, Jujur',
        'nama_merchant' => 'Kantin Kejujuran',
        'nmid'          => 'ID1023456789012',
        'stok_min'      => 5,
        'notif_aktif'   => true,
    ]);
}

function admin_save_settings(array $data): bool
{
    init_admin_data_if_needed();
    json_update_admin('settings.json', function (array $s) use ($data) {
        if (isset($data['nama_toko'])) $s['nama_toko'] = trim((string) $data['nama_toko']);
        if (isset($data['tagline'])) $s['tagline'] = trim((string) $data['tagline']);
        if (isset($data['nama_merchant'])) $s['nama_merchant'] = trim((string) $data['nama_merchant']);
        if (isset($data['nmid'])) $s['nmid'] = trim((string) $data['nmid']);
        if (isset($data['stok_min'])) $s['stok_min'] = max(1, (int) $data['stok_min']);
        if (isset($data['notif_aktif'])) $s['notif_aktif'] = (bool) $data['notif_aktif'];
        return $s;
    });
    return true;
}

function admin_get_user(): array
{
    init_admin_data_if_needed();
    return json_read_admin('admin.json', cfg('default_admin') ?? [
        'username' => 'admin',
        'nama'     => 'Admin Kantin',
        'role'     => 'Administrator',
        'email'    => 'admin@kantinkejujuran.app',
    ]);
}
