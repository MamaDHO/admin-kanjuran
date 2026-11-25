<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$products = admin_get_products();
$settings = admin_get_settings();
$defaultBatas = (int) ($settings['stok_min'] ?? 5);

$filterStatus = strtolower(trim((string) ($_GET['status'] ?? 'semua')));
$searchQ = trim((string) ($_GET['q'] ?? ''));

// Filter logic
$filteredProducts = $products;

if ($filterStatus === 'menipis') {
    $filteredProducts = array_filter($filteredProducts, fn($p) => ((int)$p['stok'] <= (int)($p['batas'] ?? $defaultBatas) && (int)$p['stok'] > 0));
} elseif ($filterStatus === 'aman') {
    $filteredProducts = array_filter($filteredProducts, fn($p) => ((int)$p['stok'] > (int)($p['batas'] ?? $defaultBatas)));
} elseif ($filterStatus === 'habis') {
    $filteredProducts = array_filter($filteredProducts, fn($p) => ((int)$p['stok'] <= 0));
}

if ($searchQ !== '') {
    $qLower = strtolower($searchQ);
    $filteredProducts = array_filter($filteredProducts, fn($p) => str_contains(strtolower($p['nama'] ?? ''), $qLower));
}

// Urutan produk agar sesuai Mockup SVG #10 & #11:
// Keripik Singkong (2 pcs), Roti Cokelat (3 pcs), Es Teh Manis (5 pcs), Mie Instan (8 pcs), Air Mineral (12 pcs)
$mockupStokOrder = ['keripik', 'roti-cokelat', 'es-teh', 'mie-instan', 'air-mineral'];
$orderedProducts = [];
foreach ($mockupStokOrder as $k) {
    if (isset($filteredProducts[$k])) {
        $orderedProducts[$k] = $filteredProducts[$k];
    }
}
foreach ($filteredProducts as $k => $v) {
    if (!isset($orderedProducts[$k])) {
        $orderedProducts[$k] = $v;
    }
}

// KPI stats
$totalBarang = count($products);
$menipisCount = count(array_filter($products, fn($p) => ((int)$p['stok'] <= (int)($p['batas'] ?? $defaultBatas) && (int)$p['stok'] > 0)));
$habisCount = count(array_filter($products, fn($p) => ((int)$p['stok'] <= 0)));

// Paginasi: 10 desktop, 5 mobile (Mockup menampilkan 5)
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalItems = count($orderedProducts);
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;
$paginatedProducts = array_slice($orderedProducts, $offset, $perPage, true);

admin_page_start('Stok', 'stok');
?>

<!-- Header Info Desktop Sesuai Mockup SVG #10 & #11 -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Stok</h1>
            <p class="page-subtitle-lg">Pantau persediaan, restock, dan koreksi stok.</p>
        </div>
        <div class="page-date-badge">
            <?= icon('calendar', 16, 'text-muted') ?>
            <span><?= e(tanggal_hari_ini()) ?></span>
        </div>
    </div>
</div>

