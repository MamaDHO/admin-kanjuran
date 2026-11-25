<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);

admin_page_start('Laporan', 'laporan', [
    'seed_bundle' => $bundle,
    'mobile_right' => 'bell',
]);

render_page_header(
    'Laporan',
    'Ringkasan pendapatan, penjualan, dan komisi per periode.'
);
?>

<!-- Filter Periode -->
<section class="filter-toolbar" aria-label="Filter Periode Laporan" style="margin-bottom:18px;">
  <div class="date-pill mobile-hide" id="lapDateRangeBox">
    <span class="date-pill-icon" style="color:var(--grey-icon);"><?= icon('calendar', 18) ?></span>
    <span id="lapRangeTextDesk">18 Nov 2026 - 24 Nov 2026</span>
  </div>

  <div class="chip-group" role="group" aria-label="Pilih rentang periode" id="lapPeriodChips">
    <button type="button" class="chip" data-period="today">Hari ini</button>
    <button type="button" class="chip is-active" data-period="7d">7 Hari</button>
    <button type="button" class="chip" data-period="30d">30 Hari</button>
    <button type="button" class="chip" data-period="month">Bulan ini</button>
  </div>

  <div class="mobile-only" style="width:100%;">
    <div class="date-pill" style="width:100%;justify-content:flex-start;">
      <span class="date-pill-icon" style="color:var(--grey-icon);"><?= icon('calendar', 18) ?></span>
      <span id="lapRangeTextMob">18 Nov 2026 - 24 Nov 2026</span>
    </div>
  </div>
</section>

<!-- 4 KPI Desktop -->
<section class="kpi-grid-4 laporan-desk-kpis" aria-label="Ringkasan Keuangan">
  <article class="kpi-card kpi-compact kpi-blue">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('cart', 22) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Omzet (Total Pendapatan)</p>
      <p class="kpi-value" id="lapKpiOmzet">Rp 0</p>
      <div class="kpi-trend"><span class="kpi-trend-up"><?= icon('arrow-up', 12, '', 2.4) ?> 12% dari periode lalu</span></div>
    </div>
  </article>
  <article class="kpi-card kpi-compact kpi-green">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('wallet', 22) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Pendapatan Kantin</p>
      <p class="kpi-value" id="lapKpiKantin">Rp 0</p>
    </div>
  </article>
  <article class="kpi-card kpi-compact kpi-amber">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('penitip', 22) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Hak Penitip</p>
      <p class="kpi-value" id="lapKpiHak">Rp 0</p>
    </div>
  </article>
  <article class="kpi-card kpi-compact kpi-purple">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('barang', 22) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Barang Terjual</p>
      <p class="kpi-value" id="lapKpiItems">0 item</p>
      <div class="kpi-trend"><span class="kpi-trend-up"><?= icon('arrow-up', 12, '', 2.4) ?> 8% dari periode lalu</span></div>
    </div>
  </article>
</section>

<!-- Mobile KPI Layout (sesuai Mobile SVG 22 Laporan admin) -->
<section class="laporan-mob-omzet" aria-label="Ringkasan Pendapatan Mobile">
  <article class="card" style="padding:16px;margin-bottom:12px;">
    <p class="kpi-label" style="margin-bottom:4px;">Total Pendapatan (Omzet)</p>
    <p class="kpi-value" id="lapMobOmzet" style="font-size:24px;">Rp 0</p>
    <div class="kpi-trend"><span class="kpi-trend-up"><?= icon('arrow-up', 12, '', 2.4) ?> 12% dari periode sebelumnya</span></div>
  </article>
  <div class="kpi-grid-3" style="grid-template-columns:1fr 1fr;margin-bottom:14px;">
    <article class="kpi-card kpi-green" style="padding:14px;">
      <p class="kpi-label">Pendapatan Kantin</p>
      <p class="kpi-value" id="lapMobKantin" style="font-size:17px;">Rp 0</p>
    </article>
    <article class="kpi-card kpi-amber" style="padding:14px;">
      <p class="kpi-label">Hak Penitip</p>
      <p class="kpi-value" id="lapMobHak" style="font-size:17px;">Rp 0</p>
    </article>
  </div>
</section>

