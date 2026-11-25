<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$filterTipe = strtolower(trim((string) ($_GET['tipe'] ?? 'semua')));
$allNotifs = admin_get_notifications();

// Filter berdasarkan tipe: semua, stok, pembayaran
$filteredNotifs = $allNotifs;
if ($filterTipe === 'stok') {
    $filteredNotifs = array_values(array_filter($filteredNotifs, fn($n) => ($n['tipe'] ?? '') === 'stok'));
} elseif ($filterTipe === 'pembayaran') {
    $filteredNotifs = array_values(array_filter($filteredNotifs, fn($n) => ($n['tipe'] ?? '') === 'pembayaran'));
}

// Paginasi: 8 per halaman sesuai Mockup SVG #17 ("Menampilkan 1–8 dari 12")
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;
$totalItems = count($filteredNotifs);
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $perPage;
$paginatedNotifs = array_slice($filteredNotifs, $offset, $perPage);

admin_page_start('Notifikasi', 'dashboard');
?>

<!-- Header Desktop Sesuai Mockup SVG #17 -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Notifikasi</h1>
            <p class="page-subtitle-lg">Peringatan stok dan status pembayaran.</p>
        </div>
    </div>
</div>

<!-- Card Wrapper Notifikasi -->
<div class="card" style="border-radius: 14px; padding: 22px;">

    <!-- Toolbar: Filter Chips di Kiri, Hapus Semua di Kanan Sesuai Mockup SVG #17 -->
    <div class="d-flex justify-between align-center" style="margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        
        <div class="chip-group" style="padding-bottom: 0;">
            <a href="notifikasi.php?tipe=semua" class="chip <?= $filterTipe === 'semua' ? 'active' : '' ?>" style="height: 36px; padding: 8px 16px; font-size: 13px; border-radius: 8px;">
                Semua
            </a>
            <a href="notifikasi.php?tipe=stok" class="chip <?= $filterTipe === 'stok' ? 'active' : '' ?>" style="height: 36px; padding: 8px 16px; font-size: 13px; border-radius: 8px;">
                Stok
            </a>
            <a href="notifikasi.php?tipe=pembayaran" class="chip <?= $filterTipe === 'pembayaran' ? 'active' : '' ?>" style="height: 36px; padding: 8px 16px; font-size: 13px; border-radius: 8px;">
                Pembayaran
            </a>
        </div>

        <?php if (!empty($allNotifs)): ?>
            <button type="button" class="btn btn-secondary btn-sm" onclick="openModal('modalHapusSemuaNotif')" style="height: 36px; padding: 0 14px; font-size: 13px; font-weight: 600; border-radius: 8px; color: var(--red); border-color: var(--line);">
                Hapus Semua
            </button>
        <?php endif; ?>

    </div>

    <!-- Daftar Notifikasi / Empty State Sesuai Mockup SVG #18 -->
    <?php if (empty($paginatedNotifs)): ?>
        <div class="empty-state" style="padding: 64px 20px; text-align: center;">
            <div class="empty-icon-wrap" style="width: 56px; height: 56px; border-radius: 50%; background: #F1F5F9; color: var(--slate); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <?= icon('bell', 28) ?>
            </div>
            <div class="fw-700 fs-16 text-navy" style="margin-bottom: 4px;">Tidak ada notifikasi</div>
            <div class="fs-13 text-slate">Peringatan stok dan status pembayaran akan muncul di sini.</div>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column;">
            <?php foreach ($paginatedNotifs as $n): 
                $isStok = (($n['tipe'] ?? '') === 'stok');
                $isBatal = str_contains(strtolower($n['judul'] ?? ''), 'batal');
                $isCheck = !$isStok && !$isBatal;

                $iconColor = $isStok ? '#F59E0B' : ($isCheck ? '#10B981' : '#EF4444');
                $iconBg = $isStok ? '#FFFBEB' : ($isCheck ? '#ECFDF5' : '#FEF2F2');
            ?>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid var(--line); gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex: 1;">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: <?= $iconBg ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <?php if ($isStok): ?>
                                <?= icon('alert-circle', 20) ?>
                            <?php elseif ($isCheck): ?>
                                <?= icon('check', 20) ?>
                            <?php else: ?>
                                <?= icon('x', 20) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="fw-700 fs-14 text-navy"><?= e($n['judul']) ?></div>
                            <div class="fs-13 text-slate" style="margin-top: 2px;"><?= e($n['pesan']) ?></div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 14px; flex-shrink: 0;">
                        <span class="fs-12 text-muted"><?= e($n['waktu']) ?></span>
                        <a href="aksi.php?act=hapus_notif&id=<?= urlencode((string) $n['id']) ?>" class="btn-icon-link" style="color: var(--grey); padding: 4px; border-radius: 4px;" title="Hapus notifikasi" onclick="return confirm('Hapus notifikasi ini?');">
                            <?= icon('x', 16) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer Pagination Sesuai Mockup SVG #17 ("Menampilkan 1–8 dari 12") -->
        <div class="d-flex justify-between align-center" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--line); flex-wrap: wrap; gap: 12px;">
            <span class="fs-13 text-muted">
                Menampilkan <?= min(1, $totalItems) ?>–<?= min($totalItems, $currentPage * $perPage) ?> dari <?= $totalItems ?>
            </span>
            <div class="pagination-mockup">
                <?php if ($currentPage > 1): ?>
                    <a href="notifikasi.php?page=<?= $currentPage - 1 ?><?= $filterTipe !== 'semua' ? '&tipe=' . urlencode($filterTipe) : '' ?>" class="page-link-btn">‹</a>
                <?php else: ?>
                    <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">‹</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="notifikasi.php?page=<?= $i ?><?= $filterTipe !== 'semua' ? '&tipe=' . urlencode($filterTipe) : '' ?>" class="page-link-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="notifikasi.php?page=<?= $currentPage + 1 ?><?= $filterTipe !== 'semua' ? '&tipe=' . urlencode($filterTipe) : '' ?>" class="page-link-btn">›</a>
                <?php else: ?>
                    <span class="page-link-btn" style="cursor: not-allowed; opacity: 0.6;">›</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Modal Konfirmasi Hapus Semua Sesuai Mockup SVG #19 -->
<div class="modal-backdrop" id="modalHapusSemuaNotif">
    <div class="modal-dialog" style="max-width: 420px;">
        <form action="aksi.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="hapus_semua_notif">

            <div class="modal-body" style="text-align: center; padding: 32px 24px 20px;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #FEE2E2; color: #EF4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <?= icon('trash', 26) ?>
                </div>
                <h4 class="fs-18 fw-700 text-navy" style="margin-bottom: 8px;">Hapus semua notifikasi?</h4>
                <p class="fs-13 text-slate" style="line-height: 1.5; margin-bottom: 0;">
                    Hanya notifikasi yang dihapus. Data barang dan transaksi tidak berubah.
                </p>
            </div>

            <div class="modal-footer" style="justify-content: center; gap: 12px; background: #fff; padding: 16px 24px 24px; border-top: none;">
                <button type="button" class="btn btn-secondary" style="min-width: 110px; border-radius: 8px; height: 38px;" onclick="closeModal('modalHapusSemuaNotif')">Batal</button>
                <button type="submit" class="btn btn-danger" style="min-width: 130px; border-radius: 8px; height: 38px; font-weight: 600;">Hapus Semua</button>
            </div>
        </form>
    </div>
</div>

<?php
admin_page_end('dashboard');