<!-- 3 KPI Cards Ringkasan Sesuai Mockup SVG #10 & #11 -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
    <!-- Card 1: Total Barang -->
    <div class="kpi-card" style="background: #E5F0FF; border-radius: 12px; padding: 16px 18px;">
        <div class="kpi-icon-wrap" style="background: #DBEAFE; color: var(--blue); width: 36px; height: 36px; margin-bottom: 6px;">
            <?= icon('barang', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Total Barang</div>
        <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $totalBarang ?></div>
    </div>

    <!-- Card 2: Menipis -->
    <div class="kpi-card" style="background: #FFF2DD; border-radius: 12px; padding: 16px 18px;">
        <div class="kpi-icon-wrap" style="background: #FEE8C8; color: var(--amber); width: 36px; height: 36px; margin-bottom: 6px;">
            <?= icon('alert-circle', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Menipis</div>
        <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $menipisCount ?></div>
    </div>

    <!-- Card 3: Habis -->
    <div class="kpi-card" style="background: #FFECEC; border-radius: 12px; padding: 16px 18px;">
        <div class="kpi-icon-wrap" style="background: #FEE2E2; color: var(--red); width: 36px; height: 36px; margin-bottom: 6px;">
            <?= icon('x', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Habis</div>
        <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $habisCount ?></div>
    </div>
</div>

<!-- Card Wrapper Tabel & Toolbar Stok -->
<div class="card" style="border-radius: 14px; padding: 22px;">

    <!-- Toolbar: Search & Chips Status -->
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
        
        <!-- Search Input -->
        <div style="flex: 1; max-width: 320px;">
            <form action="stok.php" method="GET" style="display: flex; gap: 8px;">
                <?php if ($filterStatus !== 'semua'): ?>
                    <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
                <?php endif; ?>
                <div class="input-icon-wrap flex-1" style="position: relative;">
                    <span class="input-icon-left" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--grey);">
                        <?= icon('search', 16) ?>
                    </span>
                    <input type="text" name="q" class="form-control" style="height: 38px; padding-left: 36px; font-size: 13.5px; border-radius: 8px; border: 1px solid var(--line);" placeholder="Cari barang..." value="<?= e($searchQ) ?>">
                </div>
            </form>
        </div>

        <!-- Filter Status Chips Sesuai Mockup SVG -->
        <div class="chip-group" style="padding-bottom: 0;">
            <a href="stok.php?status=semua<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" 
               class="chip <?= $filterStatus === 'semua' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                Semua
            </a>
            <a href="stok.php?status=menipis<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" 
               class="chip <?= $filterStatus === 'menipis' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                Menipis
            </a>
            <a href="stok.php?status=aman<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" 
               class="chip <?= $filterStatus === 'aman' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                Aman
            </a>
            <a href="stok.php?status=habis<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" 
               class="chip <?= $filterStatus === 'habis' ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                Habis
            </a>
        </div>

    </div>

    <!-- Tabel Stok Desktop Sesuai Mockup SVG #10 & #11 -->
    <?php if (empty($paginatedProducts)): ?>
        <div class="empty-state" style="padding: 48px 20px; text-align: center;">
            <div class="empty-icon-wrap" style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: var(--slate); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
                <?= icon('stok', 28) ?>
            </div>
            <div class="fw-700 fs-16 text-navy" style="margin-bottom: 4px;">Tidak ada barang</div>
            <div class="fs-13 text-slate">Tidak ada data persediaan stok yang cocok dengan kriteria filter.</div>
        </div>
    <?php else: ?>
        <div class="table-responsive desktop-table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="table-header-mockup">
                        <th style="padding-left: 14px;">Barang</th>
                        <th>Stok</th>
                        <th style="min-width: 140px;">Level</th>
                        <th>Status</th>
                        <th style="text-align: right; padding-right: 14px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedProducts as $p): 
                        $stok = (int) $p['stok'];
                        $batas = (int) ($p['batas'] ?? $defaultBatas);
                        $isMenipis = ($stok <= $batas && $stok > 0);
                        $isHabis = ($stok <= 0);
                        
                        $statusBadge = $isHabis ? 'badge-danger' : ($isMenipis ? 'badge-warning' : 'badge-success');
                        $statusText = $isHabis ? 'Habis' : ($isMenipis ? 'Menipis' : 'Aman');

                        // Level progress bar (max 20 pcs)
                        $maxLevel = max(20, $batas * 3);
                        $percent = min(100, max(5, (int) round(($stok / $maxLevel) * 100)));
                        $barColor = $isHabis ? '#EF4444' : ($isMenipis ? '#F59E0B' : '#10B981');

                        $pJson = json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    ?>
                        <tr>
                            <td style="padding-left: 14px;">
                                <div class="d-flex align-center gap-12">
                                    <div class="thumb-box" style="width: 40px; height: 40px; border-radius: 8px; border: 1px solid var(--line); overflow: hidden; background: #FAFAFA; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <img src="assets/img/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>" style="width: 32px; height: 32px; object-fit: contain;">
                                    </div>
                                    <div>
                                        <div class="fw-600 fs-14 text-navy"><?= e($p['nama']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="fs-13 fw-700 <?= $isMenipis ? 'text-amber' : ($isHabis ? 'text-red' : 'text-navy') ?>">
                                <?= $stok ?> pcs
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 6px; background: #F1F5F9; border-radius: 999px; overflow: hidden;">
                                        <div style="height: 100%; width: <?= $percent ?>%; background: <?= $barColor ?>; border-radius: 999px;"></div>
                                    </div>
                                    <span class="fs-11 text-muted" style="min-width: 30px;"><?= $percent ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?= $statusBadge ?>" style="font-size: 11.5px; padding: 2px 8px; border-radius: 4px;">
                                    <?= e($statusText) ?>
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 14px;">
                                <div class="d-flex align-center justify-end gap-8" style="justify-content: flex-end;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick='openRestockModal(<?= $pJson ?>)' style="height: 32px; padding: 0 12px; font-size: 12.5px; font-weight: 600; border-radius: 6px;">
                                        <?= icon('plus', 14) ?>
                                        <span>Restock</span>
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openKoreksiModal(<?= $pJson ?>)' title="Koreksi Stok" style="height: 32px; padding: 0 10px; font-size: 12.5px; border-radius: 6px;">
                                        <?= icon('edit', 14) ?>
                                        <span>Koreksi</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Card List Mobile -->
        <div class="mobile-only-orders-list">
            <?php foreach ($paginatedProducts as $p): 
                $stok = (int) $p['stok'];
                $batas = (int) ($p['batas'] ?? $defaultBatas);
                $isMenipis = ($stok <= $batas && $stok > 0);
                $isHabis = ($stok <= 0);
                $statusBadge = $isHabis ? 'badge-danger' : ($isMenipis ? 'badge-warning' : 'badge-success');
                $statusText = $isHabis ? 'Habis' : ($isMenipis ? 'Menipis' : 'Aman');
                $pJson = json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            ?>
                <div class="order-mobile-card" style="padding: 14px; border: 1px solid var(--line); border-radius: 10px; margin-bottom: 10px; background: #fff;">
                    <div class="d-flex justify-between align-center" style="margin-bottom: 8px;">
                        <div class="d-flex align-center gap-10">
                            <div class="thumb-box" style="width: 38px; height: 38px; border-radius: 8px; border: 1px solid var(--line); overflow: hidden; background: #FAFAFA; display: flex; align-items: center; justify-content: center;">
                                <img src="assets/img/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>" style="width: 28px; height: 28px; object-fit: contain;">
                            </div>
                            <div>
                                <div class="fw-700 fs-14 text-navy"><?= e($p['nama']) ?></div>
                                <div class="fs-12 text-muted"><?= e($p['kategori']) ?></div>
                            </div>
                        </div>
                        <span class="badge <?= $statusBadge ?>" style="font-size: 11px; padding: 2px 8px;"><?= $statusText ?></span>
                    </div>
                    <div class="d-flex justify-between align-center" style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--line);">
                        <div>
                            <span class="fs-12 text-muted">Stok: </span>
                            <span class="fw-700 fs-14 <?= $isMenipis ? 'text-amber' : ($isHabis ? 'text-red' : 'text-navy') ?>"><?= $stok ?> pcs</span>
                        </div>
                        <div class="d-flex align-center gap-8">
                            <button type="button" class="btn btn-primary btn-sm" onclick='openRestockModal(<?= $pJson ?>)' style="padding: 4px 10px; height: 30px; font-size: 12px;">
                                Restock
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick='openKoreksiModal(<?= $pJson ?>)' style="padding: 4px 8px; height: 30px; font-size: 12px;">
                                Koreksi
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer Pagination Sesuai Mockup SVG -->
        <div class="d-flex justify-between align-center" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--line); flex-wrap: wrap; gap: 12px;">
            <span class="fs-13 text-muted">
                Menampilkan <?= min(1, $totalItems) ?>–<?= min($totalItems, $currentPage * $perPage) ?> dari <?= $totalItems ?>
            </span>
            <div class="pagination-mockup">
                <?php if ($currentPage > 1): ?>
                    <a href="stok.php?page=<?= $currentPage - 1 ?><?= $filterStatus !== 'semua' ? '&status=' . urlencode($filterStatus) : '' ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="page-link-btn">‹</a>
                <?php else: ?>
                    <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">‹</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="stok.php?page=<?= $i ?><?= $filterStatus !== 'semua' ? '&status=' . urlencode($filterStatus) : '' ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="page-link-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="stok.php?page=<?= $currentPage + 1 ?><?= $filterStatus !== 'semua' ? '&status=' . urlencode($filterStatus) : '' ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="page-link-btn">›</a>
                <?php else: ?>
                    <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">›</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ================================================================= -->
<!-- MODAL 1: Restock Barang (Sesuai Mockup SVG #10)                    -->
<!-- ================================================================= -->
<div class="modal-backdrop" id="modalRestock">
    <div class="modal-dialog" style="max-width: 460px;">
        <form action="aksi.php" method="POST" id="formRestock">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="restock">
            <input type="hidden" name="id" id="restockId" value="">

            <div class="modal-header">
                <h3 class="modal-title">Restock Barang</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalRestock')" aria-label="Tutup">
                    <?= icon('x', 20) ?>
                </button>
            </div>

            <div class="modal-body" style="padding: 20px;">
                
                <!-- Info Barang Saat Ini Sesuai Mockup SVG #10 -->
                <div class="d-flex justify-between align-center" style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">
                    <div class="d-flex align-center gap-12">
                        <div class="thumb-box" style="width: 42px; height: 42px; border-radius: 8px; border: 1px solid var(--line); overflow: hidden; background: #fff; display: flex; align-items: center; justify-content: center;">
                            <img id="restockImg" src="assets/img/roti-cokelat.png" alt="Barang" style="width: 30px; height: 30px; object-fit: contain;">
                        </div>
                        <div>
                            <div class="fw-700 fs-15 text-navy" id="restockNama">Roti Cokelat</div>
                            <div class="fs-12 text-slate">Stok saat ini: <strong id="restockStokCur">3 pcs</strong></div>
                        </div>
                    </div>
                    <span id="restockBadge" class="badge badge-warning" style="font-size: 11.5px; padding: 2px 8px; border-radius: 4px;">Menipis</span>
                </div>

                <!-- Input Jumlah Restock -->
                <div class="form-group" style="margin-bottom: 12px;">
                    <label for="restockJumlah" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Jumlah Restock (pcs)</label>
                    <input type="number" id="restockJumlah" name="jumlah" class="form-control" placeholder="0" min="1" value="20" required style="height: 40px; border-radius: 8px; font-weight: 600;" oninput="calcRestockTotal()">
                </div>

                <!-- Quick Add Chips Sesuai Mockup SVG #10: +10, +20, +50, +100 -->
                <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                    <button type="button" class="btn btn-secondary btn-sm" style="flex: 1; height: 32px; border-radius: 6px; font-size: 12.5px; font-weight: 600;" onclick="setRestockQty(10)">+10</button>
                    <button type="button" class="btn btn-secondary btn-sm" style="flex: 1; height: 32px; border-radius: 6px; font-size: 12.5px; font-weight: 600;" onclick="setRestockQty(20)">+20</button>
                    <button type="button" class="btn btn-secondary btn-sm" style="flex: 1; height: 32px; border-radius: 6px; font-size: 12.5px; font-weight: 600;" onclick="setRestockQty(50)">+50</button>
                    <button type="button" class="btn btn-secondary btn-sm" style="flex: 1; height: 32px; border-radius: 6px; font-size: 12.5px; font-weight: 600;" onclick="setRestockQty(100)">+100</button>
                </div>

                <!-- Catatan opsional -->
                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="restockCatatan" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Catatan (opsional)</label>
                    <input type="text" id="restockCatatan" name="catatan" class="form-control" placeholder="Contoh: belanja pasar pagi" style="height: 40px; border-radius: 8px;">
                </div>

                <!-- Ringkasan Perhitungan Sesuai Mockup SVG #10 -->
                <div style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px;">
                    <div class="d-flex justify-between fs-13 text-slate" style="margin-bottom: 6px;">
                        <span>Stok sekarang</span>
                        <span id="summaryStokNow" class="fw-600 text-navy">3 pcs</span>
                    </div>
                    <div class="d-flex justify-between fs-13 text-slate" style="margin-bottom: 6px;">
                        <span>Ditambah</span>
                        <span class="text-green fw-700" id="summaryStokAdd">+20 pcs</span>
                    </div>
                    <div class="d-flex justify-between fs-14 fw-700 text-navy" style="padding-top: 8px; border-top: 1px dashed var(--line);">
                        <span>Stok setelah restock</span>
                        <span class="text-blue" id="summaryStokEnd">23 pcs</span>
                    </div>
                </div>

            </div>

            <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalRestock')" style="border-radius: 8px; height: 38px; padding: 0 16px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 8px; height: 38px; padding: 0 20px; font-weight: 600;">Simpan Restock</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL 2: Koreksi Stok (Sesuai Mockup SVG #11)                      -->
<!-- ================================================================= -->
<div class="modal-backdrop" id="modalKoreksi">
    <div class="modal-dialog" style="max-width: 460px;">
        <form action="aksi.php" method="POST" id="formKoreksi">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="koreksi_stok">
            <input type="hidden" name="id" id="koreksiId" value="">

            <div class="modal-header">
                <h3 class="modal-title">Koreksi Stok</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalKoreksi')" aria-label="Tutup">
                    <?= icon('x', 20) ?>
                </button>
            </div>

            <div class="modal-body" style="padding: 20px;">
                
                <!-- Info Barang Tercatat Sesuai Mockup SVG #11 -->
                <div class="d-flex justify-between align-center" style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">
                    <div class="d-flex align-center gap-12">
                        <div class="thumb-box" style="width: 42px; height: 42px; border-radius: 8px; border: 1px solid var(--line); overflow: hidden; background: #fff; display: flex; align-items: center; justify-content: center;">
                            <img id="koreksiImg" src="assets/img/roti-cokelat.png" alt="Barang" style="width: 30px; height: 30px; object-fit: contain;">
                        </div>
                        <div>
                            <div class="fw-700 fs-15 text-navy" id="koreksiNama">Roti Cokelat</div>
                            <div class="fs-12 text-slate">Stok tercatat: <strong id="koreksiStokCur">3 pcs</strong></div>
                        </div>
                    </div>
                    <span id="koreksiBadge" class="badge badge-warning" style="font-size: 11.5px; padding: 2px 8px; border-radius: 4px;">Menipis</span>
                </div>

                <!-- Input Stok Sebenarnya -->
                <div class="form-group" style="margin-bottom: 12px;">
                    <label for="koreksiStokBaru" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Stok Sebenarnya (pcs)</label>
                    <input type="number" id="koreksiStokBaru" name="stok_baru" class="form-control" min="0" value="0" required style="height: 40px; border-radius: 8px; font-weight: 600;" oninput="calcKoreksiDiff()">
                </div>

                <!-- Selisih Card Sesuai Mockup SVG #11 -->
                <div style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <span class="fs-12 text-slate">Selisih dengan catatan</span>
                    <span id="koreksiDiffText" class="fw-700 fs-13 text-red">0 pcs</span>
                </div>

                <!-- Alasan Koreksi (Wajib per R-33 & Mockup SVG #11) -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 8px; display: block;">Alasan (wajib)</label>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <label style="cursor: pointer; border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; background: #fff;" class="alasan-option">
                            <input type="radio" name="alasan" value="Salah hitung" checked onchange="updateAlasanStyle()">
                            <span>Salah hitung</span>
                        </label>
                        <label style="cursor: pointer; border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; background: #fff;" class="alasan-option">
                            <input type="radio" name="alasan" value="Rusak" onchange="updateAlasanStyle()">
                            <span>Rusak</span>
                        </label>
                        <label style="cursor: pointer; border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; background: #fff;" class="alasan-option">
                            <input type="radio" name="alasan" value="Hilang" onchange="updateAlasanStyle()">
                            <span>Hilang</span>
                        </label>
                        <label style="cursor: pointer; border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; background: #fff;" class="alasan-option">
                            <input type="radio" name="alasan" value="Lainnya" onchange="updateAlasanStyle()">
                            <span>Lainnya</span>
                        </label>
                    </div>
                </div>

                <!-- Catatan Detail -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="koreksiCatatan" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Catatan</label>
                    <input type="text" id="koreksiCatatan" name="catatan_detail" class="form-control" placeholder="Contoh: barang hilang saat jam istirahat" style="height: 40px; border-radius: 8px;">
                </div>

            </div>

            <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalKoreksi')" style="border-radius: 8px; height: 38px; padding: 0 16px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 8px; height: 38px; padding: 0 20px; font-weight: 600;">Simpan Koreksi</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentProductStock = 0;
let koreksiOriginalStock = 0;

function openRestockModal(data) {
    currentProductStock = parseInt(data.stok) || 0;
    document.getElementById('restockId').value = data.id;
    document.getElementById('restockNama').textContent = data.nama;
    document.getElementById('restockStokCur').textContent = currentProductStock + ' pcs';
    document.getElementById('restockImg').src = 'assets/img/' + (data.gambar || 'mie-instan.png');
    
    const batas = parseInt(data.batas) || 5;
    const badgeEl = document.getElementById('restockBadge');
    if (currentProductStock <= 0) {
        badgeEl.className = 'badge badge-danger';
        badgeEl.textContent = 'Habis';
    } else if (currentProductStock <= batas) {
        badgeEl.className = 'badge badge-warning';
        badgeEl.textContent = 'Menipis';
    } else {
        badgeEl.className = 'badge badge-success';
        badgeEl.textContent = 'Aman';
    }

    document.getElementById('restockJumlah').value = 20;
    document.getElementById('restockCatatan').value = '';
    
    calcRestockTotal();
    openModal('modalRestock');
}

function setRestockQty(val) {
    document.getElementById('restockJumlah').value = val;
    calcRestockTotal();
}

function calcRestockTotal() {
    const qty = parseInt(document.getElementById('restockJumlah').value) || 0;
    document.getElementById('summaryStokNow').textContent = currentProductStock + ' pcs';
    document.getElementById('summaryStokAdd').textContent = '+' + qty + ' pcs';
    document.getElementById('summaryStokEnd').textContent = (currentProductStock + qty) + ' pcs';
}

function openKoreksiModal(data) {
    koreksiOriginalStock = parseInt(data.stok) || 0;
    document.getElementById('koreksiId').value = data.id;
    document.getElementById('koreksiNama').textContent = data.nama;
    document.getElementById('koreksiStokCur').textContent = koreksiOriginalStock + ' pcs';
    document.getElementById('koreksiImg').src = 'assets/img/' + (data.gambar || 'mie-instan.png');
    
    const batas = parseInt(data.batas) || 5;
    const badgeEl = document.getElementById('koreksiBadge');
    if (koreksiOriginalStock <= 0) {
        badgeEl.className = 'badge badge-danger';
        badgeEl.textContent = 'Habis';
    } else if (koreksiOriginalStock <= batas) {
        badgeEl.className = 'badge badge-warning';
        badgeEl.textContent = 'Menipis';
    } else {
        badgeEl.className = 'badge badge-success';
        badgeEl.textContent = 'Aman';
    }

    // Default stok baru = stok sekarang
    document.getElementById('koreksiStokBaru').value = Math.max(0, koreksiOriginalStock - 1);
    document.getElementById('koreksiCatatan').value = '';
    calcKoreksiDiff();
    updateAlasanStyle();
    openModal('modalKoreksi');
}

function calcKoreksiDiff() {
    const newStock = parseInt(document.getElementById('koreksiStokBaru').value) || 0;
    const diff = newStock - koreksiOriginalStock;
    const diffText = (diff > 0 ? '+' : '') + diff + ' pcs (' + koreksiOriginalStock + ' → ' + newStock + ' pcs)';
    const el = document.getElementById('koreksiDiffText');
    el.textContent = diffText;
    if (diff < 0) {
        el.className = 'fw-700 fs-13 text-red';
    } else if (diff > 0) {
        el.className = 'fw-700 fs-13 text-green';
    } else {
        el.className = 'fw-700 fs-13 text-slate';
    }
}

function updateAlasanStyle() {
    document.querySelectorAll('.alasan-option').forEach(label => {
        const input = label.querySelector('input');
        if (input.checked) {
            label.style.borderColor = 'var(--blue)';
            label.style.background = '#EFF6FF';
            label.style.color = 'var(--blue)';
        } else {
            label.style.borderColor = 'var(--line)';
            label.style.background = '#fff';
            label.style.color = 'var(--navy)';
        }
    });
}
</script>

<?php
admin_page_end('stok');
