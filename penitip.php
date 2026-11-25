<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$searchQuery = trim((string) ($_GET['q'] ?? ''));
$rekap = admin_get_penitip_rekap();
$allPenitip = $rekap['items'];

// Filter pencarian jika ada
if ($searchQuery !== '') {
    $qLower = strtolower($searchQuery);
    $allPenitip = array_values(array_filter($allPenitip, function ($p) use ($qLower) {
        if (str_contains(strtolower($p['nama']), $qLower)) return true;
        if (str_contains(strtolower($p['hp']), $qLower)) return true;
        if (str_contains(strtolower($p['barang_text']), $qLower)) return true;
        return false;
    }));
}

// Paginasi: 10 di desktop, 5 di mobile
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalItems = count($allPenitip);
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;
$paginatedPenitip = array_slice($allPenitip, $offset, $perPage);

// Penitip terpilih untuk detail panel
$selectedId = isset($_GET['id']) ? (int) $_GET['id'] : (!empty($allPenitip) ? $allPenitip[0]['id'] : 0);
$selectedPenitip = null;
foreach ($allPenitip as $p) {
    if ($p['id'] === $selectedId) {
        $selectedPenitip = $p;
        break;
    }
}
if (!$selectedPenitip && !empty($allPenitip)) {
    $selectedPenitip = $allPenitip[0];
    $selectedId = $selectedPenitip['id'];
}

// Data tambahan untuk detail panel penitip terpilih
$penitipProducts = [];
$penitipSetoran = [];
$penitipSales = [];

if ($selectedPenitip) {
    $allProducts = admin_get_products();
    foreach ($allProducts as $pr) {
        if ((int) ($pr['penitip_id'] ?? 0) === $selectedId) {
            $penitipProducts[] = $pr;
        }
    }
    $penitipSetoran = admin_get_setoran_penitip($selectedId);
    
    // Pesanan terkait barang penitip ini
    $allOrders = admin_get_orders_raw();
    foreach ($allOrders as $ord) {
        if (($ord['status'] ?? '') === 'paid') {
            $hasItem = false;
            $itemsFound = [];
            foreach ($ord['items'] ?? [] as $it) {
                if (isset($allProducts[$it['id']]) && (int) ($allProducts[$it['id']]['penitip_id'] ?? 0) === $selectedId) {
                    $hasItem = true;
                    $itemsFound[] = $it;
                }
            }
            if ($hasItem) {
                $penitipSales[] = [
                    'trx'     => $ord['trx'] ?? ('#TRX' . str_pad((string)$ord['id'], 3, '0', STR_PAD_LEFT)),
                    'waktu'   => date('d M Y, H:i', is_numeric($ord['dibuat']) ? (int)$ord['dibuat'] : strtotime((string)$ord['dibuat'])),
                    'items'   => $itemsFound,
                ];
            }
        }
    }
}

admin_page_start('Penitip', 'penitip');
?>

<!-- Header Desktop: Judul & Subtitle di Kiri, Tanggal di Kanan -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Penitip</h1>
            <p class="page-subtitle-lg">Kelola penitip, hak penitip, dan setoran.</p>
        </div>
        <div class="page-date-badge">
            <?= icon('calendar', 16, 'text-muted') ?>
            <span><?= e(tanggal_hari_ini()) ?></span>
        </div>
    </div>
</div>

