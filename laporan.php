<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$period = trim((string) ($_GET['periode'] ?? '7hari'));
if (!in_array($period, ['hari_ini', '7hari', '30hari', 'bulan_ini'], true)) {
    $period = '7hari';
}

$rangeLabel = '18 Nov 2026 - 24 Nov 2026';
if ($period === 'hari_ini') {
    $rangeLabel = '24 Nov 2026';
} elseif ($period === '30hari') {
    $rangeLabel = '26 Okt 2026 - 24 Nov 2026';
} elseif ($period === 'bulan_ini') {
    $rangeLabel = '01 Nov 2026 - 24 Nov 2026';
}

// 4 KPI Stats Sesuai Mockup SVG #16 Laporan Admin
$omzetTotal = 2363000;
$pendapatanKantin = 1693000;
$hakPenitip = 670000;
$barangTerjual = 696;

// Grafik Pendapatan Mingguan (Rab - Sel) Sesuai Mockup SVG #16
$chartWeekly = [
    ['day' => 'Rab', 'label' => 'Rabu, 18 Nov', 'val' => 300000, 'active' => false],
    ['day' => 'Kam', 'label' => 'Kamis, 19 Nov', 'val' => 280000, 'active' => false],
    ['day' => 'Jum', 'label' => 'Jumat, 20 Nov', 'val' => 310000, 'active' => false],
    ['day' => 'Sab', 'label' => 'Sabtu, 21 Nov', 'val' => 295000, 'active' => false],
    ['day' => 'Min', 'label' => 'Minggu, 22 Nov', 'val' => 360000, 'active' => false],
    ['day' => 'Sen', 'label' => 'Senin, 23 Nov', 'val' => 390000, 'active' => false],
    ['day' => 'Sel', 'label' => 'Selasa, 24 Nov', 'val' => 428000, 'active' => true],
];
$maxScale = 500000;

// Data Barang Terlaris Sesuai Mockup SVG #16
$bestSellers = [
    ['nama' => 'Air Mineral', 'terjual' => 251, 'percent' => 100],
    ['nama' => 'Mie Instan', 'terjual' => 190, 'percent' => 76],
    ['nama' => 'Keripik Singkong', 'terjual' => 120, 'percent' => 48],
    ['nama' => 'Roti Cokelat', 'terjual' => 100, 'percent' => 40],
    ['nama' => 'Es Teh Manis', 'terjual' => 35, 'percent' => 14],
];

// Data Rincian Laporan per Barang Sesuai Mockup SVG #16
$productReport = [
    [
        'id'         => 'mie-instan',
        'nama'       => 'Mie Instan',
        'terjual'    => 190,
        'harga'      => 4000,
        'pendapatan' => 760000,
        'gambar'     => 'mie-instan.png',
    ],
    [
        'id'         => 'air-mineral',
        'nama'       => 'Air Mineral',
        'terjual'    => 251,
        'harga'      => 3000,
        'pendapatan' => 753000,
        'gambar'     => 'air-mineral.png',
    ],
    [
        'id'         => 'keripik',
        'nama'       => 'Keripik Singkong',
        'terjual'    => 120,
        'harga'      => 4000,
        'pendapatan' => 480000,
        'gambar'     => 'keripik.png',
    ],
    [
        'id'         => 'roti-cokelat',
        'nama'       => 'Roti Cokelat',
        'terjual'    => 100,
        'harga'      => 3000,
        'pendapatan' => 300000,
        'gambar'     => 'roti-cokelat.png',
    ],
    [
        'id'         => 'es-teh',
        'nama'       => 'Es Teh Manis',
        'terjual'    => 35,
        'harga'      => 2000,
        'pendapatan' => 70000,
        'gambar'     => 'es-teh.png',
    ],
];

// Data Rekap Penitip Sesuai Mockup SVG #16
$penitipReport = [
    [
        'id'      => 1,
        'inisial' => 'BS',
        'nama'    => 'Bu Sari',
        'warna'   => '#6366F1',
        'komisi'  => 60000,
        'hak'     => 420000,
    ],
    [
        'id'      => 2,
        'inisial' => 'PJ',
        'nama'    => 'Pak Joko',
        'warna'   => '#0EA5E9',
        'komisi'  => 50000,
        'hak'     => 250000,
    ],
];

admin_page_start('Laporan', 'laporan');
?>

<!-- Header Desktop: Judul di Kiri, Rentang Tanggal di Kanan Sesuai Mockup SVG #16 -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Laporan</h1>
            <p class="page-subtitle-lg">Ringkasan pendapatan, penjualan, dan komisi per periode.</p>
        </div>
        <div class="page-date-badge">
            <?= icon('calendar', 16, 'text-muted') ?>
            <span><?= e($rangeLabel) ?></span>
        </div>
    </div>
