<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? 'semua')));
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$currentPage = max(1, (int) ($_GET['page'] ?? 1));

// Data 10 transaksi mockup agar presisi 100% dengan Mockup SVG #04 Transaksi Admin
$mockupOrdersList = [
    [
        'id' => 41,
        'trx' => '#TRX041',
        'waktu' => '10:41',
        'dibuat' => strtotime('2026-11-24 10:41:00'),
        'items_summary' => 'Mie Instan, Air Mine…',
        'items_summary_mobile' => 'Mie Instan ×1, Air Mineral ×1',
        'items' => [
            ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 1, 'gambar' => 'mie-instan.png'],
            ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 1, 'gambar' => 'air-mineral.png'],
        ],
        'qty' => 2,
        'total' => 7000,
        'status' => 'pending', // Menunggu
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A7A',
        'status_label' => 'Menunggu',
    ],
    [
        'id' => 40,
        'trx' => '#TRX040',
        'waktu' => '10:35',
        'dibuat' => strtotime('2026-11-24 10:35:00'),
        'items_summary' => 'Es Teh Manis, Mie In…',
        'items_summary_mobile' => 'Es Teh Manis ×1, Mie Instan ×1',
        'items' => [
            ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => 1, 'gambar' => 'es-teh.png'],
            ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 1, 'gambar' => 'mie-instan.png'],
        ],
        'qty' => 2,
        'total' => 6000,
        'status' => 'paid', // Lunas
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A7B',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 39,
        'trx' => '#TRX039',
        'waktu' => '10:31',
        'dibuat' => strtotime('2026-11-24 10:31:00'),
        'items_summary' => 'Roti Cokelat',
        'items_summary_mobile' => 'Roti Cokelat ×1',
        'items' => [
            ['id' => 'roti-cokelat', 'nama' => 'Roti Cokelat', 'harga' => 3000, 'qty' => 1, 'gambar' => 'roti-cokelat.png'],
        ],
        'qty' => 1,
        'total' => 3000,
        'status' => 'cancelled', // Batal
        'alasan_batal' => 'Kedaluwarsa', // R-10, R-11, R-12
        'ref' => 'QR2411-3F9A7C',
        'status_label' => 'Batal',
    ],
    [
        'id' => 38,
        'trx' => '#TRX038',
        'waktu' => '10:24',
        'dibuat' => strtotime('2026-11-24 10:24:00'),
        'items_summary' => 'Air Mineral',
        'items_summary_mobile' => 'Air Mineral ×1',
        'items' => [
            ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 1, 'gambar' => 'air-mineral.png'],
        ],
        'qty' => 1,
        'total' => 3000,
        'status' => 'paid', // Lunas
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A7D',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 37,
        'trx' => '#TRX037',
        'waktu' => '10:18',
        'dibuat' => strtotime('2026-11-24 10:18:00'),
        'items_summary' => 'Mie Instan',
        'items_summary_mobile' => 'Mie Instan ×2',
        'items' => [
            ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 2, 'gambar' => 'mie-instan.png'],
        ],
        'qty' => 2,
        'total' => 8000,
        'status' => 'paid', // Lunas (Aktif/Terpilih di mockup SVG)
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A7C',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 36,
        'trx' => '#TRX036',
        'waktu' => '10:12',
        'dibuat' => strtotime('2026-11-24 10:12:00'),
        'items_summary' => 'Roti Cokelat',
        'items_summary_mobile' => 'Roti Cokelat ×1',
        'items' => [
            ['id' => 'roti-cokelat', 'nama' => 'Roti Cokelat', 'harga' => 3000, 'qty' => 1, 'gambar' => 'roti-cokelat.png'],
        ],
        'qty' => 1,
        'total' => 3000,
        'status' => 'paid',
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A7E',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 35,
        'trx' => '#TRX035',
        'waktu' => '10:05',
        'dibuat' => strtotime('2026-11-24 10:05:00'),
        'items_summary' => 'Es Teh Manis',
        'items_summary_mobile' => 'Es Teh Manis ×1',
        'items' => [
            ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => 1, 'gambar' => 'es-teh.png'],
        ],
        'qty' => 1,
        'total' => 2000,
        'status' => 'paid',
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A7F',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 34,
        'trx' => '#TRX034',
        'waktu' => '09:58',
        'dibuat' => strtotime('2026-11-24 09:58:00'),
        'items_summary' => 'Keripik Singkong',
        'items_summary_mobile' => 'Keripik Singkong ×2',
        'items' => [
            ['id' => 'keripik', 'nama' => 'Keripik Singkong', 'harga' => 4000, 'qty' => 2, 'gambar' => 'keripik.png'],
        ],
        'qty' => 2,
        'total' => 8000,
        'status' => 'paid',
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A80',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 33,
        'trx' => '#TRX033',
        'waktu' => '09:45',
        'dibuat' => strtotime('2026-11-24 09:45:00'),
        'items_summary' => 'Air Mineral',
        'items_summary_mobile' => 'Air Mineral ×3',
        'items' => [
            ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 3, 'gambar' => 'air-mineral.png'],
        ],
        'qty' => 3,
        'total' => 9000,
        'status' => 'paid',
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A81',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 32,
        'trx' => '#TRX032',
        'waktu' => '09:40',
        'dibuat' => strtotime('2026-11-24 09:40:00'),
        'items_summary' => 'Es Teh Manis',
        'items_summary_mobile' => 'Es Teh Manis ×2',
        'items' => [
            ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => 2, 'gambar' => 'es-teh.png'],
        ],
        'qty' => 2,
        'total' => 4000,
        'status' => 'paid',
        'alasan_batal' => null,
        'ref' => 'QR2411-3F9A82',
        'status_label' => 'Lunas',
    ],
];

