<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$statusFilter = strtolower(trim((string) ($_GET['status'] ?? 'semua')));
$searchQuery = trim((string) ($_GET['q'] ?? ''));

$filters = [
    'status' => $statusFilter,
    'q'      => $searchQuery,
];

$orders = admin_get_orders($filters);
$stats = admin_get_dashboard_stats();

admin_page_start('Transaksi', 'transaksi');
?>

<!-- Header Info Desktop -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">Transaksi</h1>
            <p class="page-subtitle-lg">Pantau semua pembayaran QRIS dari pelanggan.</p>
        </div>
    </div>
</div>

<!-- 3 KPI Card Ringkasan Transaksi -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="kpi-card kpi-blue">
        <div class="kpi-icon-wrap">
            <?= icon('transaksi', 22) ?>
        </div>
        <div class="kpi-label">Total Hari Ini</div>
        <div class="kpi-value"><?= rupiah($stats['total_penjualan_hari_ini']) ?></div>
        <div class="fs-12 text-slate fw-500">Dari pesanan lunas</div>
    </div>

    <div class="kpi-card kpi-green">
        <div class="kpi-icon-wrap">
            <?= icon('check', 22) ?>
        </div>
        <div class="kpi-label">Jumlah Transaksi</div>
        <div class="kpi-value"><?= $stats['jumlah_trx_hari_ini'] ?></div>
        <div class="fs-12 text-slate fw-500">Hari ini</div>
    </div>

    <div class="kpi-card kpi-amber">
        <div class="kpi-icon-wrap">
            <?= icon('clock', 22) ?>
        </div>
        <div class="kpi-label">Menunggu Pembayaran</div>
        <div class="kpi-value"><?= $stats['menunggu_hari_ini'] ?></div>
        <div class="fs-12 text-slate fw-500">QRIS aktif</div>
    </div>
</div>

<!-- Filter & Search Toolbar -->
<div class="card" style="margin-bottom: 20px; padding: 16px;">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 14px;">
        
        <!-- Filter Status Chips -->
        <div class="chip-group">
            <a href="transaksi.php?status=semua<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="chip <?= $statusFilter === 'semua' ? 'active' : '' ?>">
                Semua
            </a>
            <a href="transaksi.php?status=lunas<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="chip <?= $statusFilter === 'lunas' ? 'active' : '' ?>">
                Lunas
            </a>
            <a href="transaksi.php?status=menunggu<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="chip <?= $statusFilter === 'menunggu' ? 'active' : '' ?>">
                Menunggu
            </a>
            <a href="transaksi.php?status=batal<?= $searchQuery !== '' ? '&q=' . urlencode($searchQuery) : '' ?>" class="chip <?= $statusFilter === 'batal' ? 'active' : '' ?>">
                Kedaluwarsa
            </a>
        </div>

        <!-- Search Input Form -->
        <form action="transaksi.php" method="GET" style="display: flex; gap: 8px; flex: 1; max-width: 320px;">
            <?php if ($statusFilter !== 'semua'): ?>
                <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
            <?php endif; ?>
            <div class="input-icon-wrap flex-1">
                <span class="input-icon-left"><?= icon('search', 16) ?></span>
                <input type="text" name="q" class="form-control" style="height: 40px; font-size: 13.5px;" placeholder="Cari no. TRX / produk..." value="<?= e($searchQuery) ?>">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="height: 40px;">Cari</button>
        </form>

    </div>
</div>

