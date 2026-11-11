<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$stats = admin_get_dashboard_stats();
$products = admin_get_products();
$settings = admin_get_settings();

admin_page_start('Dashboard', 'dashboard');
?>

<!-- Header Info Desktop -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">Dashboard Admin</h1>
            <p class="page-subtitle-lg">Selamat datang, Admin. Berikut ringkasan aktivitas kantin kejujuran hari ini.</p>
        </div>
    </div>
</div>

<!-- Mobile Sapaan Card -->
<div class="card" style="margin-bottom: 16px; border: none; background: linear-gradient(135deg, var(--blue) 0%, var(--blue-dark) 100%); color: #fff; display: block;" class="mobile-only-card">
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

<!-- 4 Kartu KPI Statistik -->
<div class="kpi-grid">
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon-wrap">
            <?= icon('transaksi', 22) ?>
        </div>
        <div class="kpi-label">Total Penjualan</div>
        <div class="kpi-value"><?= rupiah($stats['total_penjualan_hari_ini']) ?></div>
        <div class="kpi-change up">
            <?= icon('trending-up', 14) ?>
            <span><?= $stats['pct_penjualan'] ?>%</span>
            <span class="kpi-change-sub">dari kemarin</span>
        </div>
    </div>

    <div class="kpi-card kpi-green">
        <div class="kpi-icon-wrap">
            <?= icon('check', 22) ?>
        </div>
        <div class="kpi-label">Jumlah Transaksi</div>
        <div class="kpi-value"><?= $stats['jumlah_trx_hari_ini'] ?></div>
        <div class="kpi-change up">
            <?= icon('trending-up', 14) ?>
            <span><?= $stats['pct_trx'] ?>%</span>
            <span class="kpi-change-sub">dari kemarin</span>
        </div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-icon-wrap">
            <?= icon('cart', 22) ?>
        </div>
        <div class="kpi-label">Barang Terjual</div>
        <div class="kpi-value"><?= $stats['barang_terjual_hari_ini'] ?></div>
        <div class="kpi-change up">
            <?= icon('trending-up', 14) ?>
            <span>15%</span>
            <span class="kpi-change-sub">dari kemarin</span>
        </div>
    </div>

    <div class="kpi-card kpi-purple">
        <div class="kpi-icon-wrap">
            <?= icon('alert-circle', 22) ?>
        </div>
        <div class="kpi-label">Stok Menipis</div>
        <div class="kpi-value"><?= $stats['stok_menipis_count'] ?></div>
        <div class="fs-12 text-slate fw-500">
            <span>Perlu restock segera</span>
        </div>
    </div>
</div>

