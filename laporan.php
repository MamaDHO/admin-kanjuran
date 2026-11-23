<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$tab = trim((string) ($_GET['tab'] ?? 'pendapatan'));
if (!in_array($tab, ['pendapatan', 'barang', 'stok'], true)) {
    $tab = 'pendapatan';
}

$rangeLabel = '15 Okt 2026 - 21 Okt 2026';

// Data Laporan sesuai Mockup SVG #08 Laporan Admin
$totalPendapatan = 2363000;
$totalUnitTerjual = 696;
$trxCount = 218;
$rataPerHari = 337571;
$hariTerbaik = 'Rabu, 21 Okt';

// Grafik Pendapatan Mingguan Mockup (Kam - Rab)
$chartWeeklyMock = [
    ['day' => 'Kam', 'label' => 'Kamis, 15 Okt', 'val' => 300000, 'active' => false],
    ['day' => 'Jum', 'label' => 'Jumat, 16 Okt', 'val' => 280000, 'active' => false],
    ['day' => 'Sab', 'label' => 'Sabtu, 17 Okt', 'val' => 310000, 'active' => false],
    ['day' => 'Min', 'label' => 'Minggu, 18 Okt', 'val' => 295000, 'active' => false],
    ['day' => 'Sen', 'label' => 'Senin, 19 Okt', 'val' => 360000, 'active' => false],
    ['day' => 'Sel', 'label' => 'Selasa, 20 Okt', 'val' => 390000, 'active' => false],
    ['day' => 'Rab', 'label' => 'Rabu, 21 Okt', 'val' => 428000, 'active' => true],
];
$maxScale = 500000;

// Data Rincian Laporan per Barang sesuai Mockup SVG
$productReport = [
    [
        'id'         => 'mie-instan',
        'nama'       => 'Mie Instan',
        'kategori'   => 'Makanan',
        'terjual'    => 190,
        'harga'      => 4000,
        'pendapatan' => 760000,
        'gambar'     => 'mie-instan.png',
    ],
    [
        'id'         => 'air-mineral',
        'nama'       => 'Air Mineral',
        'kategori'   => 'Minuman',
        'terjual'    => 251,
        'harga'      => 3000,
        'pendapatan' => 753000,
        'gambar'     => 'air-mineral.png',
    ],
    [
        'id'         => 'keripik',
        'nama'       => 'Keripik Singkong',
        'kategori'   => 'Camilan',
        'terjual'    => 120,
        'harga'      => 4000,
        'pendapatan' => 480000,
        'gambar'     => 'keripik.png',
    ],
    [
        'id'         => 'roti-cokelat',
        'nama'       => 'Roti Cokelat',
        'kategori'   => 'Makanan',
        'terjual'    => 100,
        'harga'      => 3000,
        'pendapatan' => 300000,
        'gambar'     => 'roti-cokelat.png',
    ],
    [
        'id'         => 'es-teh',
        'nama'       => 'Es Teh Manis',
        'kategori'   => 'Minuman',
        'terjual'    => 35,
        'harga'      => 2000,
        'pendapatan' => 70000,
        'gambar'     => 'es-teh.png',
    ],
];

// Fitur Export CSV Fungsional
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Laporan_Kantin_Kejujuran_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Barang', 'Kategori', 'Terjual', 'Harga (Rp)', 'Pendapatan (Rp)']);
    
    $i = 1;
    foreach ($productReport as $pr) {
        fputcsv($output, [$i++, $pr['nama'], $pr['kategori'], $pr['terjual'], $pr['harga'], $pr['pendapatan']]);
    }
    fputcsv($output, []);
    fputcsv($output, ['TOTAL', '', '', $totalUnitTerjual, '', $totalPendapatan]);
    fclose($output);
    exit;
}

admin_page_start('Laporan', 'laporan');
?>

