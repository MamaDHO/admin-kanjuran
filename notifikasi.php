<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);

admin_page_start('Notifikasi', 'dashboard', [
    'seed_bundle' => $bundle,
    'mobile_back' => 'dashboard.php',
    'mobile_right' => 'none',
    'hide_bottom_nav' => true,
    'page_id' => 'notifikasi',
]);

render_page_header(
    'Notifikasi',
    'Peringatan stok dan status pembayaran.'
);
?>

<section class="card" aria-label="Daftar Notifikasi">
  <div class="filter-toolbar" style="justify-content:space-between;">
    <div class="chip-group" role="group" aria-label="Filter tipe notifikasi" id="notifFilterChips">
      <button type="button" class="chip is-active" data-notif-filter="all">Semua</button>
      <button type="button" class="chip" data-notif-filter="stok">Stok</button>
      <button type="button" class="chip" data-notif-filter="pembayaran">Pembayaran</button>
    </div>
    <button type="button" class="btn btn-outline-danger btn-sm" id="notifClearAllBtn">Hapus Semua</button>
  </div>

  <div id="notifPageList"></div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, updateState, renderPagerHtml, confirmDialog, toast, escHtml, svgIcon } = window.KKApp;

  let filter = 'all';
  let page = 1;

  const listWrap = document.getElementById('notifPageList');
  const clearAllBtn = document.getElementById('notifClearAllBtn');

  function renderNotifications() {
    const all = getState().notifikasi || [];
    clearAllBtn.disabled = all.length === 0;

    const filtered = all.filter(n => filter === 'all' || n.kategori === filter);
    if (!filtered.length) {
      listWrap.innerHTML = `<div class="empty-state-box">
        <div class="empty-state">
          <div class="empty-state-icon">${svgIcon('bell', 28)}</div>
          <h3 class="empty-state-title">Tidak ada notifikasi</h3>
          <p class="empty-state-desc">Peringatan stok dan status pembayaran akan muncul di sini.</p>
        </div>
      </div>`;
      return;
    }

    const perPage = window.innerWidth < 768 ? 5 : 8;
    const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
    page = Math.max(1, Math.min(page, totalPages));
    const slice = filtered.slice((page - 1) * perPage, page * perPage);

    const rowsHtml = slice.map(n => {
      const cls = n.status === 'warning' ? 'notif-circle-warning' : (n.status === 'danger' ? 'notif-circle-danger' : 'notif-circle-success');
      const ic = n.status === 'warning' ? 'alert-triangle' : (n.status === 'danger' ? 'x' : 'check-circle');
      return `<div class="notif-page-row${n.dibaca ? '' : ' is-unread'}" data-notif-id="${n.id}">
        <span class="notif-circle ${cls}" aria-hidden="true">${svgIcon(ic, 18)}</span>
        <div class="notif-page-body" role="button" tabindex="0" data-mark-read="${n.id}" aria-label="${escHtml(n.judul)}: ${escHtml(n.pesan)}">
          <div class="notif-page-title">${escHtml(n.judul)}</div>
          <div class="notif-page-msg">${escHtml(n.pesan)}</div>
        </div>
        <div class="notif-page-right">
          <span class="notif-item-time">${escHtml(n.waktu)}</span>
          <button type="button" class="icon-btn" data-del-notif="${n.id}" aria-label="Hapus notifikasi ini">${svgIcon('x', 16)}</button>
        </div>
      </div>`;
    }).join('');

    listWrap.innerHTML = `
      <div class="notif-page-list">${rowsHtml}</div>
      ${renderPagerHtml(filtered.length, page, perPage, 'notifList')}`;
  }

  document.getElementById('notifFilterChips').addEventListener('click', function (e) {
    const chip = e.target.closest('.chip[data-notif-filter]');
    if (!chip) return;
    this.querySelectorAll('.chip').forEach(c => c.classList.remove('is-active'));
    chip.classList.add('is-active');
    filter = chip.getAttribute('data-notif-filter');
    page = 1;
    renderNotifications();
  });

  clearAllBtn.addEventListener('click', async function () {
    const ok = await confirmDialog({
      title: 'Hapus semua notifikasi?',
      desc: 'Hanya notifikasi yang dihapus. Data barang dan transaksi tidak berubah.',
      icon: 'trash',
      okLabel: 'Hapus Semua'
    });
    if (!ok) return;
    updateState(s => { s.notifikasi = []; });
    toast('Semua notifikasi telah dihapus.');
    renderNotifications();
  });

  listWrap.addEventListener('click', function (e) {
    const pgBtn = e.target.closest('.pg-btn[data-page-target]');
    if (pgBtn && !pgBtn.disabled) {
      page = parseInt(pgBtn.getAttribute('data-page-target'), 10);
      renderNotifications();
      return;
    }
    const delBtn = e.target.closest('[data-del-notif]');
    if (delBtn) {
      const id = parseInt(delBtn.getAttribute('data-del-notif'), 10);
      updateState(s => {
        s.notifikasi = (s.notifikasi || []).filter(n => n.id !== id);
      });
      renderNotifications();
      return;
    }
    const readRow = e.target.closest('[data-mark-read]');
    if (readRow) {
      const id = parseInt(readRow.getAttribute('data-mark-read'), 10);
      updateState(s => {
        const target = (s.notifikasi || []).find(n => n.id === id);
        if (target) target.dibaca = true;
      });
      renderNotifications();
    }
  });

  document.addEventListener('kk:state-changed', renderNotifications);
  window.addEventListener('resize', renderNotifications);

  renderNotifications();
});
</script>
<?php admin_page_end('dashboard'); ?>
