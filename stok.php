<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);
$todayLabel = tanggal_id($bundle['pengaturan']['tanggal_demo'] ?? DEMO_TODAY);
$initFilter = $_GET['filter'] ?? 'all';
$initRestock = $_GET['restock'] ?? '';

admin_page_start('Stok', 'stok', [
    'seed_bundle' => $bundle,
    'mobile_right' => 'bell',
]);

render_page_header(
    'Stok',
    'Pantau persediaan, restock, dan koreksi stok.',
    $todayLabel
);
?>

<div class="split-workspace" id="stokWorkspace">
  <div class="split-main-col">
    <!-- 3 KPI Cards -->
    <section class="kpi-grid-3 stok-kpi-grid" aria-label="Ringkasan Stok">
      <article class="kpi-card kpi-compact kpi-blue">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('barang', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Total Barang</p>
          <p class="kpi-value" id="stkKpiTotal">0</p>
        </div>
      </article>
      <article class="kpi-card kpi-compact kpi-amber">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('alert-triangle', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Menipis</p>
          <p class="kpi-value" id="stkKpiLow">0</p>
        </div>
      </article>
      <article class="kpi-card kpi-compact kpi-red">
        <span class="kpi-icon-circle" aria-hidden="true"><?= icon('x', 22) ?></span>
        <div class="kpi-body">
          <p class="kpi-label">Habis</p>
          <p class="kpi-value" id="stkKpiOut">0</p>
        </div>
      </article>
    </section>

    <section class="card" aria-label="Daftar Stok Barang">
      <div class="filter-toolbar">
        <div class="search-box mobile-hide">
          <span class="search-box-icon"><?= icon('search', 18) ?></span>
          <label for="stkSearchInput" class="sr-only">Cari barang</label>
          <input type="search" id="stkSearchInput" class="search-input" placeholder="Cari barang...">
        </div>
        <div class="chip-group" role="group" aria-label="Filter status stok" id="stkFilterChips">
          <button type="button" class="chip<?= $initFilter === 'all' ? ' is-active' : '' ?>" data-stk-filter="all">Semua</button>
          <button type="button" class="chip<?= $initFilter === 'menipis' ? ' is-active' : '' ?>" data-stk-filter="menipis">Menipis</button>
          <button type="button" class="chip<?= $initFilter === 'aman' ? ' is-active' : '' ?>" data-stk-filter="aman">Aman</button>
          <button type="button" class="chip<?= $initFilter === 'habis' ? ' is-active' : '' ?>" data-stk-filter="habis">Habis</button>
        </div>
      </div>

      <div id="stkListContainer"></div>
    </section>
  </div>

  <!-- Right Side Panel: Restock / Koreksi Stok -->
  <aside class="split-side-panel" id="stkPanel" aria-label="Aksi Stok">
    <div class="panel-head">
      <h2 class="panel-title" id="stkPanelTitle">Restock Barang</h2>
      <button type="button" class="panel-close-btn" id="stkPanelCloseBtn" aria-label="Tutup panel">
        <?= icon('x', 20) ?>
      </button>
    </div>
    <div id="stkPanelBody" style="display:flex;flex-direction:column;flex:1;"></div>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, updateState, renderPagerHtml, stockStatus, stockBadge, escHtml, toast, setMobileSubheader, svgIcon } = window.KKApp;

  let statusFilter = <?= json_encode(in_array($initFilter, ['all', 'menipis', 'aman', 'habis'], true) ? $initFilter : 'all') ?>;
  let searchQuery = '';
  let page = 1;
  let activeId = <?= json_encode($initRestock) ?>;
  let actionMode = 'restock'; // 'restock' | 'koreksi'
  let restockQty = 20;
  let restockNote = '';
  let koreksiActual = 0;
  let koreksiReason = 'Hilang';
  let koreksiNote = '';

  const workspace = document.getElementById('stokWorkspace');
  const listContainer = document.getElementById('stkListContainer');
  const panelTitle = document.getElementById('stkPanelTitle');
  const panelBody = document.getElementById('stkPanelBody');
  const closeBtn = document.getElementById('stkPanelCloseBtn');
  const searchInput = document.getElementById('stkSearchInput');

  function getSortedFiltered() {
    const s = getState();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const q = searchQuery.trim().toLowerCase();
    return (s.barang || [])
      .filter(b => b.aktif !== false)
      .slice()
      .sort((a, b) => (a.stok || 0) - (b.stok || 0))
      .filter(b => {
        const st = stockStatus(b.stok, threshold).toLowerCase();
        if (statusFilter !== 'all' && st !== statusFilter) return false;
        if (q && !b.nama.toLowerCase().includes(q)) return false;
        return true;
      });
  }

  function updateKpis() {
    const s = getState();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const active = (s.barang || []).filter(b => b.aktif !== false);
    const outCnt = active.filter(b => (b.stok || 0) <= 0).length;
    const lowCnt = active.filter(b => (b.stok || 0) > 0 && (b.stok || 0) <= threshold).length;
    document.getElementById('stkKpiTotal').textContent = String(active.length);
    document.getElementById('stkKpiLow').textContent = String(lowCnt);
    document.getElementById('stkKpiOut').textContent = String(outCnt);
  }

  function renderList() {
    const s = getState();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const list = getSortedFiltered();
    const perPage = 10;

    if (!list.length) {
      listContainer.innerHTML = `<div class="empty-state-box">
        <div class="empty-state">
          <div class="empty-state-icon">${svgIcon('barang', 28)}</div>
          <h3 class="empty-state-title">Tidak ada data stok</h3>
          <p class="empty-state-desc">Tidak ada barang yang sesuai filter status stok.</p>
        </div>
      </div>`;
      if (window.innerWidth >= 768) {
        activeId = '';
        renderPanel();
      }
      return;
    }

    if (window.innerWidth >= 768 && (!activeId || !list.some(b => b.id === activeId))) {
      const pref = list.find(b => b.id === 'roti-cokelat') || list[0];
      activeId = pref ? pref.id : '';
      if (pref) koreksiActual = Math.max(0, (pref.stok || 0) - 1);
    }

    const totalPages = Math.max(1, Math.ceil(list.length / perPage));
    page = Math.max(1, Math.min(page, totalPages));
    const slice = list.slice((page - 1) * perPage, page * perPage);

    const deskRows = slice.map(p => {
      const st = stockStatus(p.stok, threshold);
      const ref = Math.max(20, p.stok_ref || 20, p.stok || 0);
      const pct = Math.min(100, Math.round(((p.stok || 0) / ref) * 100));
      const fillCls = st === 'Habis' ? 'progress-fill-red' : (st === 'Menipis' ? 'progress-fill-amber' : 'progress-fill-green');
      const selCls = p.id === activeId ? ' is-selected' : '';
      return `<tr class="${selCls}">
        <td>
          <div class="product-cell">
            <img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-md">
            <span class="product-cell-name">${escHtml(p.nama)}</span>
          </div>
        </td>
        <td><strong>${p.stok} pcs</strong></td>
        <td>
          <div class="stock-level-cell">
            <div class="progress-track"><div class="progress-fill ${fillCls}" style="width:${pct}%"></div></div>
          </div>
        </td>
        <td>${stockBadge(st)}</td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;">
            <button type="button" class="icon-btn icon-btn-outline" data-stk-koreksi="${escHtml(p.id)}" aria-label="Koreksi stok ${escHtml(p.nama)}" title="Koreksi Stok">${svgIcon('edit', 16)}</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-stk-restock="${escHtml(p.id)}">Restock</button>
          </div>
        </td>
      </tr>`;
    }).join('');

    const mobRows = slice.map(p => {
      const st = stockStatus(p.stok, threshold);
      const ref = Math.max(20, p.stok_ref || 20, p.stok || 0);
      const pct = Math.min(100, Math.round(((p.stok || 0) / ref) * 100));
      const fillCls = st === 'Habis' ? 'progress-fill-red' : (st === 'Menipis' ? 'progress-fill-amber' : 'progress-fill-green');
      return `<div class="mobile-row-item" style="flex-direction:column;align-items:stretch;gap:10px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
          <div class="mobile-row-left">
            <img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-lg">
            <div class="mobile-row-meta">
              <div class="mobile-row-title">${escHtml(p.nama)}</div>
              <div class="mobile-row-sub" style="display:flex;align-items:center;gap:6px;margin-top:3px;">
                <strong style="color:var(--navy);">${p.stok} pcs</strong>
                ${stockBadge(st)}
              </div>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <button type="button" class="icon-btn icon-btn-outline" data-stk-koreksi="${escHtml(p.id)}" aria-label="Koreksi stok ${escHtml(p.nama)}">${svgIcon('edit', 15)}</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-stk-restock="${escHtml(p.id)}" style="height:32px;padding:0 12px;">Restock</button>
          </div>
        </div>
        <div class="progress-track"><div class="progress-fill ${fillCls}" style="width:${pct}%"></div></div>
      </div>`;
    }).join('');

    listContainer.innerHTML = `
      <div class="table-wrap desktop-only-table">
        <table class="data-table">
          <thead><tr><th>Barang</th><th>Stok</th><th>Level</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>${deskRows}</tbody>
        </table>
      </div>
      <div class="mobile-card-list">${mobRows}</div>
      ${renderPagerHtml(list.length, page, perPage, 'stkList')}`;

    renderPanel();
  }

  function renderPanel() {
    const s = getState();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const prod = activeId ? (s.barang || []).find(b => b.id === activeId && b.aktif !== false) : null;

    if (!prod) {
      closeBtn.hidden = true;
      panelTitle.textContent = 'Kelola Stok';
      panelBody.innerHTML = `<div class="panel-empty-state">
        <div class="panel-empty-icon">${svgIcon('barang', 28)}</div>
        <p class="panel-empty-text">Pilih barang lalu tekan Restock atau Koreksi.</p>
      </div>`;
      return;
    }

    closeBtn.hidden = false;
    const st = stockStatus(prod.stok, threshold);

    if (actionMode === 'koreksi') {
      panelTitle.textContent = 'Koreksi Stok';
      const diff = koreksiActual - prod.stok;
      const diffLabel = diff > 0 ? `+${diff} pcs` : (diff < 0 ? `−${Math.abs(diff)} pcs` : '0 pcs');
      const reasons = ['Salah hitung', 'Rusak', 'Hilang', 'Lainnya'];
      panelBody.innerHTML = `
        <div class="card" style="padding:14px;display:flex;align-items:center;gap:12px;margin-bottom:18px;">
          <img src="${escHtml(prod.gambar)}" alt="${escHtml(prod.nama)}" class="thumb-xl">
          <div style="flex:1;min-width:0;">
            <div style="font-size:15px;font-weight:700;color:var(--navy);">${escHtml(prod.nama)}</div>
            <div style="font-size:12.5px;color:var(--grey);">Stok tercatat: ${prod.stok} pcs</div>
          </div>
          ${stockBadge(st)}
        </div>

        <div class="form-group">
          <label class="form-label" id="korValLbl">Stok Sebenarnya</label>
          <div class="stepper-box-card" aria-labelledby="korValLbl">
            <button type="button" class="stepper-btn" id="korMinus" aria-label="Kurangi stok sebenarnya" ${koreksiActual <= 0 ? 'disabled' : ''}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/></svg>
            </button>
            <div class="stepper-val-wrap" style="flex-direction:column;align-items:center;gap:2px;">
              <span class="stepper-val">${koreksiActual}</span>
              <span class="stepper-unit">pcs</span>
            </div>
            <button type="button" class="stepper-btn stepper-btn-solid" id="korPlus" aria-label="Tambah stok sebenarnya">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            </button>
          </div>
        </div>

        <div class="info-preview-box info-preview-amber" style="margin-top:0;">
          <div>Selisih dengan catatan</div>
          <div class="diff-preview-row">
            <span class="diff-preview-delta">${diffLabel}</span>
            <span class="diff-preview-arrow">${prod.stok} → ${koreksiActual} pcs</span>
          </div>
        </div>

        <div class="form-group">
          <span class="form-label">Alasan (wajib)</span>
          <div class="chip-group" id="korReasonChips">
            ${reasons.map(r => `<button type="button" class="chip${koreksiReason === r ? ' is-active' : ''}" data-reason="${escHtml(r)}">${escHtml(r)}</button>`).join('')}
          </div>
        </div>

        <div class="form-group">
          <label for="korNoteInput" class="form-label">Catatan</label>
          <input type="text" id="korNoteInput" class="form-control" value="${escHtml(koreksiNote)}" placeholder="Contoh: barang hilang saat jam istirahat">
        </div>

        <div id="korError" class="form-error" role="alert" hidden></div>

        <div class="panel-footer-actions">
          <button type="button" class="btn btn-primary btn-lg btn-full" id="korSaveBtn">Simpan Koreksi</button>
        </div>`;

      document.getElementById('korMinus').onclick = () => { if (koreksiActual > 0) { koreksiActual--; renderPanel(); } };
      document.getElementById('korPlus').onclick = () => { koreksiActual++; renderPanel(); };
      document.getElementById('korReasonChips').onclick = e => {
        const c = e.target.closest('.chip[data-reason]');
        if (!c) return;
        koreksiReason = c.getAttribute('data-reason');
        renderPanel();
      };
      document.getElementById('korNoteInput').oninput = e => { koreksiNote = e.target.value; };
      document.getElementById('korSaveBtn').onclick = () => {
        const err = document.getElementById('korError');
        if (koreksiActual === prod.stok) {
          err.textContent = 'Stok sebenarnya sama dengan stok tercatat.';
          err.hidden = false;
          return;
        }
        if (!koreksiReason) {
          err.textContent = 'Pilih alasan koreksi.';
          err.hidden = false;
          return;
        }
        const delta = koreksiActual - prod.stok;
        updateState(stObj => {
          const item = (stObj.barang || []).find(b => b.id === prod.id);
          if (item) {
            item.stok = koreksiActual;
            item.stok_ref = Math.max(item.stok_ref || 20, koreksiActual);
          }
          stObj.mutasi_stok = stObj.mutasi_stok || [];
          stObj.mutasi_stok.unshift({
            id: stObj.mutasi_stok.length + 1,
            barang_id: prod.id,
            jenis: 'koreksi',
            perubahan: delta,
            stok_sebelum: prod.stok,
            stok_sesudah: koreksiActual,
            alasan: koreksiReason,
            catatan: koreksiNote.trim(),
            dibuat_pada: '2026-11-24 11:00:00'
          });
        });
        toast(`Stok ${prod.nama} dikoreksi ke ${koreksiActual} pcs (${koreksiReason}).`);
        koreksiNote = '';
        if (window.innerWidth < 768) closeMobilePanel();
        updateKpis();
        renderList();
      };
    } else {
      panelTitle.textContent = 'Restock Barang';
      const presets = [10, 20, 50, 100];
      const after = prod.stok + restockQty;
      panelBody.innerHTML = `
        <div class="card" style="padding:14px;display:flex;align-items:center;gap:12px;margin-bottom:18px;">
          <img src="${escHtml(prod.gambar)}" alt="${escHtml(prod.nama)}" class="thumb-xl">
          <div style="flex:1;min-width:0;">
            <div style="font-size:15px;font-weight:700;color:var(--navy);">${escHtml(prod.nama)}</div>
            <div style="font-size:12.5px;color:var(--grey);">Stok saat ini: ${prod.stok} pcs</div>
          </div>
          ${stockBadge(st)}
        </div>

        <div class="form-group">
          <span class="form-label">Jumlah Restock</span>
          <div class="stepper-box-card">
            <button type="button" class="stepper-btn" id="rstMinus" aria-label="Kurangi jumlah restock" ${restockQty <= 1 ? 'disabled' : ''}>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/></svg>
            </button>
            <div class="stepper-val-wrap" style="flex-direction:column;align-items:center;gap:2px;">
              <span class="stepper-val">${restockQty}</span>
              <span class="stepper-unit">pcs</span>
            </div>
            <button type="button" class="stepper-btn stepper-btn-solid" id="rstPlus" aria-label="Tambah jumlah restock">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            </button>
          </div>
          <div class="chip-group" id="rstPresetChips">
            ${presets.map(n => `<button type="button" class="chip${restockQty === n ? ' is-active' : ''}" data-preset="${n}">+${n}</button>`).join('')}
          </div>
        </div>

        <div class="form-group">
          <label for="rstNoteInput" class="form-label">Catatan (opsional)</label>
          <input type="text" id="rstNoteInput" class="form-control" value="${escHtml(restockNote)}" placeholder="Contoh: belanja pasar pagi">
        </div>

        <div class="preview-math-box">
          <div class="preview-math-row"><span>Stok sekarang</span><strong>${prod.stok} pcs</strong></div>
          <div class="preview-math-row"><span>Ditambah</span><strong style="color:var(--green-text);">+${restockQty} pcs</strong></div>
          <div class="preview-math-divider"></div>
          <div class="preview-math-total"><span>Stok setelah restock</span><span>${after} pcs</span></div>
        </div>

        <div class="panel-footer-actions">
          <button type="button" class="btn btn-primary btn-lg btn-full" id="rstSaveBtn">Simpan Restock</button>
        </div>`;

      document.getElementById('rstMinus').onclick = () => { if (restockQty > 1) { restockQty--; renderPanel(); } };
      document.getElementById('rstPlus').onclick = () => { restockQty++; renderPanel(); };
      document.getElementById('rstPresetChips').onclick = e => {
        const c = e.target.closest('.chip[data-preset]');
        if (!c) return;
        restockQty = parseInt(c.getAttribute('data-preset'), 10);
        renderPanel();
      };
      document.getElementById('rstNoteInput').oninput = e => { restockNote = e.target.value; };
      document.getElementById('rstSaveBtn').onclick = () => {
        const nextStok = prod.stok + restockQty;
        updateState(stObj => {
          const item = (stObj.barang || []).find(b => b.id === prod.id);
          if (item) {
            item.stok = nextStok;
            item.stok_ref = Math.max(item.stok_ref || 20, nextStok);
          }
          stObj.mutasi_stok = stObj.mutasi_stok || [];
          stObj.mutasi_stok.unshift({
            id: stObj.mutasi_stok.length + 1,
            barang_id: prod.id,
            jenis: 'restock',
            perubahan: restockQty,
            stok_sebelum: prod.stok,
            stok_sesudah: nextStok,
            alasan: null,
            catatan: restockNote.trim() || 'Restock barang',
            dibuat_pada: '2026-11-24 11:00:00'
          });
        });
        toast(`Stok ${prod.nama} bertambah +${restockQty} pcs.`);
        restockNote = '';
        if (window.innerWidth < 768) closeMobilePanel();
        updateKpis();
        renderList();
      };
    }
  }

  function openMobileAction(id, mode) {
    const prod = (getState().barang || []).find(b => b.id === id);
    if (!prod) return;
    activeId = id;
    actionMode = mode;
    if (mode === 'koreksi') {
      koreksiActual = Math.max(0, (prod.stok || 0) - 1);
    } else {
      restockQty = 20;
    }
    renderPanel();
    workspace.classList.add('mobile-showing-panel');
    setMobileSubheader(mode === 'koreksi' ? 'Koreksi Stok' : 'Restock Barang', closeMobilePanel);
  }

  function closeMobilePanel() {
    workspace.classList.remove('mobile-showing-panel');
    setMobileSubheader(null);
  }

  document.addEventListener('click', function (e) {
    const rsBtn = e.target.closest('[data-stk-restock]');
    if (rsBtn) {
      const id = rsBtn.getAttribute('data-stk-restock');
      if (window.innerWidth < 768) {
        openMobileAction(id, 'restock');
      } else {
        activeId = id;
        actionMode = 'restock';
        restockQty = 20;
        renderList();
      }
      return;
    }
    const krBtn = e.target.closest('[data-stk-koreksi]');
    if (krBtn) {
      const id = krBtn.getAttribute('data-stk-koreksi');
      const prod = (getState().barang || []).find(b => b.id === id);
      if (window.innerWidth < 768) {
        openMobileAction(id, 'koreksi');
      } else {
        activeId = id;
        actionMode = 'koreksi';
        koreksiActual = prod ? Math.max(0, (prod.stok || 0) - 1) : 0;
        renderList();
      }
      return;
    }
    const pgBtn = e.target.closest('.pg-btn[data-page-target]');
    if (pgBtn && !pgBtn.disabled) {
      page = parseInt(pgBtn.getAttribute('data-page-target'), 10);
      renderList();
    }
  });

  closeBtn.addEventListener('click', () => { activeId = ''; renderList(); });

  document.getElementById('stkFilterChips').addEventListener('click', function (e) {
    const c = e.target.closest('.chip[data-stk-filter]');
    if (!c) return;
    this.querySelectorAll('.chip').forEach(x => x.classList.remove('is-active'));
    c.classList.add('is-active');
    statusFilter = c.getAttribute('data-stk-filter');
    page = 1;
    renderList();
  });

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      searchQuery = this.value;
      page = 1;
      renderList();
    });
  }

  window.addEventListener('resize', function () {
    if (window.innerWidth >= 768 && workspace.classList.contains('mobile-showing-panel')) {
      closeMobilePanel();
    }
  });

  updateKpis();
  renderList();
  if (<?= json_encode($initRestock !== '') ?> && window.innerWidth < 768) {
    openMobileAction(<?= json_encode($initRestock) ?>, 'restock');
  }
});
</script>
<?php admin_page_end('stok'); ?>
