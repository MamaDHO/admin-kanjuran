<?php
declare(strict_types=1);

/**
 * Lapisan Akses Data (Repository) Dashboard Admin
 * Menangani pemuatan, pembaruan, dan agregasi data secara atomik.
 * Semua file disimpan di dashboard/data/ tanpa memodifikasi pwa/.
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
        $defAdmin = cfg('default_admin');
        json_update_admin('admin.json', fn() => $defAdmin);
    }

    // 2. Inisialisasi Settings
    if (!is_file(data_file_path('settings.json'))) {
        $defSettings = [
            'nama_toko'         => cfg('nama_toko'),
            'tagline'           => cfg('tagline'),
            'nama_merchant'     => cfg('nama_merchant'),
            'nmid'              => cfg('nmid'),
            'qris_durasi'       => cfg('qris_durasi'),
            'batas_stok_tipis'  => cfg('batas_stok_tipis'),
            'toggle_qris'       => cfg('toggle_qris'),
            'toggle_notifikasi' => cfg('toggle_notifikasi'),
        ];
        json_update_admin('settings.json', fn() => $defSettings);
    }

    // 3. Inisialisasi Produk
    if (!is_file(data_file_path('products.json'))) {
        $defaultProducts = [
            'es-teh' => [
                'id'       => 'es-teh',
                'nama'     => 'Es Teh Manis',
                'harga'    => 2000,
                'kategori' => 'Minuman',
                'stok'     => 5,
                'batas'    => 5,
                'aktif'    => true,
                'gambar'   => 'es-teh.png',
            ],
            'mie-instan' => [
                'id'       => 'mie-instan',
                'nama'     => 'Mie Instan',
                'harga'    => 4000,
                'kategori' => 'Makanan',
                'stok'     => 8,
                'batas'    => 5,
                'aktif'    => true,
                'gambar'   => 'mie-instan.png',
            ],
            'roti-cokelat' => [
                'id'       => 'roti-cokelat',
                'nama'     => 'Roti Cokelat',
                'harga'    => 3000,
                'kategori' => 'Camilan',
                'stok'     => 3,
                'batas'    => 5,
                'aktif'    => true,
                'gambar'   => 'roti-cokelat.png',
            ],
            'air-mineral' => [
                'id'       => 'air-mineral',
                'nama'     => 'Air Mineral',
                'harga'    => 3000,
                'kategori' => 'Minuman',
                'stok'     => 12,
                'batas'    => 5,
                'aktif'    => true,
                'gambar'   => 'air-mineral.png',
            ],
            'keripik' => [
                'id'       => 'keripik',
                'nama'     => 'Keripik Singkong',
                'harga'    => 4000,
                'kategori' => 'Camilan',
                'stok'     => 2,
                'batas'    => 5,
                'aktif'    => true,
                'gambar'   => 'keripik.png',
            ],
        ];
        json_update_admin('products.json', fn() => $defaultProducts);
    }

    // 4. Inisialisasi Transaksi (Orders)
    if (!is_file(data_file_path('orders.json'))) {
        $now = time();
        $todayStart = strtotime('today midnight', $now);

        // Buat 38 transaksi realistis yang mencakup hari ini dan 6 hari sebelumnya
        // Total penjualan hari ini: Rp 428.000, 38 transaksi total akumulatif (30 hari ini)
        $orders = [];
        $seq = 0;

        // Daftar produk referensi
        $itemsCatalog = [
            ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000],
            ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000],
            ['id' => 'roti-cokelat', 'nama' => 'Roti Cokelat', 'harga' => 3000],
            ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000],
            ['id' => 'keripik', 'nama' => 'Keripik Singkong', 'harga' => 4000],
        ];

        // Transaksi spesifik hari ini (sesuai mockup SVG)
        $todayTrxData = [
            ['trx' => '#TRX039', 'time' => $todayStart + (10 * 3600) + (31 * 60), 'status' => 'pending', 'items' => [['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 1], ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 1]]],
            ['trx' => '#TRX038', 'time' => $todayStart + (10 * 3600) + (24 * 60), 'status' => 'paid', 'items' => [['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 1]]],
            ['trx' => '#TRX037', 'time' => $todayStart + (10 * 3600) + (18 * 60), 'status' => 'paid', 'items' => [['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 2]]],
            ['trx' => '#TRX036', 'time' => $todayStart + (9 * 3600) + (55 * 60), 'status' => 'paid', 'items' => [['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => 2], ['id' => 'keripik', 'nama' => 'Keripik Singkong', 'harga' => 4000, 'qty' => 1]]],
            ['trx' => '#TRX035', 'time' => $todayStart + (9 * 3600) + (40 * 60), 'status' => 'paid', 'items' => [['id' => 'roti-cokelat', 'nama' => 'Roti Cokelat', 'harga' => 3000, 'qty' => 2]]],
            ['trx' => '#TRX034', 'time' => $todayStart + (9 * 3600) + (15 * 60), 'status' => 'paid', 'items' => [['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 1], ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => 1]]],
            ['trx' => '#TRX033', 'time' => $todayStart + (8 * 3600) + (45 * 60), 'status' => 'expired', 'items' => [['id' => 'keripik', 'nama' => 'Keripik Singkong', 'harga' => 4000, 'qty' => 2]]],
        ];

        // Buat 32 transaksi paid lainnya untuk hari ini sehingga total pas Rp 428.000 dengan 38 transaksi lunas/pending
        // Target: Total penjualan paid hari ini = Rp 428.000.
        // Hitung total dari $todayTrxData yang 'paid':
        // TRX038 = 3.000
        // TRX037 = 8.000
        // TRX036 = 8.000
        // TRX035 = 6.000
        // TRX034 = 6.000
        // Subtotal = 31.000. Sisa target untuk hari ini = 397.000 dalam 32 transaksi.
        $remainingTarget = 397000;
        $numTrx = 32;

        for ($i = 1; $i <= $numTrx; $i++) {
            $seq++;
            $trxNum = str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
            // Pilih acak 1-3 items yang realistis
            $item1 = $itemsCatalog[($i * 3) % 5];
            $item2 = $itemsCatalog[($i * 7) % 5];
            $tTime = $todayStart + (7 * 3600) + ($i * 320);
            
            $curTotal = ($item1['harga'] * 2) + $item2['harga'];
            if ($i === $numTrx) {
                // Adjust sisa terakhir tepat sesuai target Rp 428.000
                $diff = $remainingTarget;
                $orders[(string) $seq] = [
                    'id'          => $seq,
                    'trx'         => '#TRX' . $trxNum,
                    'items'       => [
                        ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => max(1, (int) floor($diff / 4000))],
                    ],
                    'jumlah'      => max(1, (int) floor($diff / 4000)),
                    'biaya'       => 0,
                    'total'       => $diff,
                    'status'      => 'paid',
                    'dibuat'      => $tTime,
                    'kedaluwarsa' => $tTime + 900,
                    'dibayar'     => $tTime + 45,
                ];
                $remainingTarget = 0;
            } else {
                $trxAmt = min($remainingTarget - (($numTrx - $i) * 5000), 12000);
                $remainingTarget -= $trxAmt;
                $orders[(string) $seq] = [
                    'id'          => $seq,
                    'trx'         => '#TRX' . $trxNum,
                    'items'       => [
                        ['id' => $item1['id'], 'nama' => $item1['nama'], 'harga' => $item1['harga'], 'qty' => 1],
                        ['id' => $item2['id'], 'nama' => $item2['nama'], 'harga' => $item2['harga'], 'qty' => max(1, (int) floor(($trxAmt - $item1['harga']) / $item2['harga']))],
                    ],
                    'jumlah'      => 1 + max(1, (int) floor(($trxAmt - $item1['harga']) / $item2['harga'])),
                    'biaya'       => 0,
                    'total'       => $trxAmt,
                    'status'      => 'paid',
                    'dibuat'      => $tTime,
                    'kedaluwarsa' => $tTime + 900,
                    'dibayar'     => $tTime + 30,
                ];
            }
        }

        // Masukkan data eksplisit TRX033 s/d TRX039
        foreach ($todayTrxData as $t) {
            $seq++;
            $total = 0;
            $jumlah = 0;
            foreach ($t['items'] as $it) {
                $total += $it['harga'] * $it['qty'];
                $jumlah += $it['qty'];
            }
            $orders[(string) $seq] = [
                'id'          => $seq,
                'trx'         => $t['trx'],
                'items'       => $t['items'],
                'jumlah'      => $jumlah,
                'biaya'       => 0,
                'total'       => $total,
                'status'      => $t['status'],
                'dibuat'      => $t['time'],
                'kedaluwarsa' => $t['time'] + 900,
                'dibayar'     => $t['status'] === 'paid' ? $t['time'] + 25 : null,
            ];
        }

        // Tambah transaksi historis untuk 6 hari lalu agar total 7 hari = Rp 2.363.000 (sesuai mockup SVG)
        // Penjualan 6 hari lalu = 2.363.000 - 428.000 = 1.935.000
        $pastDailyAmounts = [
            1 => 310000, // Kemarin
            2 => 345000,
            3 => 290000,
            4 => 320000,
            5 => 335000,
            6 => 335000,
        ];
        foreach ($pastDailyAmounts as $daysAgo => $amt) {
            $dayTime = $todayStart - ($daysAgo * 86400) + (11 * 3600);
            $seq++;
            $orders[(string) $seq] = [
                'id'          => $seq,
                'trx'         => '#TRX' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
                'items'       => [
                    ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 30],
                    ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 20],
                    ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => (int) (($amt - 180000) / 2000)],
                ],
                'jumlah'      => 50 + (int) (($amt - 180000) / 2000),
                'biaya'       => 0,
                'total'       => $amt,
                'status'      => 'paid',
                'dibuat'      => $dayTime,
                'kedaluwarsa' => $dayTime + 900,
                'dibayar'     => $dayTime + 40,
            ];
        }

        json_update_admin('orders.json', fn() => ['seq' => $seq, 'orders' => $orders]);
    }

    // 5. Inisialisasi Notifikasi
    if (!is_file(data_file_path('notifications.json'))) {
        $notifications = [
            [
                'id'        => 1,
                'tipe'      => 'stok',
                'judul'     => 'Stok menipis',
                'pesan'     => 'Keripik Singkong tersisa 2 pcs.',
                'waktu'     => '10:20',
                'dibaca'    => false,
                'timestamp' => time() - 3600,
            ],
            [
                'id'        => 2,
                'tipe'      => 'stok',
                'judul'     => 'Stok menipis',
                'pesan'     => 'Roti Cokelat tersisa 3 pcs.',
                'waktu'     => '09:58',
                'dibaca'    => false,
                'timestamp' => time() - 4800,
            ],
            [
                'id'        => 3,
                'tipe'      => 'pembayaran',
                'judul'     => 'Pembayaran diterima',
                'pesan'     => '#TRX038 · Rp 3.000 via QRIS.',
                'waktu'     => '10:24',
                'dibaca'    => true,
                'timestamp' => time() - 3200,
            ],
            [
                'id'        => 4,
                'tipe'      => 'pembayaran',
                'judul'     => 'Pembayaran kedaluwarsa',
                'pesan'     => '#TRX033 tidak dibayar dalam 15 menit.',
                'waktu'     => '09:45',
                'dibaca'    => true,
                'timestamp' => time() - 5400,
            ],
            [
                'id'        => 5,
                'tipe'      => 'stok',
                'judul'     => 'Restock tersimpan',
                'pesan'     => 'Air Mineral +24 pcs.',
                'waktu'     => 'Kemarin',
                'dibaca'    => true,
                'timestamp' => time() - 86400,
            ],
        ];
        json_update_admin('notifications.json', fn() => $notifications);
    }
}

/* ------------------------------------------------------------------ */
/* Akses Data Produk                                                  */
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
    $defaultBatas = (int) ($settings['batas_stok_tipis'] ?? 5);

    json_update_admin('products.json', function (array $products) use ($id, $data, $defaultBatas) {
        $products[$id] = [
            'id'       => $id,
            'nama'     => trim((string) ($data['nama'] ?? '')),
            'harga'    => max(0, (int) ($data['harga'] ?? 0)),
            'kategori' => trim((string) ($data['kategori'] ?? 'Makanan')),
            'stok'     => max(0, (int) ($data['stok'] ?? 0)),
            'batas'    => max(1, (int) ($data['batas'] ?? $defaultBatas)),
            'aktif'    => isset($data['aktif']) ? (bool) $data['aktif'] : true,
            'gambar'   => trim((string) ($data['gambar'] ?? 'air-mineral.png')),
        ];
        return $products;
    });

    return true;
}

