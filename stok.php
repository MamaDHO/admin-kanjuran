<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$products = admin_get_products();
$settings = admin_get_settings();
$defaultBatas = (int) ($settings['batas_stok_tipis'] ?? 5);

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

// KPI stats
$totalBarang = count($products);
$menipisCount = count(array_filter($products, fn($p) => ((int)$p['stok'] <= (int)($p['batas'] ?? $defaultBatas) && (int)$p['stok'] > 0)));
$habisCount = count(array_filter($products, fn($p) => ((int)$p['stok'] <= 0)));

admin_page_start('Stok', 'stok');
?>

<!-- Header Info Desktop -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">Stok</h1>
            <p class="page-subtitle-lg">Pantau persediaan dan lakukan restock barang kantin.</p>
        </div>
    </div>
</div>

<!-- 3 KPI Cards -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon-wrap">
            <?= icon('barang', 22) ?>
        </div>
        <div class="kpi-label">Total Barang</div>
        <div class="kpi-value"><?= $totalBarang ?></div>
        <div class="fs-12 text-slate fw-500">Semua item terdaftar</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-icon-wrap">
            <?= icon('alert-circle', 22) ?>
        </div>
        <div class="kpi-label">Menipis</div>
        <div class="kpi-value"><?= $menipisCount ?></div>
        <div class="fs-12 text-slate fw-500">Stok &le; batas tipis</div>
    </div>

    <div class="kpi-card kpi-green">
        <div class="kpi-icon-wrap">
            <?= icon('check', 22) ?>
        </div>
        <div class="kpi-label">Habis</div>
        <div class="kpi-value"><?= $habisCount ?></div>
        <div class="fs-12 text-slate fw-500">Stok kosong (0 pcs)</div>
    </div>
</div>

<!-- Toolbar: Filter Status & Cari -->
<div class="card" style="margin-bottom: 20px; padding: 16px;">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 14px;">
        
        <!-- Filter Chips -->
        <div class="chip-group">
            <a href="stok.php?status=semua<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="chip <?= $filterStatus === 'semua' ? 'active' : '' ?>">
                Semua
            </a>
            <a href="stok.php?status=menipis<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="chip <?= $filterStatus === 'menipis' ? 'active' : '' ?>">
                Menipis (<?= $menipisCount ?>)
            </a>
            <a href="stok.php?status=aman<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="chip <?= $filterStatus === 'aman' ? 'active' : '' ?>">
                Aman
            </a>
            <a href="stok.php?status=habis<?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="chip <?= $filterStatus === 'habis' ? 'active' : '' ?>">
                Habis (<?= $habisCount ?>)
            </a>
        </div>

        <!-- Form Cari -->
        <form action="stok.php" method="GET" style="display: flex; gap: 8px; flex: 1; max-width: 320px;">
            <?php if ($filterStatus !== 'semua'): ?>
                <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
            <?php endif; ?>
            <div class="input-icon-wrap flex-1">
                <span class="input-icon-left"><?= icon('search', 16) ?></span>
                <input type="text" name="q" class="form-control" style="height: 40px; font-size: 13.5px;" placeholder="Cari nama barang..." value="<?= e($searchQ) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="height: 40px;">Cari</button>
        </form>

    </div>
</div>

