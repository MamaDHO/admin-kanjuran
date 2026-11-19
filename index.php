<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

/* ------------------------------------------------------------------
 * Helper lokal halaman ini (prefix idx_ agar tidak bentrok dengan includes)
 * ------------------------------------------------------------------ */
const IDX_PER_PAGE = 5;

/** Potong array jadi 1 halaman. Return [items, page, pages, total, from, to]. */
function idx_paginate(array $items, string $param): array
{
    $total = count($items);
    $pages = max(1, (int) ceil($total / IDX_PER_PAGE));
    $page  = min(max(1, (int) ($_GET[$param] ?? 1)), $pages);
    $slice = array_slice(array_values($items), ($page - 1) * IDX_PER_PAGE, IDX_PER_PAGE);
    $from  = $total === 0 ? 0 : (($page - 1) * IDX_PER_PAGE) + 1;
    $to    = $total === 0 ? 0 : $from + count($slice) - 1;
    return [$slice, $page, $pages, $total, $from, $to];
}

/** URL halaman lain dengan mempertahankan query lain (mis. halaman tabel sebelahnya). */
function idx_url(string $param, int $page, string $anchor): string
{
    $q = $_GET;
    $q[$param] = $page;
    return '?' . http_build_query($q) . '#' . $anchor;
}

/** Pagination: selalu dirender (tinggi tetap) walau data kosong / hanya 1 halaman. */
function idx_pager(string $param, string $anchor, int $page, int $pages, int $total, int $from, int $to): string
{
    $info = $total === 0 ? 'Tidak ada data' : "Menampilkan {$from}–{$to} dari {$total}";
    $btn = static function (string $label, ?string $href, bool $active = false, string $aria = ''): string {
        $cls = 'pg-btn' . ($active ? ' is-active' : '') . ($href === null ? ' is-disabled' : '');
        $aria = $aria !== '' ? ' aria-label="' . htmlspecialchars($aria, ENT_QUOTES) . '"' : '';
        if ($href === null) {
            return '<span class="' . $cls . '"' . $aria . ' aria-disabled="true">' . $label . '</span>';
        }
        return '<a class="' . $cls . '" href="' . htmlspecialchars($href, ENT_QUOTES) . '"' . $aria
            . ($active ? ' aria-current="page"' : '') . '>' . $label . '</a>';
    };

    // Jendela maksimal 5 nomor halaman di sekitar halaman aktif
    $start = max(1, min($page - 2, $pages - 4));
    $end   = min($pages, $start + 4);

    $html  = '<div class="dash-pager"><span class="dash-pager-info">' . $info . '</span>';
    $html .= '<nav class="dash-pager-nav" aria-label="Halaman">';
    $html .= $btn('‹', $page > 1 ? idx_url($param, $page - 1, $anchor) : null, false, 'Halaman sebelumnya');
    for ($i = $start; $i <= $end; $i++) {
        $html .= $btn((string) $i, idx_url($param, $i, $anchor), $i === $page, 'Halaman ' . $i);
    }
    $html .= $btn('›', $page < $pages ? idx_url($param, $page + 1, $anchor) : null, false, 'Halaman berikutnya');
    $html .= '</nav></div>';
    return $html;
}

/* ------------------------------------------------------------------
 * Data
 * ------------------------------------------------------------------ */
$stats    = admin_get_dashboard_stats();
$products = admin_get_products();
$settings = admin_get_settings();

// Semua transaksi lunas (terbaru dulu) -> dipaginasi 5 per halaman
$allPaid = admin_get_orders(['status' => 'lunas']);
if (empty($allPaid)) {
    $allPaid = array_filter($stats['transaksi_terbaru'] ?? [], fn($t) => ($t['status'] ?? '') === 'paid');
}
$allPaid = array_values($allPaid);
usort($allPaid, fn($a, $b) => (int) ($b['dibuat'] ?? 0) <=> (int) ($a['dibuat'] ?? 0));
[$recentPaid, $trxPage, $trxPages, $trxTotal, $trxFrom, $trxTo] = idx_paginate($allPaid, 'trx_page');