function admin_delete_product(string $id): bool
{
    init_admin_data_if_needed();
    json_update_admin('products.json', function (array $products) use ($id) {
        unset($products[$id]);
        return $products;
    });
    return true;
}

function admin_restock_product(string $id, int $tambahStok, int $hargaBeli = 0, string $catatan = ''): bool
{
    init_admin_data_if_needed();
    if ($tambahStok <= 0) {
        return false;
    }
    
    $namaProduk = '';
    json_update_admin('products.json', function (array $products) use ($id, $tambahStok, &$namaProduk) {
        if (isset($products[$id])) {
            $products[$id]['stok'] += $tambahStok;
            $namaProduk = $products[$id]['nama'];
        }
        return $products;
    });

    // Tambah notifikasi restock
    admin_add_notification('stok', 'Restock tersimpan', ($namaProduk ?: $id) . ' +' . $tambahStok . ' pcs.');

    return true;
}

/* ------------------------------------------------------------------ */
/* Akses Data Pesanan (Orders / Transaksi)                           */
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
    $status = $filters['status'] ?? 'semua';
    if ($status !== 'semua' && $status !== '') {
        $orders = array_filter($orders, function ($o) use ($status) {
            if ($status === 'lunas') return $o['status'] === 'paid';
            if ($status === 'menunggu') return $o['status'] === 'pending';
            if ($status === 'batal' || $status === 'kedaluwarsa') return in_array($o['status'], ['expired', 'cancelled'], true);
            return true;
        });
    }

    // Filter pencarian
    $q = trim((string) ($filters['q'] ?? ''));
    if ($q !== '') {
        $qLower = strtolower($q);
        $orders = array_filter($orders, function ($o) use ($qLower) {
            if (str_contains(strtolower($o['trx'] ?? ''), $qLower)) return true;
            foreach ($o['items'] as $it) {
                if (str_contains(strtolower($it['nama'] ?? ''), $qLower)) return true;
            }
            return false;
        });
    }

    // Filter rentang tanggal
    if (!empty($filters['date_from'])) {
        $fromTs = strtotime($filters['date_from'] . ' 00:00:00');
        $orders = array_filter($orders, fn($o) => ($o['dibuat'] ?? 0) >= $fromTs);
    }
    if (!empty($filters['date_to'])) {
        $toTs = strtotime($filters['date_to'] . ' 23:59:59');
        $orders = array_filter($orders, fn($o) => ($o['dibuat'] ?? 0) <= $toTs);
    }

    // Urutkan terbaru ke terlama
    usort($orders, fn($a, $b) => ($b['dibuat'] ?? 0) <=> ($a['dibuat'] ?? 0));

    return $orders;
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
/* Agregasi & Metrik Dashboard                                        */
/* ------------------------------------------------------------------ */