<!-- Grafik Pendapatan + Barang Terlaris (Desktop only per SPESIFIKASI.md) -->
<section class="dash-grid-2" style="margin-bottom:18px;">
  <article class="card">
    <div class="card-head">
      <h2 class="card-title">Grafik Pendapatan</h2>
    </div>
    <div class="chart-wrap" id="lapChartWrap"></div>
  </article>

  <article class="card laporan-top-items-desktop">
    <div class="card-head">
      <h2 class="card-title">Barang Terlaris</h2>
      <span class="card-meta-muted" id="lapTopSub">7 hari</span>
    </div>
    <div class="top-bar-list" id="lapTopBars"></div>
  </article>
</section>

<!-- Laporan per Barang + Rekap Penitip -->
<section class="laporan-split-bottom">
  <article class="card">
    <div class="card-head">
      <h2 class="card-title">Laporan per Barang</h2>
    </div>
    <div id="lapPerBarangWrap"></div>
  </article>

  <article class="card">
    <div class="card-head">
      <h2 class="card-title">Rekap Penitip</h2>
    </div>
    <div id="lapRekapPenitipWrap"></div>
    <div class="laporan-footnotes">
      <div>Komisi = bagian kantin dari barang titipan.</div>
      <div>Hak = bagian penitip (harga jual − komisi).</div>
    </div>
  </article>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, renderPagerHtml, escHtml, rupiah, svgIcon } = window.KKApp;

  let activePeriod = '7d';
  let prodPage = 1;

  const periodMeta = {
    today: { start: '2026-11-24', end: '2026-11-24', label: '24 Nov 2026', topSub: 'Hari ini' },
    '7d':  { start: '2026-11-18', end: '2026-11-24', label: '18 Nov 2026 - 24 Nov 2026', topSub: '7 hari' },
    '30d': { start: '2026-10-26', end: '2026-11-24', label: '26 Okt 2026 - 24 Nov 2026', topSub: '30 hari' },
    month: { start: '2026-11-01', end: '2026-11-24', label: '01 Nov 2026 - 24 Nov 2026', topSub: 'Bulan ini' }
  };

  function getOrdersInPeriod() {
    const meta = periodMeta[activePeriod] || periodMeta['7d'];
    return (getState().pesanan || []).filter(o =>
      o.status === 'lunas' && o.tanggal >= meta.start && o.tanggal <= meta.end
    );
  }

  function renderReport() {
    const s = getState();
    const meta = periodMeta[activePeriod] || periodMeta['7d'];
    const orders = getOrdersInPeriod();

    document.getElementById('lapRangeTextDesk').textContent = meta.label;
    document.getElementById('lapRangeTextMob').textContent = meta.label;
    document.getElementById('lapTopSub').textContent = meta.topSub;

    let omzet = 0, totalHak = 0, totalItems = 0;
    const byProd = {};
    const byPen = {};

    (s.penitip || []).forEach(p => {
      byPen[p.id] = { id: p.id, kode: p.kode, nama: p.nama, komisi: 0, hak: 0 };
    });

    orders.forEach(ord => {
      omzet += (ord.total || 0);
      totalHak += (ord.total_hak_penitip || 0);
      totalItems += (ord.jumlah_item || 0);
      (ord.items || []).forEach(it => {
        const bid = it.barang_id;
        if (!byProd[bid]) {
          byProd[bid] = {
            barang_id: bid,
            nama: it.nama,
            gambar: it.gambar,
            harga: it.harga_satuan,
            terjual: 0,
            pendapatan: 0
          };
        }
        byProd[bid].terjual += (it.qty || 0);
        byProd[bid].pendapatan += (it.subtotal || 0);

        if (it.penitip_id && byPen[it.penitip_id]) {
          byPen[it.penitip_id].komisi += (it.komisi_satuan || 0) * (it.qty || 0);
          byPen[it.penitip_id].hak += Math.max(0, (it.harga_satuan || 0) - (it.komisi_satuan || 0)) * (it.qty || 0);
        }
      });
    });

    const kantin = Math.max(0, omzet - totalHak);

    document.getElementById('lapKpiOmzet').textContent = rupiah(omzet);
    document.getElementById('lapKpiKantin').textContent = rupiah(kantin);
    document.getElementById('lapKpiHak').textContent = rupiah(totalHak);
    document.getElementById('lapKpiItems').textContent = `${totalItems} item`;

    document.getElementById('lapMobOmzet').textContent = rupiah(omzet);
    document.getElementById('lapMobKantin').textContent = rupiah(kantin);
    document.getElementById('lapMobHak').textContent = rupiah(totalHak);

    renderRevenueChart(orders);
    renderTopBars(Object.values(byProd));
    renderProductTable(Object.values(byProd));
    renderPenitipTable(Object.values(byPen));
  }

  function renderRevenueChart(orders) {
    const wrap = document.getElementById('lapChartWrap');
    const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    const sevenDays = ['2026-11-18', '2026-11-19', '2026-11-20', '2026-11-21', '2026-11-22', '2026-11-23', '2026-11-24'];

    let cols = [];
    if (activePeriod === 'today') {
      const sumToday = orders.reduce((a, o) => a + (o.total || 0), 0);
      cols = [{ label: 'Hari ini (24 Nov)', val: sumToday, isLast: true }];
    } else if (activePeriod === '7d') {
      cols = sevenDays.map((d, idx) => {
        const dt = new Date(d + 'T00:00:00');
        const val = orders.filter(o => o.tanggal === d).reduce((a, o) => a + (o.total || 0), 0);
        return { label: dayNames[dt.getDay()], val, isLast: idx === sevenDays.length - 1 };
      });
    } else {
      // Group into 4 weekly buckets in Nov for clean readable bar chart
      const buckets = [
        { label: 'Mgg 1', min: '2026-10-26', max: '2026-11-05' },
        { label: 'Mgg 2', min: '2026-11-06', max: '2026-11-12' },
        { label: 'Mgg 3', min: '2026-11-13', max: '2026-11-19' },
        { label: 'Mgg 4', min: '2026-11-20', max: '2026-11-24' }
      ];
      cols = buckets.map((b, i) => ({
        label: b.label,
        val: orders.filter(o => o.tanggal >= b.min && o.tanggal <= b.max).reduce((a, o) => a + (o.total || 0), 0),
        isLast: i === buckets.length - 1
      }));
    }

    const rawMax = Math.max(100000, ...cols.map(c => c.val));
    const step = rawMax <= 500000 ? 100000 : Math.ceil(rawMax / 5 / 50000) * 50000;
    const yMax = Math.max(500000, step * 5);

    const W = 620, H = 225, padL = 56, padR = 16, padT = 14, padB = 30;
    const plotW = W - padL - padR, plotH = H - padT - padB;
    let lines = '';
    for (let i = 0; i <= 5; i++) {
      const val = (yMax / 5) * i;
      const y = padT + plotH - plotH * (i / 5);
      const lbl = val === 0 ? '0' : (val >= 1000000 ? (val / 1000000).toFixed(1) + 'jt' : Math.round(val / 1000) + 'rb');
      lines += `<line x1="${padL}" y1="${y.toFixed(1)}" x2="${W - padR}" y2="${y.toFixed(1)}" stroke="#EAF0F6" stroke-width="1"/>`;
      lines += `<text x="${padL - 10}" y="${(y + 4).toFixed(1)}" font-size="11.5" fill="#5E718B" text-anchor="end">${lbl}</text>`;
    }

    const slot = plotW / cols.length;
    const barW = Math.min(54, slot * 0.56);
    let bars = '';
    cols.forEach((c, idx) => {
      const h = (c.val / yMax) * plotH;
      const x = padL + slot * idx + (slot - barW) / 2;
      const y = padT + plotH - h;
      const fill = c.isLast ? '#1677F8' : '#9CC4FC';
      bars += `<g class="bar-col-group">
        <title>${c.label}: ${rupiah(c.val)}</title>
        <rect class="bar-col-rect" x="${x.toFixed(1)}" y="${y.toFixed(1)}" width="${barW.toFixed(1)}" height="${Math.max(2, h).toFixed(1)}" rx="5" fill="${fill}"/>
        <text x="${(x + barW / 2).toFixed(1)}" y="${H - 8}" font-size="12" fill="#21324F" text-anchor="middle">${c.label}</text>
      </g>`;
    });
    wrap.innerHTML = `<svg class="bar-chart-svg" viewBox="0 0 ${W} ${H}" role="img" aria-label="Grafik Pendapatan">${lines}${bars}</svg>`;
  }

  function renderTopBars(prodList) {
    const el = document.getElementById('lapTopBars');
    const sorted = prodList.slice().sort((a, b) => b.terjual - a.terjual).slice(0, 5);
    if (!sorted.length) {
      el.innerHTML = `<p style="color:var(--grey);font-size:13px;text-align:center;padding:28px 0;">Belum ada penjualan pada periode ini.</p>`;
      return;
    }
    const maxQ = Math.max(1, sorted[0].terjual);
    el.innerHTML = sorted.map((p, idx) => {
      const pct = Math.round((p.terjual / maxQ) * 100);
      const barCol = idx === 0 ? '#1677F8' : '#7FB0FB';
      return `<div class="top-bar-item">
        <img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-sm">
        <div class="top-bar-content">
          <div class="top-bar-head">
            <span>${escHtml(p.nama)}</span>
            <span class="top-bar-count">${p.terjual} item</span>
          </div>
          <div class="progress-track" style="height:6px;">
            <div class="progress-fill" style="width:${pct}%;background:${barCol};"></div>
          </div>
        </div>
      </div>`;
    }).join('');
  }

  function renderProductTable(prodList) {
    const wrap = document.getElementById('lapPerBarangWrap');
    const sorted = prodList.slice().sort((a, b) => b.pendapatan - a.pendapatan);
    if (!sorted.length) {
      wrap.innerHTML = `<div class="empty-state" style="padding:30px 12px;"><h3 class="empty-state-title">Belum ada penjualan barang</h3></div>`;
      return;
    }
    const perPage = 5;
    const totalPages = Math.max(1, Math.ceil(sorted.length / perPage));
    prodPage = Math.max(1, Math.min(prodPage, totalPages));
    const slice = sorted.slice((prodPage - 1) * perPage, prodPage * perPage);

    const rows = slice.map(p => `<tr>
      <td>
        <div class="product-cell">
          <img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-sm">
          <span class="product-cell-name">${escHtml(p.nama)}</span>
        </div>
      </td>
      <td class="t-right">${p.terjual}</td>
      <td class="t-right mobile-hide">${rupiah(p.harga)}</td>
      <td class="t-right"><strong>${rupiah(p.pendapatan)}</strong></td>
    </tr>`).join('');

    wrap.innerHTML = `
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Barang</th>
              <th class="t-right">Terjual</th>
              <th class="t-right mobile-hide">Harga</th>
              <th class="t-right">Pendapatan</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>
      ${renderPagerHtml(sorted.length, prodPage, perPage, 'lapProdList')}`;
  }

  function renderPenitipTable(penList) {
    const wrap = document.getElementById('lapRekapPenitipWrap');
    if (!penList.length) {
      wrap.innerHTML = `<p style="color:var(--grey);font-size:13px;text-align:center;padding:24px 0;">Belum ada data penitip.</p>`;
      return;
    }
    const rows = penList.map(p => `<tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px;">
          <span class="avatar-initials" style="width:30px;height:30px;font-size:11px;">${escHtml(p.kode || 'PN')}</span>
          <span style="font-weight:600;">${escHtml(p.nama)}</span>
        </div>
      </td>
      <td class="t-right">${rupiah(p.komisi)}</td>
      <td class="t-right"><strong>${rupiah(p.hak)}</strong></td>
    </tr>`).join('');

    wrap.innerHTML = `
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Penitip</th>
              <th class="t-right">Komisi</th>
              <th class="t-right">Hak</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      </div>`;
  }

  document.getElementById('lapPeriodChips').addEventListener('click', function (e) {
    const chip = e.target.closest('.chip[data-period]');
    if (!chip) return;
    this.querySelectorAll('.chip').forEach(c => c.classList.remove('is-active'));
    chip.classList.add('is-active');
    activePeriod = chip.getAttribute('data-period');
    prodPage = 1;
    renderReport();
  });

  document.addEventListener('click', function (e) {
    const pgBtn = e.target.closest('.pg-btn[data-page-target]');
    if (pgBtn && !pgBtn.disabled) {
      prodPage = parseInt(pgBtn.getAttribute('data-page-target'), 10);
      renderReport();
    }
  });

  renderReport();
});
</script>
<?php admin_page_end('laporan'); ?>
