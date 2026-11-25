<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$stats = admin_get_dashboard_stats();
$products = admin_get_products();
$settings = admin_get_settings();

// Urutan stok produk sesuai Mockup SVG #02
$stokMockupOrder = ['keripik', 'roti-cokelat', 'es-teh', 'mie-instan', 'air-mineral'];
$orderedStock = [];
foreach ($stokMockupOrder as $k) {
    if (isset($products[$k])) {
        $orderedStock[$k] = $products[$k];
    }
}
foreach ($products as $k => $v) {
    if (!isset($orderedStock[$k])) {
        $orderedStock[$k] = $v;
    }
}

// 5 Transaksi terbaru sesuai Mockup SVG #02
$recentTransactions = [
    [
        'trx'    => '#TRX041',
        'waktu'  => '10:41',
        'items'  => 'Mie Instan, Air Mineral',
        'total'  => 7000,
        'status' => 'pending',
    ],
    [
        'trx'    => '#TRX040',
        'waktu'  => '10:35',
        'items'  => 'Es Teh Manis, Mie Instan',
        'total'  => 6000,
        'status' => 'paid',
    ],
    [
        'trx'    => '#TRX039',
        'waktu'  => '10:31',
        'items'  => 'Roti Cokelat',
        'total'  => 3000,
        'status' => 'cancelled',
    ],
    [
        'trx'    => '#TRX038',
        'waktu'  => '10:24',
        'items'  => 'Air Mineral',
        'total'  => 3000,
        'status' => 'paid',
    ],
    [
        'trx'    => '#TRX037',
        'waktu'  => '10:18',
        'items'  => 'Mie Instan',
        'total'  => 8000,
        'status' => 'paid',
    ],
];

// Data grafik 7 hari sesuai Mockup SVG #02 (18 Nov - 24 Nov)
$chartWeeklyTrx = [
    ['day' => '18 Nov', 'label' => 'Rabu, 18 Nov', 'val' => 22, 'active' => false],
    ['day' => '19 Nov', 'label' => 'Kamis, 19 Nov', 'val' => 26, 'active' => false],
    ['day' => '20 Nov', 'label' => 'Jumat, 20 Nov', 'val' => 28, 'active' => false],
    ['day' => '21 Nov', 'label' => 'Sabtu, 21 Nov', 'val' => 31, 'active' => false],
    ['day' => '22 Nov', 'label' => 'Minggu, 22 Nov', 'val' => 25, 'active' => false],
    ['day' => '23 Nov', 'label' => 'Senin, 23 Nov', 'val' => 34, 'active' => false],
    ['day' => '24 Nov', 'label' => 'Selasa, 24 Nov', 'val' => 38, 'active' => true],
];
$maxTrxScale = 50;

// Barang terlaris hari ini sesuai Mockup SVG #02
$bestSellersToday = [
    ['nama' => 'Air Mineral', 'qty' => 40, 'gambar' => 'air-mineral.png', 'percent' => 100],
    ['nama' => 'Mie Instan', 'qty' => 35, 'gambar' => 'mie-instan.png', 'percent' => 88],
    ['nama' => 'Roti Cokelat', 'qty' => 25, 'gambar' => 'roti-cokelat.png', 'percent' => 63],
    ['nama' => 'Keripik Singkong', 'qty' => 18, 'gambar' => 'keripik.png', 'percent' => 45],
    ['nama' => 'Es Teh Manis', 'qty' => 8, 'gambar' => 'es-teh.png', 'percent' => 20],
];

admin_page_start('Dashboard', 'dashboard');
?>

<!-- Header Info Desktop Sesuai Mockup SVG #02 -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Dashboard Admin</h1>
            <p class="page-subtitle-lg">Selamat datang, Admin. Berikut ringkasan aktivitas kantin kejujuran hari ini.</p>
        </div>
        <div class="page-date-badge">
            <?= icon('calendar', 16, 'text-muted') ?>
            <span><?= e(tanggal_hari_ini()) ?></span>
        </div>
    </div>
</div>