<!-- Tabel Persediaan Stok -->
<div class="card">
    <div class="d-flex justify-between align-center" style="margin-bottom: 16px;">
        <h3 class="card-title">Daftar Persediaan Stok</h3>
        <span class="fs-13 text-muted"><?= count($filteredProducts) ?> barang</span>
    </div>

    <?php if (empty($filteredProducts)): ?>
        <div style="text-align: center; padding: 48px 20px; color: var(--grey);">
            <?= icon('stok', 44, 'text-muted', 'var(--line)') ?>
            <div class="fs-15 fw-600 text-slate" style="margin-top: 12px;">Tidak ada barang</div>
            <p class="fs-13 text-muted" style="margin-top: 4px;">Tidak ada persediaan barang yang sesuai dengan filter.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Stok Saat Ini</th>
                        <th>Batas Tipis</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredProducts as $p): 
                        $stok = (int) $p['stok'];
                        $batas = (int) ($p['batas'] ?? $defaultBatas);
                        $isMenipis = ($stok <= $batas && $stok > 0);
                        $isHabis = ($stok <= 0);
                        $badge = $isHabis ? 'badge-danger' : ($isMenipis ? 'badge-warning' : 'badge-success');
                        $statusTxt = $isHabis ? 'Habis' : ($isMenipis ? 'Menipis' : 'Aman');
                        $pJson = json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-center gap-12">
                                    <div class="thumb-box">
                                        <img src="assets/img/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>">
                                    </div>
                                    <div>
                                        <div class="fw-700 fs-14 text-navy"><?= e($p['nama']) ?></div>
                                        <div class="fs-12 text-muted"><?= rupiah((int) $p['harga']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= e($p['kategori']) ?></span>
                            </td>
                            <td>
                                <span class="fw-700 fs-16 <?= $isMenipis ? 'text-amber' : ($isHabis ? 'text-red' : 'text-navy') ?>">
                                    <?= $stok ?> pcs
                                </span>
                            </td>
                            <td>
                                <span class="fs-13 text-muted">&le; <?= $batas ?> pcs</span>
                            </td>
                            <td>
                                <span class="badge <?= $badge ?>">
                                    <span class="badge-dot"></span>
                                    <?= $statusTxt ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-primary btn-sm" onclick='openRestockModal(<?= $pJson ?>)'>
                                    <?= icon('plus', 16) ?>
                                    <span>Restock</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Restock Barang (Sesuai SVG 12 Restock) -->
<div class="modal-backdrop" id="modalRestock">
    <div class="modal-dialog">
        <form action="aksi.php" method="POST" id="formRestock">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="restock">
            <input type="hidden" name="id" id="restockId" value="">

            <div class="modal-header">
                <h4 class="modal-title">Restock Barang</h4>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalRestock')" aria-label="Tutup">
                    <?= icon('x', 20) ?>
                </button>
            </div>

            <div class="modal-body">
                <!-- Info Barang Saat Ini -->
                <div class="d-flex justify-between align-center" style="background: var(--tint); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px;">
                    <div class="d-flex align-center gap-12">
                        <div class="thumb-box" style="width: 44px; height: 44px;">
                            <img id="restockImg" src="assets/img/mie-instan.png" alt="Barang">
                        </div>
                        <div>
                            <div class="fw-700 fs-15 text-navy" id="restockNama">Nama Barang</div>
                            <div class="fs-13 text-slate">Stok saat ini: <strong id="restockStokCur">0 pcs</strong></div>
                        </div>
                    </div>
                    <span id="restockBadge" class="badge badge-warning">Menipis</span>
                </div>

                <!-- Input Jumlah Restock -->
                <div class="form-group">
                    <label for="restockJumlah" class="form-label">Jumlah Restock (pcs)</label>
                    <input type="number" id="restockJumlah" name="jumlah" class="form-control" placeholder="0" min="1" value="20" required oninput="calcRestockTotal()">
                </div>

                <!-- Quick Add Chips -->
                <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                    <button type="button" class="chip" onclick="addRestockQty(10)">+10</button>
                    <button type="button" class="chip" onclick="addRestockQty(20)">+20</button>
                    <button type="button" class="chip" onclick="addRestockQty(50)">+50</button>
                    <button type="button" class="chip" onclick="addRestockQty(100)">+100</button>
                </div>

                <!-- Harga Beli per pcs (opsional) -->
                <div class="form-group">
                    <label for="restockHargaBeli" class="form-label">Harga Beli per pcs (opsional)</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon-left" style="font-size: 13px; font-weight: 600;">Rp</span>
                        <input type="number" id="restockHargaBeli" name="harga_beli" class="form-control" placeholder="Contoh: 2500" min="0" step="100">
                    </div>
                </div>

                <!-- Catatan opsional -->
                <div class="form-group">
                    <label for="restockCatatan" class="form-label">Catatan (opsional)</label>
                    <input type="text" id="restockCatatan" name="catatan" class="form-control" placeholder="Contoh: Belanja pasar pagi">
                </div>

                <!-- Ringkasan Perhitungan -->
                <div style="background: var(--bg-page); border: 1px solid var(--line); border-radius: 10px; padding: 12px 16px; margin-top: 14px;">
                    <div class="d-flex justify-between fs-13 text-slate" style="margin-bottom: 6px;">
                        <span>Stok sekarang:</span>
                        <span id="summaryStokNow">0 pcs</span>
                    </div>
                    <div class="d-flex justify-between fs-13 text-slate" style="margin-bottom: 6px;">
                        <span>Ditambah:</span>
                        <span class="text-green fw-600" id="summaryStokAdd">+20 pcs</span>
                    </div>
                    <div class="d-flex justify-between fs-14 fw-700 text-navy" style="padding-top: 6px; border-top: 1px dashed var(--line);">
                        <span>Stok akhir:</span>
                        <span class="text-blue" id="summaryStokEnd">20 pcs</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalRestock')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Restock</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentProductStock = 0;

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
    document.getElementById('restockHargaBeli').value = '';
    document.getElementById('restockCatatan').value = '';
    
    calcRestockTotal();
    openModal('modalRestock');
}

function addRestockQty(add) {
    const inp = document.getElementById('restockJumlah');
    const val = parseInt(inp.value) || 0;
    inp.value = val + add;
    calcRestockTotal();
}

function calcRestockTotal() {
    const qty = parseInt(document.getElementById('restockJumlah').value) || 0;
    document.getElementById('summaryStokNow').textContent = currentProductStock + ' pcs';
    document.getElementById('summaryStokAdd').textContent = '+' + qty + ' pcs';
    document.getElementById('summaryStokEnd').textContent = (currentProductStock + qty) + ' pcs';
}
</script>

<?php
admin_page_end('stok');