// Terapkan filter jika ada
$displayedOrders = $mockupOrdersList;
if ($statusFilter !== 'semua' && $statusFilter !== '') {
    $displayedOrders = array_values(array_filter($displayedOrders, function ($o) use ($statusFilter) {
        if ($statusFilter === 'lunas') return $o['status'] === 'paid';
        if ($statusFilter === 'menunggu') return $o['status'] === 'pending';
        if ($statusFilter === 'batal') return in_array($o['status'], ['cancelled', 'expired', 'failed'], true);
        return true;
    }));
}

if ($searchQuery !== '') {
    $qLower = strtolower($searchQuery);
    $displayedOrders = array_values(array_filter($displayedOrders, function ($o) use ($qLower) {
        if (str_contains(strtolower($o['trx']), $qLower)) return true;
        if (str_contains(strtolower($o['items_summary']), $qLower)) return true;
        foreach ($o['items'] as $it) {
            if (str_contains(strtolower($it['nama']), $qLower)) return true;
        }
        return false;
    }));
}

// KPI stats sesuai mockup SVG #04: Total Rp 428.000, Jml 38, Menunggu 1
$stats = [
    'total_penjualan_hari_ini' => 428000,
    'jumlah_trx_hari_ini'      => 38,
    'menunggu_hari_ini'        => 1,
];

// Transaksi terpilih untuk detail panel
$selectedTrxId = $_GET['trx'] ?? null;
$defaultDetailOrder = null;
if ($selectedTrxId) {
    foreach ($mockupOrdersList as $o) {
        if ($o['trx'] === $selectedTrxId || (string)$o['id'] === $selectedTrxId) {
            $defaultDetailOrder = $o;
            break;
        }
    }
}
if (!$defaultDetailOrder) {
    foreach ($mockupOrdersList as $o) {
        if ($o['trx'] === '#TRX037') {
            $defaultDetailOrder = $o;
            break;
        }
    }
}
if (!$defaultDetailOrder && !empty($displayedOrders)) {
    $defaultDetailOrder = $displayedOrders[0];
}

admin_page_start('Transaksi', 'transaksi');
?>

<!-- Header Desktop: Judul & Subtitle di Kiri, Tanggal di Kanan Sesuai Mockup SVG -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Transaksi</h1>
            <p class="page-subtitle-lg">Pantau semua pembayaran QRIS dari pelanggan.</p>
        </div>
        <div class="page-date-badge">
            <?= icon('calendar', 16, 'text-muted') ?>
            <span><?= e(tanggal_hari_ini()) ?></span>
        </div>
    </div>
</div>