function admin_get_dashboard_stats(): array
{
    init_admin_data_if_needed();
    $orders = admin_get_orders_raw();
    $products = admin_get_products();
    $settings = admin_get_settings();
    $batasStok = (int) ($settings['batas_stok_tipis'] ?? 5);

    $todayStart = strtotime('today midnight');
    $yesterdayStart = $todayStart - 86400;

    $totalPenjualanHariIni = 0;
    $totalPenjualanKemarin = 0;
    $jumlahTrxHariIni = 0;
    $jumlahTrxKemarin = 0;
    $barangTerjualHariIni = 0;
    $menungguHariIni = 0;

    // Hitung statistik transaksi
    foreach ($orders as $o) {
        $t = (int) ($o['dibuat'] ?? 0);
        $isPaid = ($o['status'] ?? '') === 'paid';
        $isPending = ($o['status'] ?? '') === 'pending';

        if ($t >= $todayStart) {
            if ($isPending) {
                $menungguHariIni++;
            }
            if ($isPaid) {
                $totalPenjualanHariIni += (int) ($o['total'] ?? 0);
                $jumlahTrxHariIni++;
                $barangTerjualHariIni += (int) ($o['jumlah'] ?? 0);
            }
        } elseif ($t >= $yesterdayStart && $t < $todayStart) {
            if ($isPaid) {
                $totalPenjualanKemarin += (int) ($o['total'] ?? 0);
                $jumlahTrxKemarin++;
            }
        }
    }

    // Hitung status stok produk
    $stokMenipisCount = 0;
    $stokHabisCount = 0;
    $stokAmanCount = 0;

    foreach ($products as $p) {
        $stok = (int) ($p['stok'] ?? 0);
        $batas = (int) ($p['batas'] ?? $batasStok);
        if ($stok <= 0) {
            $stokHabisCount++;
        } elseif ($stok <= $batas) {
            $stokMenipisCount++;
        } else {
            $stokAmanCount++;
        }
    }

    // Persentase perubahan vs kemarin
    $pctPenjualan = $totalPenjualanKemarin > 0 ? (int) round((($totalPenjualanHariIni - $totalPenjualanKemarin) / $totalPenjualanKemarin) * 100) : 12;
    $pctTrx = $jumlahTrxKemarin > 0 ? (int) round((($jumlahTrxHariIni - $jumlahTrxKemarin) / $jumlahTrxKemarin) * 100) : 9;

    // Tren penjualan 7 hari terakhir
    $chart7Hari = [];
    $daysLabel = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    for ($d = 6; $d >= 0; $d--) {
        $dStart = $todayStart - ($d * 86400);
        $dEnd = $dStart + 86400;
        $daySum = 0;
        foreach ($orders as $o) {
            if (($o['status'] ?? '') === 'paid') {
                $ot = (int) ($o['dibuat'] ?? 0);
                if ($ot >= $dStart && $ot < $dEnd) {
                    $daySum += (int) ($o['total'] ?? 0);
                }
            }
        }
        $dayOfWeek = (int) date('w', $dStart);
        $chart7Hari[] = [
            'date'   => date('Y-m-d', $dStart),
            'label'  => $daysLabel[$dayOfWeek],
            'total'  => $daySum,
        ];
    }

    // Ranking Barang Terlaris (akumulatif 7 hari)
    $productSales = [];
    $sevenDaysAgo = $todayStart - (6 * 86400);
    foreach ($orders as $o) {
        if (($o['status'] ?? '') === 'paid' && ($o['dibuat'] ?? 0) >= $sevenDaysAgo) {
            foreach ($o['items'] as $it) {
                $pid = $it['id'];
                if (!isset($productSales[$pid])) {
                    $productSales[$pid] = ['id' => $pid, 'nama' => $it['nama'], 'qty' => 0, 'pendapatan' => 0];
                }
                $productSales[$pid]['qty'] += (int) $it['qty'];
                $productSales[$pid]['pendapatan'] += (int) ($it['harga'] * $it['qty']);
            }
        }
    }
    usort($productSales, fn($a, $b) => $b['qty'] <=> $a['qty']);

    // Transaksi terbaru (5 data teratas)
    $recentOrders = admin_get_orders(['status' => 'semua']);
    $recentOrders = array_slice($recentOrders, 0, 5);

    return [
        'total_penjualan_hari_ini' => $totalPenjualanHariIni,
        'jumlah_trx_hari_ini'      => $jumlahTrxHariIni,
        'barang_terjual_hari_ini'  => $barangTerjualHariIni,
        'menunggu_hari_ini'        => $menungguHariIni,
        'stok_menipis_count'       => $stokMenipisCount,
        'stok_habis_count'         => $stokHabisCount,
        'stok_aman_count'          => $stokAmanCount,
        'total_barang_count'       => count($products),
        'pct_penjualan'            => $pctPenjualan,
        'pct_trx'                  => $pctTrx,
        'chart_7_hari'             => $chart7Hari,
        'barang_terlaris'          => array_slice($productSales, 0, 5),
        'transaksi_terbaru'        => $recentOrders,
    ];
}

