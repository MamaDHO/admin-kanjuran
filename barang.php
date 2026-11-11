<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$products = admin_get_products();
$categories = ['Semua', 'Makanan', 'Minuman', 'Camilan'];
$filterKat = trim((string) ($_GET['kategori'] ?? 'Semua'));
$searchQ = trim((string) ($_GET['q'] ?? ''));

// Filter kategori & search
$filteredProducts = $products;
if ($filterKat !== 'Semua' && $filterKat !== '') {
    $filteredProducts = array_filter($filteredProducts, fn($p) => strcasecmp($p['kategori'] ?? '', $filterKat) === 0);
}
if ($searchQ !== '') {
    $qLower = strtolower($searchQ);
    $filteredProducts = array_filter($filteredProducts, fn($p) => str_contains(strtolower($p['nama'] ?? ''), $qLower));
}

// KPI stats
$totalBarang = count($products);
$uniqueKat = count(array_unique(array_column($products, 'kategori')));
$stokMenipisCount = count(array_filter($products, fn($p) => ((int)$p['stok'] <= (int)($p['batas'] ?? 5) && (int)$p['stok'] > 0)));

admin_page_start('Barang', 'barang');
?>

<!-- Header Info Desktop -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">Barang</h1>
            <p class="page-subtitle-lg">Kelola daftar barang yang tampil di menu pelanggan.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddModal()">
            <?= icon('plus', 18) ?>
            <span>Tambah Barang</span>
        </button>
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
        <div class="fs-12 text-slate fw-500">Terdaftar di etalase</div>
    </div>

    <div class="kpi-card kpi-green">
        <div class="kpi-icon-wrap">
            <?= icon('filter', 22) ?>
        </div>
        <div class="kpi-label">Kategori</div>
        <div class="kpi-value"><?= $uniqueKat ?></div>
        <div class="fs-12 text-slate fw-500">Makanan, Minuman, Camilan</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-icon-wrap">
            <?= icon('alert-circle', 22) ?>
        </div>
        <div class="kpi-label">Stok Menipis</div>
        <div class="kpi-value"><?= $stokMenipisCount ?></div>
        <div class="fs-12 text-slate fw-500">Perlu restock</div>
    </div>
</div>