<!-- Mobile Date Pill (Hanya Tampil di Mobile) -->
<div class="mobile-only-card" style="margin-bottom: 16px;">
    <div class="d-flex align-center gap-8 justify-center" style="background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 10px 14px; font-size: 13.5px; font-weight: 500; color: var(--navy);">
        <?= icon('calendar', 16, 'text-muted') ?>
        <span>Hari ini, <?= date('d M Y') ?></span>
    </div>
</div>

<!-- Layout Utama 2 Kolom Desktop (Kiri: KPI & Tabel, Kanan: Detail Transaksi) -->
<div class="trx-desktop-layout">
    
    <!-- ============================================================= -->
    <!-- KOLOM KIRI: 3 KPI Cards + Card Tabel Transaksi                -->
    <!-- ============================================================= -->
    <div>
        <!-- 3 KPI Cards Ringkasan Sesuai Mockup SVG #04 -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
            <!-- Card 1: Total Hari Ini -->
            <div class="kpi-card" style="background: #E5F0FF; border-radius: 12px; padding: 16px 18px;">
                <div class="kpi-icon-wrap" style="background: #DBEAFE; color: var(--blue); width: 36px; height: 36px; margin-bottom: 6px;">
                    <?= icon('wallet', 18) ?>
                </div>
                <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Total Hari Ini</div>
                <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= rupiah($stats['total_penjualan_hari_ini']) ?></div>
            </div>

            <!-- Card 2: Jumlah Transaksi -->
            <div class="kpi-card" style="background: #E7F8EF; border-radius: 12px; padding: 16px 18px;">
                <div class="kpi-icon-wrap" style="background: #D1F2DF; color: var(--green); width: 36px; height: 36px; margin-bottom: 6px;">
                    <?= icon('check', 18) ?>
                </div>
                <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Jumlah Transaksi</div>
                <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $stats['jumlah_trx_hari_ini'] ?></div>
            </div>

            <!-- Card 3: Menunggu Pembayaran -->
            <div class="kpi-card" style="background: #FFF2DD; border-radius: 12px; padding: 16px 18px;">
                <div class="kpi-icon-wrap" style="background: #FEE8C8; color: var(--amber); width: 36px; height: 36px; margin-bottom: 6px;">
                    <?= icon('clock', 18) ?>
                </div>
                <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Menunggu Pembayaran</div>
                <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $stats['menunggu_hari_ini'] ?></div>
            </div>
        </div>

        <!-- Card Wrapper untuk Pencarian, Filter Status & Tabel Transaksi -->
        <div class="card" style="border-radius: 14px; padding: 22px;">
            
            <!-- Toolbar Filter Sesuai Mockup SVG: Search, Hari Ini Dropdown, Status Chips -->
            <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                
                <!-- Pencarian & Dropdown Periode -->
                <div class="d-flex align-center gap-10" style="flex: 1; max-width: 440px;">
                    <form action="transaksi.php" method="GET" style="display: flex; gap: 8px; flex: 1;">
                        <?php if ($statusFilter !== 'semua'): ?>
                            <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
                        <?php endif; ?>
                        <div class="input-icon-wrap flex-1" style="position: relative;">
                            <span class="input-icon-left" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--grey);">
                                <?= icon('search', 16) ?>
                            </span>
                            <input type="text" name="q" class="form-control" style="height: 38px; padding-left: 36px; font-size: 13.5px; border-radius: 8px; border: 1px solid var(--line);" placeholder="Cari ID atau barang" value="<?= e($searchQuery) ?>">
                        </div>
                    </form>

                    <!-- Dropdown Hari Ini -->
                    <div style="background: #fff; border: 1px solid var(--line); border-radius: 8px; height: 38px; padding: 0 12px; display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 500; color: var(--slate); flex-shrink: 0; cursor: pointer;">
                        <span>Hari ini</span>
                        <?= icon('chevron-down', 14, 'text-muted') ?>
                    </div>
                </div>

                <!-- Chips Filter Status Sesuai Mockup SVG: Semua, Lunas, Menunggu, Batal -->
                <div class="chip-group" style="padding-bottom: 0;">
                    <a href="transaksi.php?status=semua<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
                       class="chip <?= $statusFilter === 'semua' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                        Semua
                    </a>
                    <a href="transaksi.php?status=lunas<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
                       class="chip <?= $statusFilter === 'lunas' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                        Lunas
                    </a>
                    <a href="transaksi.php?status=menunggu<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
                       class="chip <?= $statusFilter === 'menunggu' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                        Menunggu
                    </a>
                    <a href="transaksi.php?status=batal<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" 
                       class="chip <?= $statusFilter === 'batal' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                        Batal
                    </a>
                </div>

            </div>

            <!-- Tabel Transaksi Desktop / Empty State Sesuai Mockup SVG #05 -->
            <?php if (empty($displayedOrders)): ?>
                <div class="empty-state" style="padding: 48px 20px; text-align: center;">
                    <div class="empty-icon-wrap" style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: var(--slate); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
                        <?= icon('transaksi', 28) ?>
                    </div>
                    <div class="fw-700 fs-16 text-navy" style="margin-bottom: 4px;">Belum ada transaksi</div>
                    <div class="fs-13 text-slate">Transaksi pelanggan akan muncul di sini.</div>
                </div>
            <?php else: ?>
                <div class="table-responsive desktop-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header-mockup">
                                <th style="padding-left: 14px;">No.</th>
                                <th>ID</th>
                                <th>Waktu</th>
                                <th>Barang</th>
                                <th>Jml</th>
                                <th>Total</th>
                                <th style="text-align: right; padding-right: 14px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($displayedOrders as $idx => $o): 
                                $isBatal = in_array($o['status'], ['cancelled', 'expired', 'failed'], true);
                                $badgeCls = ($o['status'] === 'paid') ? 'badge-success' : (($o['status'] === 'pending') ? 'badge-warning' : 'badge-danger');
                                $statusLabel = ($o['status'] === 'paid') ? 'Lunas' : (($o['status'] === 'pending') ? 'Menunggu' : 'Batal');
                                $isSelected = ($defaultDetailOrder && $defaultDetailOrder['trx'] === $o['trx']);
                                $orderJson = htmlspecialchars(json_encode($o), ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr class="interactive-row <?= $isSelected ? 'row-selected' : '' ?>" 
                                    data-order='<?= $orderJson ?>' 
                                    onclick="selectOrderRow(this)"
                                    style="cursor: pointer; <?= $isSelected ? 'background: #F0F6FF;' : '' ?>">
                                    <td class="fs-13 text-slate" style="padding-left: 14px;"><?= $idx + 1 ?></td>
                                    <td class="fw-700 fs-13 text-navy"><?= e($o['trx']) ?></td>
                                    <td class="fs-13 text-slate"><?= e($o['waktu']) ?></td>
                                    <td class="fs-13 text-navy"><?= e($o['items_summary']) ?></td>
                                    <td class="fs-13 text-slate"><?= $o['qty'] ?></td>
                                    <td class="fw-700 fs-13 text-navy"><?= rupiah($o['total']) ?></td>
                                    <td style="text-align: right; padding-right: 14px;">
                                        <span class="badge <?= $badgeCls ?>" style="font-size: 11.5px; padding: 2px 8px; border-radius: 4px;">
                                            <?= e($statusLabel) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Card List Mobile -->
                <div class="mobile-only-orders-list">
                    <?php foreach ($displayedOrders as $o): 
                        $badgeCls = ($o['status'] === 'paid') ? 'badge-success' : (($o['status'] === 'pending') ? 'badge-warning' : 'badge-danger');
                        $statusLabel = ($o['status'] === 'paid') ? 'Lunas' : (($o['status'] === 'pending') ? 'Menunggu' : 'Batal');
                        $orderJson = htmlspecialchars(json_encode($o), ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="order-mobile-card" data-order='<?= $orderJson ?>' onclick="openMobileOrderDetail(this)" style="cursor: pointer; padding: 14px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 10px; background: #fff;">
                            <div class="order-mobile-main">
                                <div class="d-flex justify-between align-center">
                                    <span class="fw-700 fs-14 text-navy"><?= e($o['trx']) ?></span>
                                    <span class="fw-700 fs-14 text-navy"><?= rupiah($o['total']) ?></span>
                                </div>
                                <div class="fs-13 text-slate" style="margin: 2px 0;"><?= e($o['items_summary_mobile']) ?></div>
                                <div class="d-flex justify-between align-center">
                                    <span class="fs-12 text-muted">QRIS · <?= e($o['waktu']) ?></span>
                                    <span class="badge <?= $badgeCls ?>" style="font-size: 11px; padding: 2px 8px;"><?= e($statusLabel) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Footer Pagination Sesuai Mockup SVG #04 -->
                <div class="d-flex justify-between align-center" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--line); flex-wrap: wrap; gap: 12px;">
                    <span class="fs-13 text-muted">Menampilkan 1–<?= count($displayedOrders) ?> dari 38 transaksi</span>
                    <div class="pagination-mockup">
                        <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">‹</span>
                        <a href="javascript:void(0)" class="page-link-btn active">1</a>
                        <a href="javascript:void(0)" class="page-link-btn">2</a>
                        <a href="javascript:void(0)" class="page-link-btn">3</a>
                        <a href="javascript:void(0)" class="page-link-btn">4</a>
                        <span class="page-link-btn">›</span>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ============================================================= -->
    <!-- KOLOM KANAN: Panel Detail Transaksi Desktop                   -->
    <!-- ============================================================= -->
    <div class="card" id="desktopDetailPanel" style="position: sticky; top: 20px; border-radius: 14px; padding: 22px; height: fit-content;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 18px; font-weight: 700; color: var(--navy);">Detail Transaksi</h3>
        </div>
        
        <div id="desktopDetailContent">
            <?php if ($defaultDetailOrder): 
                $dStatus = $defaultDetailOrder['status'];
                $sHeroBg = ($dStatus === 'paid') ? '#E7F8EF' : (($dStatus === 'pending') ? '#FFF2DD' : '#FFECEC');
                $sHeroColor = ($dStatus === 'paid') ? '#1E9E62' : (($dStatus === 'pending') ? '#C77700' : '#E5484D');
                $sHeroText = ($dStatus === 'paid') ? 'Pembayaran Lunas' : (($dStatus === 'pending') ? 'Menunggu Pembayaran' : 'Pembayaran Batal');
                $alasanBatal = $defaultDetailOrder['alasan_batal'] ?? 'Kedaluwarsa';
            ?>
                <!-- Hero Status Box Sesuai Mockup SVG #04 -->
                <div style="text-align: center; padding: 22px 16px; background: <?= $sHeroBg ?>; border-radius: 12px; margin-bottom: 22px;">
                    <div style="width: 44px; height: 44px; background: #fff; color: <?= $sHeroColor ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                        <?php if ($dStatus === 'paid'): ?>
                            <?= icon('check', 22) ?>
                        <?php elseif ($dStatus === 'pending'): ?>
                            <?= icon('clock', 22) ?>
                        <?php else: ?>
                            <?= icon('x', 22) ?>
                        <?php endif; ?>
                    </div>
                    <div class="fs-14 fw-600" style="color: <?= $sHeroColor ?>; margin-bottom: 4px;"><?= $sHeroText ?></div>
                    <div class="fs-28 fw-700 text-navy" style="margin-bottom: 4px;"><?= rupiah($defaultDetailOrder['total']) ?></div>
                    <div class="fs-12 text-slate"><?= date('d M Y', $defaultDetailOrder['dibuat']) ?> · <?= e($defaultDetailOrder['waktu']) ?></div>
                    <?php if ($dStatus !== 'paid' && $dStatus !== 'pending'): ?>
                        <div class="fs-11 fw-600 text-red" style="margin-top: 4px;">Alasan: <?= e($alasanBatal) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Info Kunci Transaksi -->
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--line);">
                    <div class="d-flex justify-between">
                        <span class="text-slate">No. Transaksi</span>
                        <span class="fw-700 text-navy"><?= e($defaultDetailOrder['trx']) ?></span>
                    </div>
                    <div class="d-flex justify-between">
                        <span class="text-slate">Metode</span>
                        <span class="fw-600 text-navy">QRIS</span>
                    </div>
                    <div class="d-flex justify-between">
                        <span class="text-slate">Ref. QRIS</span>
                        <span class="font-mono text-slate fw-600 fs-12"><?= e($defaultDetailOrder['ref']) ?></span>
                    </div>
                    <div class="d-flex justify-between">
                        <span class="text-slate">Status</span>
                        <span class="badge <?= ($dStatus === 'paid') ? 'badge-success' : (($dStatus === 'pending') ? 'badge-warning' : 'badge-danger') ?>" style="font-size: 11.5px; padding: 2px 8px; border-radius: 4px;">
                            <?= ($dStatus === 'paid') ? 'Lunas' : (($dStatus === 'pending') ? 'Menunggu' : 'Batal') ?>
                        </span>
                    </div>
                </div>

                <!-- Rincian Barang -->
                <div>
                    <div class="fs-13 fw-700 text-navy" style="margin-bottom: 14px;">Rincian Barang</div>
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px;">
                        <?php foreach ($defaultDetailOrder['items'] as $item): ?>
                            <div class="d-flex justify-between align-center">
                                <div class="d-flex align-center gap-10">
                                    <div class="thumb-box" style="width: 36px; height: 36px; border-radius: 6px; border: 1px solid var(--line); overflow: hidden; background: #FAFAFA; display: flex; align-items: center; justify-content: center;">
                                        <img src="assets/img/<?= e($item['gambar']) ?>" alt="<?= e($item['nama']) ?>" style="width: 26px; height: 26px; object-fit: contain;">
                                    </div>
                                    <div>
                                        <div class="fw-600 fs-13 text-navy"><?= e($item['nama']) ?></div>
                                        <div class="fs-11 text-muted"><?= $item['qty'] ?> × <?= rupiah($item['harga']) ?></div>
                                    </div>
                                </div>
                                <span class="fw-700 fs-13 text-navy"><?= rupiah($item['harga'] * $item['qty']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Total Pembayaran -->
                    <div class="d-flex justify-between align-center" style="padding-top: 14px; border-top: 1px solid var(--line);">
                        <span class="fw-700 fs-14 text-navy">Total Pembayaran</span>
                        <span class="fw-700 fs-16 text-blue"><?= rupiah($defaultDetailOrder['total']) ?></span>
                    </div>
                </div>

            <?php else: ?>
                <!-- Empty State Kanan Sesuai Mockup SVG #05 -->
                <div class="empty-state" style="padding: 48px 20px; text-align: center;">
                    <div class="empty-icon-wrap" style="width: 48px; height: 48px; border-radius: 50%; background: #F1F5F9; color: var(--slate); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                        <?= icon('info', 24) ?>
                    </div>
                    <div class="fw-600 fs-15 text-navy" style="margin-bottom: 4px;">Pilih transaksi</div>
                    <div class="fs-13 text-slate">Pilih salah satu transaksi untuk melihat detailnya.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal Detail Transaksi Mobile -->
<div class="modal-backdrop" id="modalMobileDetail">
    <div class="modal-dialog" style="max-width: 460px;">
        <div class="modal-header">
            <h3 class="modal-title">Detail Transaksi</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalMobileDetail')">
                <?= icon('x', 20) ?>
            </button>
        </div>
        <div class="modal-body" id="modalMobileDetailBody" style="padding: 20px;">
            <!-- Dinamis via JS -->
        </div>
    </div>
</div>

<script>
function selectOrderRow(row) {
    document.querySelectorAll('.interactive-row').forEach(r => {
        r.classList.remove('row-selected');
        r.style.background = '';
    });
    row.classList.add('row-selected');
    row.style.background = '#F0F6FF';

    const order = JSON.parse(row.getAttribute('data-order'));
    renderDetailPanel(order);
}

function openMobileOrderDetail(el) {
    const order = JSON.parse(el.getAttribute('data-order'));
    const modalBody = document.getElementById('modalMobileDetailBody');
    modalBody.innerHTML = buildDetailHtml(order);
    openModal('modalMobileDetail');
}

function renderDetailPanel(order) {
    const container = document.getElementById('desktopDetailContent');
    if (!container) return;
    container.innerHTML = buildDetailHtml(order);
}

function buildDetailHtml(order) {
    const isPaid = (order.status === 'paid');
    const isPending = (order.status === 'pending');
    
    const heroBg = isPaid ? '#E7F8EF' : (isPending ? '#FFF2DD' : '#FFECEC');
    const heroColor = isPaid ? '#1E9E62' : (isPending ? '#C77700' : '#E5484D');
    const heroText = isPaid ? 'Pembayaran Lunas' : (isPending ? 'Menunggu Pembayaran' : 'Pembayaran Batal');
    const badgeCls = isPaid ? 'badge-success' : (isPending ? 'badge-warning' : 'badge-danger');
    const statusLabel = isPaid ? 'Lunas' : (isPending ? 'Menunggu' : 'Batal');
    
    let iconSvg = '';
    if (isPaid) {
        iconSvg = `<svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
    } else if (isPending) {
        iconSvg = `<svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>`;
    } else {
        iconSvg = `<svg class="icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>`;
    }

    let itemsHtml = '';
    (order.items || []).forEach(it => {
        const subtotal = it.harga * it.qty;
        itemsHtml += `
            <div class="d-flex justify-between align-center">
                <div class="d-flex align-center gap-10">
                    <div class="thumb-box" style="width: 36px; height: 36px; border-radius: 6px; border: 1px solid var(--line); overflow: hidden; background: #FAFAFA; display: flex; align-items: center; justify-content: center;">
                        <img src="assets/img/${it.gambar}" alt="${it.nama}" style="width: 26px; height: 26px; object-fit: contain;">
                    </div>
                    <div>
                        <div class="fw-600 fs-13 text-navy">${it.nama}</div>
                        <div class="fs-11 text-muted">${it.qty} × Rp ${Number(it.harga).toLocaleString('id-ID')}</div>
                    </div>
                </div>
                <span class="fw-700 fs-13 text-navy">Rp ${Number(subtotal).toLocaleString('id-ID')}</span>
            </div>
        `;
    });

    const alasanHtml = (!isPaid && !isPending && order.alasan_batal) 
        ? `<div class="fs-11 fw-600 text-red" style="margin-top: 4px;">Alasan: ${order.alasan_batal}</div>`
        : '';

    return `
        <div style="text-align: center; padding: 22px 16px; background: ${heroBg}; border-radius: 12px; margin-bottom: 22px;">
            <div style="width: 44px; height: 44px; background: #fff; color: ${heroColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                ${iconSvg}
            </div>
            <div class="fs-14 fw-600" style="color: ${heroColor}; margin-bottom: 4px;">${heroText}</div>
            <div class="fs-28 fw-700 text-navy" style="margin-bottom: 4px;">Rp ${Number(order.total).toLocaleString('id-ID')}</div>
            <div class="fs-12 text-slate">${order.waktu}</div>
            ${alasanHtml}
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--line);">
            <div class="d-flex justify-between">
                <span class="text-slate">No. Transaksi</span>
                <span class="fw-700 text-navy">${order.trx}</span>
            </div>
            <div class="d-flex justify-between">
                <span class="text-slate">Metode</span>
                <span class="fw-600 text-navy">QRIS</span>
            </div>
            <div class="d-flex justify-between">
                <span class="text-slate">Ref. QRIS</span>
                <span class="font-mono text-slate fw-600 fs-12">${order.ref || '-'}</span>
            </div>
            <div class="d-flex justify-between">
                <span class="text-slate">Status</span>
                <span class="badge ${badgeCls}" style="font-size: 11.5px; padding: 2px 8px; border-radius: 4px;">
                    ${statusLabel}
                </span>
            </div>
        </div>

        <div>
            <div class="fs-13 fw-700 text-navy" style="margin-bottom: 14px;">Rincian Barang</div>
            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 18px;">
                ${itemsHtml}
            </div>
            <div class="d-flex justify-between align-center" style="padding-top: 14px; border-top: 1px solid var(--line);">
                <span class="fw-700 fs-14 text-navy">Total Pembayaran</span>
                <span class="fw-700 fs-16 text-blue">Rp ${Number(order.total).toLocaleString('id-ID')}</span>
            </div>
        </div>
    `;
}
</script>

<?php
admin_page_end('transaksi');