<!-- Layout Utama 2 Kolom Desktop (Kiri: KPI & Tabel, Kanan: Detail Penitip) -->
<div class="trx-desktop-layout">

    <!-- ============================================================= -->
    <!-- KOLOM KIRI: 3 KPI Cards + Toolbar + Tabel Penitip             -->
    <!-- ============================================================= -->
    <div>
        <!-- 3 KPI Cards Ringkasan -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
            <!-- Card 1: Total Penitip -->
            <div class="kpi-card" style="background: #E5F0FF; border-radius: 12px; padding: 16px 18px;">
                <div class="kpi-icon-wrap" style="background: #DBEAFE; color: var(--blue); width: 36px; height: 36px; margin-bottom: 6px;">
                    <?= icon('penitip', 18) ?>
                </div>
                <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Total Penitip</div>
                <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $rekap['count'] ?></div>
            </div>

            <!-- Card 2: Total Hak Penitip -->
            <div class="kpi-card" style="background: #E7F8EF; border-radius: 12px; padding: 16px 18px;">
                <div class="kpi-icon-wrap" style="background: #D1F2DF; color: var(--green); width: 36px; height: 36px; margin-bottom: 6px;">
                    <?= icon('wallet', 18) ?>
                </div>
                <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Total Hak Penitip</div>
                <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= rupiah($rekap['total_hak']) ?></div>
            </div>

            <!-- Card 3: Sisa Dibayar -->
            <div class="kpi-card" style="background: #FFF2DD; border-radius: 12px; padding: 16px 18px;">
                <div class="kpi-icon-wrap" style="background: #FEE8C8; color: var(--amber); width: 36px; height: 36px; margin-bottom: 6px;">
                    <?= icon('clock', 18) ?>
                </div>
                <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Sisa Dibayar</div>
                <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= rupiah($rekap['total_sisa']) ?></div>
            </div>
        </div>

        <!-- Card Wrapper Tabel Penitip -->
        <div class="card" style="border-radius: 14px; padding: 22px;">
            
            <!-- Toolbar: Pencarian & Tambah Penitip -->
            <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                <form action="penitip.php" method="GET" style="display: flex; gap: 8px; flex: 1; max-width: 380px;">
                    <div class="input-icon-wrap flex-1" style="position: relative;">
                        <span class="input-icon-left" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--grey);">
                            <?= icon('search', 16) ?>
                        </span>
                        <input type="text" name="q" class="form-control" style="height: 38px; padding-left: 36px; font-size: 13.5px; border-radius: 8px; border: 1px solid var(--line);" placeholder="Cari penitip..." value="<?= e($searchQuery) ?>">
                    </div>
                </form>

                <button type="button" class="btn btn-primary" onclick="openModal('modalTambahPenitip')" style="height: 38px; padding: 0 16px; font-size: 13.5px; font-weight: 600; border-radius: 8px; display: flex; align-items: center; gap: 6px;">
                    <?= icon('plus', 16) ?>
                    <span>Tambah Penitip</span>
                </button>
            </div>

            <!-- Tabel Penitip Desktop -->
            <?php if (empty($paginatedPenitip)): ?>
                <div class="empty-state" style="padding: 48px 20px; text-align: center;">
                    <div class="empty-icon-wrap" style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: var(--slate); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
                        <?= icon('penitip', 28) ?>
                    </div>
                    <div class="fw-700 fs-16 text-navy" style="margin-bottom: 4px;">Belum ada penitip</div>
                    <div class="fs-13 text-slate" style="max-width: 320px; margin: 0 auto 16px;">
                        Tambahkan penitip untuk mulai mencatat barang titipan dan komisinya.
                    </div>
                    <button type="button" class="btn btn-primary" onclick="openModal('modalTambahPenitip')" style="border-radius: 8px; padding: 8px 18px; font-size: 13px;">
                        Tambah Penitip
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive desktop-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr class="table-header-mockup">
                                <th style="padding-left: 14px;">Penitip</th>
                                <th>Barang</th>
                                <th>Terjual</th>
                                <th>Hak</th>
                                <th>Dibayar</th>
                                <th style="text-align: right; padding-right: 14px;">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paginatedPenitip as $p): 
                                $isSelected = ($selectedPenitip && $selectedPenitip['id'] === $p['id']);
                                $initials = strtoupper(substr($p['nama'], 0, 2));
                            ?>
                                <tr class="interactive-row <?= $isSelected ? 'row-selected' : '' ?>" 
                                    onclick="window.location.href='penitip.php?id=<?= $p['id'] ?><?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>'" 
                                    style="cursor: pointer; <?= $isSelected ? 'background: #F0F6FF;' : '' ?>">
                                    <td style="padding-left: 14px;">
                                        <div class="d-flex align-center gap-10">
                                            <div class="avatar-circle" style="background: <?= $p['id'] % 2 === 1 ? '#6366F1' : '#0EA5E9' ?>; color: #fff; width: 34px; height: 34px; font-size: 12px; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <?= e($initials) ?>
                                            </div>
                                            <div>
                                                <div class="fw-600 fs-14 text-navy"><?= e($p['nama']) ?></div>
                                                <div class="fs-12 text-muted"><?= e($p['hp']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fs-13 text-slate"><?= e($p['barang_text']) ?></td>
                                    <td class="fs-13 text-navy fw-600"><?= $p['terjual'] ?> item</td>
                                    <td class="fs-13 text-navy fw-600"><?= rupiah($p['hak']) ?></td>
                                    <td class="fs-13 text-slate"><?= rupiah($p['dibayar']) ?></td>
                                    <td style="text-align: right; padding-right: 14px;">
                                        <span class="fw-700 fs-13 text-blue"><?= rupiah($p['sisa_hak']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Card List Mobile -->
                <div class="mobile-only-orders-list">
                    <?php foreach ($paginatedPenitip as $p): 
                        $initials = strtoupper(substr($p['nama'], 0, 2));
                    ?>
                        <div class="order-mobile-card" onclick="window.location.href='penitip.php?id=<?= $p['id'] ?><?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>'" style="cursor: pointer; padding: 14px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 10px; background: #fff;">
                            <div class="d-flex justify-between align-center" style="margin-bottom: 8px;">
                                <div class="d-flex align-center gap-10">
                                    <div class="avatar-circle" style="background: <?= $p['id'] % 2 === 1 ? '#6366F1' : '#0EA5E9' ?>; color: #fff; width: 36px; height: 36px; font-size: 13px; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <?= e($initials) ?>
                                    </div>
                                    <div>
                                        <div class="fw-700 fs-14 text-navy"><?= e($p['nama']) ?></div>
                                        <div class="fs-12 text-muted"><?= e($p['barang_text']) ?> · <?= e($p['hp']) ?></div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div class="fs-11 text-muted">Sisa</div>
                                    <div class="fw-700 fs-14 text-blue"><?= rupiah($p['sisa_hak']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Footer Pagination -->
                <div class="d-flex justify-between align-center" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--line); flex-wrap: wrap; gap: 12px;">
                    <span class="fs-13 text-muted">
                        Menampilkan <?= min(1, $totalItems) ?>–<?= min($totalItems, $currentPage * $perPage) ?> dari <?= $totalItems ?>
                    </span>
                    <div class="pagination-mockup">
                        <?php if ($currentPage > 1): ?>
                            <a href="penitip.php?page=<?= $currentPage - 1 ?><?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="page-link-btn">‹</a>
                        <?php else: ?>
                            <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">‹</span>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="penitip.php?page=<?= $i ?><?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="page-link-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="penitip.php?page=<?= $currentPage + 1 ?><?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="page-link-btn">›</a>
                        <?php else: ?>
                            <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">›</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ============================================================= -->
    <!-- KOLOM KANAN: Panel Detail Penitip Desktop                     -->
    <!-- ============================================================= -->
    <div class="card" id="desktopDetailPanel" style="position: sticky; top: 20px; border-radius: 14px; padding: 22px; height: fit-content;">
        <div class="d-flex justify-between align-center" style="margin-bottom: 20px;">
            <h3 class="card-title" style="margin: 0; font-size: 18px; font-weight: 700; color: var(--navy);">Detail Penitip</h3>
        </div>

        <?php if ($selectedPenitip): 
            $initials = strtoupper(substr($selectedPenitip['nama'], 0, 2));
        ?>
            <!-- Header Profil Penitip -->
            <div class="d-flex justify-between align-center" style="margin-bottom: 18px; padding-bottom: 16px; border-bottom: 1px solid var(--line);">
                <div class="d-flex align-center gap-12">
                    <div class="avatar-circle" style="background: <?= $selectedPenitip['id'] % 2 === 1 ? '#6366F1' : '#0EA5E9' ?>; color: #fff; width: 44px; height: 44px; font-size: 16px; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <?= e($initials) ?>
                    </div>
                    <div>
                        <div class="fw-700 fs-16 text-navy"><?= e($selectedPenitip['nama']) ?></div>
                        <div class="fs-12 text-muted"><?= e($selectedPenitip['hp']) ?></div>
                    </div>
                </div>
                <span class="badge badge-success" style="font-size: 12px; padding: 4px 10px; border-radius: 20px;">Aktif</span>
            </div>

            <!-- 4 Metrik Ringkasan Penitip Sesuai Mockup SVG -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
                <div style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px;">
                    <div class="fs-12 text-slate" style="margin-bottom: 4px;">Total Terjual</div>
                    <div class="fs-16 fw-700 text-navy"><?= $selectedPenitip['terjual'] ?> item</div>
                </div>
                <div style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px;">
                    <div class="fs-12 text-slate" style="margin-bottom: 4px;">Hak Penitip</div>
                    <div class="fs-16 fw-700 text-navy"><?= rupiah($selectedPenitip['hak']) ?></div>
                </div>
                <div style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px;">
                    <div class="fs-12 text-slate" style="margin-bottom: 4px;">Sudah Dibayar</div>
                    <div class="fs-16 fw-700 text-slate"><?= rupiah($selectedPenitip['dibayar']) ?></div>
                </div>
                <div style="background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 12px;">
                    <div class="fs-12 text-blue fw-600" style="margin-bottom: 4px;">Sisa Dibayar</div>
                    <div class="fs-16 fw-700 text-blue"><?= rupiah($selectedPenitip['sisa_hak']) ?></div>
                </div>
            </div>

            <!-- Tab Navigasi: Barang | Penjualan | Setoran -->
            <div class="d-flex align-center gap-8" style="margin-bottom: 16px; border-bottom: 1px solid var(--line); padding-bottom: 8px;">
                <button type="button" class="btn-penitip-tab active" data-target="tabContentBarang" style="background: none; border: none; font-size: 13.5px; font-weight: 600; color: var(--blue); padding: 6px 12px; cursor: pointer; border-bottom: 2px solid var(--blue); margin-bottom: -9px;">
                    Barang
                </button>
                <button type="button" class="btn-penitip-tab" data-target="tabContentPenjualan" style="background: none; border: none; font-size: 13.5px; font-weight: 500; color: var(--slate); padding: 6px 12px; cursor: pointer;">
                    Penjualan
                </button>
                <button type="button" class="btn-penitip-tab" data-target="tabContentSetoran" style="background: none; border: none; font-size: 13.5px; font-weight: 500; color: var(--slate); padding: 6px 12px; cursor: pointer;">
                    Setoran
                </button>
            </div>

            <!-- Isi Tab 1: Barang Titipan -->
            <div id="tabContentBarang" class="penitip-tab-pane">
                <?php if (empty($penitipProducts)): ?>
                    <div class="text-center text-muted fs-13" style="padding: 24px 0;">Belum ada barang titipan terdaftar.</div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($penitipProducts as $pr): 
                            $komisi = (int) ($pr['komisi'] ?? 0);
                            $hakSatuan = max(0, (int) $pr['harga'] - $komisi);
                        ?>
                            <div style="border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <div class="fw-600 fs-13 text-navy"><?= e($pr['nama']) ?></div>
                                    <div class="fs-12 text-muted">Harga: <?= rupiah($pr['harga']) ?> · Komisi: <?= rupiah($komisi) ?></div>
                                </div>
                                <div style="text-align: right;">
                                    <div class="fs-11 text-muted">Penitip terima</div>
                                    <div class="fw-700 fs-13 text-green"><?= rupiah($hakSatuan) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Isi Tab 2: Penjualan -->
            <div id="tabContentPenjualan" class="penitip-tab-pane" style="display: none;">
                <?php if (empty($penitipSales)): ?>
                    <div class="text-center text-muted fs-13" style="padding: 24px 0;">Belum ada riwayat penjualan barang.</div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($penitipSales as $sl): ?>
                            <div style="border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px;">
                                <div class="d-flex justify-between align-center" style="margin-bottom: 4px;">
                                    <span class="fw-600 fs-13 text-navy"><?= e($sl['trx']) ?></span>
                                    <span class="fs-11 text-muted"><?= e($sl['waktu']) ?></span>
                                </div>
                                <?php foreach ($sl['items'] as $sit): ?>
                                    <div class="d-flex justify-between fs-12 text-slate">
                                        <span><?= e($sit['nama']) ?> × <?= (int) $sit['qty'] ?></span>
                                        <span><?= rupiah($sit['harga'] * $sit['qty']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Isi Tab 3: Riwayat Setoran Sesuai Mockup SVG -->
            <div id="tabContentSetoran" class="penitip-tab-pane" style="display: none;">
                <?php if (empty($penitipSetoran)): ?>
                    <div class="text-center text-muted fs-13" style="padding: 24px 0;">Belum ada riwayat setoran.</div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($penitipSetoran as $st): ?>
                            <div style="border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <div class="fw-600 fs-13 text-navy"><?= date('d M Y', strtotime($st['tanggal'])) ?></div>
                                    <div class="fs-12 text-muted"><?= e($st['catatan'] ?: 'Setoran') ?></div>
                                </div>
                                <div class="fw-700 fs-13 text-green">
                                    <?= rupiah($st['jumlah']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tombol Aksi di Bawah Detail Sesuai Mockup SVG -->
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--line); display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary flex-1" onclick="openModal('modalCatatSetoran')" style="font-size: 13.5px; font-weight: 600; height: 40px; border-radius: 8px;">
                    Catat Setoran
                </button>
                <button type="button" class="btn btn-secondary" onclick="openEditPenitip(<?= htmlspecialchars(json_encode($selectedPenitip), ENT_QUOTES) ?>)" style="font-size: 13.5px; height: 40px; border-radius: 8px;">
                    <?= icon('edit', 16) ?>
                </button>
                <?php 
                $canDelete = empty($penitipProducts);
                ?>
                <form action="aksi.php?act=hapus_penitip" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penitip <?= e($selectedPenitip['nama']) ?>?');" style="margin: 0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $selectedPenitip['id'] ?>">
                    <button type="submit" class="btn btn-danger" style="font-size: 13.5px; height: 40px; border-radius: 8px;" <?= !$canDelete ? 'disabled title="Penitip memiliki barang terkait dan tidak dapat dihapus (R-27)"' : '' ?>>
                        <?= icon('trash', 16) ?>
                    </button>
                </form>
            </div>

        <?php else: ?>
            <div class="empty-state" style="padding: 40px 20px; text-align: center;">
                <div class="empty-icon-wrap" style="width: 48px; height: 48px; border-radius: 50%; background: #F1F5F9; color: var(--slate); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <?= icon('info', 24) ?>
                </div>
                <div class="fw-600 fs-15 text-navy" style="margin-bottom: 4px;">Pilih penitip</div>
                <div class="fs-13 text-slate">Pilih salah satu penitip dari tabel untuk melihat detailnya.</div>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- ================================================================= -->
<!-- MODAL 1: Tambah Penitip (Sesuai Mockup SVG #13)                    -->
<!-- ================================================================= -->
<div class="modal-backdrop" id="modalTambahPenitip">
    <div class="modal-dialog" style="max-width: 460px;">
        <div class="modal-header">
            <h3 class="modal-title">Tambah Penitip</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalTambahPenitip')">
                <?= icon('x', 20) ?>
            </button>
        </div>
        <form action="aksi.php?act=tambah_penitip" method="POST">
            <?= csrf_field() ?>
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Nama Penitip</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Bu Sari" required style="height: 40px; border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">No. HP</label>
                    <input type="text" name="hp" class="form-control" placeholder="08xx-xxxx-xxxx" required style="height: 40px; border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Catatan (opsional)</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Contoh: titip keripik tiap Senin" style="border-radius: 8px; resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahPenitip')" style="border-radius: 8px; height: 38px; padding: 0 16px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 8px; height: 38px; padding: 0 20px; font-weight: 600;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL 2: Edit Penitip                                             -->
<!-- ================================================================= -->
<div class="modal-backdrop" id="modalEditPenitip">
    <div class="modal-dialog" style="max-width: 460px;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Penitip</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalEditPenitip')">
                <?= icon('x', 20) ?>
            </button>
        </div>
        <form action="aksi.php?act=edit_penitip" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="editPenitipId">
            <div class="modal-body" style="padding: 20px;">
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Nama Penitip</label>
                    <input type="text" name="nama" id="editPenitipNama" class="form-control" required style="height: 40px; border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">No. HP</label>
                    <input type="text" name="hp" id="editPenitipHp" class="form-control" required style="height: 40px; border-radius: 8px;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Catatan (opsional)</label>
                    <textarea name="catatan" id="editPenitipCatatan" class="form-control" rows="3" style="border-radius: 8px; resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditPenitip')" style="border-radius: 8px; height: 38px; padding: 0 16px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 8px; height: 38px; padding: 0 20px; font-weight: 600;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL 3: Catat Setoran (Sesuai Mockup SVG #14)                     -->
<!-- ================================================================= -->
<?php if ($selectedPenitip): 
    $initials = strtoupper(substr($selectedPenitip['nama'], 0, 2));
?>
<div class="modal-backdrop" id="modalCatatSetoran">
    <div class="modal-dialog" style="max-width: 460px;">
        <div class="modal-header">
            <h3 class="modal-title">Catat Setoran</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalCatatSetoran')">
                <?= icon('x', 20) ?>
            </button>
        </div>
        <form action="aksi.php?act=catat_setoran" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="penitip_id" value="<?= $selectedPenitip['id'] ?>">
            <div class="modal-body" style="padding: 20px;">
                
                <!-- Info Penitip Header di Modal Sesuai Mockup SVG #14 -->
                <div style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; margin-bottom: 18px; display: flex; align-items: center; gap: 12px;">
                    <div class="avatar-circle" style="background: <?= $selectedPenitip['id'] % 2 === 1 ? '#6366F1' : '#0EA5E9' ?>; color: #fff; width: 38px; height: 38px; font-size: 13px; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <?= e($initials) ?>
                    </div>
                    <div>
                        <div class="fw-700 fs-14 text-navy"><?= e($selectedPenitip['nama']) ?></div>
                        <div class="fs-12 text-slate">Sisa yang harus dibayar: <strong class="text-blue"><?= rupiah($selectedPenitip['sisa_hak']) ?></strong></div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Jumlah Setoran (Rp)</label>
                    <input type="number" name="jumlah" class="form-control" min="1000" max="<?= max(1, $selectedPenitip['sisa_hak']) ?>" value="<?= $selectedPenitip['sisa_hak'] ?>" required style="height: 40px; border-radius: 8px; font-weight: 600;">
                    <span class="fs-11 text-muted" style="display: block; margin-top: 4px;">Maksimal setoran: <?= rupiah($selectedPenitip['sisa_hak']) ?> (R-26)</span>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required style="height: 40px; border-radius: 8px;">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Catatan (opsional)</label>
                    <input type="text" name="catatan" class="form-control" placeholder="Contoh: setoran mingguan" style="height: 40px; border-radius: 8px;">
                </div>

            </div>
            <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCatatSetoran')" style="border-radius: 8px; height: 38px; padding: 0 16px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 8px; height: 38px; padding: 0 20px; font-weight: 600;" <?= $selectedPenitip['sisa_hak'] <= 0 ? 'disabled' : '' ?>>
                    Simpan Setoran
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Interaktivitas tab di panel detail
    const tabBtns = document.querySelectorAll('.btn-penitip-tab');
    const tabPanes = document.querySelectorAll('.penitip-tab-pane');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.style.color = 'var(--slate)';
                b.style.fontWeight = '500';
                b.style.borderBottom = 'none';
                b.style.marginBottom = '0';
            });
            tabPanes.forEach(p => p.style.display = 'none');

            btn.style.color = 'var(--blue)';
            btn.style.fontWeight = '600';
            btn.style.borderBottom = '2px solid var(--blue)';
            btn.style.marginBottom = '-9px';

            const targetId = btn.getAttribute('data-target');
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.style.display = 'block';
            }
        });
    });
});

function openEditPenitip(p) {
    document.getElementById('editPenitipId').value = p.id;
    document.getElementById('editPenitipNama').value = p.nama;
    document.getElementById('editPenitipHp').value = p.hp;
    document.getElementById('editPenitipCatatan').value = p.catatan || '';
    openModal('modalEditPenitip');
}
</script>

<?php
admin_page_end('penitip');