<!-- Main Grid: Grafik & Transaksi (Kiri) + Terlaris & Stok (Kanan) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">

    <!-- Kolom Kiri -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Grafik Penjualan 7 Hari -->
        <div class="card">
            <div class="d-flex justify-between align-center" style="margin-bottom: 16px;">
                <div>
                    <h3 class="card-title">Tren Penjualan 7 Hari</h3>
                    <p class="card-subtitle">Pendapatan harian seminggu terakhir</p>
                </div>
                <div class="badge badge-blue">7 Hari Terakhir</div>
            </div>

            <!-- SVG Bar Chart Dinamis -->
            <div style="height: 200px; display: flex; align-items: flex-end; gap: 12px; padding: 10px 0 0; border-bottom: 1px solid var(--line);">
                <?php
                $maxVal = max([1, ...array_column($stats['chart_7_hari'], 'total')]);
                foreach ($stats['chart_7_hari'] as $c):
                    $pct = max(10, (int) round(($c['total'] / $maxVal) * 100));
                ?>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; justify-content: flex-end;">
                        <div class="fs-11 fw-600 text-muted"><?= round($c['total'] / 1000) ?>k</div>
                        <div style="width: 100%; max-width: 38px; height: <?= $pct ?>%; background: var(--blue); border-radius: 6px 6px 0 0; transition: height 0.4s; position: relative;" title="<?= e($c['date']) ?>: <?= rupiah($c['total']) ?>"></div>
                        <div class="fs-12 fw-600 text-slate" style="margin-top: 4px;"><?= e($c['label']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="card">
            <div class="d-flex justify-between align-center" style="margin-bottom: 16px;">
                <div>
                    <h3 class="card-title">Transaksi Terbaru</h3>
                    <p class="card-subtitle">5 pembayaran QRIS terakhir</p>
                </div>
                <a href="transaksi.php" class="fs-13 fw-600 text-blue">Lihat Semua &rarr;</a>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. TRX</th>
                            <th>Rincian Barang</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stats['transaksi_terbaru'])): ?>
                            <tr>
                                <td colspan="4" class="text-muted fs-13" style="text-align: center; padding: 20px;">
                                    Belum ada transaksi terbaru.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stats['transaksi_terbaru'] as $trx): 
                                $status = $trx['status'];
                                $badgeCls = ($status === 'paid') ? 'badge-success' : (($status === 'pending') ? 'badge-warning' : 'badge-danger');
                                $statusTxt = ($status === 'paid') ? 'Lunas' : (($status === 'pending') ? 'Menunggu' : 'Batal');
                                $itemsSummary = implode(', ', array_map(fn($it) => $it['nama'] . ' x' . $it['qty'], $trx['items']));
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-700 fs-13"><?= e($trx['trx']) ?></div>
                                        <div class="fs-11 text-muted"><?= date('H:i', (int) ($trx['dibuat'] ?? time())) ?></div>
                                    </td>
                                    <td>
                                        <div class="fs-13 text-slate" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($itemsSummary) ?>">
                                            <?= e($itemsSummary) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-700 fs-14 text-blue"><?= rupiah((int) ($trx['total'] ?? 0)) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeCls ?>">
                                            <span class="badge-dot"></span>
                                            <?= $statusTxt ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Kolom Kanan -->
    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- Barang Terlaris -->
        <div class="card">
            <div class="d-flex justify-between align-center" style="margin-bottom: 16px;">
                <div>
                    <h3 class="card-title">Barang Terlaris</h3>
                    <p class="card-subtitle">Berdasarkan volume penjualan</p>
                </div>
                <a href="penjualan.php" class="fs-13 fw-600 text-blue">Detail &rarr;</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php if (empty($stats['barang_terlaris'])): ?>
                    <div class="fs-13 text-muted" style="padding: 16px 0; text-align: center;">
                        Belum ada penjualan dalam 7 hari terakhir.
                    </div>
                <?php else: ?>
                    <?php 
                    $maxQty = max([1, ...array_column($stats['barang_terlaris'], 'qty')]);
                    foreach ($stats['barang_terlaris'] as $rank => $prod): 
                        $pData = $products[$prod['id']] ?? null;
                        $gbr = $pData['gambar'] ?? 'mie-instan.png';
                        $pct = (int) round(($prod['qty'] / $maxQty) * 100);
                    ?>
                        <div>
                            <div class="d-flex justify-between align-center" style="margin-bottom: 6px;">
                                <div class="d-flex align-center gap-10">
                                    <div class="thumb-box" style="width: 36px; height: 36px;">
                                        <img src="assets/img/<?= e($gbr) ?>" alt="<?= e($prod['nama']) ?>">
                                    </div>
                                    <div>
                                        <div class="fs-14 fw-600"><?= e($prod['nama']) ?></div>
                                        <div class="fs-12 text-muted"><?= $prod['qty'] ?> terjual</div>
                                    </div>
                                </div>
                                <div class="fw-700 fs-13 text-navy"><?= rupiah($prod['pendapatan']) ?></div>
                            </div>
                            <div style="width: 100%; height: 6px; background: var(--tint); border-radius: 999px; overflow: hidden;">
                                <div style="width: <?= $pct ?>%; height: 100%; background: var(--blue); border-radius: 999px;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stok Barang -->
        <div class="card">
            <div class="d-flex justify-between align-center" style="margin-bottom: 16px;">
                <div>
                    <h3 class="card-title">Stok Barang</h3>
                    <p class="card-subtitle">Persediaan etalase kantin</p>
                </div>
                <a href="stok.php" class="fs-13 fw-600 text-blue">Lihat Semua &rarr;</a>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($products, 0, 5) as $p): 
                            $stok = (int) $p['stok'];
                            $batas = (int) ($p['batas'] ?? 5);
                            $isMenipis = ($stok <= $batas && $stok > 0);
                            $isHabis = ($stok <= 0);
                            $badge = $isHabis ? 'badge-danger' : ($isMenipis ? 'badge-warning' : 'badge-success');
                            $statusTxt = $isHabis ? 'Habis' : ($isMenipis ? 'Menipis' : 'Aman');
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-center gap-10">
                                        <div class="thumb-box" style="width: 32px; height: 32px;">
                                            <img src="assets/img/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>">
                                        </div>
                                        <span class="fs-13 fw-600"><?= e($p['nama']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="fs-14 fw-700 <?= $isMenipis ? 'text-amber' : ($isHabis ? 'text-red' : 'text-navy') ?>">
                                        <?= $stok ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $badge ?>">
                                        <?= $statusTxt ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<?php
admin_page_end('dashboard');
