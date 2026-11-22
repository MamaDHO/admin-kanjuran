<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? 'semua')));
$searchQuery = trim((string) ($_GET['q'] ?? ''));
$currentPage = max(1, (int) ($_GET['page'] ?? 1));

// Data 9 transaksi mockup agar presisi 100% dengan Mockup SVG #04 Transaksi Admin
$mockupOrdersList = [
    [
        'id' => 39,
        'trx' => '#TRX039',
        'waktu' => '10:31',
        'dibuat' => strtotime('today 10:31:00'),
        'items_summary' => 'Mie Instan, Air Miner…',
        'items_summary_mobile' => 'Mie Instan ×1, Air Minera…',
        'items' => [
            ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 1, 'gambar' => 'mie-instan.png'],
            ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 1, 'gambar' => 'air-mineral.png'],
        ],
        'qty' => 2,
        'total' => 7000,
        'status' => 'pending', // Menunggu
        'ref' => 'QR2110-3F9A7B',
        'status_label' => 'Menunggu',
    ],
    [
        'id' => 38,
        'trx' => '#TRX038',
        'waktu' => '10:24',
        'dibuat' => strtotime('today 10:24:00'),
        'items_summary' => 'Air Mineral',
        'items_summary_mobile' => 'Air Mineral ×1',
        'items' => [
            ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 1, 'gambar' => 'air-mineral.png'],
        ],
        'qty' => 1,
        'total' => 3000,
        'status' => 'paid', // Lunas
        'ref' => 'QR2110-3F9A7A',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 37,
        'trx' => '#TRX037',
        'waktu' => '10:18',
        'dibuat' => strtotime('today 10:18:00'),
        'items_summary' => 'Mie Instan',
        'items_summary_mobile' => 'Mie Instan ×2',
        'items' => [
            ['id' => 'mie-instan', 'nama' => 'Mie Instan', 'harga' => 4000, 'qty' => 2, 'gambar' => 'mie-instan.png'],
        ],
        'qty' => 2,
        'total' => 8000,
        'status' => 'paid', // Lunas (Aktif/Terpilih di mockup)
        'ref' => 'QR2110-3F9A7C',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 36,
        'trx' => '#TRX036',
        'waktu' => '10:12',
        'dibuat' => strtotime('today 10:12:00'),
        'items_summary' => 'Roti Cokelat',
        'items_summary_mobile' => 'Roti Cokelat ×1',
        'items' => [
            ['id' => 'roti-cokelat', 'nama' => 'Roti Cokelat', 'harga' => 3000, 'qty' => 1, 'gambar' => 'roti-cokelat.png'],
        ],
        'qty' => 1,
        'total' => 3000,
        'status' => 'paid',
        'ref' => 'QR2110-3F9A7D',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 35,
        'trx' => '#TRX035',
        'waktu' => '10:05',
        'dibuat' => strtotime('today 10:05:00'),
        'items_summary' => 'Es Teh Manis',
        'items_summary_mobile' => 'Es Teh Manis ×1',
        'items' => [
            ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => 1, 'gambar' => 'es-teh.png'],
        ],
        'qty' => 1,
        'total' => 2000,
        'status' => 'paid',
        'ref' => 'QR2110-3F9A7E',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 34,
        'trx' => '#TRX034',
        'waktu' => '09:58',
        'dibuat' => strtotime('today 09:58:00'),
        'items_summary' => 'Keripik Singkong',
        'items_summary_mobile' => 'Keripik Singkong ×2',
        'items' => [
            ['id' => 'keripik', 'nama' => 'Keripik Singkong', 'harga' => 4000, 'qty' => 2, 'gambar' => 'keripik.png'],
        ],
        'qty' => 2,
        'total' => 8000,
        'status' => 'paid',
        'ref' => 'QR2110-3F9A7F',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 33,
        'trx' => '#TRX033',
        'waktu' => '09:45',
        'dibuat' => strtotime('today 09:45:00'),
        'items_summary' => 'Roti Cokelat',
        'items_summary_mobile' => 'Roti Cokelat ×1',
        'items' => [
            ['id' => 'roti-cokelat', 'nama' => 'Roti Cokelat', 'harga' => 3000, 'qty' => 1, 'gambar' => 'roti-cokelat.png'],
        ],
        'qty' => 1,
        'total' => 3000,
        'status' => 'expired', // Kedaluwarsa
        'ref' => 'QR2110-3F9A80',
        'status_label' => 'Kedaluwarsa',
    ],
    [
        'id' => 32,
        'trx' => '#TRX032',
        'waktu' => '09:40',
        'dibuat' => strtotime('today 09:40:00'),
        'items_summary' => 'Air Mineral',
        'items_summary_mobile' => 'Air Mineral ×3',
        'items' => [
            ['id' => 'air-mineral', 'nama' => 'Air Mineral', 'harga' => 3000, 'qty' => 3, 'gambar' => 'air-mineral.png'],
        ],
        'qty' => 3,
        'total' => 9000,
        'status' => 'paid',
        'ref' => 'QR2110-3F9A81',
        'status_label' => 'Lunas',
    ],
    [
        'id' => 31,
        'trx' => '#TRX031',
        'waktu' => '09:32',
        'dibuat' => strtotime('today 09:32:00'),
        'items_summary' => 'Es Teh Manis',
        'items_summary_mobile' => 'Es Teh Manis ×2',
        'items' => [
            ['id' => 'es-teh', 'nama' => 'Es Teh Manis', 'harga' => 2000, 'qty' => 2, 'gambar' => 'es-teh.png'],
        ],
        'qty' => 2,
        'total' => 4000,
        'status' => 'paid',
        'ref' => 'QR2110-3F9A82',
        'status_label' => 'Lunas',
    ],
];

