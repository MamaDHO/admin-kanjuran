<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/data.php';

/**
 * Render shell awal halaman admin (Sidebar Desktop, Topbar + Popover Notifikasi,
 * Mobile Header, Mobile Drawer, Mobile Bottom Nav).
 *
 * Opsi $opts:
 * - active_nav: dashboard | transaksi | barang | stok | penitip | laporan | pengaturan
 * - title: Judul di <title> & mobile header
 * - mobile_back: string|null URL jika mobile header memakai tombol kembali (<-) bukan hamburger
 * - mobile_right: 'bell' | 'add_barang' | 'none' | 'custom_menu'
 * - hide_bottom_nav: bool (true untuk subhalaman Notifikasi / QR Kantin)
 * - seed_bundle: array|null data bundle opsional agar tidak dibaca ganda
 */
function admin_nav_items(): array
{
    return [
        ['key' => 'dashboard',  'label' => 'Dashboard',  'href' => 'dashboard.php',  'icon' => 'dashboard'],
        ['key' => 'transaksi',  'label' => 'Transaksi',  'href' => 'transaksi.php',  'icon' => 'transaksi'],
        ['key' => 'barang',     'label' => 'Barang',     'href' => 'barang.php',     'icon' => 'barang'],
        ['key' => 'stok',       'label' => 'Stok',       'href' => 'stok.php',       'icon' => 'stok'],
        ['key' => 'penitip',    'label' => 'Penitip',    'href' => 'penitip.php',    'icon' => 'penitip'],
        ['key' => 'laporan',    'label' => 'Laporan',    'href' => 'laporan.php',    'icon' => 'laporan'],
        ['key' => 'pengaturan', 'label' => 'Pengaturan', 'href' => 'pengaturan.php', 'icon' => 'pengaturan'],
    ];
}

function admin_bottom_nav_items(): array
{
    return [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'dashboard.php', 'icon' => 'dashboard'],
        ['key' => 'transaksi', 'label' => 'Transaksi', 'href' => 'transaksi.php', 'icon' => 'transaksi'],
        ['key' => 'barang',    'label' => 'Barang',    'href' => 'barang.php',    'icon' => 'barang'],
        ['key' => 'laporan',   'label' => 'Laporan',   'href' => 'laporan.php',   'icon' => 'laporan'],
    ];
}