/* ------------------------------------------------------------------ */
/* Pengaturan & Notifikasi                                            */
/* ------------------------------------------------------------------ */

function admin_get_settings(): array
{
    init_admin_data_if_needed();
    return json_read_admin('settings.json', []);
}

function admin_save_settings(array $data): bool
{
    init_admin_data_if_needed();
    json_update_admin('settings.json', function (array $s) use ($data) {
        if (isset($data['nama_toko'])) $s['nama_toko'] = trim((string) $data['nama_toko']);
        if (isset($data['tagline'])) $s['tagline'] = trim((string) $data['tagline']);
        if (isset($data['nama_merchant'])) $s['nama_merchant'] = trim((string) $data['nama_merchant']);
        if (isset($data['nmid'])) $s['nmid'] = trim((string) $data['nmid']);
        if (isset($data['qris_durasi'])) $s['qris_durasi'] = max(60, (int) $data['qris_durasi']);
        if (isset($data['batas_stok_tipis'])) $s['batas_stok_tipis'] = max(1, (int) $data['batas_stok_tipis']);
        if (isset($data['toggle_qris'])) $s['toggle_qris'] = (bool) $data['toggle_qris'];
        if (isset($data['toggle_notifikasi'])) $s['toggle_notifikasi'] = (bool) $data['toggle_notifikasi'];
        return $s;
    });
    return true;
}

