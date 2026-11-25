<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);
$todayLabel = tanggal_id($bundle['pengaturan']['tanggal_demo'] ?? DEMO_TODAY);

admin_page_start('Penitip', 'penitip', [
    'seed_bundle' => $bundle,
    'mobile_right' => 'bell',
]);

render_page_header(
    'Penitip',
    'Kelola penitip, hak penitip, dan setoran.',
    $todayLabel
);
?>

<div class="split-workspace" id="penitipWorkspace">
  <div class="split-main-col">
    <!-- Mobile Search Input top -->
    <div class="mobile-only" style="margin-bottom:12px;">
      <div class="search-box">
        <span class="search-box-icon"><?= icon('search', 18) ?></span>
        <label for="penSearchMob" class="sr-only">Cari penitip</label>
        <input type="search" id="penSearchMob" class="search-input" placeholder="Cari penitip...">
      </div>
    </div>

    <!-- KPI Row -->
    <section class="kpi-grid-3" aria-label="Ringkasan Penitip">
      <article class="kpi-card kpi-compact kpi-blue">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('penitip', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Total Penitip</p>
          <p class="kpi-value" id="penKpiCount">0</p>
        </div>
      </article>
      <article class="kpi-card kpi-compact kpi-green mobile-hide">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('wallet', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Total Hak Penitip</p>
          <p class="kpi-value" id="penKpiHak">Rp 0</p>
        </div>
      </article>
      <article class="kpi-card kpi-compact kpi-amber">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('clock', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Sisa Dibayar</p>
          <p class="kpi-value" id="penKpiSisa">Rp 0</p>
        </div>
      </article>
    </section>

    <section class="card" aria-label="Daftar Penitip">
      <div class="filter-toolbar mobile-hide">
        <div class="search-box">
          <span class="search-box-icon"><?= icon('search', 18) ?></span>
          <label for="penSearchDesk" class="sr-only">Cari penitip</label>
          <input type="search" id="penSearchDesk" class="search-input" placeholder="Cari penitip...">
        </div>
        <button type="button" class="btn btn-primary btn-sm" data-open-pen-add style="height:42px;">
          <?= icon('plus', 16) ?>
          <span>Tambah Penitip</span>
        </button>
      </div>

      <div id="penListContainer"></div>
    </section>

    <div class="mobile-only" style="margin-top:16px;">
      <button type="button" class="btn btn-primary btn-lg btn-full" data-open-pen-add>
        <?= icon('plus', 18) ?>
        <span>Tambah Penitip</span>
      </button>
    </div>
  </div>

  <!-- Right Side Panel: Detail / Tambah / Edit Penitip -->
  <aside class="split-side-panel" id="penPanel" aria-label="Panel Penitip">
    <div class="panel-head">
      <h2 class="panel-title" id="penPanelTitle">Detail Penitip</h2>
      <button type="button" class="panel-close-btn" id="penPanelCloseBtn" aria-label="Tutup panel" hidden>
        <?= icon('x', 20) ?>
      </button>
    </div>
    <div id="penPanelBody" style="display:flex;flex-direction:column;flex:1;"></div>
  </aside>
</div>

<!-- Modal Catat Setoran -->
<div class="modal-backdrop" id="setoranModalBackdrop" hidden>
  <div class="modal-card modal-card-left" role="dialog" aria-modal="true" aria-labelledby="setoranModalTitle" style="max-width:410px;">
    <div class="panel-head" style="margin-bottom:14px;">
      <h2 class="panel-title" id="setoranModalTitle">Catat Setoran</h2>
      <button type="button" class="panel-close-btn" id="setoranModalClose" aria-label="Tutup modal setoran">
        <?= icon('x', 20) ?>
      </button>
    </div>

    <div id="setoranPenBanner" class="card" style="background:var(--tint);border:0;padding:12px 14px;display:flex;align-items:center;gap:12px;margin-bottom:16px;"></div>

    <form id="setoranForm" novalidate>
      <div class="form-group">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
          <label for="setoranAmountInput" class="form-label" style="margin:0;">Jumlah Setoran</label>
          <button type="button" class="chip" id="setoranFillFullBtn" style="height:26px;padding:0 10px;font-size:11.5px;background:var(--blue-light);color:var(--blue-dark);">Isi sesuai sisa</button>
        </div>
        <input type="number" id="setoranAmountInput" class="form-control" min="100" step="500" required>
      </div>

      <div class="form-group">
        <label for="setoranDateInput" class="form-label">Tanggal</label>
        <input type="date" id="setoranDateInput" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="setoranNoteInput" class="form-label">Catatan (opsional)</label>
        <input type="text" id="setoranNoteInput" class="form-control" placeholder="Contoh: setoran mingguan">
      </div>

      <div class="preview-math-box" id="setoranMathBox"></div>
      <div id="setoranError" class="form-error" role="alert" hidden></div>

      <div class="modal-actions" style="margin-top:16px;">
        <button type="button" class="btn btn-outline btn-lg" id="setoranCancelBtn">Batal</button>
        <button type="submit" class="btn btn-primary btn-lg" style="flex:1.4;">Simpan Setoran</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, updateState, renderPagerHtml, escHtml, rupiah, formatShortDate, toast, setMobileSubheader, svgIcon } = window.KKApp;

  let searchQuery = '';
  let page = 1;
  let subPage = 1;
  let selectedId = '';
  let panelMode = 'detail'; // 'detail' | 'add' | 'edit'
  let activeTab = 'setoran'; // 'barang' | 'penjualan' | 'setoran'
  let userClosedDetail = false;
  let activeSetoranPen = null;

  const workspace = document.getElementById('penitipWorkspace');
  const listContainer = document.getElementById('penListContainer');
  const panelTitle = document.getElementById('penPanelTitle');
  const panelBody = document.getElementById('penPanelBody');
  const closeBtn = document.getElementById('penPanelCloseBtn');
  const searchDesk = document.getElementById('penSearchDesk');
  const searchMob = document.getElementById('penSearchMob');

  const setoranBackdrop = document.getElementById('setoranModalBackdrop');
  const setoranBanner = document.getElementById('setoranPenBanner');
  const setoranAmtEl = document.getElementById('setoranAmountInput');
  const setoranDateEl = document.getElementById('setoranDateInput');
  const setoranNoteEl = document.getElementById('setoranNoteInput');
  const setoranMathBox = document.getElementById('setoranMathBox');
  const setoranErr = document.getElementById('setoranError');

  function makeInitials(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return 'PN';
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }

  function computeLedger(p) {
    const s = getState();
    const linked = (s.barang || []).filter(b => b.penitip_id === p.id && b.aktif !== false);
    const deposits = (s.setoran || []).filter(st => st.penitip_id === p.id);
    const paid = deposits.reduce((a, st) => a + (st.jumlah || 0), 0);
    const hak = Number(p.hak_siklus || 0);
    const terjual = Number(p.terjual_siklus || 0);
    const sisa = Math.max(0, hak - paid);

    // Kumpulkan riwayat penjualan item milik penitip dari pesanan lunas
    const sales = [];
    (s.pesanan || []).forEach(ord => {
      if (ord.status !== 'lunas') return;
      (ord.items || []).forEach(it => {
        if (it.penitip_id === p.id) {
          const hakPerItem = Math.max(0, (it.harga_satuan || 0) - (it.komisi_satuan || 0));
          sales.push({
            kode: ord.kode,
            tanggal: ord.tanggal,
            waktu: ord.waktu,
            barang: it.nama,
            qty: it.qty,
            hak: hakPerItem * it.qty
          });
        }
      });
    });

    return { linked, deposits, terjual, hak, paid, sisa, sales };
  }

  function updateKpis() {
    const s = getState();
    const list = s.penitip || [];
    let totalHak = 0, totalSisa = 0;
    list.forEach(p => {
      const lg = computeLedger(p);
      totalHak += lg.hak;
      totalSisa += lg.sisa;
    });
    document.getElementById('penKpiCount').textContent = String(list.length);
    document.getElementById('penKpiHak').textContent = rupiah(totalHak);
    document.getElementById('penKpiSisa').textContent = rupiah(totalSisa);
  }

  function getFiltered() {
    const q = searchQuery.trim().toLowerCase();
    return (getState().penitip || []).filter(p => {
      if (!q) return true;
      return (p.nama || '').toLowerCase().includes(q) || (p.no_hp || '').toLowerCase().includes(q);
    });
  }

  function renderList() {
    const list = getFiltered();
    const perPage = 10;

    if (!list.length) {
      listContainer.innerHTML = `<div class="empty-state-box">
        <div class="empty-state">
          <div class="empty-state-icon">${svgIcon('penitip', 28)}</div>
          <h3 class="empty-state-title">Belum ada penitip</h3>
          <p class="empty-state-desc">Tambahkan penitip untuk mulai mencatat barang titipan dan komisinya.</p>
        </div>
      </div>`;
      if (window.innerWidth >= 768 && panelMode === 'detail') {
        selectedId = '';
        renderPanel();
      }
      return;
    }

    if (window.innerWidth >= 768 && panelMode === 'detail' && !userClosedDetail && (!selectedId || !list.some(p => p.id === selectedId))) {
      selectedId = list[0].id;
    }

    const totalPages = Math.max(1, Math.ceil(list.length / perPage));
    page = Math.max(1, Math.min(page, totalPages));
    const slice = list.slice((page - 1) * perPage, page * perPage);

    const deskRows = slice.map(p => {
      const lg = computeLedger(p);
      const barangNames = lg.linked.map(b => b.nama).join(', ') || '—';
      const sel = p.id === selectedId && panelMode !== 'add' ? ' is-selected' : '';
      return `<tr class="is-clickable${sel}" data-pen-id="${escHtml(p.id)}">
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <span class="avatar-initials">${escHtml(p.kode || makeInitials(p.nama))}</span>
            <div style="min-width:0;">
              <div style="font-weight:600;color:var(--navy);white-space:nowrap;">${escHtml(p.nama)}</div>
              <div style="font-size:11.5px;color:var(--grey);white-space:nowrap;">${escHtml(p.no_hp)}</div>
            </div>
          </div>
        </td>
        <td class="t-truncate">${escHtml(barangNames)}</td>
        <td class="t-right t-nowrap">${lg.terjual} item</td>
        <td class="t-right t-nowrap">${rupiah(lg.hak)}</td>
        <td class="t-right t-nowrap">${rupiah(lg.paid)}</td>
        <td class="t-right t-nowrap"><strong style="color:${lg.sisa > 0 ? 'var(--amber-text)' : 'var(--green-text)'};">${rupiah(lg.sisa)}</strong></td>
        <td class="t-right" style="color:var(--grey-icon);">${svgIcon('chevron-right', 16)}</td>
      </tr>`;
    }).join('');

    const mobRows = slice.map(p => {
      const lg = computeLedger(p);
      return `<button type="button" class="mobile-row-item" data-pen-id="${escHtml(p.id)}" style="width:100%;border:0;background:transparent;text-align:left;padding:12px 0;border-bottom:1px solid var(--line);">
        <div class="mobile-row-left">
          <span class="avatar-initials">${escHtml(p.kode || makeInitials(p.nama))}</span>
          <div class="mobile-row-meta">
            <div class="mobile-row-title">${escHtml(p.nama)}</div>
            <div class="mobile-row-sub">${lg.linked.length} barang · ${escHtml(p.no_hp)}</div>
          </div>
        </div>
        <div class="mobile-row-right">
          <span style="font-size:10.5px;color:var(--grey);">Sisa</span>
          <span class="mobile-row-amount" style="color:${lg.sisa > 0 ? 'var(--amber-text)' : 'var(--green-text)'};">${rupiah(lg.sisa)}</span>
        </div>
      </button>`;
    }).join('');

    listContainer.innerHTML = `
      <div class="table-wrap desktop-only-table">
        <table class="data-table">
          <thead>
            <tr>
              <th>Penitip</th><th>Barang</th>
              <th class="t-right">Terjual</th><th class="t-right">Hak</th>
              <th class="t-right">Dibayar</th><th class="t-right">Sisa</th><th></th>
            </tr>
          </thead>
          <tbody>${deskRows}</tbody>
        </table>
      </div>
      <div class="mobile-card-list">${mobRows}</div>
      ${renderPagerHtml(list.length, page, perPage, 'penList')}`;

    renderPanel();
  }

  function renderPanel() {
    const s = getState();

    if (panelMode === 'add' || panelMode === 'edit') {
      const p = panelMode === 'edit' ? (s.penitip || []).find(x => x.id === selectedId) : null;
      panelTitle.textContent = p ? 'Edit Penitip' : 'Tambah Penitip';
      closeBtn.hidden = false;
      panelBody.innerHTML = `
        <form id="penForm" style="display:flex;flex-direction:column;flex:1;" novalidate>
          <div class="form-group">
            <label for="penNameInput" class="form-label">Nama Penitip</label>
            <input type="text" id="penNameInput" class="form-control" value="${escHtml(p ? p.nama : '')}" placeholder="Contoh: Bu Sari" required>
          </div>
          <div class="form-group">
            <label for="penPhoneInput" class="form-label">No. HP</label>
            <input type="tel" id="penPhoneInput" class="form-control" value="${escHtml(p ? p.no_hp : '')}" placeholder="08xx-xxxx-xxxx" required>
          </div>
          <div class="form-group">
            <label for="penNoteInput" class="form-label">Catatan (opsional)</label>
            <input type="text" id="penNoteInput" class="form-control" value="${escHtml(p ? p.catatan : '')}" placeholder="Contoh: titip keripik tiap Senin">
          </div>
          <div id="penFormError" class="form-error" role="alert" hidden></div>
          <div class="panel-footer-actions">
            <button type="button" class="btn btn-outline btn-lg" id="penFormCancel" style="flex:0.8;">Batal</button>
            <button type="submit" class="btn btn-primary btn-lg" style="flex:1.4;">Simpan</button>
          </div>
        </form>`;

      document.getElementById('penFormCancel').onclick = handleCancelPanel;
      document.getElementById('penForm').onsubmit = function (e) {
        e.preventDefault();
        const nama = document.getElementById('penNameInput').value.trim();
        const hp = document.getElementById('penPhoneInput').value.trim();
        const cat = document.getElementById('penNoteInput').value.trim();
        const err = document.getElementById('penFormError');
        if (!nama || !hp) {
          err.textContent = 'Nama penitip dan No. HP wajib diisi.';
          err.hidden = false;
          return;
        }
        err.hidden = true;
        if (p) {
          updateState(st => {
            const target = (st.penitip || []).find(x => x.id === p.id);
            if (target) {
              target.nama = nama;
              target.kode = makeInitials(nama);
              target.no_hp = hp;
              target.catatan = cat;
            }
          });
          toast(`Data penitip "${nama}" diperbarui.`);
          panelMode = 'detail';
          if (window.innerWidth < 768) {
            openMobileDetail(p.id);
          }
        } else {
          const slug = nama.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || ('penitip-' + Date.now());
          const newId = slug + '-' + Math.floor(Math.random() * 90 + 10);
          updateState(st => {
            st.penitip = st.penitip || [];
            st.penitip.push({
              id: newId,
              kode: makeInitials(nama),
              nama,
              no_hp: hp,
              catatan: cat,
              aktif: true,
              terjual_siklus: 0,
              hak_siklus: 0,
              dibuat_pada: '2026-11-24 11:00:00'
            });
          });
          selectedId = newId;
          panelMode = 'detail';
          toast(`Penitip "${nama}" ditambahkan.`);
          if (window.innerWidth < 768) {
            closeMobilePanel();
          }
        }
        updateKpis();
        renderList();
      };
      return;
    }

    const p = selectedId ? (s.penitip || []).find(x => x.id === selectedId) : null;
    if (!p) {
      panelTitle.textContent = 'Detail Penitip';
      closeBtn.hidden = true;
      panelBody.innerHTML = `<div class="panel-empty-state">
        <div class="panel-empty-icon">${svgIcon('penitip', 28)}</div>
        <h3 style="margin:0 0 6px;font-size:15px;font-weight:700;color:var(--navy);">Pilih penitip</h3>
        <p class="panel-empty-text">untuk melihat detailnya.</p>
      </div>`;
      return;
    }

    panelTitle.textContent = 'Detail Penitip';
    closeBtn.hidden = false;
    const lg = computeLedger(p);

    // Tab content paginated 2 rows/page on detail panel
    let tabRows = [];
    if (activeTab === 'barang') tabRows = lg.linked;
    else if (activeTab === 'penjualan') tabRows = lg.sales;
    else tabRows = lg.deposits;

    const subPerPage = 2;
    const subPages = Math.max(1, Math.ceil(tabRows.length / subPerPage));
    subPage = Math.max(1, Math.min(subPage, subPages));
    const subSlice = tabRows.slice((subPage - 1) * subPerPage, subPage * subPerPage);

    let tabListHtml = '';
    if (!tabRows.length) {
      tabListHtml = `<p style="text-align:center;color:var(--grey);font-size:13px;padding:24px 0;">Belum ada data pada tab ini.</p>`;
    } else if (activeTab === 'barang') {
      tabListHtml = subSlice.map(b => `
        <div class="ledger-row">
          <div style="display:flex;align-items:center;gap:10px;min-width:0;">
            <img src="${escHtml(b.gambar)}" alt="${escHtml(b.nama)}" class="thumb-md">
            <div>
              <div class="ledger-date">${escHtml(b.nama)}</div>
              <div class="ledger-note">Komisi ${rupiah(b.komisi_kantin)} · Stok ${b.stok}</div>
            </div>
          </div>
          <div class="ledger-amt" style="color:var(--blue);">${rupiah(b.harga)}</div>
        </div>`).join('');
    } else if (activeTab === 'penjualan') {
      tabListHtml = subSlice.map(sl => `
        <div class="ledger-row">
          <div>
            <div class="ledger-date">${escHtml(sl.barang)} (${sl.qty} item)</div>
            <div class="ledger-note">${escHtml(sl.kode)} · ${escHtml(formatShortDate(sl.tanggal))} ${escHtml(sl.waktu)}</div>
          </div>
          <div class="ledger-amt">${rupiah(sl.hak)}</div>
        </div>`).join('');
    } else {
      tabListHtml = subSlice.map(st => `
        <div class="ledger-row">
          <div style="display:flex;align-items:center;gap:10px;min-width:0;">
            <span class="notif-circle notif-circle-success" style="width:34px;height:34px;" aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
            </span>
            <div>
              <div class="ledger-date">${escHtml(formatShortDate(st.tanggal))}</div>
              <div class="ledger-note">${escHtml(st.catatan || 'Setoran')}</div>
            </div>
          </div>
          <div class="ledger-amt">${rupiah(st.jumlah)}</div>
        </div>`).join('');
    }

    panelBody.innerHTML = `
      <div class="penitip-header-card">
        <span class="avatar-initials avatar-initials-lg">${escHtml(p.kode || makeInitials(p.nama))}</span>
        <div style="flex:1;min-width:0;">
          <div style="font-size:17px;font-weight:700;color:var(--navy);">${escHtml(p.nama)}</div>
          <div style="font-size:12.5px;color:var(--grey);">${escHtml(p.no_hp)}</div>
        </div>
        <button type="button" class="icon-btn" id="penEditBtn" aria-label="Edit data ${escHtml(p.nama)}" title="Edit Penitip">${svgIcon('edit', 16)}</button>
        <span class="badge badge-success">Aktif</span>
      </div>

      <div class="mini-stats-2x2">
        <div class="mini-stat-box is-blue">
          <div class="mini-stat-lbl">Total Terjual</div>
          <div class="mini-stat-val">${lg.terjual} item</div>
        </div>
        <div class="mini-stat-box is-green">
          <div class="mini-stat-lbl">Hak Penitip</div>
          <div class="mini-stat-val">${rupiah(lg.hak)}</div>
        </div>
        <div class="mini-stat-box" style="background:var(--purple-bg);">
          <div class="mini-stat-lbl">Sudah Dibayar</div>
          <div class="mini-stat-val">${rupiah(lg.paid)}</div>
        </div>
        <div class="mini-stat-box is-amber">
          <div class="mini-stat-lbl">Sisa Dibayar</div>
          <div class="mini-stat-val">${rupiah(lg.sisa)}</div>
        </div>
      </div>

      <div class="segmented-tabs" role="tablist" aria-label="Detail aktivitas penitip">
        <button type="button" role="tab" class="seg-tab-btn${activeTab === 'barang' ? ' is-active' : ''}" data-pen-tab="barang" aria-selected="${activeTab === 'barang'}">Barang</button>
        <button type="button" role="tab" class="seg-tab-btn${activeTab === 'penjualan' ? ' is-active' : ''}" data-pen-tab="penjualan" aria-selected="${activeTab === 'penjualan'}">Penjualan</button>
        <button type="button" role="tab" class="seg-tab-btn${activeTab === 'setoran' ? ' is-active' : ''}" data-pen-tab="setoran" aria-selected="${activeTab === 'setoran'}">Setoran</button>
      </div>

      <div style="flex:1;">
        ${tabListHtml}
        ${renderPagerHtml(tabRows.length, subPage, subPerPage, 'penSubList')}
      </div>

      <div class="panel-footer-actions">
        <button type="button" class="btn btn-primary btn-lg btn-full" id="openCatatSetoranBtn" ${lg.sisa <= 0 ? 'disabled' : ''}>
          ${svgIcon('plus', 17)}
          <span>Catat Setoran</span>
        </button>
      </div>`;

    document.getElementById('penEditBtn').onclick = () => openEditMode(p.id);
    document.getElementById('openCatatSetoranBtn').onclick = () => openSetoranModal(p, lg);
  }

  function openMobileDetail(id) {
    selectedId = id;
    panelMode = 'detail';
    subPage = 1;
    renderPanel();
    workspace.classList.add('mobile-showing-panel');
    setMobileSubheader('Detail Penitip', closeMobilePanel, `<button type="button" class="mobile-header-btn" id="mobEditPenHeaderBtn" aria-label="Edit penitip">${svgIcon('edit', 20)}</button>`);
    const mobEdit = document.getElementById('mobEditPenHeaderBtn');
    if (mobEdit) mobEdit.onclick = () => openEditMode(id);
  }

  function openAddMode(openMob) {
    panelMode = 'add';
    renderPanel();
    if (openMob && window.innerWidth < 768) {
      workspace.classList.add('mobile-showing-panel');
      setMobileSubheader('Tambah Penitip', closeMobilePanel);
    }
  }

  function openEditMode(id) {
    selectedId = id;
    panelMode = 'edit';
    renderPanel();
    if (window.innerWidth < 768) {
      workspace.classList.add('mobile-showing-panel');
      setMobileSubheader('Edit Penitip', () => openMobileDetail(id));
    }
  }

  function closeMobilePanel() {
    workspace.classList.remove('mobile-showing-panel');
    setMobileSubheader(null);
  }

  function handleCancelPanel() {
    if (panelMode === 'edit') {
      panelMode = 'detail';
      if (window.innerWidth < 768) openMobileDetail(selectedId);
      else renderPanel();
    } else if (window.innerWidth < 768) {
      closeMobilePanel();
    } else {
      panelMode = 'detail';
      renderList();
    }
  }

  function updateSetoranMath() {
    if (!activeSetoranPen) return;
    const lg = computeLedger(activeSetoranPen);
    const val = parseInt(setoranAmtEl.value || '0', 10) || 0;
    const after = Math.max(0, lg.sisa - val);
    setoranMathBox.innerHTML = `
      <div class="preview-math-row"><span>Sisa sekarang</span><strong>${rupiah(lg.sisa)}</strong></div>
      <div class="preview-math-row"><span>Disetor</span><strong style="color:var(--green-text);">− ${rupiah(val)}</strong></div>
      <div class="preview-math-divider"></div>
      <div class="preview-math-total"><span>Sisa setelah setoran</span><span>${rupiah(after)}</span></div>`;
  }

  function openSetoranModal(p, lg) {
    activeSetoranPen = p;
    setoranBanner.innerHTML = `
      <span class="avatar-initials">${escHtml(p.kode || makeInitials(p.nama))}</span>
      <div>
        <div style="font-size:14px;font-weight:700;color:var(--navy);">${escHtml(p.nama)}</div>
        <div style="font-size:12px;color:var(--grey);">Sisa yang harus dibayar: ${rupiah(lg.sisa)}</div>
      </div>`;
    setoranAmtEl.value = String(lg.sisa);
    setoranDateEl.value = (getState().pengaturan && getState().pengaturan.tanggal_demo) || '2026-11-24';
    setoranNoteEl.value = '';
    setoranErr.hidden = true;
    updateSetoranMath();
    setoranBackdrop.hidden = false;
    setoranAmtEl.focus();
  }

  function closeSetoranModal() {
    setoranBackdrop.hidden = true;
    activeSetoranPen = null;
  }

  document.getElementById('setoranModalClose').onclick = closeSetoranModal;
  document.getElementById('setoranCancelBtn').onclick = closeSetoranModal;
  setoranBackdrop.addEventListener('click', e => { if (e.target === setoranBackdrop) closeSetoranModal(); });
  document.getElementById('setoranFillFullBtn').onclick = () => {
    if (!activeSetoranPen) return;
    setoranAmtEl.value = String(computeLedger(activeSetoranPen).sisa);
    updateSetoranMath();
  };
  setoranAmtEl.addEventListener('input', updateSetoranMath);

  document.getElementById('setoranForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!activeSetoranPen) return;
    const lg = computeLedger(activeSetoranPen);
    const val = parseInt(setoranAmtEl.value || '0', 10) || 0;
    const tgl = setoranDateEl.value;
    const note = setoranNoteEl.value.trim() || 'Setoran mingguan';

    if (val <= 0) {
      setoranErr.textContent = 'Jumlah setoran harus lebih dari Rp 0.';
      setoranErr.hidden = false;
      return;
    }
    if (val > lg.sisa) {
      setoranErr.textContent = `Jumlah setoran tidak boleh melebihi sisa (${rupiah(lg.sisa)}).`;
      setoranErr.hidden = false;
      return;
    }
    if (!tgl) {
      setoranErr.textContent = 'Tanggal setoran wajib diisi.';
      setoranErr.hidden = false;
      return;
    }
    setoranErr.hidden = true;
    const penName = activeSetoranPen.nama;
    const penId = activeSetoranPen.id;
    updateState(st => {
      st.setoran = st.setoran || [];
      st.setoran.unshift({
        id: st.setoran.length + 1,
        penitip_id: penId,
        tanggal: tgl,
        jumlah: val,
        catatan: note
      });
    });
    closeSetoranModal();
    activeTab = 'setoran';
    subPage = 1;
    toast(`Setoran ${rupiah(val)} untuk ${penName} dicatat.`);
    updateKpis();
    renderList();
  });

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-open-pen-add]')) {
      openAddMode(true);
      return;
    }
    const tabBtn = e.target.closest('.seg-tab-btn[data-pen-tab]');
    if (tabBtn) {
      activeTab = tabBtn.getAttribute('data-pen-tab');
      subPage = 1;
      renderPanel();
      return;
    }
    const pgBtn = e.target.closest('.pg-btn[data-page-target]');
    if (pgBtn && !pgBtn.disabled) {
      const pager = pgBtn.closest('[data-pager]');
      const target = parseInt(pgBtn.getAttribute('data-page-target'), 10);
      if (pager && pager.getAttribute('data-pager') === 'penSubList') {
        subPage = target;
        renderPanel();
      } else {
        page = target;
        renderList();
      }
      return;
    }
    const row = e.target.closest('[data-pen-id]');
    if (row) {
      const id = row.getAttribute('data-pen-id');
      userClosedDetail = false;
      panelMode = 'detail';
      activeTab = 'setoran';
      subPage = 1;
      if (window.innerWidth < 768) {
        openMobileDetail(id);
      } else {
        selectedId = id;
        renderList();
      }
    }
  });

  closeBtn.addEventListener('click', function () {
    userClosedDetail = true;
    panelMode = 'detail';
    selectedId = '';
    renderList();
  });

  function syncSearch(val) {
    searchQuery = val;
    if (searchDesk) searchDesk.value = val;
    if (searchMob) searchMob.value = val;
    page = 1;
    renderList();
  }
  if (searchDesk) searchDesk.addEventListener('input', e => syncSearch(e.target.value));
  if (searchMob)  searchMob.addEventListener('input', e => syncSearch(e.target.value));

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !setoranBackdrop.hidden) closeSetoranModal();
  });

  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768 && workspace.classList.contains('mobile-showing-panel')) {
      closeMobilePanel();
      renderList();
    }
  });

  updateKpis();
  renderList();
});
</script>
<?php admin_page_end('penitip'); ?>