<!-- Daftar Transaksi -->
<div class="card">
    <div class="d-flex justify-between align-center" style="margin-bottom: 16px;">
        <h3 class="card-title">Daftar Transaksi</h3>
        <span class="fs-13 text-muted"><?= count($orders) ?> transaksi ditemukan</span>
    </div>

    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 48px 20px; color: var(--grey);">
            <?= icon('search', 40, 'text-muted', 'var(--line)') ?>
            <div class="fs-15 fw-600 text-slate" style="margin-top: 12px;">Tidak ada transaksi</div>
            <p class="fs-13 text-muted" style="margin-top: 4px;">Belum ada data transaksi yang cocok dengan filter yang dipilih.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Waktu</th>
                        <th>Rincian Barang</th>
                        <th>Metode</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): 
                        $status = $o['status'];
                        $badgeCls = ($status === 'paid') ? 'badge-success' : (($status === 'pending') ? 'badge-warning' : 'badge-danger');
                        $statusTxt = ($status === 'paid') ? 'Lunas' : (($status === 'pending') ? 'Menunggu' : 'Kedaluwarsa');
                        $itemsSummary = implode(', ', array_map(fn($it) => $it['nama'] . ' x' . $it['qty'], $o['items']));
                        $jsonData = json_encode($o, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    ?>
                        <tr>
                            <td>
                                <span class="fw-700 fs-14 text-navy"><?= e($o['trx']) ?></span>
                            </td>
                            <td>
                                <div class="fs-13 text-slate"><?= date('H:i', (int) ($o['dibuat'] ?? time())) ?></div>
                                <div class="fs-11 text-muted"><?= date('d M Y', (int) ($o['dibuat'] ?? time())) ?></div>
                            </td>
                            <td>
                                <div class="fs-13 text-slate" style="max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($itemsSummary) ?>">
                                    <?= e($itemsSummary) ?>
                                </div>
                            </td>
                            <td>
                                <span class="fs-13 fw-600 text-slate">QRIS</span>
                            </td>
                            <td>
                                <span class="fw-700 fs-14 text-blue"><?= rupiah((int) ($o['total'] ?? 0)) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $badgeCls ?>">
                                    <span class="badge-dot"></span>
                                    <?= $statusTxt ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-outline btn-sm" onclick='showTrxDetail(<?= $jsonData ?>)'>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail Transaksi (Sesuai SVG 05 Detail transaksi) -->
<div class="modal-backdrop" id="modalTrxDetail">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4 class="modal-title">Detail Transaksi</h4>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalTrxDetail')" aria-label="Tutup">
                <?= icon('x', 20) ?>
            </button>
        </div>
        <div class="modal-body" id="modalTrxBody">
            <!-- Diisi secara dinamis via JavaScript showTrxDetail -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal('modalTrxDetail')">Tutup</button>
        </div>
    </div>
</div>

<script>
function showTrxDetail(data) {
    const statusMap = {
        'paid': { cls: 'badge-success', txt: 'Lunas', icon: 'check', color: 'var(--green)' },
        'pending': { cls: 'badge-warning', txt: 'Menunggu', icon: 'clock', color: 'var(--amber)' },
        'expired': { cls: 'badge-danger', txt: 'Kedaluwarsa', icon: 'alert-circle', color: 'var(--red)' },
        'cancelled': { cls: 'badge-danger', txt: 'Batal', icon: 'x', color: 'var(--red)' }
    };
    const s = statusMap[data.status] || { cls: 'badge-blue', txt: data.status, color: 'var(--blue)' };
    
    const d = new Date(data.dibuat * 1000);
    const dateFormatted = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ', ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const refCode = 'QR' + (d.getDate() < 10 ? '0' : '') + d.getDate() + (d.getMonth() + 1) + '-' + Math.abs(data.id * 1337).toString(16).toUpperCase().padStart(6, '0');

    let itemsHtml = '';
    data.items.forEach(it => {
        const sub = it.harga * it.qty;
        itemsHtml += `
            <div class="d-flex justify-between align-center" style="padding: 10px 0; border-bottom: 1px dashed var(--line);">
                <div>
                    <div class="fw-600 fs-14">${it.nama}</div>
                    <div class="fs-12 text-muted">${it.qty} × Rp ${Number(it.harga).toLocaleString('id-ID')}</div>
                </div>
                <div class="fw-600 fs-14 text-navy">Rp ${Number(sub).toLocaleString('id-ID')}</div>
            </div>
        `;
    });

    const html = `
        <div style="text-align: center; padding: 10px 0 20px; border-bottom: 1px solid var(--line); margin-bottom: 18px;">
            <div class="badge ${s.cls}" style="margin-bottom: 10px; font-size: 13px; padding: 6px 14px;">
                ${s.txt}
            </div>
            <div class="fs-28 fw-700 text-blue">Rp ${Number(data.total).toLocaleString('id-ID')}</div>
            <div class="fs-12 text-muted" style="margin-top: 4px;">${dateFormatted}</div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13.5px; margin-bottom: 20px;">
            <div class="d-flex justify-between">
                <span class="text-slate">No. Transaksi</span>
                <span class="fw-700 text-navy">${data.trx}</span>
            </div>
            <div class="d-flex justify-between">
                <span class="text-slate">Metode</span>
                <span class="fw-600 text-navy">QRIS</span>
            </div>
            <div class="d-flex justify-between">
                <span class="text-slate">Ref. QRIS</span>
                <span class="fw-600 text-navy">${refCode}</span>
            </div>
        </div>

        <div>
            <h5 class="fs-14 fw-700 text-navy" style="margin-bottom: 8px;">Rincian Barang</h5>
            <div style="background: var(--tint); border-radius: 10px; padding: 6px 14px;">
                ${itemsHtml}
                <div class="d-flex justify-between align-center" style="padding: 12px 0 6px;">
                    <span class="fw-700 fs-14">Total Pembayaran</span>
                    <span class="fw-700 fs-16 text-blue">Rp ${Number(data.total).toLocaleString('id-ID')}</span>
                </div>
            </div>
        </div>
    `;

    document.getElementById('modalTrxBody').innerHTML = html;
    openModal('modalTrxDetail');
}
</script>

<?php
admin_page_end('transaksi');