// Map produk
$productMap = [];
foreach ($products as $p) {
    $productMap[$p['id']] = $p;
}

// Stok barang (semua produk) -> dipaginasi 5 per halaman
[$stokRows, $stokPage, $stokPages, $stokTotal, $stokFrom, $stokTo] = idx_paginate($products, 'stok_page');

// Barang terlaris: top 5 saja (tidak dipaginasi)
$topItems = array_slice($stats['barang_terlaris'] ?? [], 0, 5);

// Grafik: skala sumbu Y dinamis (min 50, kelipatan 10)
$chartData = $stats['chart_7_hari'] ?? [];
$maxVal    = 0;
foreach ($chartData as $d) {
    $maxVal = max($maxVal, (int) ($d['trx_count'] ?? 0));
}
$maxTick = max(50, (int) (ceil($maxVal / 10) * 10));
$tickStep = (int) ($maxTick / 5);

admin_page_start('Dashboard', 'dashboard');
?>

<style>
    /* ===== Dashboard: tinggi list seragam (5 baris) + pagination ===== */
    .dashboard-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.96fr) minmax(0, 1fr);
        gap: 20px;
        align-items: stretch;           /* kartu satu baris selalu sama tinggi */
    }
    .dashboard-main-grid > .card { display: flex; flex-direction: column; min-width: 0; }
    @media (max-width: 1023px) { .dashboard-main-grid { grid-template-columns: minmax(0, 1fr); } }

    /* Wadah list: tinggi TETAP = 5 baris, kosong / sedikit data tidak menggeser layout */
    .dash-rows { --row-h: 44px; height: calc(var(--row-h) * 5); position: relative; flex: none; }
    .dash-rows.is-top { --row-h: 52px; }
    .dash-rows .dash-table-row,
    .dash-rows .top-item-row {
        height: var(--row-h); box-sizing: border-box; display: flex; align-items: center;
        padding-top: 0; padding-bottom: 0; margin: 0;
    }
    .dash-table-scroll { overflow-x: auto; }
    .dash-table-scroll > .dash-table-inner { min-width: 460px; }

    /* Empty state mengisi penuh wadah (tinggi sama dengan 5 baris) */
    .dash-empty {
        height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 6px; color: #7B879C; font-size: 13px; text-align: center;
    }
    .dash-empty strong { color: #21324F; font-size: 14px; font-weight: 600; }

    /* Pagination (tinggi tetap, selalu tampil) */
    .dash-pager {
        margin-top: auto; padding-top: 12px; height: 52px; box-sizing: border-box;
        display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: nowrap;
    }
    .dash-pager-info { font-size: 12px; color: #7B879C; white-space: nowrap; }
    .dash-pager-nav { display: flex; gap: 4px; }
    .pg-btn {
        min-width: 30px; height: 30px; padding: 0 8px; box-sizing: border-box; border-radius: 8px;
        border: 1px solid #E3EAF3; background: #fff; color: #21324F; font-size: 13px; font-weight: 500;
        display: inline-flex; align-items: center; justify-content: center; text-decoration: none; line-height: 1;
    }
    a.pg-btn:hover { border-color: #1677F8; color: #1677F8; }
    .pg-btn.is-active { background: #1677F8; border-color: #1677F8; color: #fff; }
    .pg-btn.is-disabled { color: #B8C2D1; background: #F5F8FC; cursor: not-allowed; }
    @media (max-width: 520px) { .dash-pager { flex-direction: column; height: auto; min-height: 52px; } }
</style>

<!-- Header Info Desktop (Mockup 02 Dashboard admin) -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">Dashboard Admin</h1>
            <p class="page-subtitle-lg">Selamat datang, Admin. Berikut ringkasan aktivitas kantin kejujuran hari ini.</p>
        </div>
        <div class="header-date-pill">
            <?= icon('calendar', 18, '', '#1677F8') ?>
            <span><?= e(tanggal_hari_ini()) ?></span>
        </div>
    </div>
</div>

<!-- Mobile Sapaan Card (hanya < 1024px) -->
<div class="mobile-only-card" style="margin-bottom: 16px; border: none; background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%); color: #fff; border-radius: 14px; padding: 18px 20px;">
    <div class="d-flex justify-between align-center">
        <div>
            <div class="fs-12" style="color: #D6E6FF;"><?= e(tanggal_hari_ini()) ?></div>
            <h2 class="fs-20 fw-700" style="margin-top: 2px;">Halo, Admin</h2>
            <p class="fs-13" style="color: #E2EDFF; margin-top: 4px;">Berikut ringkasan aktivitas kantin hari ini.</p>
        </div>
        <div style="background: rgba(255,255,255,0.18); width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center;">
            <?= icon('store', 24, '', '#fff') ?>
        </div>
    </div>
</div>

<!-- 4 Kartu KPI -->
<div class="kpi-grid">
    <div class="kpi-card kpi-blue">
        <div class="kpi-circle-icon blue"><?= icon('cart', 26, '', '#fff') ?></div>
        <div class="kpi-body">
            <div class="kpi-label">Total Penjualan</div>
            <div class="kpi-value"><?= rupiah($stats['total_penjualan_hari_ini']) ?></div>
            <div class="kpi-change">
                <?= icon('arrow-up', 14, '', '#1E9E62') ?>
                <span><?= $stats['pct_penjualan'] ?>%</span>
                <span class="kpi-change-sub">dari kemarin</span>
            </div>
        </div>
    </div>

    <div class="kpi-card kpi-green">
        <div class="kpi-circle-icon green"><?= icon('receipt', 26, '', '#fff') ?></div>
        <div class="kpi-body">
            <div class="kpi-label">Jumlah Transaksi</div>
            <div class="kpi-value"><?= $stats['jumlah_trx_hari_ini'] ?></div>
            <div class="kpi-change">
                <?= icon('arrow-up', 14, '', '#1E9E62') ?>
                <span><?= $stats['pct_trx'] ?>%</span>
                <span class="kpi-change-sub">dari kemarin</span>
            </div>
        </div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-circle-icon amber"><?= icon('package', 26, '', '#fff') ?></div>
        <div class="kpi-body">
            <div class="kpi-label">Barang Terjual</div>
            <div class="kpi-value"><?= $stats['barang_terjual_hari_ini'] ?></div>
            <div class="kpi-change">
                <?= icon('arrow-up', 14, '', '#1E9E62') ?>
                <!-- TODO: ganti dengan nilai asli dari admin_get_dashboard_stats() bila tersedia -->
                <span><?= $stats['pct_barang'] ?? 15 ?>%</span>
                <span class="kpi-change-sub">dari kemarin</span>
            </div>
        </div>
    </div>

    <div class="kpi-card kpi-purple">
        <div class="kpi-circle-icon purple"><?= icon('layers', 26, '', '#fff') ?></div>
        <div class="kpi-body">
            <div class="kpi-label">Stok Menipis</div>
            <div class="kpi-value"><?= $stats['stok_menipis_count'] ?></div>
            <div class="kpi-alert-text">perlu restock</div>
        </div>
    </div>
</div>

<!-- Grid 2x2: baris 1 = Grafik | Barang Terlaris, baris 2 = Transaksi Terbaru | Stok Barang -->
<div class="dashboard-main-grid">

    <!-- 1. Grafik Penjualan (7 Hari Terakhir) -->
    <div class="card card-dashboard">
        <div class="d-flex justify-between align-center" style="margin-bottom: 20px;">
            <div>
                <span class="card-title-main">Grafik Penjualan</span>
                <span class="card-subtitle-main">(7 Hari Terakhir)</span>
            </div>
            <div class="chart-dropdown-pill">
                <span>Jumlah Transaksi</span>
                <?= icon('chevron-down', 13, '', '#21324F') ?>
            </div>
        </div>

        <div style="width: 100%; overflow-x: auto;">
            <svg viewBox="0 0 620 225" preserveAspectRatio="xMidYMid meet" style="width: 100%; min-width: 480px; height: auto; display: block;" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Grafik jumlah transaksi 7 hari terakhir">
                <?php for ($i = 0; $i <= 5; $i++):
                    $gy = 190 - ($i * 34); ?>
                    <text x="24" y="<?= $gy + 4 ?>" font-family="Inter, system-ui, sans-serif" font-size="12" fill="#51617A" text-anchor="end"><?= $i * $tickStep ?></text>
                    <line x1="32" y1="<?= $gy ?>" x2="615" y2="<?= $gy ?>" stroke="#EAF0F6" stroke-width="1"/>
                <?php endfor; ?>

                <?php
                $barWidth = 44; $startX = 52; $spacing = 80; $baselineY = 190; $chartHeight = 170;
                foreach ($chartData as $idx => $d):
                    $val  = (int) ($d['trx_count'] ?? 0);
                    $barH = $val > 0 ? max(6, (int) round(($val / $maxTick) * $chartHeight)) : 0;
                    $barY = $baselineY - $barH;
                    $bx   = $startX + ($idx * $spacing);
                    $barFill = !empty($d['is_today']) ? '#1677F8' : '#9CC4FC';
                ?>
                    <g>
                        <?php if ($barH > 0): ?>
                        <rect x="<?= $bx ?>" y="<?= $barY ?>" width="<?= $barWidth ?>" height="<?= $barH ?>" rx="5" fill="<?= $barFill ?>">
                            <title><?= e($d['label']) ?>: <?= $val ?> Transaksi (<?= rupiah((int) ($d['total'] ?? 0)) ?>)</title>
                        </rect>
                        <?php endif; ?>
                        <text x="<?= $bx + ($barWidth / 2) ?>" y="212" font-family="Inter, system-ui, sans-serif" font-size="13" fill="#21324F" text-anchor="middle"><?= e($d['label']) ?></text>
                    </g>
                <?php endforeach; ?>
            </svg>
        </div>
    </div>

    <!-- 2. Barang Terlaris (top 5, tinggi tetap 5 baris) -->
    <div class="card card-dashboard">
        <div class="d-flex justify-between align-center" style="margin-bottom: 10px;">
            <div class="d-flex align-center gap-8">
                <span style="color: #F3A827;"><?= icon('star', 20) ?></span>
                <span class="card-title-section">Barang Terlaris</span>
            </div>
            <a href="penjualan.php" class="card-link-more">Lihat Semua</a>
        </div>

        <div class="dash-rows is-top">
            <?php if (empty($topItems)): ?>
                <div class="dash-empty">
                    <strong>Belum ada penjualan</strong>
                    <span>Barang terlaris akan tampil di sini.</span>
                </div>
            <?php else: ?>
                <?php foreach ($topItems as $rIdx => $top):
                    $pInfo  = $productMap[$top['id']] ?? null;
                    $imgSrc = $pInfo['gambar'] ?? 'mie-instan.png';
                ?>
                    <div class="top-item-row">
                        <div class="d-flex align-center" style="flex: 1; min-width: 0;">
                            <span class="top-item-rank"><?= $rIdx + 1 ?>.</span>
                            <img src="assets/img/<?= e($imgSrc) ?>" alt="<?= e($top['nama']) ?>" class="top-item-img" loading="lazy">
                            <div class="top-item-info">
                                <div class="top-item-name"><?= e($top['nama']) ?></div>
                                <div class="top-item-sub">Terjual <?= (int) $top['qty'] ?> item</div>
                            </div>
                        </div>
                        <div class="top-item-qty"><?= (int) $top['qty'] ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Transaksi Terbaru (5 baris/halaman + pagination) -->
    <div class="card card-dashboard" id="trx-card">
        <div class="d-flex justify-between align-center">
            <div class="d-flex align-center gap-8">
                <span style="color: #1677F8;"><?= icon('receipt-long', 20) ?></span>
                <span class="card-title-section">Transaksi Terbaru</span>
            </div>
            <a href="transaksi.php" class="card-link-more">Lihat Semua</a>
        </div>

        <div class="dash-table-scroll">
            <div class="dash-table-inner">
                <div class="table-header-pill d-flex align-center">
                    <div style="width: 40px;">No.</div>
                    <div style="width: 75px;">Waktu</div>
                    <div class="flex-1">Barang</div>
                    <div style="width: 60px; text-align: center;">Jumlah</div>
                    <div style="width: 100px;">Total</div>
                    <div style="width: 80px; text-align: right;">Status</div>
                </div>

                <div class="dash-rows">
                    <?php if (empty($recentPaid)): ?>
                        <div class="dash-empty">
                            <strong>Belum ada transaksi</strong>
                            <span>Transaksi lunas akan tampil di sini.</span>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentPaid as $rIdx => $trx):
                            $num       = (($trxPage - 1) * IDX_PER_PAGE) + $rIdx + 1;
                            $waktu     = date('H:i', (int) ($trx['dibuat'] ?? time()));
                            $items     = $trx['items'] ?? [];
                            $firstItem = $items[0]['nama'] ?? '-';
                            $extra     = max(0, count($items) - 1);
                            $totalQty  = (int) ($trx['jumlah'] ?? 1);
                        ?>
                            <div class="dash-table-row">
                                <div style="width: 40px; color: var(--navy);"><?= $num ?>.</div>
                                <div style="width: 75px; color: var(--navy);"><?= e($waktu) ?></div>
                                <div class="flex-1" style="color: var(--navy); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($firstItem) ?><?php if ($extra > 0): ?> <span style="color:#7B879C;font-weight:400;">+<?= $extra ?></span><?php endif; ?>
                                </div>
                                <div style="width: 60px; text-align: center; color: var(--navy);"><?= $totalQty ?></div>
                                <div style="width: 100px; color: var(--navy); font-weight: 500;"><?= rupiah((int) ($trx['total'] ?? 0)) ?></div>
                                <div style="width: 80px; text-align: right;"><span class="badge-pill-lunas">Lunas</span></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?= idx_pager('trx_page', 'trx-card', $trxPage, $trxPages, $trxTotal, $trxFrom, $trxTo) ?>
    </div>

    <!-- 4. Stok Barang (5 baris/halaman + pagination) -->
    <div class="card card-dashboard" id="stok-card">
        <div class="d-flex justify-between align-center">
            <div class="d-flex align-center gap-8">
                <span style="color: #1677F8;"><?= icon('layers', 20) ?></span>
                <span class="card-title-section">Stok Barang</span>
            </div>
            <a href="stok.php" class="card-link-more">Lihat Semua</a>
        </div>

        <div class="table-header-pill d-flex align-center">
            <div class="flex-1">Barang</div>
            <div style="width: 70px;">Stok</div>
            <div style="width: 80px; text-align: right;">Status</div>
        </div>

        <div class="dash-rows">
            <?php if (empty($stokRows)): ?>
                <div class="dash-empty">
                    <strong>Belum ada barang</strong>
                    <span>Tambahkan barang di menu Barang.</span>
                </div>
            <?php else: ?>
                <?php foreach ($stokRows as $p):
                    $stok   = (int) ($p['stok'] ?? 0);
                    $batas  = (int) ($p['batas'] ?? 5);
                    $isAman = ($stok > $batas);
                ?>
                    <div class="dash-table-row">
                        <div class="flex-1 d-flex align-center" style="min-width: 0;">
                            <img src="assets/img/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>" style="width: 30px; height: 30px; border-radius: 6px; object-fit: contain; margin-right: 12px; flex: none;" loading="lazy">
                            <span style="font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= e($p['nama']) ?></span>
                        </div>
                        <div style="width: 70px; font-weight: 500;"><?= $stok ?></div>
                        <div style="width: 80px; text-align: right;">
                            <span class="<?= $isAman ? 'badge-pill-aman' : 'badge-pill-menipis' ?>"><?= $isAman ? 'Aman' : 'Menipis' ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?= idx_pager('stok_page', 'stok-card', $stokPage, $stokPages, $stokTotal, $stokFrom, $stokTo) ?>
    </div>

</div>

<?php
admin_page_end('dashboard');