<!-- Toolbar: Filter Kategori & Cari & Tambah (Mobile) -->
<div class="card" style="margin-bottom: 20px; padding: 16px;">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 14px;">
        
        <!-- Filter Kategori Chips -->
        <div class="chip-group">
            <?php foreach ($categories as $kat): ?>
                <a href="barang.php?kategori=<?= urlencode($kat) ?><?= $searchQ !== '' ? '&q=' . urlencode($searchQ) : '' ?>" class="chip <?= $filterKat === $kat ? 'active' : '' ?>">
                    <?= e($kat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Form Cari & Tombol Tambah Mobile -->
        <div style="display: flex; gap: 10px; flex: 1; max-width: 440px; justify-content: flex-end;">
            <form action="barang.php" method="GET" style="display: flex; gap: 8px; flex: 1;">
                <?php if ($filterKat !== 'Semua'): ?>
                    <input type="hidden" name="kategori" value="<?= e($filterKat) ?>">
                <?php endif; ?>
                <div class="input-icon-wrap flex-1">
                    <span class="input-icon-left"><?= icon('search', 16) ?></span>
                    <input type="text" name="q" class="form-control" style="height: 40px; font-size: 13.5px;" placeholder="Cari nama barang..." value="<?= e($searchQ) ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="height: 40px;">Cari</button>
            </form>

            <button type="button" class="btn btn-primary btn-sm mobile-only-btn" style="height: 40px;" onclick="openAddModal()" title="Tambah Barang">
                <?= icon('plus', 18) ?>
            </button>
        </div>

    </div>
</div>

<!-- Tabel / Daftar Barang -->
<div class="card">
    <div class="d-flex justify-between align-center" style="margin-bottom: 16px;">
        <h3 class="card-title">Katalog Barang</h3>
        <span class="fs-13 text-muted"><?= count($filteredProducts) ?> produk</span>
    </div>

    <?php if (empty($filteredProducts)): ?>
        <div style="text-align: center; padding: 48px 20px; color: var(--grey);">
            <?= icon('barang', 44, 'text-muted', 'var(--line)') ?>
            <div class="fs-15 fw-600 text-slate" style="margin-top: 12px;">Tidak ada barang ditemukan</div>
            <p class="fs-13 text-muted" style="margin-top: 4px;">Silakan sesuaikan kata kunci pencarian atau tambah barang baru.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredProducts as $p): 
                        $stok = (int) $p['stok'];
                        $batas = (int) ($p['batas'] ?? 5);
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
                                        <div class="fs-12 text-muted">ID: <?= e($p['id']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-blue"><?= e($p['kategori']) ?></span>
                            </td>
                            <td>
                                <span class="fw-700 fs-14 text-blue"><?= rupiah((int) $p['harga']) ?></span>
                            </td>
                            <td>
                                <span class="fw-700 fs-14 <?= $isMenipis ? 'text-amber' : ($isHabis ? 'text-red' : 'text-navy') ?>">
                                    <?= $stok ?> pcs
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $badge ?>">
                                    <span class="badge-dot"></span>
                                    <?= $statusTxt ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="d-flex justify-between" style="justify-content: flex-end; gap: 8px;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick='openEditModal(<?= $pJson ?>)' title="Edit Barang">
                                        <?= icon('edit', 16) ?>
                                        <span>Edit</span>
                                    </button>
                                    <button type="button" class="btn btn-danger-outline btn-sm" onclick="openDeleteModal('<?= e($p['id']) ?>', '<?= e($p['nama']) ?>')" title="Hapus Barang">
                                        <?= icon('trash', 16) ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah / Edit Barang -->
<div class="modal-backdrop" id="modalBarang">
    <div class="modal-dialog">
        <form action="aksi.php" method="POST" id="formBarang">
            <?= csrf_field() ?>
            <input type="hidden" name="act" id="barangAct" value="tambah_barang">
            <input type="hidden" name="id" id="barangId" value="">

            <div class="modal-header">
                <h4 class="modal-title" id="modalBarangTitle">Tambah Barang</h4>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalBarang')" aria-label="Tutup">
                    <?= icon('x', 20) ?>
                </button>
            </div>

            <div class="modal-body">
                <!-- Pilihan Gambar Avatar -->
                <div class="form-group" style="text-align: center; margin-bottom: 20px;">
                    <label class="form-label" style="text-align: center;">Foto Barang</label>
                    <div style="display: flex; justify-content: center; gap: 12px; margin-top: 8px;">
                        <?php foreach (['mie-instan.png', 'air-mineral.png', 'es-teh.png', 'roti-cokelat.png', 'keripik.png'] as $imgName): ?>
                            <label style="cursor: pointer; text-align: center;">
                                <input type="radio" name="gambar" value="<?= e($imgName) ?>" class="radio-img" style="display: none;">
                                <div class="thumb-box img-option" style="width: 52px; height: 52px; border: 2px solid var(--line); transition: border-color 0.15s;" data-img="<?= e($imgName) ?>">
                                    <img src="assets/img/<?= e($imgName) ?>" alt="<?= e($imgName) ?>">
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="fs-12 text-muted" style="margin-top: 6px;">Pilih salah satu ikon foto produk</div>
                </div>

                <div class="form-group">
                    <label for="barangNama" class="form-label">Nama Barang</label>
                    <input type="text" id="barangNama" name="nama" class="form-control" placeholder="Contoh: Es Teh Manis" required>
                </div>

                <div class="form-group">
                    <label for="barangKategori" class="form-label">Kategori</label>
                    <select id="barangKategori" name="kategori" class="form-select" required>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Camilan">Camilan</option>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label for="barangHarga" class="form-label">Harga Jual (Rp)</label>
                        <input type="number" id="barangHarga" name="harga" class="form-control" placeholder="0" min="500" step="500" required>
                    </div>

                    <div class="form-group">
                        <label for="barangStok" class="form-label">Stok Awal</label>
                        <input type="number" id="barangStok" name="stok" class="form-control" placeholder="0" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="barangBatas" class="form-label">Batas Stok Menipis</label>
                    <input type="number" id="barangBatas" name="batas" class="form-control" placeholder="5" min="1" value="5" required>
                    <div class="fs-12 text-muted" style="margin-top: 4px;">Peringatan "Menipis" muncul jika stok &le; angka ini</div>
                </div>

                <div class="toggle-switch" style="border-top: 1px solid var(--line); padding-top: 14px;">
                    <div class="switch-label-wrap">
                        <div class="fw-600 fs-14">Tampilkan di menu pelanggan</div>
                        <div class="fs-12 text-muted">Pelanggan bisa memesan barang ini</div>
                    </div>
                    <input type="checkbox" name="aktif" id="barangAktif" class="switch-input" checked>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalBarang')">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSubmitBarang">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Barang -->
<div class="modal-backdrop" id="modalDeleteBarang">
    <div class="modal-dialog" style="max-width: 420px;">
        <form action="aksi.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="hapus_barang">
            <input type="hidden" name="id" id="deleteBarangId" value="">

            <div class="modal-body" style="text-align: center; padding: 32px 24px;">
                <div style="width: 54px; height: 54px; border-radius: 50%; background: var(--red-bg); color: var(--red); display: grid; place-items: center; margin: 0 auto 16px;">
                    <?= icon('trash', 28) ?>
                </div>
                <h4 class="fs-18 fw-700 text-navy" style="margin-bottom: 8px;">Hapus Barang?</h4>
                <p class="fs-14 text-slate" style="line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus <strong id="deleteBarangNama"></strong> dari katalog? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="modal-footer" style="justify-content: center; gap: 14px; background: #fff; padding-top: 0;">
                <button type="button" class="btn btn-outline" style="min-width: 120px;" onclick="closeModal('modalDeleteBarang')">Batal</button>
                <button type="submit" class="btn btn-danger" style="min-width: 120px;">Ya, Hapus</button>
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
            el.style.background = 'var(--blue-light)';
        } else {
            el.style.borderColor = 'var(--line)';
            el.style.background = 'var(--tint)';
        }
    });
}

