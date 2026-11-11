<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$filterTipe = trim((string) ($_GET['tipe'] ?? 'semua'));
$allNotifs = admin_get_notifications();

$filteredNotifs = $allNotifs;
if ($filterTipe === 'stok') {
    $filteredNotifs = array_filter($filteredNotifs, fn($n) => ($n['tipe'] ?? '') === 'stok');
} elseif ($filterTipe === 'pembayaran') {
    $filteredNotifs = array_filter($filteredNotifs, fn($n) => ($n['tipe'] ?? '') === 'pembayaran');
}

admin_page_start('Notifikasi', 'dashboard');
?>

<div class="page-header-desktop">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">Pusat Notifikasi</h1>
            <p class="page-subtitle-lg">Pemberitahuan aktivitas pembayaran dan persediaan stok.</p>
        </div>
        <a href="aksi.php?act=baca_notif" class="btn btn-outline btn-sm">
            <?= icon('check', 16) ?>
            <span>Tandai Semua Dibaca</span>
        </a>
    </div>
</div>

<!-- Filter Tipe Chips -->
<div class="card" style="margin-bottom: 20px; padding: 14px 18px;">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 12px;">
        <div class="chip-group">
            <a href="notifikasi.php?tipe=semua" class="chip <?= $filterTipe === 'semua' ? 'active' : '' ?>">Semua</a>
            <a href="notifikasi.php?tipe=stok" class="chip <?= $filterTipe === 'stok' ? 'active' : '' ?>">Stok</a>
            <a href="notifikasi.php?tipe=pembayaran" class="chip <?= $filterTipe === 'pembayaran' ? 'active' : '' ?>">Pembayaran</a>
        </div>
        <a href="aksi.php?act=baca_notif" class="fs-13 fw-600 text-blue mobile-only-btn">Tandai Dibaca</a>
    </div>
</div>

<!-- Daftar Notifikasi -->
<div class="card" style="padding: 0; overflow: hidden;">
    <?php if (empty($filteredNotifs)): ?>
        <div style="text-align: center; padding: 48px 20px; color: var(--grey);">
            <?= icon('bell', 44, 'text-muted', 'var(--line)') ?>
            <div class="fs-15 fw-600 text-slate" style="margin-top: 12px;">Tidak ada notifikasi</div>
            <p class="fs-13 text-muted" style="margin-top: 4px;">Semua pemberitahuan telah dibaca atau belum ada aktivitas baru.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column;">
            <?php foreach ($filteredNotifs as $n): 
                $isStok = ($n['tipe'] === 'stok');
                $unread = empty($n['dibaca']);
            ?>
                <div class="notif-item <?= $unread ? 'unread' : '' ?>" style="padding: 16px 20px;">
                    <div class="notif-item-icon <?= $isStok ? 'stok' : 'pembayaran' ?>">
                        <?= icon($isStok ? 'alert-circle' : 'check', 20) ?>
                    </div>
                    <div class="flex-1">
                        <div class="d-flex justify-between align-center">
                            <span class="fs-14 fw-700 text-navy"><?= e($n['judul']) ?></span>
                            <span class="fs-12 text-muted"><?= e($n['waktu']) ?></span>
                        </div>
                        <div class="fs-13 text-slate" style="margin-top: 4px;"><?= e($n['pesan']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
admin_page_end('dashboard');