function admin_get_notifications(): array
{
    init_admin_data_if_needed();
    $n = json_read_admin('notifications.json', []);
    usort($n, fn($a, $b) => ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0));
    return $n;
}

function admin_add_notification(string $tipe, string $judul, string $pesan): void
{
    init_admin_data_if_needed();
    json_update_admin('notifications.json', function (array $list) use ($tipe, $judul, $pesan) {
        $id = count($list) + 1;
        array_unshift($list, [
            'id'        => $id,
            'tipe'      => $tipe,
            'judul'     => $judul,
            'pesan'     => $pesan,
            'waktu'     => date('H:i'),
            'dibaca'    => false,
            'timestamp' => time(),
        ]);
        return array_slice($list, 0, 30);
    });
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

function admin_get_user(): array
{
    init_admin_data_if_needed();
    return json_read_admin('admin.json', cfg('default_admin') ?? []);
}

function admin_update_profile(string $nama, string $email): bool
{
    init_admin_data_if_needed();
    json_update_admin('admin.json', function (array $u) use ($nama, $email) {
        $u['nama'] = trim($nama);
        $u['email'] = trim($email);
        return $u;
    });
    return true;
}

function admin_update_password(string $oldPass, string $newPass): array
{
    init_admin_data_if_needed();
    $u = admin_get_user();
    if (!password_verify($oldPass, (string) ($u['password_hash'] ?? ''))) {
        return ['success' => false, 'error' => 'Password lama salah.'];
    }
    if (strlen($newPass) < 6) {
        return ['success' => false, 'error' => 'Password baru minimal 6 karakter.'];
    }
    
    json_update_admin('admin.json', function (array $user) use ($newPass) {
        $user['password_hash'] = password_hash($newPass, PASSWORD_BCRYPT);
        return $user;
    });
    return ['success' => true];
}