<!-- Header Desktop: Judul di Kiri, Tombol Export di Kanan Sesuai Mockup SVG -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Laporan</h1>
            <p class="page-subtitle-lg">Ringkasan pendapatan dan penjualan per periode.</p>
        </div>
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
            <div class="d-flex gap-10">
                <button type="button" class="btn btn-outline" onclick="window.print()" style="background: #fff; border-color: var(--line); color: var(--slate); border-radius: 8px; height: 42px; padding: 0 16px; font-weight: 600; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
                    <?= icon('printer', 18, 'text-muted') ?>
                    <span>Export PDF</span>
                </button>
                <a href="laporan.php?export=csv" class="btn btn-outline" style="background: #fff; border-color: var(--line); color: var(--slate); border-radius: 8px; height: 42px; padding: 0 16px; font-weight: 600; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
                    <?= icon('download', 18, 'text-muted') ?>
                    <span>Export CSV</span>
                </a>
            </div>
            <span class="fs-11 fw-600 text-blue" style="font-size: 11.5px;">Export tersedia pada pengembangan lanjut</span>
        </div>
    </div>
</div>

<!-- Toolbar: Rentang Waktu di Kiri, Tab Kategori di Kanan Sesuai Mockup SVG -->
<div class="d-flex justify-between align-center" style="margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <!-- Box Rentang Tanggal -->
    <div class="d-flex align-center gap-8" style="background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 10px 16px; font-size: 13.5px; font-weight: 500; color: var(--navy); box-shadow: var(--shadow-sm);">
        <?= icon('calendar', 18, 'text-muted') ?>
        <span><?= e($rangeLabel) ?></span>
    </div>

    <!-- Tabs Kategori: Pendapatan, Barang, Stok -->
    <div class="chip-group" style="padding-bottom: 0;">
        <a href="laporan.php?tab=pendapatan" class="chip <?= $tab === 'pendapatan' ? 'active' : '' ?>" style="height: 38px; padding: 8px 18px; font-size: 13px; border-radius: 8px;">
            Pendapatan
        </a>
        <a href="laporan.php?tab=barang" class="chip <?= $tab === 'barang' ? 'active' : '' ?>" style="height: 38px; padding: 8px 18px; font-size: 13px; border-radius: 8px;">
            Barang
        </a>
        <a href="laporan.php?tab=stok" class="chip <?= $tab === 'stok' ? 'active' : '' ?>" style="height: 38px; padding: 8px 18px; font-size: 13px; border-radius: 8px;">
            Stok
        </a>
    </div>
</div>