</div>

<!-- Toolbar: Filter Periode Sesuai Mockup SVG #16 -->
<div class="d-flex align-center justify-between" style="margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    <div class="chip-group" style="padding-bottom: 0;">
        <a href="laporan.php?periode=hari_ini" class="chip <?= $period === 'hari_ini' ? 'active' : '' ?>" style="height: 36px; padding: 8px 16px; font-size: 13px; border-radius: 8px;">
            Hari ini
        </a>
        <a href="laporan.php?periode=7hari" class="chip <?= $period === '7hari' ? 'active' : '' ?>" style="height: 36px; padding: 8px 16px; font-size: 13px; border-radius: 8px;">
            7 Hari
        </a>
        <a href="laporan.php?periode=30hari" class="chip <?= $period === '30hari' ? 'active' : '' ?>" style="height: 36px; padding: 8px 16px; font-size: 13px; border-radius: 8px;">
            30 Hari
        </a>
        <a href="laporan.php?periode=bulan_ini" class="chip <?= $period === 'bulan_ini' ? 'active' : '' ?>" style="height: 36px; padding: 8px 16px; font-size: 13px; border-radius: 8px;">
            Bulan ini
        </a>
    </div>
</div>

<!-- 4 KPI Cards Sesuai Mockup SVG #16 -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
    <!-- Card 1: Omzet (Total Pendapatan) -->
    <div class="kpi-card" style="background: #E5F0FF; border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #DBEAFE; color: var(--blue); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('wallet', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Omzet (Total Pendapatan)</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;"><?= rupiah($omzetTotal) ?></div>
        <div class="d-flex align-center gap-6" style="font-size: 12px; color: var(--green); font-weight: 600;">
            <?= icon('trending-up', 14) ?>
            <span>12% dari periode lalu</span>
        </div>
    </div>

    <!-- Card 2: Pendapatan Kantin -->
    <div class="kpi-card" style="background: #E7F8EF; border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #D1F2DF; color: var(--green); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('check', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Pendapatan Kantin</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;"><?= rupiah($pendapatanKantin) ?></div>
        <div class="fs-12 text-slate fw-500">Kantin & komisi titipan</div>
    </div>

    <!-- Card 3: Hak Penitip -->
    <div class="kpi-card" style="background: #FFF2DD; border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #FEE8C8; color: var(--amber); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('penitip', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Hak Penitip</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;"><?= rupiah($hakPenitip) ?></div>
        <div class="fs-12 text-slate fw-500">Milik mitra titipan</div>
    </div>

    <!-- Card 4: Barang Terjual -->
    <div class="kpi-card" style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 12px; padding: 18px 20px;">
        <div class="kpi-icon-wrap" style="background: #EFF6FF; color: var(--blue); width: 36px; height: 36px; margin-bottom: 8px;">
            <?= icon('cart', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Barang Terjual</div>
        <div class="kpi-value" style="font-size: 24px; font-weight: 700; color: var(--navy); margin: 2px 0 6px;"><?= $barangTerjual ?> item</div>
        <div class="d-flex align-center gap-6" style="font-size: 12px; color: var(--green); font-weight: 600;">
            <?= icon('trending-up', 14) ?>
            <span>8% dari periode lalu</span>
        </div>
    </div>
</div>

<!-- Grid Baris 1: Grafik Pendapatan (Kiri) & Barang Terlaris (Kanan) Sesuai Mockup SVG #16 -->
<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 20px; margin-bottom: 24px;">
    
    <!-- Kolom Kiri: Grafik Pendapatan -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 20px;">
            <div>
                <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Grafik Pendapatan</h3>
            </div>
        </div>

        <!-- Visualisasi Bar Chart SVG Inline Sesuai Mockup SVG #16 -->
        <div style="display: flex; gap: 14px; height: 220px; align-items: flex-end; padding-bottom: 28px; position: relative;">
            
            <!-- Gridlines Horisontal & Label Y -->
            <div style="position: absolute; left: 0; right: 0; top: 0; bottom: 28px; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none;">
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">500rb</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">400rb</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">300rb</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">200rb</span>
                </div>
                <div style="border-bottom: 1px dashed #E2E8F0; width: 100%; display: flex; justify-content: flex-end;">
                    <span class="fs-11 text-muted" style="margin-top: -8px; background: #fff; padding-left: 6px;">100rb</span>
                </div>
            </div>

            <!-- Batang-Batang Grafik 7 Hari -->
            <div style="display: flex; width: 100%; height: 100%; align-items: flex-end; justify-content: space-around; z-index: 1;">
                <?php foreach ($chartWeekly as $bar): 
                    $heightPercent = (int) round(($bar['val'] / $maxScale) * 100);
                    $barBg = $bar['active'] ? 'var(--blue)' : '#BFDBFE';
                ?>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; flex: 1; max-width: 44px; height: 100%; justify-content: flex-end;" title="<?= e($bar['label']) ?>: <?= rupiah($bar['val']) ?>">
                        <div style="width: 100%; height: <?= $heightPercent ?>%; background: <?= $barBg ?>; border-radius: 6px 6px 0 0; transition: height 0.3s ease;"></div>
                        <span class="fs-12 fw-600 <?= $bar['active'] ? 'text-blue' : 'text-slate' ?>"><?= e($bar['day']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Barang Terlaris Sesuai Mockup SVG #16 -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 18px;">
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Barang Terlaris</h3>
            <span class="badge badge-blue" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">7 hari</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($bestSellers as $bs): ?>
                <div>
                    <div class="d-flex justify-between align-center" style="margin-bottom: 4px;">
                        <span class="fs-13 fw-600 text-navy"><?= e($bs['nama']) ?></span>
                        <span class="fs-13 fw-700 text-navy"><?= $bs['terjual'] ?> item</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #F1F5F9; border-radius: 999px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $bs['percent'] ?>%; background: var(--blue); border-radius: 999px;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<!-- Grid Baris 2: Laporan per Barang (Kiri) & Rekap Penitip (Kanan) Sesuai Mockup SVG #16 -->
<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 20px;">
    
    <!-- Kolom Kiri: Laporan per Barang -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 18px;">
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Laporan per Barang</h3>
        </div>

        <div class="table-responsive desktop-table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="table-header-mockup">
                        <th style="padding-left: 14px;">Barang</th>
                        <th>Terjual</th>
                        <th>Harga</th>
                        <th style="text-align: right; padding-right: 14px;">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productReport as $pr): ?>
                        <tr>
                            <td style="padding-left: 14px;">
                                <div class="d-flex align-center gap-10">
                                    <div class="thumb-box" style="width: 36px; height: 36px; border-radius: 6px; border: 1px solid var(--line); overflow: hidden; background: #FAFAFA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <img src="assets/img/<?= e($pr['gambar']) ?>" alt="<?= e($pr['nama']) ?>" style="width: 26px; height: 26px; object-fit: contain;">
                                    </div>
                                    <span class="fw-600 fs-13 text-navy"><?= e($pr['nama']) ?></span>
                                </div>
                            </td>
                            <td class="fs-13 fw-600 text-navy"><?= $pr['terjual'] ?></td>
                            <td class="fs-13 text-slate"><?= rupiah($pr['harga']) ?></td>
                            <td style="text-align: right; padding-right: 14px;">
                                <span class="fw-700 fs-13 text-blue"><?= rupiah($pr['pendapatan']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer Pagination -->
        <div class="d-flex justify-between align-center" style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--line); flex-wrap: wrap; gap: 10px;">
            <span class="fs-12 text-muted">Menampilkan 1–5 dari 5</span>
            <div class="pagination-mockup">
                <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">‹</span>
                <span class="page-link-btn active">1</span>
                <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">›</span>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Rekap Penitip Sesuai Mockup SVG #16 -->
    <div class="card" style="border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 18px;">
            <h3 class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--navy);">Rekap Penitip</h3>
        </div>

        <div class="table-responsive desktop-table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="table-header-mockup">
                        <th style="padding-left: 14px;">Penitip</th>
                        <th>Komisi</th>
                        <th style="text-align: right; padding-right: 14px;">Hak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($penitipReport as $pt): ?>
                        <tr>
                            <td style="padding-left: 14px;">
                                <div class="d-flex align-center gap-10">
                                    <div class="avatar-circle" style="background: <?= $pt['warna'] ?>; color: #fff; width: 32px; height: 32px; font-size: 11.5px; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <?= e($pt['inisial']) ?>
                                    </div>
                                    <span class="fw-600 fs-13 text-navy"><?= e($pt['nama']) ?></span>
                                </div>
                            </td>
                            <td class="fs-13 text-slate"><?= rupiah($pt['komisi']) ?></td>
                            <td style="text-align: right; padding-right: 14px;">
                                <span class="fw-700 fs-13 text-green"><?= rupiah($pt['hak']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Footnote Penjelasan Komisi & Hak Sesuai Mockup SVG #16 -->
        <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--line); font-size: 11.5px; color: var(--slate); line-height: 1.5;">
            <div>* <strong>Komisi</strong> = bagian kantin dari barang titipan.</div>
            <div>* <strong>Hak</strong> = bagian penitip (harga jual − komisi).</div>
        </div>
    </div>

</div>

<?php
admin_page_end('laporan');