function admin_page_start(string $title, string $activeNav = 'dashboard', array $opts = []): void
{
    $bundle = $opts['seed_bundle'] ?? mock_seed_bundle(isset($_GET['empty']) && $_GET['empty'] === '1');
    $notifs = $bundle['notifikasi'] ?? [];
    $unreadCount = 0;
    foreach ($notifs as $n) {
        if (empty($n['dibaca'])) {
            $unreadCount++;
        }
    }

    $mobileBack = $opts['mobile_back'] ?? null;
    $mobileRight = $opts['mobile_right'] ?? 'bell';
    $hideBottomNav = (bool)($opts['hide_bottom_nav'] ?? false);
    $pageId = $opts['page_id'] ?? $activeNav;

    $navItems = admin_nav_items();
    $bottomNavItems = admin_bottom_nav_items();

    $seedJson = json_encode($bundle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
    ?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#1677F8">
<title><?= esc($title) ?> — <?= esc(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<script>window.__KK_SEED__ = <?= $seedJson ?: '{}' ?>;</script>
</head>
<body class="app-body<?= $hideBottomNav ? ' no-bottom-nav' : '' ?>" data-page="<?= esc($pageId) ?>"<?= isset($_GET['empty']) && $_GET['empty'] === '1' ? ' data-empty-preview="1"' : '' ?>>
<a href="#mainContent" class="skip-link">Lewati ke konten utama</a>

<div class="app-shell">
  <!-- DESKTOP SIDEBAR -->
  <aside class="desktop-sidebar" aria-label="Navigasi Utama">
    <a href="dashboard.php" class="sidebar-brand">
      <span class="sidebar-logo-wrap"><?= icon('logo', 38, '', 1.6) ?></span>
      <span class="sidebar-brand-text">
        <span class="sidebar-brand-title"><?= esc(APP_NAME) ?></span>
        <span class="sidebar-brand-sub"><?= esc(APP_TAGLINE) ?></span>
      </span>
    </a>

    <nav class="sidebar-nav" aria-label="Menu Admin">
      <?php foreach ($navItems as $item): ?>
        <a href="<?= esc($item['href']) ?>"
           class="sidebar-link<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"
           <?= $activeNav === $item['key'] ? 'aria-current="page"' : '' ?>>
          <span class="sidebar-link-icon"><?= icon($item['icon'], 22) ?></span>
          <span><?= esc($item['label']) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-divider" aria-hidden="true"></div>
      <a href="index.php?action=logout" class="sidebar-link sidebar-logout" data-action="logout">
        <span class="sidebar-link-icon"><?= icon('keluar', 22) ?></span>
        <span>Keluar</span>
      </a>
      <p class="sidebar-version"><?= esc(APP_VERSION) ?></p>
    </div>
  </aside>

  <div class="app-main-wrap">
    <!-- DESKTOP TOPBAR -->
    <header class="desktop-topbar">
      <div class="topbar-actions">
        <div class="popover-anchor">
          <button type="button"
                  id="notifBellBtn"
                  class="topbar-bell-btn"
                  aria-label="Buka notifikasi"
                  aria-expanded="false"
                  aria-controls="notifPopover">
            <?= icon('bell', 22) ?>
            <span class="bell-unread-dot<?= $unreadCount > 0 ? '' : ' is-hidden' ?>" data-unread-dot aria-hidden="true"></span>
          </button>
        </div>
        <span class="topbar-v-divider" aria-hidden="true"></span>
        <div class="topbar-admin-chip" aria-label="Admin aktif">
          <span class="topbar-admin-avatar"><?= icon('user', 18) ?></span>
          <span class="topbar-admin-label">Admin</span>
        </div>
      </div>
    </header>

    <!-- MOBILE HEADER -->
    <header class="mobile-header" id="mobileHeader">
      <?php if ($mobileBack !== null): ?>
        <a href="<?= esc($mobileBack) ?>" class="mobile-header-btn" id="mobileBackBtn" aria-label="Kembali">
          <?= icon('arrow-left', 22) ?>
        </a>
      <?php else: ?>
        <button type="button"
                class="mobile-header-btn"
                id="mobileMenuToggle"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
                aria-controls="mobileDrawer">
          <?= icon('menu', 22) ?>
        </button>
      <?php endif; ?>

      <span class="mobile-header-title" id="mobileHeaderTitle"><?= esc($title) ?></span>

      <div class="mobile-header-right" id="mobileHeaderRight">
        <?php if ($mobileRight === 'bell'): ?>
          <a href="notifikasi.php" class="mobile-header-btn mobile-bell-link" aria-label="Notifikasi">
            <?= icon('bell', 22) ?>
            <span class="bell-unread-dot<?= $unreadCount > 0 ? '' : ' is-hidden' ?>" data-unread-dot aria-hidden="true"></span>
          </a>
        <?php elseif ($mobileRight === 'add_barang'): ?>
          <button type="button" class="mobile-header-btn" data-open-barang-add aria-label="Tambah Barang">
            <?= icon('plus', 22) ?>
          </button>
        <?php else: ?>
          <span class="mobile-header-spacer" aria-hidden="true"></span>
        <?php endif; ?>
      </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent" tabindex="-1">
<?php
}

function admin_page_end(string $activeNav = 'dashboard'): void
{
    $navItems = admin_nav_items();
    $bottomNavItems = admin_bottom_nav_items();
    ?>
    </main>
  </div>
</div>

<!-- DESKTOP NOTIFICATION POPOVER & BACKDROP -->
<div class="popover-backdrop" id="notifPopoverBackdrop" hidden></div>
<section class="notif-popover"
         id="notifPopover"
         role="dialog"
         aria-label="Notifikasi Terbaru"
         aria-modal="false"
         hidden>
  <div class="notif-popover-head">
    <h2 class="notif-popover-title">Notifikasi</h2>
    <button type="button" class="text-danger-btn" id="popoverClearAllBtn">Hapus Semua</button>
  </div>
  <div class="notif-popover-list" id="popoverNotifList"></div>
  <div class="notif-popover-foot">
    <a href="notifikasi.php" class="notif-popover-all-link">Lihat Semua Notifikasi</a>
  </div>
</section>

<!-- MOBILE DRAWER OVERLAY & MENU -->
<div class="drawer-backdrop" id="mobileDrawerBackdrop" hidden></div>
<aside class="mobile-drawer"
       id="mobileDrawer"
       role="dialog"
       aria-modal="true"
       aria-label="Menu Samping Admin"
       hidden>
  <div class="drawer-header">
    <div class="drawer-brand-row">
      <span class="drawer-logo-wrap"><?= icon('logo', 34, '', 1.7) ?></span>
      <div>
        <div class="drawer-brand-title"><?= esc(APP_NAME) ?></div>
        <div class="drawer-brand-sub"><?= esc(APP_TAGLINE) ?></div>
      </div>
    </div>
  </div>
  <nav class="drawer-nav" aria-label="Menu Samping">
    <?php foreach ($navItems as $item): ?>
      <a href="<?= esc($item['href']) ?>"
         class="drawer-link<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"
         <?= $activeNav === $item['key'] ? 'aria-current="page"' : '' ?>>
        <span class="drawer-link-icon"><?= icon($item['icon'], 20) ?></span>
        <span><?= esc($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
    <div class="drawer-divider" aria-hidden="true"></div>
    <a href="index.php?action=logout" class="drawer-link drawer-logout" data-action="logout">
      <span class="drawer-link-icon"><?= icon('keluar', 20) ?></span>
      <span>Keluar</span>
    </a>
  </nav>
  <div class="drawer-footer">
    <span><?= esc(APP_VERSION) ?></span>
  </div>
</aside>

<!-- MOBILE BOTTOM NAVIGATION -->
<nav class="mobile-bottom-nav" id="mobileBottomNav" aria-label="Navigasi Bawah">
  <?php foreach ($bottomNavItems as $item): ?>
    <a href="<?= esc($item['href']) ?>"
       class="bottom-nav-link<?= $activeNav === $item['key'] ? ' is-active' : '' ?>"
       <?= $activeNav === $item['key'] ? 'aria-current="page"' : '' ?>>
      <?= icon($item['icon'], 20) ?>
      <span><?= esc($item['label']) ?></span>
    </a>
  <?php endforeach; ?>
</nav>

<!-- GLOBAL CONFIRMATION MODAL CONTAINER (accessible reusable) -->
<div class="modal-backdrop" id="confirmModalBackdrop" hidden>
  <div class="modal-card"
       id="confirmModal"
       role="dialog"
       aria-modal="true"
       aria-labelledby="confirmModalTitle"
       aria-describedby="confirmModalDesc">
    <div class="modal-icon-circle" id="confirmModalIconWrap" aria-hidden="true"></div>
    <h2 class="modal-title" id="confirmModalTitle">Konfirmasi</h2>
    <p class="modal-desc" id="confirmModalDesc"></p>
    <div class="modal-actions">
      <button type="button" class="btn btn-outline" id="confirmModalCancel">Batal</button>
      <button type="button" class="btn btn-danger" id="confirmModalOk">Konfirmasi</button>
    </div>
  </div>
</div>

<!-- TOAST CONTAINER -->
<div class="toast-region" id="toastRegion" aria-live="polite" aria-atomic="true"></div>

<script src="assets/js/app.js"></script>
</body>
</html>
<?php
}
