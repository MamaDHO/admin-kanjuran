<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);
$summary = build_dashboard_summary($bundle);
$threshold = (int)($bundle['pengaturan']['batas_stok_menipis'] ?? 5);
$todayStr = $bundle['pengaturan']['tanggal_demo'] ?? DEMO_TODAY;
$todayLabel = tanggal_id($todayStr);

$orders = $bundle['pesanan'] ?? [];
$ordersSlice = paginate_slice($orders, 1, PAGE_SIZE_DASHBOARD);

$products = $summary['active_products'];
$productsSlice = paginate_slice($products, 1, PAGE_SIZE_DASHBOARD);

admin_page_start('Dashboard', 'dashboard', [
    'seed_bundle' => $bundle,
    'mobile_right' => 'bell',
]);

render_page_header(
    'Dashboard Admin',
    'Selamat datang, Admin. Berikut ringkasan aktivitas kantin kejujuran hari ini.',
    $todayLabel
);
?>

<!-- Greeting Mobile -->
<section class="mobile-greeting" aria-label="Sapaan Admin">
  <div class="mobile-greeting-top">
    <span class="mobile-greeting-avatar" aria-hidden="true"><?= icon('user', 22) ?></span>
    <div>
      <h1 class="mobile-greeting-title">Halo, Admin</h1>
      <p class="mobile-greeting-sub">Berikut ringkasan aktivitas kantin hari ini.</p>
    </div>
  </div>
  <div class="mobile-date-bar">
    <?= icon('calendar', 18) ?>
    <span><?= esc($todayLabel) ?></span>
  </div>
</section>

<!-- 4 KPI Cards -->
<section class="kpi-grid-4" aria-label="Ringkasan KPI Hari Ini">
  <article class="kpi-card kpi-blue">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('cart', 26) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Total Penjualan</p>
      <p class="kpi-value" id="kpiTotalPenjualan"><?= esc(rupiah($summary['total_penjualan'])) ?></p>
      <div class="kpi-trend">
        <span class="kpi-trend-up"><?= icon('arrow-up', 14, '', 2.4) ?> 12%</span>
        <span class="kpi-trend-muted">dari kemarin</span>
      </div>
    </div>
  </article>

  <article class="kpi-card kpi-green">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('receipt', 26) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Jumlah Transaksi</p>
      <p class="kpi-value" id="kpiJumlahTransaksi"><?= (int)$summary['jumlah_transaksi'] ?></p>
      <div class="kpi-trend">
        <span class="kpi-trend-up"><?= icon('arrow-up', 14, '', 2.4) ?> 9%</span>
        <span class="kpi-trend-muted">dari kemarin</span>
      </div>
    </div>
  </article>

  <article class="kpi-card kpi-amber">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('barang', 26) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Barang Terjual</p>
      <p class="kpi-value" id="kpiBarangTerjual"><?= (int)$summary['barang_terjual'] ?></p>
      <div class="kpi-trend">
        <span class="kpi-trend-up"><?= icon('arrow-up', 14, '', 2.4) ?> 15%</span>
        <span class="kpi-trend-muted">dari kemarin</span>
      </div>
    </div>
  </article>

  <a href="stok.php?filter=menipis" class="kpi-card kpi-purple" aria-label="Lihat barang stok menipis">
    <span class="kpi-icon-circle" aria-hidden="true"><?= icon('stok', 26) ?></span>
    <div class="kpi-body">
      <p class="kpi-label">Stok Menipis</p>
      <p class="kpi-value" id="kpiStokMenipis"><?= (int)$summary['stok_menipis'] ?></p>
      <div class="kpi-trend">
        <span class="kpi-trend-warn">perlu restock</span>
      </div>
    </div>
  </a>
</section>