<!-- Grid Tengah: Total Pendapatan di Kiri (31%), Grafik Pendapatan di Kanan (69%) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
    
    <!-- Kolom Kiri: Total Pendapatan Ringkasan Card -->
    <div class="card" style="border-radius: 14px; padding: 24px;">
        <div class="fs-14 text-muted" style="margin-bottom: 4px;">Total Pendapatan</div>
        <div class="fs-32 fw-700 text-navy" style="margin-bottom: 6px; letter-spacing: -0.5px;">
            <?= rupiah($totalPendapatan) ?>
        </div>
        <div class="d-flex align-center gap-6" style="color: var(--green); font-size: 13px; font-weight: 600; margin-bottom: 20px;">
            <?= icon('trending-up', 16) ?>
            <span>12% dari periode sebelumnya</span>
        </div>

        <div style="border-top: 1px solid var(--line); padding-top: 18px; display: flex; flex-direction: column; gap: 14px;">
            <div class="d-flex justify-between align-center">
                <span class="fs-13 text-muted">Transaksi</span>
                <span class="fs-14 fw-600 text-navy"><?= $trxCount ?></span>
            </div>
            <div class="d-flex justify-between align-center">
                <span class="fs-13 text-muted">Barang terjual</span>
                <span class="fs-14 fw-600 text-navy"><?= $totalUnitTerjual ?> item</span>
            </div>
            <div class="d-flex justify-between align-center">
                <span class="fs-13 text-muted">Rata-rata / hari</span>
                <span class="fs-14 fw-600 text-navy"><?= rupiah($rataPerHari) ?></span>
            </div>
            <div class="d-flex justify-between align-center">
                <span class="fs-13 text-muted">Hari terbaik</span>
                <span class="fs-14 fw-600 text-navy"><?= e($hariTerbaik) ?></span>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Grafik Pendapatan Sumbu Vertikal Sesuai Mockup SVG -->
    <div class="card" style="border-radius: 14px; padding: 24px; grid-column: span 2;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 8px;">
            <h3 class="card-title" style="margin: 0; font-size: 18px; font-weight: 700; color: var(--navy);">Grafik Pendapatan</h3>
        </div>

        <div class="chart-box" style="height: 230px;">
            <!-- Sumbu Y Vertikal: 500rb s/d 0 -->
            <div class="chart-y-axis">
                <span>500rb</span>
                <span>400rb</span>
                <span>300rb</span>
                <span>200rb</span>
                <span>100rb</span>
                <span>0</span>
            </div>

            <!-- Plot Area & Garis Kisi-kisi -->
            <div class="chart-plot-area">
                <div class="chart-gridlines">
                    <div class="chart-gridline"></div>
                    <div class="chart-gridline"></div>
                    <div class="chart-gridline"></div>
                    <div class="chart-gridline"></div>
                    <div class="chart-gridline"></div>
                    <div class="chart-gridline"></div>
                </div>

                <div class="chart-bars-wrap">
                    <?php foreach ($chartWeeklyMock as $c): 
                        $pctH = max(10, min(100, (int) round(($c['val'] / $maxScale) * 100)));
                    ?>
                        <div class="chart-col">
                            <div class="chart-bar-rect <?= $c['active'] ? 'active' : '' ?>" 
                                 style="height: <?= $pctH ?>%;" 
                                 title="<?= e($c['label']) ?>: <?= rupiah($c['val']) ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Sumbu X: Hari Kam s/d Rab -->
                <div class="chart-x-labels">
                    <?php foreach ($chartWeeklyMock as $c): ?>
                        <div class="chart-col">
                            <span><?= e($c['day']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Tabel Laporan per Barang Sesuai Mockup SVG -->
<div class="card" style="border-radius: 14px; padding: 24px;">
    <div class="d-flex justify-between align-center" style="margin-bottom: 18px;">
        <h3 class="card-title" style="margin: 0; font-size: 18px; font-weight: 700; color: var(--navy);">Laporan per Barang</h3>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr class="table-header-mockup">
                    <th style="padding-left: 18px;">Barang</th>
                    <th>Kategori</th>
                    <th>Terjual</th>
                    <th>Harga</th>
                    <th class="text-right" style="padding-right: 18px;">Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productReport as $pr): ?>
                    <tr>
                        <td style="padding-left: 18px;">
                            <div class="d-flex align-center gap-12">
                                <div class="thumb-box" style="width: 38px; height: 38px; border-radius: 8px; background: #F1F5F9; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <img src="assets/img/<?= e($pr['gambar']) ?>" alt="<?= e($pr['nama']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <span class="fw-600 fs-14 text-navy"><?= e($pr['nama']) ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="fs-13 text-navy"><?= e($pr['kategori']) ?></span>
                        </td>
                        <td>
                            <span class="fs-14 text-navy"><?= $pr['terjual'] ?></span>
                        </td>
                        <td>
                            <span class="fs-14 text-navy"><?= rupiah($pr['harga']) ?></span>
                        </td>
                        <td class="text-right" style="padding-right: 18px;">
                            <span class="fw-700 fs-14 text-navy"><?= rupiah($pr['pendapatan']) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <!-- Baris Total Highlight Sesuai Mockup SVG -->
            <tfoot>
                <tr style="background: #E5F0FF; border-radius: 8px;">
                    <td style="padding: 14px 18px; font-weight: 700; color: var(--navy); border-radius: 8px 0 0 8px;">
                        Total
                    </td>
                    <td></td>
                    <td class="fw-700 text-navy fs-14"><?= $totalUnitTerjual ?></td>
                    <td></td>
                    <td class="text-right fw-700 fs-15 text-blue" style="padding-right: 18px; border-radius: 0 8px 8px 0;">
                        <?= rupiah($totalPendapatan) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Mobile Notice Card (Sesuai SVG Mockup Mobile) -->
<div class="mobile-only-card" style="margin-top: 20px;">
    <div class="card" style="text-align: center; padding: 20px; border-radius: 12px; background: #fff;">
        <div class="fs-14 fw-600 text-navy">Export PDF / CSV · segera hadir</div>
        <div class="fs-12 text-muted" style="margin-top: 4px;">Tersedia pada tahap pengembangan lanjut</div>
    </div>
</div>

<?php
admin_page_end('laporan');
