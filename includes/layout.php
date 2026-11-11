<?php
declare(strict_types=1);

/**
 * Kerangka Shell Responsif Dashboard Admin (Desktop & Mobile)
 */

function admin_page_start(string $title, string $activeNav = 'dashboard', array $opts = []): void
{
    $admin = auth_user() ?? [
        'nama'  => 'Admin Kantin',
        'role'  => 'Administrator',
        'email' => 'admin@kantinkejujuran.app'
    ];
    $notifications = admin_get_notifications();
    $unreadCount = count(array_filter($notifications, fn($n) => empty($n['dibaca'])));
    $flash = flash();
    $todayText = tanggal_hari_ini();
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1677F8">
    <title><?= e($title) ?> · Admin Kantin Kejujuran</title>
    <base href="<?= e(base_url('/')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="admin-layout">

    <!-- Desktop Sidebar (>= 1024px) -->
    <aside class="desktop-sidebar" aria-label="Navigasi Utama">
        <div class="brand-section">
            <div class="brand-logo-icon">
                <?= icon('logo', 26, '', '#fff') ?>
            </div>
            <div>
                <div class="brand-title">Kantin Kejujuran</div>
                <div class="brand-subtitle"><?= e(cfg('tagline')) ?></div>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="index.php" class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                <?= icon('dashboard', 20) ?>
                <span>Dashboard</span>
            </a>
            <a href="transaksi.php" class="nav-link <?= $activeNav === 'transaksi' ? 'active' : '' ?>">
                <?= icon('transaksi', 20) ?>
                <span>Transaksi</span>
            </a>
            <a href="penjualan.php" class="nav-link <?= $activeNav === 'penjualan' ? 'active' : '' ?>">
                <?= icon('penjualan', 20) ?>
                <span>Penjualan</span>
            </a>
            <a href="barang.php" class="nav-link <?= $activeNav === 'barang' ? 'active' : '' ?>">
                <?= icon('barang', 20) ?>
                <span>Barang</span>
            </a>
            <a href="stok.php" class="nav-link <?= $activeNav === 'stok' ? 'active' : '' ?>">
                <?= icon('stok', 20) ?>
                <span>Stok</span>
            </a>
            <a href="laporan.php" class="nav-link <?= $activeNav === 'laporan' ? 'active' : '' ?>">
                <?= icon('laporan', 20) ?>
                <span>Laporan</span>
            </a>
            <a href="pengaturan.php" class="nav-link <?= $activeNav === 'pengaturan' ? 'active' : '' ?>">
                <?= icon('pengaturan', 20) ?>
                <span>Pengaturan</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="aksi.php?act=logout" class="sidebar-logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun admin?');">
                <?= icon('keluar', 20) ?>
                <span>Keluar</span>
            </a>
            <div class="app-version-tag">Versi 1.0 · Kantin Kejujuran</div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="admin-main">

        <!-- Desktop Topbar (>= 1024px) -->
        <header class="desktop-topbar">
            <div class="topbar-date-pill">
                <?= icon('calendar', 16, 'text-muted') ?>
                <span><?= e($todayText) ?></span>
            </div>

            <!-- Bell Notifikasi Popover -->
            <div style="position: relative;">
                <button type="button" class="topbar-bell-btn" id="btnTopNotif" aria-label="Notifikasi">
                    <?= icon('bell', 20) ?>
                    <?php if ($unreadCount > 0): ?>
                        <span class="bell-badge-count"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </button>

                <div class="notif-popover" id="notifPopover">
                    <div class="notif-popover-header">
                        <span class="fw-700 fs-15">Notifikasi</span>
                        <a href="aksi.php?act=baca_notif" class="fs-12 text-blue fw-600">Tandai Dibaca</a>
                    </div>
                    <div class="notif-list-wrap">
                        <?php if (empty($notifications)): ?>
                            <div class="p-4 text-center text-muted fs-13" style="padding: 24px;">Tidak ada notifikasi.</div>
                        <?php else: ?>
                            <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
                                <div class="notif-item <?= empty($n['dibaca']) ? 'unread' : '' ?>">
                                    <div class="notif-item-icon <?= e($n['tipe'] ?? 'stok') ?>">
                                        <?= icon($n['tipe'] === 'pembayaran' ? 'check' : 'alert-circle', 18) ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="d-flex justify-between align-center">
                                            <span class="fs-13 fw-600"><?= e($n['judul']) ?></span>
                                            <span class="fs-11 text-muted"><?= e($n['waktu']) ?></span>
                                        </div>
                                        <div class="fs-12 text-slate" style="margin-top: 2px;"><?= e($n['pesan']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div class="topbar-user-dropdown">
                <button type="button" class="user-profile-btn" id="btnUserProfile">
                    <div class="avatar-circle">AD</div>
                    <span class="user-name-text"><?= e($admin['nama']) ?></span>
                    <?= icon('chevron-down', 16, 'text-muted') ?>
                </button>
                <div class="notif-popover" id="userDropdownMenu" style="width: 200px; right: 0;">
                    <div style="padding: 12px 16px; border-bottom: 1px solid var(--line);">
                        <div class="fw-600 fs-14"><?= e($admin['nama']) ?></div>
                        <div class="fs-12 text-muted"><?= e($admin['role']) ?></div>
                    </div>
                    <div style="padding: 6px;">
                        <a href="pengaturan.php" class="d-flex align-center gap-8 fs-13" style="padding: 8px 12px; border-radius: 6px;">
                            <?= icon('pengaturan', 16) ?>
                            <span>Pengaturan</span>
                        </a>
                        <a href="aksi.php?act=logout" class="d-flex align-center gap-8 fs-13 text-red" style="padding: 8px 12px; border-radius: 6px;">
                            <?= icon('keluar', 16) ?>
                            <span>Keluar</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile Header (<= 768px) -->
        <header class="mobile-header">
            <button type="button" class="header-icon-btn" id="btnToggleDrawer" aria-label="Buka Menu">
                <?= icon('menu', 24) ?>
            </button>
            <h1 class="mobile-title"><?= e($title) ?></h1>
            <a href="notifikasi.php" class="header-icon-btn" aria-label="Notifikasi">
                <?= icon('bell', 22) ?>
                <?php if ($unreadCount > 0): ?>
                    <span class="bell-badge-count" style="top: 2px; right: 2px;"><?= $unreadCount ?></span>
                <?php endif; ?>
            </a>
        </header>

        <!-- Toast Notifications Container -->
        <?php if ($flash): ?>
            <div class="toast-container">
                <div class="toast toast-<?= e($flash['type']) ?>" role="alert">
                    <?= icon($flash['type'] === 'error' ? 'alert-circle' : 'check', 20) ?>
                    <span><?= e($flash['msg']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Main Screen Body Content -->
        <main class="page-container">
    <?php
}

function admin_page_end(string $activeNav = 'dashboard'): void
{
    $admin = auth_user() ?? ['nama' => 'Admin Kantin'];
    ?>
        </main>
    </div><!-- /.admin-main -->

    <!-- Mobile Drawer Overlay Menu -->
    <div class="drawer-backdrop" id="drawerBackdrop">
        <aside class="mobile-drawer" aria-label="Menu Samping">
            <div class="drawer-header">
                <div class="d-flex justify-between align-center">
                    <div class="drawer-brand">
                        <div class="brand-logo-icon" style="width: 36px; height: 36px;">
                            <?= icon('logo', 22, '', '#fff') ?>
                        </div>
                        <div>
                            <div class="fw-700 fs-16">Kantin Kejujuran</div>
                            <div class="fs-12" style="color: #D6E6FF;"><?= e(cfg('app_name')) ?></div>
                        </div>
                    </div>
                    <button type="button" id="btnCloseDrawer" style="color: #fff; padding: 4px;" aria-label="Tutup Menu">
                        <?= icon('x', 24) ?>
                    </button>
                </div>

                <div class="drawer-user-card">
                    <div class="drawer-user-avatar">AD</div>
                    <div>
                        <div class="fw-600 fs-14"><?= e($admin['nama']) ?></div>
                        <div class="fs-12" style="color: #D6E6FF;">Administrator</div>
                    </div>
                </div>
            </div>

            <div class="drawer-menu-list">
                <a href="index.php" class="drawer-menu-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
                    <?= icon('dashboard', 20) ?>
                    <span>Dashboard</span>
                </a>
                <a href="transaksi.php" class="drawer-menu-item <?= $activeNav === 'transaksi' ? 'active' : '' ?>">
                    <?= icon('transaksi', 20) ?>
                    <span>Transaksi</span>
                </a>
                <a href="penjualan.php" class="drawer-menu-item <?= $activeNav === 'penjualan' ? 'active' : '' ?>">
                    <?= icon('penjualan', 20) ?>
                    <span>Penjualan</span>
                </a>
                <a href="barang.php" class="drawer-menu-item <?= $activeNav === 'barang' ? 'active' : '' ?>">
                    <?= icon('barang', 20) ?>
                    <span>Barang</span>
                </a>
                <a href="stok.php" class="drawer-menu-item <?= $activeNav === 'stok' ? 'active' : '' ?>">
                    <?= icon('stok', 20) ?>
                    <span>Stok</span>
                </a>
                <a href="laporan.php" class="drawer-menu-item <?= $activeNav === 'laporan' ? 'active' : '' ?>">
                    <?= icon('laporan', 20) ?>
                    <span>Laporan</span>
                </a>
                <a href="pengaturan.php" class="drawer-menu-item <?= $activeNav === 'pengaturan' ? 'active' : '' ?>">
                    <?= icon('pengaturan', 20) ?>
                    <span>Pengaturan</span>
                </a>
                
                <div class="drawer-divider"></div>

                <a href="aksi.php?act=logout" class="drawer-menu-item logout" onclick="return confirm('Keluar dari dashboard admin?');">
                    <?= icon('keluar', 20) ?>
                    <span>Keluar</span>
                </a>
            </div>

            <div class="drawer-footer">
                Versi 1.0 · Kantin Kejujuran
            </div>
        </aside>
    </div>

    <!-- Mobile Bottom Navigation Bar (<= 768px) -->
    <nav class="mobile-bottom-nav" aria-label="Navigasi Bawah">
        <a href="index.php" class="bottom-nav-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
            <?= icon('dashboard', 22) ?>
            <span>Dashboard</span>
        </a>
        <a href="transaksi.php" class="bottom-nav-item <?= $activeNav === 'transaksi' ? 'active' : '' ?>">
            <?= icon('transaksi', 22) ?>
            <span>Transaksi</span>
        </a>
        <a href="barang.php" class="bottom-nav-item <?= $activeNav === 'barang' ? 'active' : '' ?>">
            <?= icon('barang', 22) ?>
            <span>Barang</span>
        </a>
        <a href="laporan.php" class="bottom-nav-item <?= $activeNav === 'laporan' ? 'active' : '' ?>">
            <?= icon('laporan', 22) ?>
            <span>Laporan</span>
        </a>
    </nav>

</div><!-- /.admin-layout -->

<script src="assets/js/admin.js"></script>
</body>
</html>
<?php
}