<!-- 4 Kartu KPI Statistik Sesuai Mockup SVG #02 -->
<div class="kpi-grid" style="margin-bottom: 24px;">
    <!-- Card 1: Total Penjualan -->
    <div class="kpi-card" style="background: #E5F0FF; border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #DBEAFE; color: var(--blue); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('transaksi', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Total Penjualan</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;">Rp 428.000</div>
        <div class="d-flex align-center gap-6" style="font-size: 12px; color: var(--green); font-weight: 600;">
            <?= icon('trending-up', 14) ?>
            <span>12% dari kemarin</span>
        </div>
    </div>

    <!-- Card 2: Jumlah Transaksi -->
    <div class="kpi-card" style="background: #E7F8EF; border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #D1F2DF; color: var(--green); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('check', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Jumlah Transaksi</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;">38</div>
        <div class="d-flex align-center gap-6" style="font-size: 12px; color: var(--green); font-weight: 600;">
            <?= icon('trending-up', 14) ?>
            <span>9% dari kemarin</span>
        </div>
    </div>

    <!-- Card 3: Barang Terjual -->
    <div class="kpi-card" style="background: #FFF2DD; border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #FEE8C8; color: var(--amber); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('cart', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Barang Terjual</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;">126</div>
        <div class="d-flex align-center gap-6" style="font-size: 12px; color: var(--green); font-weight: 600;">
            <?= icon('trending-up', 14) ?>
            <span>15% dari kemarin</span>
        </div>
    </div>

    <!-- Card 4: Stok Menipis -->
    <div class="kpi-card" style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #EFF6FF; color: var(--blue); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('alert-circle', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Stok Menipis</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;">3</div>
        <div class="fs-12 text-slate fw-500">perlu restock</div>
    </div>
</div>

