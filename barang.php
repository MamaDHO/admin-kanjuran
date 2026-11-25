<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);
$todayLabel = tanggal_id($bundle['pengaturan']['tanggal_demo'] ?? DEMO_TODAY);

admin_page_start('Barang', 'barang', [
    'seed_bundle' => $bundle,
    'mobile_right' => 'add_barang',
]);

render_page_header(
    'Barang',
    'Kelola daftar barang yang tampil di menu pelanggan.',
    $todayLabel
);
?>

<div class="split-workspace" id="barangWorkspace">
  <div class="split-main-col">
    <!-- KPI Row (Desktop only per Mobile SVG 08 Barang admin) -->
    <section class="kpi-grid-3 mobile-hide" aria-label="Ringkasan Barang">
      <article class="kpi-card kpi-compact kpi-blue">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('barang', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Total Barang</p>
          <p class="kpi-value" id="brgKpiTotal">0</p>
        </div>
      </article>
      <article class="kpi-card kpi-compact kpi-purple">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('stok', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Kategori</p>
          <p class="kpi-value" id="brgKpiCats">0</p>
        </div>
      </article>
      <article class="kpi-card kpi-compact kpi-amber">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('alert-triangle', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Stok Menipis</p>
          <p class="kpi-value" id="brgKpiLow">0</p>
        </div>
      </article>
    </section>

    <section class="card" aria-label="Daftar Barang">
      <div class="filter-toolbar">
        <div class="search-box">
          <span class="search-box-icon"><?= icon('search', 18) ?></span>
          <label for="brgSearchInput" class="sr-only">Cari barang</label>
          <input type="search" id="brgSearchInput" class="search-input" placeholder="Cari barang...">
        </div>
        <div class="chip-group" role="group" aria-label="Filter kategori barang" id="brgCatChips">
          <button type="button" class="chip is-active" data-cat="all">Semua</button>
          <button type="button" class="chip" data-cat="Makanan">Makanan</button>
          <button type="button" class="chip" data-cat="Minuman">Minuman</button>
          <button type="button" class="chip" data-cat="Camilan">Camilan</button>
        </div>
        <button type="button" class="btn btn-primary btn-sm mobile-hide" data-open-barang-add style="height:42px;">
          <?= icon('plus', 16) ?>
          <span>Tambah</span>
        </button>
      </div>

      <div id="brgListContainer"></div>
    </section>

    <!-- Tombol Tambah Barang sticky bawah khusus Mobile -->
    <div class="mobile-only" style="margin-top:16px;">
      <button type="button" class="btn btn-primary btn-lg btn-full" data-open-barang-add>
        <?= icon('plus', 18) ?>
        <span>Tambah Barang</span>
      </button>
    </div>
  </div>

  <!-- Right Form Panel (Tambah / Edit Barang) -->
  <aside class="split-side-panel" id="brgFormPanel" aria-label="Formulir Barang">
    <div class="panel-head">
      <h2 class="panel-title" id="brgPanelTitle">Tambah Barang</h2>
      <button type="button" class="panel-close-btn" id="brgPanelCloseBtn" aria-label="Tutup form">
        <?= icon('x', 20) ?>
      </button>
    </div>
    <form id="brgForm" style="display:flex;flex-direction:column;flex:1;" novalidate>
      <input type="hidden" id="brgEditId" value="">

      <div class="photo-upload-row">
        <button type="button" class="photo-dropzone" id="brgPhotoBtn" aria-label="Unggah foto barang">
          <span id="brgPhotoPlaceholder" style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <?= icon('camera', 26) ?>
            <span class="photo-dropzone-label">Foto</span>
          </span>
          <img id="brgPhotoPreview" src="" alt="Preview foto barang" hidden>
        </button>
        <div>
          <div class="photo-meta-title">Foto Barang</div>
          <div class="photo-meta-sub">Ketuk untuk unggah foto</div>
          <div class="photo-meta-sub">JPG/PNG, maks. 2 MB</div>
          <input type="file" id="brgPhotoInput" accept="image/jpeg,image/png" hidden>
        </div>
      </div>

      <div class="form-group">
        <label for="brgNameInput" class="form-label">Nama Barang</label>
        <input type="text" id="brgNameInput" class="form-control" placeholder="Contoh: Es Teh Manis" required>
      </div>

      <div class="form-group">
        <label for="brgCatSelect" class="form-label">Kategori</label>
        <select id="brgCatSelect" class="form-control" required>
          <option value="">Pilih kategori</option>
          <option value="Makanan">Makanan</option>
          <option value="Minuman">Minuman</option>
          <option value="Camilan">Camilan</option>
        </select>
      </div>

      <div class="form-group">
        <label for="brgPriceInput" class="form-label">Harga Jual</label>
        <input type="number" id="brgPriceInput" class="form-control" min="0" step="500" placeholder="Rp 0" required>
      </div>

      <div class="form-group">
        <label for="brgPenitipSelect" class="form-label">Penitip</label>
        <select id="brgPenitipSelect" class="form-control">
          <option value="">Milik kantin (tanpa penitip)</option>
        </select>
      </div>

      <div class="form-group">
        <label for="brgKomisiInput" class="form-label">Komisi Kantin (per item)</label>
        <input type="number" id="brgKomisiInput" class="form-control" min="0" step="100" placeholder="Rp 0">
        <p class="form-hint">Bagian kantin dari harga jual. Kosongkan jika milik kantin.</p>
      </div>

      <div class="form-group" id="brgStokAwalGroup">
        <label for="brgStokInput" class="form-label" id="brgStokLabel">Stok Awal</label>
        <input type="number" id="brgStokInput" class="form-control" min="0" step="1" placeholder="0">
        <p class="form-hint" id="brgStokHint" hidden>Diubah lewat menu Stok (restock / koreksi).</p>
      </div>

      <div id="brgCommissionBox" class="info-preview-box info-preview-neutral">
        Pilih penitip untuk melihat pembagian hasil.
      </div>

      <div id="brgFormError" class="form-error" role="alert" hidden></div>

      <div class="panel-footer-actions" id="brgFooterActions"></div>
    </form>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, updateState, renderPagerHtml, stockStatus, stockBadge, escHtml, rupiah, confirmDialog, toast, setMobileSubheader, svgIcon } = window.KKApp;

  let catFilter = 'all';
  let searchQuery = '';
  let page = 1;
  let formMode = 'add'; // 'add' | 'edit'
  let editingId = '';
  let uploadedDataUrl = '';

  const workspace = document.getElementById('barangWorkspace');
  const listContainer = document.getElementById('brgListContainer');
  const panelTitle = document.getElementById('brgPanelTitle');
  const formEl = document.getElementById('brgForm');
  const editIdEl = document.getElementById('brgEditId');
  const photoBtn = document.getElementById('brgPhotoBtn');
  const photoInput = document.getElementById('brgPhotoInput');
  const photoPlaceholder = document.getElementById('brgPhotoPlaceholder');
  const photoPreview = document.getElementById('brgPhotoPreview');
  const nameEl = document.getElementById('brgNameInput');
  const catEl = document.getElementById('brgCatSelect');
  const priceEl = document.getElementById('brgPriceInput');
  const penitipEl = document.getElementById('brgPenitipSelect');
  const komisiEl = document.getElementById('brgKomisiInput');
  const stokLabelEl = document.getElementById('brgStokLabel');
  const stokInputEl = document.getElementById('brgStokInput');
  const stokHintEl = document.getElementById('brgStokHint');
  const commBox = document.getElementById('brgCommissionBox');
  const formError = document.getElementById('brgFormError');
  const footerActions = document.getElementById('brgFooterActions');

  function populatePenitipOptions() {
    const s = getState();
    const current = penitipEl.value;
    const opts = ['<option value="">Milik kantin (tanpa penitip)</option>'];
    (s.penitip || []).filter(p => p.aktif !== false).forEach(p => {
      opts.push(`<option value="${escHtml(p.id)}">${escHtml(p.nama)}</option>`);
    });
    penitipEl.innerHTML = opts.join('');
    penitipEl.value = current;
  }

  function updateCommissionPreview() {
    const pid = penitipEl.value;
    const harga = parseInt(priceEl.value || '0', 10) || 0;
    const kom = parseInt(komisiEl.value || '0', 10) || 0;
    komisiEl.disabled = !pid;
    if (!pid) {
      komisiEl.value = '0';
      commBox.className = 'info-preview-box info-preview-neutral';
      commBox.innerHTML = 'Pilih penitip untuk melihat pembagian hasil.';
      return;
    }
    if (kom > harga) {
      commBox.className = 'info-preview-box info-preview-amber';
      commBox.innerHTML = `Komisi (${rupiah(kom)}) melebihi harga jual (${rupiah(harga)}).`;
      return;
    }
    const net = Math.max(0, harga - kom);
    commBox.className = 'info-preview-box info-preview-blue';
    commBox.innerHTML = `<div>Harga ${rupiah(harga)} − komisi ${rupiah(kom)}</div>
      <div class="info-preview-highlight">Penitip menerima ${rupiah(net)} per item</div>`;
  }

  function setPhotoPreview(src) {
    if (src) {
      photoPreview.src = src;
      photoPreview.hidden = false;
      photoPlaceholder.hidden = true;
    } else {
      photoPreview.src = '';
      photoPreview.hidden = true;
      photoPlaceholder.hidden = false;
    }
  }

  function openAddMode(openMobile) {
    formMode = 'add';
    editingId = '';
    uploadedDataUrl = '';
    editIdEl.value = '';
    panelTitle.textContent = 'Tambah Barang';
    nameEl.value = '';
    catEl.value = '';
    priceEl.value = '';
    populatePenitipOptions();
    penitipEl.value = '';
    komisiEl.value = '0';
    stokLabelEl.textContent = 'Stok Awal';
    stokInputEl.value = '0';
    stokInputEl.readOnly = false;
    stokHintEl.hidden = true;
    formError.hidden = true;
    setPhotoPreview('');
    updateCommissionPreview();

    footerActions.innerHTML = `
      <button type="button" class="btn btn-outline btn-lg" id="brgCancelBtn" style="flex:0.8;">Batal</button>
      <button type="submit" class="btn btn-primary btn-lg" style="flex:1.4;">Simpan Barang</button>`;
    document.getElementById('brgCancelBtn').onclick = handleCancelForm;

    renderList();
    if (openMobile && window.innerWidth < 768) {
      workspace.classList.add('mobile-showing-panel');
      setMobileSubheader('Tambah Barang', handleCancelForm);
    }
  }

  function openEditMode(id, openMobile) {
    const prod = (getState().barang || []).find(b => b.id === id);
    if (!prod) return;
    formMode = 'edit';
    editingId = prod.id;
    uploadedDataUrl = prod.gambar || '';
    editIdEl.value = prod.id;
    panelTitle.textContent = 'Edit Barang';
    nameEl.value = prod.nama || '';
    catEl.value = prod.kategori || '';
    priceEl.value = String(prod.harga || 0);
    populatePenitipOptions();
    penitipEl.value = prod.penitip_id || '';
    komisiEl.value = String(prod.komisi_kantin || 0);
    stokLabelEl.textContent = 'Stok';
    stokInputEl.value = String(prod.stok || 0);
    stokInputEl.readOnly = true;
    stokHintEl.hidden = false;
    formError.hidden = true;
    setPhotoPreview(prod.gambar || '');
    updateCommissionPreview();

    footerActions.innerHTML = `
      <button type="button" class="btn btn-outline-danger panel-btn-square" id="brgPanelDeactivateBtn" aria-label="Nonaktifkan barang ini">${svgIcon('ban', 18)}</button>
      <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">Simpan Perubahan</button>`;
    document.getElementById('brgPanelDeactivateBtn').onclick = () => triggerDeactivate(prod.id);

    renderList();
    if (openMobile && window.innerWidth < 768) {
      workspace.classList.add('mobile-showing-panel');
      setMobileSubheader('Edit Barang', handleCancelForm);
    }
  }

  function handleCancelForm() {
    if (window.innerWidth < 768 && workspace.classList.contains('mobile-showing-panel')) {
      workspace.classList.remove('mobile-showing-panel');
      setMobileSubheader(null);
    } else {
      openAddMode(false);
    }
  }

  async function triggerDeactivate(id) {
    const prod = (getState().barang || []).find(b => b.id === id);
    if (!prod) return;
    const ok = await confirmDialog({
      title: 'Nonaktifkan Barang?',
      desc: `${prod.nama} tidak akan tampil di menu pelanggan. Riwayat transaksi tetap tersimpan.`,
      icon: 'ban',
      okLabel: 'Nonaktifkan'
    });
    if (!ok) return;
    updateState(s => {
      const target = (s.barang || []).find(b => b.id === id);
      if (target) target.aktif = false;
    });
    toast(`${prod.nama} dinonaktifkan.`);
    if (editingId === id) {
      openAddMode(false);
    }
    if (window.innerWidth < 768 && workspace.classList.contains('mobile-showing-panel')) {
      workspace.classList.remove('mobile-showing-panel');
      setMobileSubheader(null);
    }
    updateKpis();
    renderList();
  }

  function updateKpis() {
    const s = getState();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const active = (s.barang || []).filter(b => b.aktif !== false);
    const cats = new Set(active.map(b => b.kategori));
    const low = active.filter(b => (b.stok || 0) <= threshold).length;
    document.getElementById('brgKpiTotal').textContent = String(active.length);
    document.getElementById('brgKpiCats').textContent = String((s.kategori && s.kategori.length) || cats.size);
    document.getElementById('brgKpiLow').textContent = String(low);
  }

  function getFilteredProducts() {
    const s = getState();
    const q = searchQuery.trim().toLowerCase();
    return (s.barang || []).filter(b => {
      if (b.aktif === false) return false;
      if (catFilter !== 'all' && b.kategori !== catFilter) return false;
      if (q && !b.nama.toLowerCase().includes(q)) return false;
      return true;
    });
  }

  function renderList() {
    const s = getState();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const list = getFilteredProducts();
    const perPage = 10;

    if (!list.length) {
      listContainer.innerHTML = `<div class="empty-state-box">
        <div class="empty-state">
          <div class="empty-state-icon">${svgIcon('barang', 28)}</div>
          <h3 class="empty-state-title">Belum ada barang</h3>
          <p class="empty-state-desc">Tambahkan barang pertama untuk ditampilkan di menu pelanggan.</p>
        </div>
      </div>`;
      return;
    }

    const totalPages = Math.max(1, Math.ceil(list.length / perPage));
    page = Math.max(1, Math.min(page, totalPages));
    const slice = list.slice((page - 1) * perPage, page * perPage);

    const deskRows = slice.map(p => {
      const st = stockStatus(p.stok, threshold);
      const sel = p.id === editingId ? ' is-selected' : '';
      const pen = p.penitip_nama ? `<span style="color:var(--slate);">${escHtml(p.penitip_nama)}</span>` : `<span style="color:var(--grey);">Kantin</span>`;
      return `<tr class="is-clickable${sel}" data-brg-row="${escHtml(p.id)}">
        <td>
          <div class="product-cell">
            <img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-md">
            <span class="product-cell-name">${escHtml(p.nama)}</span>
          </div>
        </td>
        <td>${escHtml(p.kategori)}</td>
        <td class="t-nowrap"><strong style="color:var(--blue);">${rupiah(p.harga)}</strong></td>
        <td class="t-nowrap">${p.stok}</td>
        <td class="t-nowrap">${pen}</td>
        <td>${stockBadge(st)}</td>
        <td>
          <div style="display:flex;gap:4px;">
            <button type="button" class="icon-btn" data-edit-brg="${escHtml(p.id)}" aria-label="Edit ${escHtml(p.nama)}">${svgIcon('edit', 17)}</button>
            <button type="button" class="icon-btn icon-btn-danger" data-ban-brg="${escHtml(p.id)}" aria-label="Nonaktifkan ${escHtml(p.nama)}">${svgIcon('ban', 17)}</button>
          </div>
        </td>
      </tr>`;
    }).join('');

    const mobRows = slice.map(p => {
      const st = stockStatus(p.stok, threshold);
      const subPen = p.penitip_nama ? ` · Titipan ${p.penitip_nama}` : '';
      return `<div class="mobile-row-item">
        <button type="button" data-edit-brg="${escHtml(p.id)}" style="display:flex;align-items:center;gap:10px;border:0;background:transparent;text-align:left;flex:1;min-width:0;padding:0;">
          <img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-lg">
          <div class="mobile-row-meta">
            <div class="mobile-row-title">${escHtml(p.nama)}</div>
            <div class="mobile-row-sub" style="color:var(--blue);font-weight:600;">${rupiah(p.harga)}</div>
            <div class="mobile-row-sub">Stok ${p.stok}${escHtml(subPen)}</div>
          </div>
        </button>
        <div class="mobile-row-right">
          ${stockBadge(st)}
          <button type="button" class="icon-btn icon-btn-danger" data-ban-brg="${escHtml(p.id)}" aria-label="Nonaktifkan ${escHtml(p.nama)}" style="width:28px;height:28px;">${svgIcon('ban', 15)}</button>
        </div>
      </div>`;
    }).join('');

    listContainer.innerHTML = `
      <div class="table-wrap desktop-only-table">
        <table class="data-table">
          <thead>
            <tr><th>Barang</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Penitip</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>${deskRows}</tbody>
        </table>
      </div>
      <div class="mobile-card-list">${mobRows}</div>
      ${renderPagerHtml(list.length, page, perPage, 'brgList')}`;
  }

  // Events
  photoBtn.addEventListener('click', () => photoInput.click());
  photoInput.addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png'].includes(file.type)) {
      formError.textContent = 'Format foto harus JPG atau PNG.';
      formError.hidden = false;
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      formError.textContent = 'Ukuran foto maksimal 2 MB.';
      formError.hidden = false;
      return;
    }
    formError.hidden = true;
    const reader = new FileReader();
    reader.onload = ev => {
      uploadedDataUrl = String(ev.target.result || '');
      setPhotoPreview(uploadedDataUrl);
    };
    reader.readAsDataURL(file);
  });

  [priceEl, penitipEl, komisiEl].forEach(el => {
    el.addEventListener('input', updateCommissionPreview);
    el.addEventListener('change', updateCommissionPreview);
  });

  formEl.addEventListener('submit', function (e) {
    e.preventDefault();
    const nama = nameEl.value.trim();
    const kategori = catEl.value;
    const harga = parseInt(priceEl.value || '0', 10) || 0;
    const pid = penitipEl.value || null;
    const komisi = pid ? (parseInt(komisiEl.value || '0', 10) || 0) : 0;
    const stokVal = parseInt(stokInputEl.value || '0', 10) || 0;

    if (!nama) { formError.textContent = 'Nama barang wajib diisi.'; formError.hidden = false; nameEl.focus(); return; }
    if (!kategori) { formError.textContent = 'Pilih kategori terlebih dahulu.'; formError.hidden = false; catEl.focus(); return; }
    if (harga <= 0) { formError.textContent = 'Harga jual harus lebih besar dari Rp 0.'; formError.hidden = false; priceEl.focus(); return; }
    if (pid && komisi > harga) { formError.textContent = 'Komisi kantin tidak boleh melebihi harga jual.'; formError.hidden = false; komisiEl.focus(); return; }
    formError.hidden = true;

    const s = getState();
    const penObj = pid ? (s.penitip || []).find(p => p.id === pid) : null;
    const catObj = (s.kategori || []).find(c => c.nama === kategori);

    if (formMode === 'edit' && editingId) {
      updateState(st => {
        const target = (st.barang || []).find(b => b.id === editingId);
        if (!target) return;
        target.nama = nama;
        target.kategori = kategori;
        target.kategori_id = catObj ? catObj.id : 1;
        target.harga = harga;
        target.penitip_id = pid;
        target.penitip_nama = penObj ? penObj.nama : null;
        target.komisi_kantin = komisi;
        if (uploadedDataUrl) target.gambar = uploadedDataUrl;
      });
      toast(`Perubahan "${nama}" disimpan.`);
      if (window.innerWidth < 768) handleCancelForm();
    } else {
      const slug = nama.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || ('barang-' + Date.now());
      const newId = slug + '-' + Math.floor(Math.random() * 900 + 100);
      updateState(st => {
        st.barang = st.barang || [];
        st.barang.unshift({
          id: newId,
          nama,
          kategori_id: catObj ? catObj.id : 1,
          kategori,
          harga,
          stok: Math.max(0, stokVal),
          stok_ref: Math.max(20, stokVal),
          penitip_id: pid,
          penitip_nama: penObj ? penObj.nama : null,
          komisi_kantin: komisi,
          aktif: true,
          gambar: uploadedDataUrl || 'assets/img/air-mineral.png'
        });
      });
      toast(`"${nama}" ditambahkan ke katalog.`);
      openAddMode(false);
      if (window.innerWidth < 768) handleCancelForm();
    }
    updateKpis();
    renderList();
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-open-barang-add]')) {
      openAddMode(true);
      return;
    }
    const banBtn = e.target.closest('[data-ban-brg]');
    if (banBtn) {
      e.stopPropagation();
      triggerDeactivate(banBtn.getAttribute('data-ban-brg'));
      return;
    }
    const editBtn = e.target.closest('[data-edit-brg]');
    if (editBtn) {
      openEditMode(editBtn.getAttribute('data-edit-brg'), true);
      return;
    }
    const row = e.target.closest('tr[data-brg-row]');
    if (row) {
      openEditMode(row.getAttribute('data-brg-row'), false);
      return;
    }
    const pgBtn = e.target.closest('.pg-btn[data-page-target]');
    if (pgBtn && !pgBtn.disabled) {
      page = parseInt(pgBtn.getAttribute('data-page-target'), 10);
      renderList();
    }
  });

  document.getElementById('brgPanelCloseBtn').addEventListener('click', () => openAddMode(false));

  document.getElementById('brgCatChips').addEventListener('click', function (e) {
    const chip = e.target.closest('.chip[data-cat]');
    if (!chip) return;
    this.querySelectorAll('.chip').forEach(c => c.classList.remove('is-active'));
    chip.classList.add('is-active');
    catFilter = chip.getAttribute('data-cat');
    page = 1;
    renderList();
  });

  document.getElementById('brgSearchInput').addEventListener('input', function () {
    searchQuery = this.value;
    page = 1;
    renderList();
  });

  updateKpis();
  openAddMode(false);
  renderList();
});
</script>
<?php admin_page_end('barang'); ?>
