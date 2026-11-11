<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$settings = admin_get_settings();
$namaToko = $settings['nama_toko'] ?? 'Kantin Kejujuran';

// URL akses pelanggan (lokal atau domain)
$serverHost = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$pwaUrl = 'http://' . $serverHost . '/pwa/index.php';

// Generate QR code SVG menggunakan generator bawaan di qr.php
$qrCodeSvg = qr_svg($pwaUrl, 260);

admin_page_start('QR Akses Pelanggan', 'pengaturan');
?>

<style>
@media print {
    .desktop-sidebar, .desktop-topbar, .mobile-header, .mobile-bottom-nav, .no-print {
        display: none !important;
    }
    .admin-main {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .page-container {
        padding: 0 !important;
        max-width: 100% !important;
    }
    .qr-stand-card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 auto !important;
    }
}
</style>

<!-- Header Info Desktop -->
<div class="page-header-desktop no-print">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">QR Akses Pelanggan</h1>
            <p class="page-subtitle-lg">Cetak atau unduh kode QR untuk diletakkan di etalase kantin.</p>
        </div>
        <div class="d-flex gap-10">
            <button type="button" class="btn btn-outline" onclick="window.print()">
                <?= icon('printer', 18) ?>
                <span>Cetak Stand QR</span>
            </button>
            <button type="button" class="btn btn-primary" onclick="downloadQrSvg()">
                <?= icon('download', 18) ?>
                <span>Unduh QR</span>
            </button>
        </div>
    </div>
</div>

<div style="display: flex; justify-content: center; padding: 10px 0;">
    
    <!-- Standing QR Display Card (Sesuai SVG 15 QR akses pelanggan) -->
    <div class="card qr-stand-card" style="max-width: 440px; width: 100%; text-align: center; padding: 36px 28px; border-radius: 24px; box-shadow: var(--shadow-lg);">
        
        <!-- Header QR -->
        <div style="display: flex; justify-content: center; margin-bottom: 14px;">
            <div style="width: 52px; height: 52px; border-radius: 16px; background: var(--blue); color: #fff; display: grid; place-items: center;">
                <?= icon('logo', 30, '', '#fff') ?>
            </div>
        </div>

        <h2 class="fs-24 fw-700 text-navy" style="margin-bottom: 4px;"><?= e($namaToko) ?></h2>
        <p class="fs-14 fw-600 text-blue" style="margin-bottom: 24px;">Scan untuk mulai belanja</p>

        <!-- QR Code Frame -->
        <div id="qrContainer" style="background: #fff; padding: 16px; border: 2px dashed var(--line); border-radius: 20px; display: inline-block; margin-bottom: 18px; box-shadow: var(--shadow-sm);">
            <?= $qrCodeSvg ?>
        </div>

        <div class="fs-13 fw-600 text-slate" style="margin-bottom: 12px; font-family: monospace;">
            <?= e($pwaUrl) ?>
        </div>

        <div style="margin-bottom: 28px;">
            <span class="badge badge-blue" style="padding: 6px 16px; font-size: 13px;">
                Tanpa akun · Tanpa login
            </span>
        </div>

        <!-- Panduan 3 Langkah -->
        <div style="background: var(--tint); border-radius: 14px; padding: 18px 20px; text-align: left; display: flex; flex-direction: column; gap: 14px;">
            <div class="d-flex align-center gap-12">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--blue); color: #fff; font-size: 12px; font-weight: 700; display: grid; place-items: center; flex: none;">
                    1
                </div>
                <div class="fs-13 fw-600 text-navy">Pelanggan scan QR dengan kamera HP</div>
            </div>

            <div class="d-flex align-center gap-12">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--blue); color: #fff; font-size: 12px; font-weight: 700; display: grid; place-items: center; flex: none;">
                    2
                </div>
                <div class="fs-13 fw-600 text-navy">Pilih barang dan masuk ke keranjang</div>
            </div>

            <div class="d-flex align-center gap-12">
                <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--blue); color: #fff; font-size: 12px; font-weight: 700; display: grid; place-items: center; flex: none;">
                    3
                </div>
                <div class="fs-13 fw-600 text-navy">Bayar dengan QRIS, selesai</div>
            </div>
        </div>

        <!-- Tombol Aksi Mobile -->
        <div class="mobile-only-btn no-print" style="margin-top: 24px; display: flex; gap: 10px;">
            <button type="button" class="btn btn-outline btn-block" onclick="window.print()">
                <?= icon('printer', 18) ?>
                <span>Cetak</span>
            </button>
            <button type="button" class="btn btn-primary btn-block" onclick="downloadQrSvg()">
                <?= icon('download', 18) ?>
                <span>Unduh QR</span>
            </button>
        </div>

    </div>

</div>

<script>
function downloadQrSvg() {
    const svgEl = document.querySelector('#qrContainer svg');
    if (!svgEl) return;
    const svgData = new XMLSerializer().serializeToString(svgEl);
    const blob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'QR_Kantin_Kejujuran.svg';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>

<?php
admin_page_end('pengaturan');