<!-- Main Grid Baris 1: Grafik Penjualan 7 Hari (Kiri) & Barang Terlaris (Kanan) -->
<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 20px; margin-bottom: 24px;">
    
    <!-- Kolom Kiri: Grafik Penjualan -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 20px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Grafik Penjualan</h3>
                <span class="fs-12 text-muted">(7 Hari Terakhir)</span>
            </div>
            <span class="badge badge-blue" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">Jumlah Transaksi</span>
        </div>

        <div style="display: flex; gap: 14px; height: 210px; align-items: flex-end; padding-bottom: 28px; position: relative;">
            <!-- Gridlines Horisontal & Label Y -->
            <div style="position: absolute; left: 0; right: 0; top: 0; bottom: 28px; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none;">
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">50</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">40</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">30</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">20</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">10</span>
                </div>
            </div>

            <!-- Batang-Batang Grafik 7 Hari -->
            <div style="display: flex; width: 100%; height: 100%; align-items: flex-end; justify-content: space-around; z-index: 1;">
                <?php foreach ($chartWeeklyTrx as $bar): 
                    $heightPercent = (int) round(($bar['val'] / $maxTrxScale) * 100);
                    $barBg = $bar['active'] ? 'var(--blue)' : '#BFDBFE';
                ?>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; max-width: 44px; height: 100%; justify-content: flex-end;" title="<?= e($bar['label']) ?>: <?= $bar['val'] ?> transaksi">
                        <div style="width: 100%; height: <?= $heightPercent ?>%; background: <?= $barBg ?>; border-radius: 6px 6px 0 0; transition: height 0.3s ease;"></div>
                        <span class="fs-11 fw-600 <?= $bar['active'] ? 'text-blue' : 'text-slate' ?>"><?= e($bar['day']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Barang Terlaris Hari Ini -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 18px;">
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Barang Terlaris</h3>
            <span class="badge badge-blue" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">Hari ini</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($bestSellersToday as $idx => $bs): ?>
                <div class="d-flex align-center justify-between">
                    <div class="d-flex align-center gap-10">
                        <div class="thumb-box" style="width: 34px; height: 34px; border-radius: 6px; border: 1px solid var(--line); overflow: hidden; background: #FAFAFA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <img src="assets/img/<?= e($bs['gambar']) ?>" alt="<?= e($bs['nama']) ?>" style="width: 24px; height: 24px; object-fit: contain;">
                        </div>
                        <div>
                            <div class="fs-13 fw-600 text-navy"><?= $idx + 1 ?>. <?= e($bs['nama']) ?></div>
                            <div class="fs-11 text-muted">Terjual <?= $bs['qty'] ?> item</div>
                        </div>
                    </div>
                    <div style="width: 80px; height: 6px; background: #F1F5F9; border-radius: 999px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $bs['percent'] ?>%; background: var(--blue); border-radius: 999px;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Main Grid Baris 2: Transaksi Terbaru (Kiri) & Status Stok (Kanan) -->
<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 20px;">
    
    <!-- Kolom Kiri: Transaksi Terbaru -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 18px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Transaksi Terbaru</h3>
            </div>
            <a href="transaksi.php" class="fs-13 fw-600 text-blue">Lihat Semua &rarr;</a>
        </div>

        <div class="table-responsive desktop-table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="table-header-mockup">
                        <th style="padding-left: 14px;">No. TRX</th>
                        <th>Barang</th>
                        <th>Total</th>
                        <th style="text-align: right; padding-right: 14px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentTransactions as $rx): 
                        $status = $rx['status'];
                        $badgeCls = ($status === 'paid') ? 'badge-success' : (($status === 'pending') ? 'badge-warning' : 'badge-danger');
                        $statusTxt = ($status === 'paid') ? 'Lunas' : (($status === 'pending') ? 'Menunggu' : 'Batal');
                    ?>
                        <tr>
                            <td style="padding-left: 14px;">
                                <div class="fw-700 fs-13 text-navy"><?= e($rx['trx']) ?></div>
                                <div class="fs-11 text-muted"><?= e($rx['waktu']) ?></div>
                            </td>
                            <td class="fs-13 text-slate"><?= e($rx['items']) ?></td>
                            <td class="fs-13 fw-700 text-navy"><?= rupiah($rx['total']) ?></td>
                            <td style="text-align: right; padding-right: 14px;">
                                <span class="badge <?= $badgeCls ?>" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                    <?= e($statusTxt) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kolom Kanan: Status Stok -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 18px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Status Stok</h3>
            </div>
            <a href="stok.php" class="fs-13 fw-600 text-blue">Lihat Semua &rarr;</a>
        </div>

        <div class="table-responsive desktop-table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="table-header-mockup">
                        <th style="padding-left: 14px;">Barang</th>
                        <th>Stok</th>
                        <th style="text-align: right; padding-right: 14px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($orderedStock, 0, 5) as $sp): 
                        $stk = (int) $sp['stok'];
                        $bts = (int) ($sp['batas'] ?? 5);
                        $isMenipis = ($stk <= $bts && $stk > 0);
                        $isHabis = ($stk <= 0);
                        $badge = $isHabis ? 'badge-danger' : ($isMenipis ? 'badge-warning' : 'badge-success');
                        $statusTxt = $isHabis ? 'Habis' : ($isMenipis ? 'Menipis' : 'Aman');
                    ?>
                        <tr>
                            <td style="padding-left: 14px;">
                                <div class="d-flex align-center gap-10">
                                    <div class="thumb-box" style="width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--line); overflow: hidden; background: #FAFAFA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <img src="assets/img/<?= e($sp['gambar']) ?>" alt="<?= e($sp['nama']) ?>" style="width: 22px; height: 22px; object-fit: contain;">
                                    </div>
                                    <span class="fs-13 fw-600 text-navy"><?= e($sp['nama']) ?></span>
                                </div>
                            </td>
                            <td class="fs-13 fw-700 <?= $isMenipis ? 'text-amber' : ($isHabis ? 'text-red' : 'text-navy') ?>">
                                <?= $stk ?> pcs
                            </td>
                            <td style="text-align: right; padding-right: 14px;">
                                <span class="badge <?= $badge ?>" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                    <?= e($statusTxt) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
admin_page_end('dashboard');
