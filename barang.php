<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$products = admin_get_products();
$penitipList = admin_get_penitip_list();
$penitipMap = [];
foreach ($penitipList as $pt) {
    $penitipMap[(int) $pt['id']] = $pt['nama'];
}

$categories = ['Semua', 'Makanan', 'Minuman', 'Camilan'];
$filterKat = trim((string) ($_GET['kategori'] ?? 'Semua'));
$searchQ = trim((string) ($_GET['q'] ?? ''));

// Filter kategori & pencarian
$filteredProducts = $products;
if ($filterKat !== 'Semua' && $filterKat !== '') {
    $filteredProducts = array_filter($filteredProducts, fn($p) => strcasecmp($p['kategori'] ?? '', $filterKat) === 0);
}
if ($searchQ !== '') {
    $qLower = strtolower($searchQ);
    $filteredProducts = array_filter($filteredProducts, function ($p) use ($qLower, $penitipMap) {
        if (str_contains(strtolower($p['nama'] ?? ''), $qLower)) return true;
        if (str_contains(strtolower($p['kategori'] ?? ''), $qLower)) return true;
        $pName = isset($p['penitip_id'], $penitipMap[$p['penitip_id']]) ? $penitipMap[$p['penitip_id']] : 'Kantin';
        if (str_contains(strtolower($pName), $qLower)) return true;
        return false;
    });
}

