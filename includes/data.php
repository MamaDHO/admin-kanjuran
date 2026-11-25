<?php
declare(strict_types=1);

/**
 * Lapisan pembacaan Mock Data JSON (Read-Only UI Seed).
 * Tidak ada penulisan database atau backend API pada tahap ini.
 */
function mock_read_json(string $filename, $fallback = [])
{
    $path = __DIR__ . '/../data/' . $filename;
    if (!is_file($path)) {
        return $fallback;
    }
    $raw = file_get_contents($path);
    if ($raw === false || $raw === '') {
        return $fallback;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : $fallback;
}

function mock_seed_bundle(bool $emptyMode = false): array
{
    $pengaturan = mock_read_json('pengaturan.json', [
        'nama_merchant' => 'Kantin Kejujuran',
        'nmid' => 'ID1023456789012',
        'batas_stok_menipis' => 5,
        'notifikasi_aktif' => true,
        'url_pwa' => 'kantinkejujuran.app/mulai',
        'tanggal_demo' => DEMO_TODAY,
    ]);
    $kategori = mock_read_json('kategori.json', []);
    $barang = $emptyMode ? [] : mock_read_json('barang.json', []);
    $penitip = $emptyMode ? [] : mock_read_json('penitip.json', []);
    $pesanan = $emptyMode ? [] : mock_read_json('pesanan.json', []);
    $setoran = $emptyMode ? [] : mock_read_json('setoran_penitip.json', []);
    $mutasi = $emptyMode ? [] : mock_read_json('mutasi_stok.json', []);
    $notifikasi = $emptyMode ? [] : mock_read_json('notifikasi.json', []);

    return [
        'pengaturan' => $pengaturan,
        'kategori' => $kategori,
        'barang' => $barang,
        'penitip' => $penitip,
        'pesanan' => $pesanan,
        'setoran' => $setoran,
        'mutasi_stok' => $mutasi,
        'notifikasi' => $notifikasi,
    ];
}

function stock_status(int $stok, int $threshold): string
{
    if ($stok <= 0) {
        return 'Habis';
    }
    if ($stok <= $threshold) {
        return 'Menipis';
    }
    return 'Aman';
}

function stock_badge_class(string $status): string
{
    return match ($status) {
        'Habis' => 'badge-danger',
        'Menipis' => 'badge-warning',
        default => 'badge-success',
    };
}

function order_status_label(string $status): string
{
    return match ($status) {
        'lunas' => 'Lunas',
        'batal' => 'Batal',
        default => 'Menunggu',
    };
}

function order_badge_class(string $status): string
{
    return match ($status) {
        'lunas' => 'badge-success',
        'batal' => 'badge-danger',
        default => 'badge-warning',
    };
}

function paginate_slice(array $items, int $page, int $perPage): array
{
    $total = count($items);
    $totalPages = max(1, (int)ceil($total / max(1, $perPage)));
    $currentPage = max(1, min($page, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    $slice = array_slice($items, $offset, $perPage);
    return [
        'items' => $slice,
        'total' => $total,
        'page' => $currentPage,
        'per_page' => $perPage,
        'total_pages' => $totalPages,
        'from' => $total > 0 ? $offset + 1 : 0,
        'to' => min($offset + $perPage, $total),
    ];
}

/**
 * Agregasi metrik Dashboard untuk tanggal demo.
 */
function build_dashboard_summary(array $bundle): array
{
    $today = $bundle['pengaturan']['tanggal_demo'] ?? DEMO_TODAY;
    $threshold = (int)($bundle['pengaturan']['batas_stok_menipis'] ?? 5);
    $orders = $bundle['pesanan'];
    $products = $bundle['barang'];

    $todayPaid = array_values(array_filter(
        $orders,
        fn(array $o): bool => ($o['tanggal'] ?? '') === $today && ($o['status'] ?? '') === 'lunas'
    ));
    $todayAll = array_values(array_filter(
        $orders,
        fn(array $o): bool => ($o['tanggal'] ?? '') === $today
    ));

    $totalPenjualan = array_sum(array_map(fn(array $o): int => (int)($o['total'] ?? 0), $todayPaid));
    $barangTerjual = array_sum(array_map(fn(array $o): int => (int)($o['jumlah_item'] ?? 0), $todayPaid));
    $jumlahTransaksi = count($todayAll); // Selaras mockup: 38 transaksi hari ini (36 Lunas, 1 Menunggu, 1 Batal)

    $activeProducts = array_values(array_filter($products, fn(array $p): bool => (bool)($p['aktif'] ?? true)));
    $stokMenipisCount = 0;
    foreach ($activeProducts as $p) {
        if ((int)$p['stok'] <= $threshold) {
            $stokMenipisCount++;
        }
    }

    // Barang terlaris hari ini
    $soldByProduct = [];
    foreach ($todayPaid as $order) {
        foreach ($order['items'] ?? [] as $it) {
            $bid = $it['barang_id'] ?? '';
            if (!isset($soldByProduct[$bid])) {
                $soldByProduct[$bid] = [
                    'barang_id' => $bid,
                    'nama' => $it['nama'] ?? $bid,
                    'gambar' => $it['gambar'] ?? 'assets/img/air-mineral.png',
                    'qty' => 0,
                ];
            }
            $soldByProduct[$bid]['qty'] += (int)($it['qty'] ?? 0);
        }
    }
    usort($soldByProduct, fn(array $a, array $b): int => $b['qty'] <=> $a['qty']);
    $topItems = array_slice($soldByProduct, 0, 5);

    // 7-day chart series (18 Nov - 24 Nov 2026)
    $dates7 = ['2026-11-18', '2026-11-19', '2026-11-20', '2026-11-21', '2026-11-22', '2026-11-23', '2026-11-24'];
    $series = [];
    foreach ($dates7 as $d) {
        $dayOrders = array_filter($orders, fn(array $o): bool => ($o['tanggal'] ?? '') === $d);
        $dayPaid = array_filter($dayOrders, fn(array $o): bool => ($o['status'] ?? '') === 'lunas');
        $ts = strtotime($d);
        $series[] = [
            'tanggal' => $d,
            'label_desktop' => date('j', $ts) . ' Nov',
            'label_mobile' => date('j', $ts),
            'transaksi' => count($dayOrders),
            'pendapatan' => array_sum(array_map(fn(array $o): int => (int)($o['total'] ?? 0), $dayPaid)),
            'terjual' => array_sum(array_map(fn(array $o): int => (int)($o['jumlah_item'] ?? 0), $dayPaid)),
            'is_today' => $d === $today,
        ];
    }

    return [
        'total_penjualan' => $totalPenjualan,
        'jumlah_transaksi' => $jumlahTransaksi,
        'barang_terjual' => $barangTerjual,
        'stok_menipis' => $stokMenipisCount,
        'top_items' => $topItems,
        'chart_7d' => $series,
        'active_products' => $activeProducts,
    ];
}