<!-- Bar Chart + Barang Terlaris -->
<section class="dash-grid-2">
  <article class="card">
    <div class="card-head">
      <div>
        <h2 class="card-title">Grafik Penjualan <span class="card-subtitle-inline">(7 Hari Terakhir)</span></h2>
        <div class="mobile-only" style="font-size:11px;color:var(--grey);margin-top:2px;">18–24 Nov 2026</div>
      </div>
      <label for="dashChartMetric" class="sr-only">Metrik Grafik Penjualan</label>
      <select id="dashChartMetric" class="select-compact">
        <option value="transaksi" selected>Jumlah Transaksi</option>
        <option value="pendapatan">Pendapatan</option>
        <option value="terjual">Barang Terjual</option>
      </select>
    </div>
    <div class="chart-wrap" id="dashChartWrap"></div>
  </article>

  <article class="card">
    <div class="card-head">
      <div class="card-title-group">
        <span style="color:var(--amber);display:inline-flex;"><?= icon('star', 19) ?></span>
        <h2 class="card-title">Barang Terlaris</h2>
      </div>
      <span class="card-meta-label">Hari ini</span>
    </div>
    <div class="top-items-list" id="dashTopItems">
      <?php if (!$summary['top_items']): ?>
        <?= render_empty_state('star', 'Belum ada penjualan hari ini', 'Data barang terlaris akan muncul setelah ada transaksi lunas.') ?>
      <?php else: ?>
        <?php foreach ($summary['top_items'] as $idx => $it): ?>
          <div class="top-item-row">
            <span class="top-item-rank"><?= $idx + 1 ?>.</span>
            <img src="<?= esc($it['gambar']) ?>" alt="<?= esc($it['nama']) ?>" class="thumb-lg">
            <div class="top-item-info">
              <div class="top-item-name"><?= esc($it['nama']) ?></div>
              <div class="top-item-sub">Terjual <?= (int)$it['qty'] ?> item</div>
            </div>
            <span class="top-item-qty"><?= (int)$it['qty'] ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </article>
</section>