// Urutan produk agar sesuai mockup SVG #06 & #07:
// Es Teh Manis, Mie Instan, Keripik Singkong, Air Mineral, Roti Cokelat
$mockupOrder = ['es-teh', 'mie-instan', 'keripik', 'air-mineral', 'roti-cokelat'];
$orderedProducts = [];
foreach ($mockupOrder as $k) {
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
$uniqueKat = count(array_unique(array_column($products, 'kategori')));
$stokMenipisCount = count(array_filter($products, fn($p) => ((int)$p['stok'] <= (int)($p['batas'] ?? 5) && (int)$p['stok'] > 0)));

// Paginasi: 10 desktop, 5 mobile (Mockup menampilkan 5)
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalItems = count($orderedProducts);
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;
$paginatedProducts = array_slice($orderedProducts, $offset, $perPage, true);

admin_page_start('Barang', 'barang');
?>

<!-- Header Info Desktop Sesuai Mockup SVG -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Barang</h1>
            <p class="page-subtitle-lg">Kelola daftar barang yang tampil di menu pelanggan.</p>
        </div>
        <div class="page-date-badge">
            <?= icon('calendar', 16, 'text-muted') ?>
            <span><?= e(tanggal_hari_ini()) ?></span>
        </div>
    </div>
</div>

<!-- 3 KPI Cards Ringkasan Sesuai Mockup SVG #06 & #07 -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
    <!-- Card 1: Total Barang -->
    <div class="kpi-card" style="background: #E5F0FF; border-radius: 12px; padding: 16px 18px;">
        <div class="kpi-icon-wrap" style="background: #DBEAFE; color: var(--blue); width: 36px; height: 36px; margin-bottom: 6px;">
            <?= icon('barang', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Total Barang</div>
        <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $totalBarang ?></div>
    </div>

    <!-- Card 2: Kategori -->
    <div class="kpi-card" style="background: #E7F8EF; border-radius: 12px; padding: 16px 18px;">
        <div class="kpi-icon-wrap" style="background: #D1F2DF; color: var(--green); width: 36px; height: 36px; margin-bottom: 6px;">
            <?= icon('filter', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Kategori</div>
        <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $uniqueKat ?></div>
    </div>

    <!-- Card 3: Stok Menipis -->
    <div class="kpi-card" style="background: #FFF2DD; border-radius: 12px; padding: 16px 18px;">
        <div class="kpi-icon-wrap" style="background: #FEE8C8; color: var(--amber); width: 36px; height: 36px; margin-bottom: 6px;">
            <?= icon('alert-circle', 18) ?>
        </div>
        <div class="kpi-label" style="font-size: 13px; color: var(--slate); font-weight: 500;">Stok Menipis</div>
        <div class="kpi-value" style="font-size: 22px; font-weight: 700; color: var(--navy);"><?= $stokMenipisCount ?></div>
    </div>
</div>

<!-- Card Wrapper Tabel & Toolbar Barang -->
<div class="card" style="border-radius: 14px; padding: 22px;">

    <!-- Toolbar: Search, Chips Kategori, Tambah Barang -->
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
        
        <!-- Search Input -->
        <div style="flex: 1; max-width: 320px;">
            <form action="barang.php" method="GET" style="display: flex; gap: 8px;">
                <?php if ($filterKat !== 'Semua'): ?>
                    <input type="hidden" name="kategori" value="<?= e($filterKat) ?>">
                <?php endif; ?>
                <div class="input-icon-wrap flex-1" style="position: relative;">
                    <span class="input-icon-left" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--grey);">
                        <?= icon('search', 16) ?>
                    </span>
                    <input type="text" name="q" class="form-control" style="height: 38px; padding-left: 36px; font-size: 13.5px; border-radius: 8px; border: 1px solid var(--line);" placeholder="Cari barang..." value="<?= e($searchQ) ?>">
                </div>
            </form>
        </div>

        <!-- Filter Kategori Chips -->
        <div class="chip-group" style="padding-bottom: 0;">
            <?php foreach ($categories as $kat): ?>
                <a href="barang.php?kategori=<?= urlencode($kat) ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" 
                   class="chip <?= $filterKat === $kat ? 'active' : '' ?>" style="height: 36px; padding: 8px 14px; font-size: 13px; border-radius: 8px;">
                    <?= e($kat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Tombol Tambah Barang -->
        <button type="button" class="btn btn-primary" onclick="openAddModal()" style="height: 38px; padding: 0 16px; font-size: 13.5px; font-weight: 600; border-radius: 8px; display: flex; align-items: center; gap: 6px;">
            <?= icon('plus', 16) ?>
            <span>Tambah</span>
        </button>

    </div>

    <!-- Konten Tabel Barang / Empty State Sesuai Mockup SVG #08 -->
    <?php if (empty($paginatedProducts)): ?>
        <div class="empty-state" style="padding: 48px 20px; text-align: center;">
            <div class="empty-icon-wrap" style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: var(--slate); display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;">
                <?= icon('barang', 28) ?>
            </div>
            <div class="fw-700 fs-16 text-navy" style="margin-bottom: 4px;">Belum ada barang</div>
            <div class="fs-13 text-slate" style="max-width: 340px; margin: 0 auto 16px;">
                Tambahkan barang pertama untuk ditampilkan di menu pelanggan.
            </div>
            <button type="button" class="btn btn-primary" onclick="openAddModal()" style="border-radius: 8px; padding: 8px 18px; font-size: 13px;">
                Tambah Barang
            </button>
        </div>
    <?php else: ?>
        <div class="table-responsive desktop-table-wrap">
            <table class="data-table">
                <thead>
                    <tr class="table-header-mockup">
                        <th style="padding-left: 14px;">Barang</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Penitip</th>
                        <th>Status</th>
                        <th style="text-align: right; padding-right: 14px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedProducts as $p): 
                        $stok = (int) $p['stok'];
                        $batas = (int) ($p['batas'] ?? 5);
                        $isAktif = ($p['aktif'] ?? true);
                        $isMenipis = ($stok <= $batas && $stok > 0);
                        $isHabis = ($stok <= 0);
                        
                        $statusBadge = !$isAktif ? 'badge-secondary' : ($isHabis ? 'badge-danger' : ($isMenipis ? 'badge-warning' : 'badge-success'));
                        $statusText = !$isAktif ? 'Nonaktif' : ($isHabis ? 'Habis' : ($isMenipis ? 'Menipis' : 'Aman'));

                        $penitipName = 'Kantin';
                        if (!empty($p['penitip_id']) && isset($penitipMap[$p['penitip_id']])) {
                            $penitipName = $penitipMap[$p['penitip_id']];
                        }
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
                            <td class="fs-13 text-slate"><?= e($p['kategori']) ?></td>
                            <td class="fs-13 fw-600 text-navy"><?= rupiah((int) $p['harga']) ?></td>
                            <td class="fs-13 fw-600 <?= $isMenipis ? 'text-amber' : ($isHabis ? 'text-red' : 'text-navy') ?>">
                                <?= $stok ?>
                            </td>
                            <td class="fs-13 text-slate"><?= e($penitipName) ?></td>
                            <td>
                                <span class="badge <?= $statusBadge ?>" style="font-size: 11.5px; padding: 2px 8px; border-radius: 4px;">
                                    <?= e($statusText) ?>
                                </span>
                            </td>
                            <td style="text-align: right; padding-right: 14px;">
                                <div class="d-flex align-center justify-end gap-8" style="justify-content: flex-end;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openEditModal(<?= $pJson ?>)' title="Edit Barang" style="height: 32px; padding: 0 10px; border-radius: 6px;">
                                        <?= icon('edit', 14) ?>
                                    </button>
                                    
                                    <?php if ($isAktif): ?>
                                        <button type="button" class="btn btn-danger-outline btn-sm" onclick="openNonaktifkanModal('<?= e($p['id']) ?>', '<?= e($p['nama']) ?>')" title="Nonaktifkan Barang" style="height: 32px; padding: 0 10px; border-radius: 6px;">
                                            <?= icon('trash', 14) ?>
                                        </button>
                                    <?php else: ?>
                                        <form action="aksi.php?act=aktifkan_barang" method="POST" style="margin: 0;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" title="Aktifkan Kembali" style="height: 32px; padding: 0 10px; border-radius: 6px; color: var(--green); border-color: var(--green);">
                                                <?= icon('check', 14) ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
                $batas = (int) ($p['batas'] ?? 5);
                $isAktif = ($p['aktif'] ?? true);
                $isMenipis = ($stok <= $batas && $stok > 0);
                $isHabis = ($stok <= 0);
                $statusBadge = !$isAktif ? 'badge-secondary' : ($isHabis ? 'badge-danger' : ($isMenipis ? 'badge-warning' : 'badge-success'));
                $statusText = !$isAktif ? 'Nonaktif' : ($isHabis ? 'Habis' : ($isMenipis ? 'Menipis' : 'Aman'));

                $penitipName = 'Kantin';
                if (!empty($p['penitip_id']) && isset($penitipMap[$p['penitip_id']])) {
                    $penitipName = $penitipMap[$p['penitip_id']];
                }
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
                                <div class="fs-12 text-muted"><?= e($p['kategori']) ?> · <?= e($penitipName) ?></div>
                            </div>
                        </div>
                        <span class="badge <?= $statusBadge ?>" style="font-size: 11px; padding: 2px 8px;"><?= $statusText ?></span>
                    </div>
                    <div class="d-flex justify-between align-center" style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed var(--line);">
                        <div>
                            <span class="fs-12 text-muted">Stok: </span>
                            <span class="fw-700 fs-13 text-navy"><?= $stok ?> pcs</span>
                        </div>
                        <div class="d-flex align-center gap-8">
                            <span class="fw-700 fs-14 text-blue" style="margin-right: 8px;"><?= rupiah($p['harga']) ?></span>
                            <button type="button" class="btn btn-outline btn-sm" onclick='openEditModal(<?= $pJson ?>)' style="padding: 4px 8px; height: 30px;">
                                <?= icon('edit', 14) ?>
                            </button>
                            <?php if ($isAktif): ?>
                                <button type="button" class="btn btn-danger-outline btn-sm" onclick="openNonaktifkanModal('<?= e($p['id']) ?>', '<?= e($p['nama']) ?>')" style="padding: 4px 8px; height: 30px;">
                                    <?= icon('trash', 14) ?>
                                </button>
                            <?php endif; ?>
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
                    <a href="barang.php?page=<?= $currentPage - 1 ?><?= $filterKat !== 'Semua' ? '&kategori=' . urlencode($filterKat) : '' ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="page-link-btn">‹</a>
                <?php else: ?>
                    <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">‹</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="barang.php?page=<?= $i ?><?= $filterKat !== 'Semua' ? '&kategori=' . urlencode($filterKat) : '' ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="page-link-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="barang.php?page=<?= $currentPage + 1 ?><?= $filterKat !== 'Semua' ? '&kategori=' . urlencode($filterKat) : '' ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="page-link-btn">›</a>
                <?php else: ?>
                    <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">›</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ================================================================= -->
<!-- MODAL 1: Tambah / Edit Barang (Sesuai Mockup SVG #06 & #07)        -->
<!-- ================================================================= -->
<div class="modal-backdrop" id="modalBarang">
    <div class="modal-dialog" style="max-width: 480px;">
        <form action="aksi.php" method="POST" id="formBarang">
            <?= csrf_field() ?>
            <input type="hidden" name="act" id="barangAct" value="tambah_barang">
            <input type="hidden" name="id" id="barangId" value="">

            <div class="modal-header">
                <h3 class="modal-title" id="modalBarangTitle">Tambah Barang</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalBarang')" aria-label="Tutup">
                    <?= icon('x', 20) ?>
                </button>
            </div>

            <div class="modal-body" style="padding: 20px;">
                
                <!-- Foto Barang Picker -->
                <div class="form-group" style="text-align: center; margin-bottom: 18px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Foto Barang</label>
                    <div style="display: flex; justify-content: center; gap: 10px; margin-top: 6px;">
                        <?php foreach (['mie-instan.png', 'air-mineral.png', 'es-teh.png', 'roti-cokelat.png', 'keripik.png'] as $imgName): ?>
                            <label style="cursor: pointer; text-align: center;">
                                <input type="radio" name="gambar" value="<?= e($imgName) ?>" class="radio-img" style="display: none;">
                                <div class="thumb-box img-option" style="width: 48px; height: 48px; border-radius: 8px; border: 2px solid var(--line); transition: all 0.15s; background: #fff; display: flex; align-items: center; justify-content: center;" data-img="<?= e($imgName) ?>">
                                    <img src="assets/img/<?= e($imgName) ?>" alt="<?= e($imgName) ?>" style="width: 32px; height: 32px; object-fit: contain;">
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="fs-11 text-muted" style="margin-top: 6px;">Ketuk untuk memilih foto barang (JPG/PNG, maks. 2 MB)</div>
                </div>

                <!-- Nama Barang -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="barangNama" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Nama Barang</label>
                    <input type="text" id="barangNama" name="nama" class="form-control" placeholder="Contoh: Es Teh Manis" required style="height: 40px; border-radius: 8px;">
                </div>

                <!-- Kategori -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="barangKategori" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Kategori</label>
                    <select id="barangKategori" name="kategori" class="form-select" required style="height: 40px; border-radius: 8px;">
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Camilan">Camilan</option>
                    </select>
                </div>

                <!-- Harga Jual -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="barangHarga" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Harga Jual (Rp)</label>
                    <input type="number" id="barangHarga" name="harga" class="form-control" placeholder="0" min="500" step="500" required style="height: 40px; border-radius: 8px;" oninput="updateCalculationPreview()">
                </div>

                <!-- Penitip Dropdown -->
                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="barangPenitip" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Penitip</label>
                    <select id="barangPenitip" name="penitip_id" class="form-select" style="height: 40px; border-radius: 8px;" onchange="updateCalculationPreview()">
                        <option value="">Milik kantin (tanpa penitip)</option>
                        <?php foreach ($penitipList as $pt): ?>
                            <option value="<?= $pt['id'] ?>"><?= e($pt['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Komisi Kantin Nominal (per item) -->
                <div class="form-group" id="komisiFieldGroup" style="margin-bottom: 14px;">
                    <label for="barangKomisi" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Komisi Kantin (per item)</label>
                    <input type="number" id="barangKomisi" name="komisi" class="form-control" placeholder="0" min="0" step="100" value="0" style="height: 40px; border-radius: 8px;" oninput="updateCalculationPreview()">
                </div>

                <!-- Card Pratinjau Kalkulasi Pembagian Hasil (Sesuai Mockup SVG #06 & #07) -->
                <div id="calcPreviewCard" style="background: #F8FAFC; border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                    <div id="calcFormula" class="fs-12 text-slate" style="margin-bottom: 2px;">Pilih penitip untuk melihat pembagian hasil.</div>
                    <div id="calcResult" class="fs-13 fw-700 text-green"></div>
                </div>

                <!-- Stok Field: Stok Awal di Tambah, Read-only di Edit (Sesuai Mockup SVG #06) -->
                <div id="stokAwalGroup" class="form-group" style="margin-bottom: 14px;">
                    <label for="barangStok" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Stok Awal</label>
                    <input type="number" id="barangStok" name="stok" class="form-control" placeholder="0" min="0" value="0" style="height: 40px; border-radius: 8px;">
                </div>

                <div id="stokReadOnlyGroup" class="form-group" style="margin-bottom: 14px; display: none;">
                    <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Stok</label>
                    <div style="background: #F1F5F9; border: 1px solid var(--line); border-radius: 8px; padding: 10px 14px;">
                        <span id="stokReadOnlyVal" class="fw-700 fs-14 text-navy">0 pcs</span>
                        <div class="fs-11 text-muted" style="margin-top: 2px;">Diubah lewat menu Stok (restock / koreksi).</div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="barangBatas" class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Batas Stok Menipis</label>
                    <input type="number" id="barangBatas" name="batas" class="form-control" placeholder="5" min="1" value="5" required style="height: 40px; border-radius: 8px;">
                </div>

            </div>

            <div class="modal-footer" style="padding: 16px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--line);">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalBarang')" style="border-radius: 8px; height: 38px; padding: 0 16px;">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitBarang" style="border-radius: 8px; height: 38px; padding: 0 20px; font-weight: 600;">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- ================================================================= -->
<!-- MODAL 2: Konfirmasi Nonaktifkan Barang (Sesuai Mockup SVG #09)     -->
<!-- ================================================================= -->
<div class="modal-backdrop" id="modalNonaktifkanBarang">
    <div class="modal-dialog" style="max-width: 420px;">
        <form action="aksi.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="nonaktifkan_barang">
            <input type="hidden" name="id" id="nonaktifkanBarangId" value="">

            <div class="modal-body" style="text-align: center; padding: 32px 24px 20px;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #FEE2E2; color: #EF4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <?= icon('alert-circle', 28) ?>
                </div>
                <h4 class="fs-18 fw-700 text-navy" style="margin-bottom: 8px;">Nonaktifkan Barang?</h4>
                <p class="fs-13 text-slate" style="line-height: 1.5; margin-bottom: 0;">
                    <strong id="nonaktifkanBarangNama"></strong> tidak akan tampil di menu pelanggan. Riwayat transaksi tetap tersimpan.
                </p>
            </div>

            <div class="modal-footer" style="justify-content: center; gap: 12px; background: #fff; padding: 16px 24px 24px; border-top: none;">
                <button type="button" class="btn btn-secondary" style="min-width: 110px; border-radius: 8px; height: 38px;" onclick="closeModal('modalNonaktifkanBarang')">Batal</button>
                <button type="submit" class="btn btn-danger" style="min-width: 130px; border-radius: 8px; height: 38px; font-weight: 600;">Nonaktifkan</button>
            </div>
        </form>
    </div>
</div>

<script>
function selectRadioImage(imgName) {
    document.querySelectorAll('.radio-img').forEach(r => {
        r.checked = (r.value === imgName);
    });
    document.querySelectorAll('.img-option').forEach(el => {
        if (el.getAttribute('data-img') === imgName) {
            el.style.borderColor = 'var(--blue)';
            el.style.background = '#EFF6FF';
        } else {
            el.style.borderColor = 'var(--line)';
            el.style.background = '#fff';
        }
    });
}

document.querySelectorAll('.img-option').forEach(el => {
    el.addEventListener('click', () => {
        selectRadioImage(el.getAttribute('data-img'));
    });
});

function formatRupiah(num) {
    return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function updateCalculationPreview() {
    const harga = parseInt(document.getElementById('barangHarga').value) || 0;
    const komisi = parseInt(document.getElementById('barangKomisi').value) || 0;
    const penitipVal = document.getElementById('barangPenitip').value;
    const formulaEl = document.getElementById('calcFormula');
    const resultEl = document.getElementById('calcResult');

    if (!penitipVal) {
        formulaEl.textContent = 'Pilih penitip untuk melihat pembagian hasil.';
        resultEl.textContent = '';
        return;
    }

    const sisa = Math.max(0, harga - komisi);
    formulaEl.textContent = 'Harga ' + formatRupiah(harga) + ' − komisi ' + formatRupiah(komisi);
    resultEl.textContent = 'Penitip menerima ' + formatRupiah(sisa) + ' per item';
}

function openAddModal() {
    document.getElementById('barangAct').value = 'tambah_barang';
    document.getElementById('barangId').value = '';
    document.getElementById('modalBarangTitle').textContent = 'Tambah Barang';
    document.getElementById('btnSubmitBarang').textContent = 'Simpan Barang';
    document.getElementById('barangNama').value = '';
    document.getElementById('barangKategori').value = 'Makanan';
    document.getElementById('barangHarga').value = '';
    document.getElementById('barangPenitip').value = '';
    document.getElementById('barangKomisi').value = '0';
    document.getElementById('barangStok').value = '0';
    document.getElementById('barangBatas').value = '5';
    document.getElementById('stokAwalGroup').style.display = 'block';
    document.getElementById('stokReadOnlyGroup').style.display = 'none';
    selectRadioImage('mie-instan.png');
    updateCalculationPreview();
    openModal('modalBarang');
}

function openEditModal(data) {
    document.getElementById('barangAct').value = 'edit_barang';
    document.getElementById('barangId').value = data.id;
    document.getElementById('modalBarangTitle').textContent = 'Edit Barang';
    document.getElementById('btnSubmitBarang').textContent = 'Simpan Perubahan';
    document.getElementById('barangNama').value = data.nama;
    document.getElementById('barangKategori').value = data.kategori || 'Makanan';
    document.getElementById('barangHarga').value = data.harga;
    document.getElementById('barangPenitip').value = data.penitip_id || '';
    document.getElementById('barangKomisi').value = data.komisi || 0;
    document.getElementById('barangBatas').value = data.batas || 5;
    
    // Stok read-only di edit per mockup SVG #06
    document.getElementById('stokAwalGroup').style.display = 'none';
    document.getElementById('stokReadOnlyGroup').style.display = 'block';
    document.getElementById('stokReadOnlyVal').textContent = (data.stok || 0) + ' pcs';
    
    selectRadioImage(data.gambar || 'mie-instan.png');
    updateCalculationPreview();
    openModal('modalBarang');
}

function openNonaktifkanModal(id, nama) {
    document.getElementById('nonaktifkanBarangId').value = id;
    document.getElementById('nonaktifkanBarangNama').textContent = nama;
    openModal('modalNonaktifkanBarang');
}
</script>

<?php
admin_page_end('barang');