document.querySelectorAll('.img-option').forEach(el => {
    el.addEventListener('click', () => {
        selectRadioImage(el.getAttribute('data-img'));
    });
});

function openAddModal() {
    document.getElementById('barangAct').value = 'tambah_barang';
    document.getElementById('barangId').value = '';
    document.getElementById('modalBarangTitle').textContent = 'Tambah Barang';
    document.getElementById('btnSubmitBarang').textContent = 'Simpan Barang';
    document.getElementById('barangNama').value = '';
    document.getElementById('barangKategori').value = 'Makanan';
    document.getElementById('barangHarga').value = '';
    document.getElementById('barangStok').value = '0';
    document.getElementById('barangBatas').value = '5';
    document.getElementById('barangAktif').checked = true;
    selectRadioImage('mie-instan.png');
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
    document.getElementById('barangStok').value = data.stok;
    document.getElementById('barangBatas').value = data.batas || 5;
    document.getElementById('barangAktif').checked = (data.aktif !== false);
    selectRadioImage(data.gambar || 'mie-instan.png');
    openModal('modalBarang');
}

function openDeleteModal(id, nama) {
    document.getElementById('deleteBarangId').value = id;
    document.getElementById('deleteBarangNama').textContent = nama;
    openModal('modalDeleteBarang');
}
</script>

<?php
admin_page_end('barang');
