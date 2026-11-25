/**
 * Kantin Kejujuran — Dashboard New Client Runtime (Prototype Interaktif Tanpa Backend)
 * Mengelola state simulasi sessionStorage dari __KK_SEED__, drawer mobile,
 * popover notifikasi, modal konfirmasi, toast, dan helper UI bersama.
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'kk_dashboard_new_state_v1';
  const AUTH_KEY = 'kk_dashboard_new_auth_v1';
  const EMPTY_PREVIEW = document.body.getAttribute('data-empty-preview') === '1';

  const ICONS = {
    'alert-triangle': '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>',
    'check-circle': '<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1M22 4 12 14l-3-3"/>',
    'check': '<path d="M20 6 9 17l-5-5"/>',
    'x': '<path d="M18 6 6 18M6 6l12 12"/>',
    'ban': '<circle cx="12" cy="12" r="9"/><path d="m5.6 5.6 12.8 12.8"/>',
    'trash': '<path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/>',
    'bell': '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 0 0 3.4 0"/>',
    'edit': '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
    'plus': '<path d="M12 5v14M5 12h14"/>',
    'chevron-right': '<path d="m9 18 6-6-6-6"/>',
    'receipt': '<path d="M5 2v20l2.5-1.5L10 22l2-1.5 2 1.5 2.5-1.5L19 22V2l-2.5 1.5L14 2l-2 1.5L10 2 7.5 3.5zM9 8h6M9 12h6M9 16h4"/>',
    'barang': '<path d="M21 8 12 3 3 8v8l9 5 9-5zM3 8l9 5 9-5M12 13v8"/>',
    'penitip': '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6M17 4a4 4 0 0 1 0 8M22 21c0-3-2-5-4-5.6"/>',
    'more-vertical': '<circle cx="12" cy="5" r="1.2" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/><circle cx="12" cy="19" r="1.2" fill="currentColor" stroke="none"/>'
  };

  function svgIcon(name, size, strokeWidth) {
    const s = size || 20;
    const sw = strokeWidth || 1.9;
    return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${sw}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] || ''}</svg>`;
  }

  function escHtml(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function rupiah(n) {
    const num = Number(n) || 0;
    const formatted = 'Rp ' + Math.abs(Math.round(num)).toLocaleString('id-ID');
    return num < 0 ? '− ' + formatted : formatted;
  }

  function formatShortDate(ymd) {
    if (!ymd) return '';
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
    const parts = String(ymd).split('-');
    if (parts.length !== 3) return ymd;
    return `${parseInt(parts[2], 10)} ${months[parseInt(parts[1], 10) - 1] || parts[1]} ${parts[0]}`;
  }

  function clone(obj) {
    return JSON.parse(JSON.stringify(obj));
  }

  function loadState() {
    if (EMPTY_PREVIEW) {
      return clone(window.__KK_SEED__ || {});
    }
    try {
      const saved = sessionStorage.getItem(STORAGE_KEY);
      if (saved) {
        const parsed = JSON.parse(saved);
        if (parsed && parsed.pengaturan) {
          return parsed;
        }
      }
    } catch (_) { /* ignore storage restrictions */ }
    const seed = clone(window.__KK_SEED__ || {});
    saveState(seed);
    return seed;
  }

  function saveState(nextState) {
    if (EMPTY_PREVIEW) return;
    try {
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(nextState));
    } catch (_) { /* ignore */ }
  }

  let state = loadState();

  function getState() {
    return state;
  }

  function updateState(mutatorFn) {
    mutatorFn(state);
    saveState(state);
    syncUnreadDots();
    document.dispatchEvent(new CustomEvent('kk:state-changed', { detail: state }));
  }

  function syncUnreadDots() {
    const unread = (state.notifikasi || []).filter(n => !n.dibaca).length;
    document.querySelectorAll('[data-unread-dot]').forEach(dot => {
      dot.classList.toggle('is-hidden', unread <= 0);
    });
  }

  function toast(message, type) {
    const region = document.getElementById('toastRegion');
    if (!region) return;
    const item = document.createElement('div');
    item.className = 'toast-item ' + (type === 'error' ? 'is-error' : 'is-success');
    item.innerHTML = svgIcon(type === 'error' ? 'alert-triangle' : 'check-circle', 18) + `<span>${escHtml(message)}</span>`;
    region.appendChild(item);
    setTimeout(() => {
      item.remove();
    }, 3400);
  }

  // Reusable Confirmation Modal
  let confirmResolver = null;
  let lastFocused = null;
  const confirmBackdrop = document.getElementById('confirmModalBackdrop');
  const confirmTitle = document.getElementById('confirmModalTitle');
  const confirmDesc = document.getElementById('confirmModalDesc');
  const confirmIconWrap = document.getElementById('confirmModalIconWrap');
  const confirmCancel = document.getElementById('confirmModalCancel');
  const confirmOk = document.getElementById('confirmModalOk');

  function confirmDialog(opts) {
    if (!confirmBackdrop) return Promise.resolve(false);
    lastFocused = document.activeElement;
    confirmTitle.textContent = opts.title || 'Konfirmasi';
    confirmDesc.textContent = opts.desc || '';
    confirmIconWrap.innerHTML = svgIcon(opts.icon || 'ban', 24);
    confirmOk.textContent = opts.okLabel || 'Konfirmasi';
    confirmBackdrop.hidden = false;
    confirmOk.focus();
    return new Promise(resolve => {
      confirmResolver = resolve;
    });
  }

  function closeConfirm(result) {
    if (!confirmBackdrop || confirmBackdrop.hidden) return;
    confirmBackdrop.hidden = true;
    if (lastFocused && typeof lastFocused.focus === 'function') {
      lastFocused.focus();
    }
    const r = confirmResolver;
    confirmResolver = null;
    if (r) r(result);
  }

  if (confirmCancel) confirmCancel.addEventListener('click', () => closeConfirm(false));
  if (confirmOk) confirmOk.addEventListener('click', () => closeConfirm(true));
  if (confirmBackdrop) {
    confirmBackdrop.addEventListener('click', e => {
      if (e.target === confirmBackdrop) closeConfirm(false);
    });
  }

  // Notification Popover (Desktop)
  const bellBtn = document.getElementById('notifBellBtn');
  const popover = document.getElementById('notifPopover');
  const popoverBackdrop = document.getElementById('notifPopoverBackdrop');
  const popoverList = document.getElementById('popoverNotifList');
  const popoverClearBtn = document.getElementById('popoverClearAllBtn');

  function renderPopoverItems() {
    if (!popoverList) return;
    const list = (state.notifikasi || []).slice(0, 5);
    if (popoverClearBtn) {
      popoverClearBtn.disabled = (state.notifikasi || []).length === 0;
    }
    if (!list.length) {
      popoverList.innerHTML = `<div class="empty-state" style="padding:32px 16px;">
        <div class="empty-state-icon">${svgIcon('bell', 24)}</div>
        <h3 class="empty-state-title">Tidak ada notifikasi</h3>
        <p class="empty-state-desc">Peringatan stok dan pembayaran akan muncul di sini.</p>
      </div>`;
      return;
    }
    popoverList.innerHTML = list.map(n => {
      const cls = n.status === 'warning' ? 'notif-circle-warning' : (n.status === 'danger' ? 'notif-circle-danger' : 'notif-circle-success');
      const ic = n.status === 'warning' ? 'alert-triangle' : (n.status === 'danger' ? 'x' : 'check-circle');
      return `<div class="notif-popover-item${n.dibaca ? '' : ' is-unread'}">
        <span class="notif-circle ${cls}" aria-hidden="true">${svgIcon(ic, 18)}</span>
        <div class="notif-item-body">
          <div class="notif-item-top">
            <span class="notif-item-title">${escHtml(n.judul)}</span>
            <span class="notif-item-time">${escHtml(n.waktu)}</span>
          </div>
          <div class="notif-item-msg">${escHtml(n.pesan)}</div>
        </div>
      </div>`;
    }).join('');
  }

  function openPopover() {
    if (!popover || !popoverBackdrop) return;
    renderPopoverItems();
    popoverBackdrop.hidden = false;
    popover.hidden = false;
    if (bellBtn) bellBtn.setAttribute('aria-expanded', 'true');
  }

  function closePopover() {
    if (!popover || !popoverBackdrop || popover.hidden) return;
    popover.hidden = true;
    popoverBackdrop.hidden = true;
    if (bellBtn) {
      bellBtn.setAttribute('aria-expanded', 'false');
    }
  }

  if (bellBtn) {
    bellBtn.addEventListener('click', () => {
      if (popover && !popover.hidden) {
        closePopover();
      } else {
        openPopover();
      }
    });
  }
  if (popoverBackdrop) {
    popoverBackdrop.addEventListener('click', closePopover);
  }
  if (popoverClearBtn) {
    popoverClearBtn.addEventListener('click', async () => {
      closePopover();
      const ok = await confirmDialog({
        title: 'Hapus semua notifikasi?',
        desc: 'Hanya notifikasi yang dihapus. Data barang dan transaksi tidak berubah.',
        icon: 'trash',
        okLabel: 'Hapus Semua'
      });
      if (ok) {
        updateState(s => { s.notifikasi = []; });
        renderPopoverItems();
        toast('Semua notifikasi telah dihapus.');
      }
    });
  }

  // Mobile Drawer
  const menuToggle = document.getElementById('mobileMenuToggle');
  const drawer = document.getElementById('mobileDrawer');
  const drawerBackdrop = document.getElementById('mobileDrawerBackdrop');

  function openDrawer() {
    if (!drawer || !drawerBackdrop) return;
    drawerBackdrop.hidden = false;
    drawer.hidden = false;
    if (menuToggle) menuToggle.setAttribute('aria-expanded', 'true');
  }
  function closeDrawer() {
    if (!drawer || !drawerBackdrop || drawer.hidden) return;
    drawer.hidden = true;
    drawerBackdrop.hidden = true;
    if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
  }
  if (menuToggle) menuToggle.addEventListener('click', openDrawer);
  if (drawerBackdrop) drawerBackdrop.addEventListener('click', closeDrawer);

  // Mobile Subheader controller (for mobile single-column subviews inside pages)
  const mobileHeaderDefault = (function () {
    const titleEl = document.getElementById('mobileHeaderTitle');
    const rightEl = document.getElementById('mobileHeaderRight');
    return {
      title: titleEl ? titleEl.textContent : '',
      rightHtml: rightEl ? rightEl.innerHTML : '',
      hadBottomNav: !document.body.classList.contains('no-bottom-nav')
    };
  })();

  let subBackBtnEl = null;
  function setMobileSubheader(title, onBack, rightHtml) {
    const header = document.getElementById('mobileHeader');
    const titleEl = document.getElementById('mobileHeaderTitle');
    const rightEl = document.getElementById('mobileHeaderRight');
    const toggleBtn = document.getElementById('mobileMenuToggle');
    if (!header || !titleEl) return;

    if (onBack) {
      if (toggleBtn) toggleBtn.hidden = true;
      if (!subBackBtnEl) {
        subBackBtnEl = document.createElement('button');
        subBackBtnEl.type = 'button';
        subBackBtnEl.className = 'mobile-header-btn';
        subBackBtnEl.setAttribute('aria-label', 'Kembali');
        subBackBtnEl.innerHTML = svgIcon('arrow-left', 22);
        header.insertBefore(subBackBtnEl, titleEl);
      }
      subBackBtnEl.hidden = false;
      subBackBtnEl.onclick = onBack;
      titleEl.textContent = title;
      if (rightEl) {
        rightEl.innerHTML = rightHtml || '<span class="mobile-header-spacer" aria-hidden="true"></span>';
      }
      document.body.classList.add('no-bottom-nav');
    } else {
      if (subBackBtnEl) subBackBtnEl.hidden = true;
      if (toggleBtn) toggleBtn.hidden = false;
      titleEl.textContent = mobileHeaderDefault.title;
      if (rightEl) rightEl.innerHTML = mobileHeaderDefault.rightHtml;
      if (mobileHeaderDefault.hadBottomNav) {
        document.body.classList.remove('no-bottom-nav');
      }
    }
  }

  // Logout click handler (clears mock auth flag)
  document.querySelectorAll('[data-action="logout"]').forEach(link => {
    link.addEventListener('click', () => {
      try { sessionStorage.removeItem(AUTH_KEY); } catch (_) { /* ignore */ }
    });
  });

  // Escape closes active overlays
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      closeConfirm(false);
      closePopover();
      closeDrawer();
    }
  });

  // Shared Pagination HTML generator
  function renderPagerHtml(total, page, perPage, dataName) {
    if (total <= 0) return '';
    const totalPages = Math.max(1, Math.ceil(total / perPage));
    const current = Math.max(1, Math.min(page, totalPages));
    const from = (current - 1) * perPage + 1;
    const to = Math.min(current * perPage, total);
    const maxBtns = 5;
    let start = Math.max(1, current - 2);
    const end = Math.min(totalPages, start + maxBtns - 1);
    if (end - start + 1 < maxBtns) {
      start = Math.max(1, end - maxBtns + 1);
    }
    let btns = `<button type="button" class="pg-btn" data-page-target="${current - 1}" aria-label="Halaman sebelumnya" ${current <= 1 ? 'disabled' : ''}>‹</button>`;
    for (let i = start; i <= end; i++) {
      btns += `<button type="button" class="pg-btn${i === current ? ' is-active' : ''}" data-page-target="${i}" ${i === current ? 'aria-current="page"' : ''}>${i}</button>`;
    }
    btns += `<button type="button" class="pg-btn" data-page-target="${current + 1}" aria-label="Halaman berikutnya" ${current >= totalPages ? 'disabled' : ''}>›</button>`;
    return `<div class="pager-bar" data-pager="${escHtml(dataName || '')}">
      <span class="pager-info">Menampilkan ${from}–${to} dari ${total}</span>
      <div class="pager-controls">${btns}</div>
    </div>`;
  }

  function stockStatus(stok, threshold) {
    if (stok <= 0) return 'Habis';
    if (stok <= threshold) return 'Menipis';
    return 'Aman';
  }

  function stockBadge(status) {
    const cls = status === 'Habis' ? 'badge-danger' : (status === 'Menipis' ? 'badge-warning' : 'badge-success');
    return `<span class="badge ${cls}">${escHtml(status)}</span>`;
  }

  function orderBadge(status) {
    const map = { lunas: ['Lunas', 'badge-success'], batal: ['Batal', 'badge-danger'], menunggu: ['Menunggu', 'badge-warning'] };
    const [label, cls] = map[status] || map.menunggu;
    return `<span class="badge ${cls}">${label}</span>`;
  }

  syncUnreadDots();

  window.KKApp = {
    getState,
    updateState,
    toast,
    confirmDialog,
    setMobileSubheader,
    renderPagerHtml,
    stockStatus,
    stockBadge,
    orderBadge,
    svgIcon,
    escHtml,
    rupiah,
    formatShortDate,
    AUTH_KEY,
    STORAGE_KEY,
    isEmptyPreview: EMPTY_PREVIEW
  };
})();