<!-- Transaksi Terbaru + Stok Barang -->
<section class="dash-grid-2">
  <article class="card">
    <div class="card-head">
      <div class="card-title-group">
        <span style="color:var(--blue);display:inline-flex;"><?= icon('transaksi', 19) ?></span>
        <h2 class="card-title">Transaksi Terbaru</h2>
      </div>
      <a href="transaksi.php" class="card-link">Lihat Semua</a>
    </div>
    <div id="dashRecentTrxBody">
      <?php if (!$ordersSlice['items']): ?>
        <?= render_empty_state('transaksi', 'Belum ada transaksi', 'Transaksi pelanggan akan muncul di sini.') ?>
      <?php else: ?>
        <div class="table-wrap desktop-only-table">
          <table class="data-table">
            <thead>
              <tr>
                <th>No.</th>
                <th>Waktu</th>
                <th>Barang</th>
                <th class="t-center">Jumlah</th>
                <th>Total</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ordersSlice['items'] as $i => $ord): ?>
                <?php
                  $names = implode(', ', array_map(fn($x) => $x['nama'], $ord['items'] ?? []));
                ?>
                <tr class="is-clickable" data-href="transaksi.php?kode=<?= urlencode($ord['kode']) ?>">
                  <td><?= $i + 1 ?>.</td>
                  <td><?= esc($ord['waktu']) ?></td>
                  <td class="t-truncate"><?= esc($names) ?></td>
                  <td class="t-center"><?= (int)$ord['jumlah_item'] ?></td>
                  <td><?= esc(rupiah((int)$ord['total'])) ?></td>
                  <td><?= render_badge(order_status_label($ord['status']), order_badge_class($ord['status'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mobile-card-list">
          <?php foreach ($ordersSlice['items'] as $ord): ?>
            <?php
              $summaryTxt = implode(', ', array_map(fn($x) => $x['nama'] . ' ×' . $x['qty'], $ord['items'] ?? []));
            ?>
            <a href="transaksi.php?kode=<?= urlencode($ord['kode']) ?>" class="mobile-row-item">
              <div class="mobile-row-meta">
                <div class="mobile-row-title"><?= esc($ord['kode']) ?></div>
                <div class="mobile-row-sub"><?= esc($ord['waktu']) ?> · <?= esc($summaryTxt) ?></div>
              </div>
              <div class="mobile-row-right">
                <span class="mobile-row-amount"><?= esc(rupiah((int)$ord['total'])) ?></span>
                <?= render_badge(order_status_label($ord['status']), order_badge_class($ord['status'])) ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <?= render_pagination($ordersSlice['total'], $ordersSlice['page'], $ordersSlice['per_page'], 'dashTrx') ?>
      <?php endif; ?>
    </div>
  </article>

  <article class="card">
    <div class="card-head">
      <div class="card-title-group">
        <span style="color:var(--blue);display:inline-flex;"><?= icon('stok', 19) ?></span>
        <h2 class="card-title">Stok Barang</h2>
      </div>
      <a href="stok.php" class="card-link">Lihat Semua</a>
    </div>
    <div id="dashStockBody">
      <?php if (!$productsSlice['items']): ?>
        <?= render_empty_state('stok', 'Belum ada barang', 'Daftar stok barang akan tampil setelah barang ditambahkan.') ?>
      <?php else: ?>
        <div class="table-wrap desktop-only-table">
          <table class="data-table">
            <thead>
              <tr>
                <th>Barang</th>
                <th>Stok</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productsSlice['items'] as $p): ?>
                <?php $st = stock_status((int)$p['stok'], $threshold); ?>
                <tr class="is-clickable" data-href="stok.php?restock=<?= urlencode($p['id']) ?>">
                  <td>
                    <div class="product-cell">
                      <img src="<?= esc($p['gambar']) ?>" alt="<?= esc($p['nama']) ?>" class="thumb-sm">
                      <span class="product-cell-name"><?= esc($p['nama']) ?></span>
                    </div>
                  </td>
                  <td><?= (int)$p['stok'] ?></td>
                  <td><?= render_badge($st, stock_badge_class($st)) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mobile-card-list">
          <?php foreach ($productsSlice['items'] as $p): ?>
            <?php $st = stock_status((int)$p['stok'], $threshold); ?>
            <a href="stok.php?restock=<?= urlencode($p['id']) ?>" class="mobile-row-item">
              <div class="mobile-row-left">
                <img src="<?= esc($p['gambar']) ?>" alt="<?= esc($p['nama']) ?>" class="thumb-md">
                <div class="mobile-row-meta">
                  <div class="mobile-row-title"><?= esc($p['nama']) ?></div>
                  <div class="mobile-row-sub">Stok <?= (int)$p['stok'] ?> pcs</div>
                </div>
              </div>
              <div class="mobile-row-right">
                <?= render_badge($st, stock_badge_class($st)) ?>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
        <?= render_pagination($productsSlice['total'], $productsSlice['page'], $productsSlice['per_page'], 'dashStock') ?>
      <?php endif; ?>
    </div>
  </article>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, renderPagerHtml, stockStatus, stockBadge, orderBadge, escHtml, rupiah, svgIcon } = window.KKApp;

  let trxPage = 1;
  let stockPage = 1;
  const chartMetricEl = document.getElementById('dashChartMetric');
  const chartWrap = document.getElementById('dashChartWrap');
  const trxBody = document.getElementById('dashRecentTrxBody');
  const stockBody = document.getElementById('dashStockBody');

  function getToday() {
    return (getState().pengaturan && getState().pengaturan.tanggal_demo) || '2026-11-24';
  }

  function buildChartSeries() {
    const s = getState();
    const orders = s.pesanan || [];
    const today = getToday();
    const days = ['2026-11-18', '2026-11-19', '2026-11-20', '2026-11-21', '2026-11-22', '2026-11-23', '2026-11-24'];
    return days.map(d => {
      const dayOrd = orders.filter(o => o.tanggal === d);
      const dayPaid = dayOrd.filter(o => o.status === 'lunas');
      return {
        date: d,
        labelDesktop: parseInt(d.slice(8), 10) + ' Nov',
        labelMobile: String(parseInt(d.slice(8), 10)),
        transaksi: dayOrd.length,
        pendapatan: dayPaid.reduce((a, o) => a + (o.total || 0), 0),
        terjual: dayPaid.reduce((a, o) => a + (o.jumlah_item || 0), 0),
        isToday: d === today
      };
    });
  }

  function renderChart() {
    if (!chartWrap) return;
    const metric = chartMetricEl ? chartMetricEl.value : 'transaksi';
    const series = buildChartSeries();
    const rawMax = Math.max(1, ...series.map(d => d[metric]));
    let step, yMax;
    if (metric === 'pendapatan') {
      step = 100000;
      yMax = Math.max(500000, Math.ceil(rawMax / step) * step);
    } else if (metric === 'terjual') {
      step = 30;
      yMax = Math.max(150, Math.ceil(rawMax / step) * step);
    } else {
      step = 10;
      yMax = Math.max(50, Math.ceil(rawMax / step) * step);
    }
    const ticks = 5;
    const W = 580, H = 225, padL = 52, padR = 14, padT = 14, padB = 30;
    const plotW = W - padL - padR;
    const plotH = H - padT - padB;
    const isMob = window.innerWidth < 768;

    let linesHtml = '';
    for (let i = 0; i <= ticks; i++) {
      const val = (yMax / ticks) * i;
      const y = padT + plotH - (plotH * (i / ticks));
      const lbl = metric === 'pendapatan' ? (val === 0 ? '0' : Math.round(val / 1000) + 'rb') : String(Math.round(val));
      linesHtml += `<line x1="${padL}" y1="${y.toFixed(1)}" x2="${W - padR}" y2="${y.toFixed(1)}" stroke="#EAF0F6" stroke-width="1"/>`;
      linesHtml += `<text x="${padL - 10}" y="${(y + 4).toFixed(1)}" font-size="11.5" fill="#5E718B" text-anchor="end">${lbl}</text>`;
    }

    const slot = plotW / series.length;
    const barW = Math.min(44, slot * 0.56);
    let barsHtml = '';
    series.forEach((d, idx) => {
      const val = d[metric];
      const h = yMax > 0 ? (val / yMax) * plotH : 0;
      const x = padL + slot * idx + (slot - barW) / 2;
      const y = padT + plotH - h;
      const fill = d.isToday ? '#1677F8' : '#9CC4FC';
      const tip = metric === 'pendapatan' ? rupiah(val) : (val + (metric === 'terjual' ? ' item' : ' transaksi'));
      const xLabel = isMob ? d.labelMobile : d.labelDesktop;
      barsHtml += `<g class="bar-col-group">
        <title>${d.labelDesktop}: ${tip}</title>
        <rect class="bar-col-rect" x="${x.toFixed(1)}" y="${y.toFixed(1)}" width="${barW.toFixed(1)}" height="${Math.max(2, h).toFixed(1)}" rx="5" fill="${fill}"/>
        <text x="${(x + barW / 2).toFixed(1)}" y="${H - 8}" font-size="12" fill="#21324F" text-anchor="middle">${xLabel}</text>
      </g>`;
    });

    chartWrap.innerHTML = `<svg class="bar-chart-svg" viewBox="0 0 ${W} ${H}" role="img" aria-label="Grafik Penjualan 7 Hari Terakhir">${linesHtml}${barsHtml}</svg>`;
  }

  function renderKpisAndTop() {
    const s = getState();
    const today = getToday();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const orders = s.pesanan || [];
    const todayAll = orders.filter(o => o.tanggal === today);
    const todayPaid = todayAll.filter(o => o.status === 'lunas');

    document.getElementById('kpiTotalPenjualan').textContent = rupiah(todayPaid.reduce((a, o) => a + (o.total || 0), 0));
    document.getElementById('kpiJumlahTransaksi').textContent = String(todayAll.length);
    document.getElementById('kpiBarangTerjual').textContent = String(todayPaid.reduce((a, o) => a + (o.jumlah_item || 0), 0));
    const lowStock = (s.barang || []).filter(b => b.aktif !== false && (b.stok || 0) <= threshold).length;
    document.getElementById('kpiStokMenipis').textContent = String(lowStock);
  }

  function renderRecentTrx() {
    if (!trxBody) return;
    const orders = getState().pesanan || [];
    if (!orders.length) {
      trxBody.innerHTML = `<div class="empty-state"><div class="empty-state-icon">${svgIcon('receipt', 28)}</div><h3 class="empty-state-title">Belum ada transaksi</h3><p class="empty-state-desc">Transaksi pelanggan akan muncul di sini.</p></div>`;
      return;
    }
    const perPage = 5;
    const totalPages = Math.max(1, Math.ceil(orders.length / perPage));
    trxPage = Math.max(1, Math.min(trxPage, totalPages));
    const slice = orders.slice((trxPage - 1) * perPage, trxPage * perPage);

    const deskRows = slice.map((o, i) => {
      const rowNo = (trxPage - 1) * perPage + i + 1;
      const itemsText = (o.items || []).map(it => it.nama).join(', ');
      return `<tr class="is-clickable" data-href="transaksi.php?kode=${encodeURIComponent(o.kode)}">
        <td>${rowNo}.</td>
        <td>${escHtml(o.waktu)}</td>
        <td class="t-truncate">${escHtml(itemsText)}</td>
        <td class="t-center">${o.jumlah_item}</td>
        <td>${rupiah(o.total)}</td>
        <td>${orderBadge(o.status)}</td>
      </tr>`;
    }).join('');

    const mobRows = slice.map(o => {
      const sumText = (o.items || []).map(it => `${it.nama} ×${it.qty}`).join(', ');
      return `<a href="transaksi.php?kode=${encodeURIComponent(o.kode)}" class="mobile-row-item">
        <div class="mobile-row-meta">
          <div class="mobile-row-title">${escHtml(o.kode)}</div>
          <div class="mobile-row-sub">${escHtml(o.waktu)} · ${escHtml(sumText)}</div>
        </div>
        <div class="mobile-row-right">
          <span class="mobile-row-amount">${rupiah(o.total)}</span>
          ${orderBadge(o.status)}
        </div>
      </a>`;
    }).join('');

    trxBody.innerHTML = `
      <div class="table-wrap desktop-only-table">
        <table class="data-table">
          <thead><tr><th>No.</th><th>Waktu</th><th>Barang</th><th class="t-center">Jumlah</th><th>Total</th><th>Status</th></tr></thead>
          <tbody>${deskRows}</tbody>
        </table>
      </div>
      <div class="mobile-card-list">${mobRows}</div>
      ${renderPagerHtml(orders.length, trxPage, perPage, 'dashTrx')}`;
  }

  function renderStockTable() {
    if (!stockBody) return;
    const s = getState();
    const threshold = (s.pengaturan && s.pengaturan.batas_stok_menipis) || 5;
    const active = (s.barang || []).filter(b => b.aktif !== false);
    if (!active.length) {
      stockBody.innerHTML = `<div class="empty-state"><div class="empty-state-icon">${svgIcon('barang', 28)}</div><h3 class="empty-state-title">Belum ada barang</h3><p class="empty-state-desc">Daftar stok barang akan tampil setelah barang ditambahkan.</p></div>`;
      return;
    }
    const perPage = 5;
    const totalPages = Math.max(1, Math.ceil(active.length / perPage));
    stockPage = Math.max(1, Math.min(stockPage, totalPages));
    const slice = active.slice((stockPage - 1) * perPage, stockPage * perPage);

    const deskRows = slice.map(p => {
      const st = stockStatus(p.stok, threshold);
      return `<tr class="is-clickable" data-href="stok.php?restock=${encodeURIComponent(p.id)}">
        <td><div class="product-cell"><img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-sm"><span class="product-cell-name">${escHtml(p.nama)}</span></div></td>
        <td>${p.stok}</td>
        <td>${stockBadge(st)}</td>
      </tr>`;
    }).join('');

    const mobRows = slice.map(p => {
      const st = stockStatus(p.stok, threshold);
      return `<a href="stok.php?restock=${encodeURIComponent(p.id)}" class="mobile-row-item">
        <div class="mobile-row-left">
          <img src="${escHtml(p.gambar)}" alt="${escHtml(p.nama)}" class="thumb-md">
          <div class="mobile-row-meta">
            <div class="mobile-row-title">${escHtml(p.nama)}</div>
            <div class="mobile-row-sub">Stok ${p.stok} pcs</div>
          </div>
        </div>
        <div class="mobile-row-right">${stockBadge(st)}</div>
      </a>`;
    }).join('');

    stockBody.innerHTML = `
      <div class="table-wrap desktop-only-table">
        <table class="data-table">
          <thead><tr><th>Barang</th><th>Stok</th><th>Status</th></tr></thead>
          <tbody>${deskRows}</tbody>
        </table>
      </div>
      <div class="mobile-card-list">${mobRows}</div>
      ${renderPagerHtml(active.length, stockPage, perPage, 'dashStock')}`;
  }

  document.addEventListener('click', function (e) {
    const pgBtn = e.target.closest('.pg-btn[data-page-target]');
    if (pgBtn && !pgBtn.disabled) {
      const pager = pgBtn.closest('[data-pager]');
      if (!pager) return;
      const target = parseInt(pgBtn.getAttribute('data-page-target'), 10);
      if (pager.getAttribute('data-pager') === 'dashTrx') {
        trxPage = target;
        renderRecentTrx();
      } else if (pager.getAttribute('data-pager') === 'dashStock') {
        stockPage = target;
        renderStockTable();
      }
      return;
    }
    const row = e.target.closest('tr[data-href]');
    if (row) {
      window.location.href = row.getAttribute('data-href');
    }
  });

  if (chartMetricEl) chartMetricEl.addEventListener('change', renderChart);
  window.addEventListener('resize', renderChart);
  document.addEventListener('kk:state-changed', function () {
    renderKpisAndTop();
    renderRecentTrx();
    renderStockTable();
  });

  renderKpisAndTop();
  renderChart();
  renderRecentTrx();
  renderStockTable();
});
</script>
<?php admin_page_end('dashboard'); ?>