// Terapkan filter jika ada
$displayedOrders = $mockupOrdersList;
if ($statusFilter !== 'semua' && $statusFilter !== '') {
    $displayedOrders = array_values(array_filter($displayedOrders, function ($o) use ($statusFilter) {
        if ($statusFilter === 'lunas') return $o['status'] === 'paid';
        if ($statusFilter === 'menunggu') return $o['status'] === 'pending';
        if ($statusFilter === 'batal' || $statusFilter === 'kedaluwarsa') return in_array($o['status'], ['expired', 'cancelled'], true);
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

// KPI stats sesuai mockup SVG: Total Rp 428.000, Jml 38, Menunggu 1
$stats = [
    'total_penjualan_hari_ini' => 428000,
    'jumlah_trx_hari_ini'      => 38,
    'menunggu_hari_ini'        => 1,
];

// Transaksi default yang ditampilkan di panel detail (TRX037)
$defaultDetailOrder = null;
foreach ($mockupOrdersList as $o) {
    if ($o['trx'] === '#TRX037') {
        $defaultDetailOrder = $o;
        break;
    }
}
if (!$defaultDetailOrder && !empty($displayedOrders)) {
    $defaultDetailOrder = $displayedOrders[0];
}

admin_page_start('Transaksi', 'transaksi');
?>

<!-- Header Desktop: Judul & Subtitle di Kiri, Tanggal di Kanan -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Transaksi</h1>
            <p class="page-subtitle-lg">Pantau semua pembayaran QRIS dari pelanggan.</p>
        </div>
        <div class="page-date-badge">
            <?= icon('calendar', 16, 'text-muted') ?>
            <span>Rabu, 21 Oktober 2026</span>
        </div>
    </div>
</div>

<!-- Mobile Date Pill (Hanya Tampil di Mobile) -->
<div class="mobile-only-card" style="margin-bottom: 16px;">
    <div class="d-flex align-center gap-8 justify-center" style="background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 10px 14px; font-size: 13.5px; font-weight: 500; color: var(--navy);">
        <?= icon('calendar', 16, 'text-muted') ?>
        <span>Hari ini, 21 Okt 2026</span>
    </div>
</div>

<!-- Layout Utama 2 Kolom Desktop (Kiri: KPI & Tabel, Kanan: Detail Transaksi) -->
<div class="trx-desktop-layout">
    
    <!-- ============================================================= -->
    <!-- KOLOM KIRI: 3 KPI Cards + Card Tabel Transaksi                -->
    <!-- ============================================================= -->
    <div>
        <!-- 3 KPI Cards Ringkasan -->
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

                <!-- Chips Filter Status: Semua, Lunas, Menunggu, Kedaluwarsa -->
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
                       class="chip <?= ($statusFilter === 'batal' || $statusFilter === 'kedaluwarsa') ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                        Kedaluwarsa
                    </a>
                </div>

            </div>

            <!-- Tabel Transaksi Desktop -->
            <div class="table-responsive desktop-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr class="table-header-mockup">
                            <th style="padding-left: 14px;">No.</th>
                            <th>ID</th>
                            <th>Waktu</th>
                            <th>Barang</th>
                            <th class="text-center">Jml</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1; 
                        foreach ($displayedOrders as $o): 
                            $isSelected = ($o['trx'] === '#TRX037');
                            $status = $o['status'];
                            $badgeCls = ($status === 'paid') ? 'badge-success' : (($status === 'pending') ? 'badge-warning' : 'badge-danger');
                            $jsonData = json_encode($o, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                        ?>
                            <tr onclick='selectDesktopRow(<?= $jsonData ?>, this)' 
                                id="row-<?= e($o['trx']) ?>" 
                                style="cursor: pointer; <?= $isSelected ? 'background: #F0F6FF;' : '' ?>"
                                class="trx-table-row">
                                <td class="fs-13 text-muted" style="padding-left: 14px;"><?= $i++ ?></td>
                                <td class="fs-13 fw-700 text-navy"><?= e($o['trx']) ?></td>
                                <td class="fs-13 text-navy"><?= e($o['waktu']) ?></td>
                                <td class="fs-13 text-navy fw-500">
                                    <div style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($o['items_summary']) ?>">
                                        <?= e($o['items_summary']) ?>
                                    </div>
                                </td>
                                <td class="fs-13 text-navy text-center"><?= $o['qty'] ?></td>
                                <td class="fs-13 fw-600 text-navy"><?= rupiah($o['total']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeCls ?>" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px;">
                                        <?= e($o['status_label']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Transaction Cards List (Tampil di Layar Kecil) -->
            <div class="mobile-trx-list">
                <div class="fs-15 fw-700 text-navy" style="margin-bottom: 8px;">Daftar Transaksi</div>
                <?php foreach ($displayedOrders as $o): 
                    $status = $o['status'];
                    $badgeCls = ($status === 'paid') ? 'badge-success' : (($status === 'pending') ? 'badge-warning' : 'badge-danger');
                    $jsonData = json_encode($o, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                ?>
                    <div class="mobile-trx-card" onclick='openMobileDetail(<?= $jsonData ?>)'>
                        <div class="mobile-trx-icon <?= e($status) ?>">
                            <?php if ($status === 'paid'): ?>
                                <?= icon('check', 18) ?>
                            <?php elseif ($status === 'pending'): ?>
                                <?= icon('clock', 18) ?>
                            <?php else: ?>
                                <?= icon('x', 18) ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <div class="d-flex justify-between align-center">
                                <span class="fw-700 fs-14 text-navy"><?= e($o['trx']) ?></span>
                                <span class="fw-700 fs-14 text-navy"><?= rupiah($o['total']) ?></span>
                            </div>
                            <div class="fs-13 text-slate" style="margin: 2px 0;"><?= e($o['items_summary_mobile']) ?></div>
                            <div class="d-flex justify-between align-center">
                                <span class="fs-12 text-muted">QRIS · <?= e($o['waktu']) ?></span>
                                <span class="badge <?= $badgeCls ?>" style="font-size: 11px; padding: 2px 8px;"><?= e($o['status_label']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer Pagination Sesuai Mockup SVG -->
            <div class="d-flex justify-between align-center" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--line); flex-wrap: wrap; gap: 12px;">
                <span class="fs-13 text-muted">Menampilkan 1–9 dari 38 transaksi</span>
                <div class="pagination-mockup">
                    <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">‹</span>
                    <a href="javascript:void(0)" class="page-link-btn active">1</a>
                    <a href="javascript:void(0)" class="page-link-btn">2</a>
                    <a href="javascript:void(0)" class="page-link-btn">3</a>
                    <a href="javascript:void(0)" class="page-link-btn">4</a>
                    <span class="page-link-btn">›</span>
                </div>
            </div>

        </div>
    </div>

    <!-- ============================================================= -->
    <!-- KOLOM KANAN: Panel Detail Transaksi Desktop                   -->
    <!-- ============================================================= -->
    <div class="card" id="desktopDetailPanel" style="position: sticky; top: 20px; border-radius: 14px; padding: 22px;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 18px; font-weight: 700; color: var(--navy);">Detail Transaksi</h3>
        </div>
        
        <div id="desktopDetailContent">
            <!-- Diisi langsung saat render awal atau via JavaScript -->
            <?php if ($defaultDetailOrder): 
                $dStatus = $defaultDetailOrder['status'];
                $sHeroBg = ($dStatus === 'paid') ? '#E7F8EF' : (($dStatus === 'pending') ? '#FFF2DD' : '#FFECEC');
                $sHeroColor = ($dStatus === 'paid') ? '#1E9E62' : (($dStatus === 'pending') ? '#C77700' : '#E5484D');
                $sHeroText = ($dStatus === 'paid') ? 'Pembayaran Lunas' : (($dStatus === 'pending') ? 'Menunggu Pembayaran' : 'Pembayaran Kedaluwarsa');
                $sHeroBadge = ($dStatus === 'paid') ? 'badge-success' : (($dStatus === 'pending') ? 'badge-warning' : 'badge-danger');
            ?>
                <!-- Hero Status Box -->
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
                    <div class="fs-12 text-slate">21 Okt 2026 · <?= e($defaultDetailOrder['waktu']) ?></div>
                </div>

                <!-- Info Kunci Transaksi -->
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--line);">
                    <div class="d-flex justify-between">
                        <span class="text-slate">No. Transaksi</span>
                        <span class="fw-700 text-navy"><?= e($defaultDetailOrder['trx']) ?></span>
                    </div>
                    <div class="d-flex justify-between">
                        <span class="text-slate">Metode</span>
                        <span class="fw-500 text-navy">QRIS</span>
                    </div>
                    <div class="d-flex justify-between">
                        <span class="text-slate">Ref. QRIS</span>
                        <span class="fw-500 text-navy"><?= e($defaultDetailOrder['ref']) ?></span>
                    </div>
                    <div class="d-flex justify-between align-center">
                        <span class="text-slate">Status</span>
                        <span class="badge <?= $sHeroBadge ?>"><?= e($defaultDetailOrder['status_label']) ?></span>
                    </div>
                </div>

                <!-- Rincian Barang -->
                <div style="margin-bottom: 22px;">
                    <h5 class="fs-14 fw-700 text-navy" style="margin-bottom: 14px;">Rincian Barang</h5>
                    <div>
                        <?php foreach ($defaultDetailOrder['items'] as $it): 
                            $sub = $it['harga'] * $it['qty'];
                        ?>
                            <div class="d-flex justify-between align-center" style="padding: 8px 0;">
                                <div class="d-flex align-center gap-10">
                                    <div class="thumb-box" style="width: 36px; height: 36px; border-radius: 8px; background: #F1F5F9; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <img src="assets/img/<?= e($it['gambar'] ?? 'mie-instan.png') ?>" alt="<?= e($it['nama']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                    <div>
                                        <div class="fw-600 fs-14 text-navy"><?= e($it['nama']) ?></div>
                                        <div class="fs-12 text-slate"><?= $it['qty'] ?> × <?= rupiah($it['harga']) ?></div>
                                    </div>
                                </div>
                                <div class="fw-600 fs-14 text-navy"><?= rupiah($sub) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex justify-between align-center" style="padding-top: 14px; margin-top: 6px; border-top: 1px solid var(--line);">
                        <span class="fw-700 fs-14 text-navy">Total Pembayaran</span>
                        <span class="fw-700 fs-16 text-navy"><?= rupiah($defaultDetailOrder['total']) ?></span>
                    </div>
                </div>

                <!-- Tombol Unduh Struk Mockup -->
                <button type="button" class="btn btn-outline w-100" onclick="window.print()" style="border-color: var(--blue); color: var(--blue); border-radius: 8px; height: 46px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <?= icon('download', 18) ?>
                    <span>Unduh Struk</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Modal Detail Transaksi untuk Mobile -->
<div class="modal-backdrop" id="modalTrxDetail">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title">Detail Transaksi</h4>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalTrxDetail')" aria-label="Tutup">
                <?= icon('x', 20) ?>
            </button>
        </div>
        <div class="modal-body" id="modalTrxBody">
            <!-- Diisi secara dinamis via JavaScript -->
        </div>
    </div>
</div>

<script>
// Fungsi Render Detail Transaksi (Desktop & Mobile)
function buildDetailHtml(data) {
    const isPaid = (data.status === 'paid');
    const isPending = (data.status === 'pending');
    
    const heroBg = isPaid ? '#E7F8EF' : (isPending ? '#FFF2DD' : '#FFECEC');
    const heroColor = isPaid ? '#1E9E62' : (isPending ? '#C77700' : '#E5484D');
    const heroText = isPaid ? 'Pembayaran Lunas' : (isPending ? 'Menunggu Pembayaran' : 'Pembayaran Kedaluwarsa');
    const badgeCls = isPaid ? 'badge-success' : (isPending ? 'badge-warning' : 'badge-danger');
    const iconSvg = isPaid 
        ? '<polyline points="20 6 9 17 4 12"></polyline>' 
        : (isPending 
            ? '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>' 
            : '<path d="M18 6 6 18M6 6l12 12"></path>');

    let itemsHtml = '';
    data.items.forEach(it => {
        const sub = it.harga * it.qty;
        const gbr = it.gambar || 'mie-instan.png';
        itemsHtml += `
            <div class="d-flex justify-between align-center" style="padding: 8px 0;">
                <div class="d-flex align-center gap-10">
                    <div class="thumb-box" style="width: 36px; height: 36px; border-radius: 8px; background: #F1F5F9; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img src="assets/img/${gbr}" alt="${it.nama}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div>
                        <div class="fw-600 fs-14 text-navy">${it.nama}</div>
                        <div class="fs-12 text-slate">${it.qty} × Rp ${Number(it.harga).toLocaleString('id-ID')}</div>
                    </div>
                </div>
                <div class="fw-600 fs-14 text-navy">Rp ${Number(sub).toLocaleString('id-ID')}</div>
            </div>
        `;
    });

    return `
        <div style="text-align: center; padding: 22px 16px; background: ${heroBg}; border-radius: 12px; margin-bottom: 22px;">
            <div style="width: 44px; height: 44px; background: #fff; color: ${heroColor}; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">${iconSvg}</svg>
            </div>
            <div class="fs-14 fw-600" style="color: ${heroColor}; margin-bottom: 4px;">${heroText}</div>
            <div class="fs-28 fw-700 text-navy" style="margin-bottom: 4px;">Rp ${Number(data.total).toLocaleString('id-ID')}</div>
            <div class="fs-12 text-slate">21 Okt 2026 · ${data.waktu}</div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--line);">
            <div class="d-flex justify-between">
                <span class="text-slate">No. Transaksi</span>
                <span class="fw-700 text-navy">${data.trx}</span>
            </div>
            <div class="d-flex justify-between">
                <span class="text-slate">Metode</span>
                <span class="fw-500 text-navy">QRIS</span>
            </div>
            <div class="d-flex justify-between">
                <span class="text-slate">Ref. QRIS</span>
                <span class="fw-500 text-navy">${data.ref}</span>
            </div>
            <div class="d-flex justify-between align-center">
                <span class="text-slate">Status</span>
                <span class="badge ${badgeCls}">${data.status_label}</span>
            </div>
        </div>

        <div style="margin-bottom: 22px;">
            <h5 class="fs-14 fw-700 text-navy" style="margin-bottom: 14px;">Rincian Barang</h5>
            <div>${itemsHtml}</div>
            <div class="d-flex justify-between align-center" style="padding-top: 14px; margin-top: 6px; border-top: 1px solid var(--line);">
                <span class="fw-700 fs-14 text-navy">Total Pembayaran</span>
                <span class="fw-700 fs-16 text-navy">Rp ${Number(data.total).toLocaleString('id-ID')}</span>
            </div>
        </div>

        <button type="button" class="btn btn-outline w-100" onclick="window.print()" style="border-color: var(--blue); color: var(--blue); border-radius: 8px; height: 46px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Unduh Struk</span>
        </button>
    `;
}

// Handler klik baris tabel di Desktop
function selectDesktopRow(data, rowEl) {
    document.getElementById('desktopDetailContent').innerHTML = buildDetailHtml(data);
    document.querySelectorAll('.trx-table-row').forEach(tr => tr.style.backgroundColor = '');
    rowEl.style.backgroundColor = '#F0F6FF';
}

// Handler klik kartu transaksi di Mobile
function openMobileDetail(data) {
    document.getElementById('modalTrxBody').innerHTML = buildDetailHtml(data);
    openModal('modalTrxDetail');
}

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('show');
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('show');
}
</script>

<?php
admin_page_end('transaksi');
