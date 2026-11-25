<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/includes/qr.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);
$merchantName = $bundle['pengaturan']['nama_merchant'] ?? APP_NAME;
$pwaUrlDisplay = $bundle['pengaturan']['url_pwa'] ?? 'kantinkejujuran.app/mulai';
$qrTarget = 'https://' . ltrim($pwaUrlDisplay, 'https://');
$qrSvgMarkup = qr_svg($qrTarget, 220, '#21324F');

admin_page_start('QR Akses Pelanggan', 'pengaturan', [
    'seed_bundle' => $bundle,
    'mobile_back' => 'pengaturan.php',
    'mobile_right' => 'none',
    'hide_bottom_nav' => true,
    'page_id' => 'qr',
]);

render_page_header(
    'QR Kantin',
    'QR akses PWA untuk pelanggan.'
);
?>

<section class="card card-lg" aria-label="Kartu dan Panduan QR Kantin">
  <div class="qr-grid">
    <!-- Kartu QR Standee -->
    <div class="card qr-card-center" id="qrStandCard">
      <div class="qr-brand-pill">
        <span class="qr-brand-badge" aria-hidden="true"><?= icon('logo', 22) ?></span>
        <span id="qrMerchantLabel"><?= esc($merchantName) ?></span>
      </div>

      <div class="qr-frame" id="qrSvgWrap">
        <span class="qr-corner qr-corner-tl" aria-hidden="true"></span>
        <span class="qr-corner qr-corner-tr" aria-hidden="true"></span>
        <span class="qr-corner qr-corner-bl" aria-hidden="true"></span>
        <span class="qr-corner qr-corner-br" aria-hidden="true"></span>
        <?= $qrSvgMarkup ?>
      </div>

      <p style="margin:0 0 6px;font-size:15px;font-weight:700;color:var(--navy);">Scan untuk mulai belanja</p>
      <p style="margin:0;font-size:13.5px;font-weight:500;color:var(--blue);"><?= esc($pwaUrlDisplay) ?></p>
      <p class="mobile-only" style="margin:10px 0 0;font-size:11.5px;color:var(--grey);">Tanpa akun · Tanpa login</p>
    </div>

    <!-- Panduan Cara Kerja + Tombol Unduh/Cetak -->
    <div style="display:flex;flex-direction:column;justify-content:center;" class="qr-info-col">
      <div class="qr-info-card">
        <h2 class="settings-card-title" style="margin-bottom:4px;">Cara kerja</h2>

        <div class="qr-step-list">
          <div class="qr-step-item">
            <span class="qr-step-num">1</span>
            <div>
              <div style="font-size:14.5px;font-weight:700;color:var(--navy);">Scan QR</div>
              <div style="font-size:13px;color:var(--grey);">Pelanggan memindai QR dengan kamera HP.</div>
            </div>
          </div>
          <div class="qr-step-item">
            <span class="qr-step-num">2</span>
            <div>
              <div style="font-size:14.5px;font-weight:700;color:var(--navy);">Pilih barang</div>
              <div style="font-size:13px;color:var(--grey);">Menu terbuka sebagai PWA, tanpa akun dan login.</div>
            </div>
          </div>
          <div class="qr-step-item">
            <span class="qr-step-num">3</span>
            <div>
              <div style="font-size:14.5px;font-weight:700;color:var(--navy);">Bayar QRIS</div>
              <div style="font-size:13px;color:var(--grey);">Pembayaran hanya lewat QRIS, lalu selesai.</div>
            </div>
          </div>
        </div>

        <div class="qr-tip-banner mobile-hide">
          <span style="display:inline-flex;margin-top:2px;" aria-hidden="true"><?= icon('info', 18) ?></span>
          <div>
            <strong>Cetak dan tempel QR di meja kantin.</strong>
            <span>QR tetap sama selama alamat PWA tidak berubah.</span>
          </div>
        </div>
      </div>

      <div class="form-actions-row qr-actions-row">
        <button type="button" class="btn btn-primary btn-lg" id="qrDownloadPngBtn">
          <?= icon('download', 18) ?>
          <span>Unduh PNG</span>
        </button>
        <button type="button" class="btn btn-outline-primary btn-lg" id="qrPrintBtn">
          <?= icon('printer', 18) ?>
          <span>Cetak</span>
        </button>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, toast } = window.KKApp;

  const sett = getState().pengaturan || {};
  const merchantName = sett.nama_merchant || <?= json_encode($merchantName) ?>;
  const pwaUrl = sett.url_pwa || <?= json_encode($pwaUrlDisplay) ?>;
  document.getElementById('qrMerchantLabel').textContent = merchantName;

  document.getElementById('qrPrintBtn').addEventListener('click', () => window.print());

  document.getElementById('qrDownloadPngBtn').addEventListener('click', function () {
    const svgEl = document.querySelector('#qrSvgWrap svg');
    if (!svgEl) return;

    const svgData = new XMLSerializer().serializeToString(svgEl);
    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const url = URL.createObjectURL(svgBlob);
    const img = new Image();

    img.onload = function () {
      const canvas = document.createElement('canvas');
      canvas.width = 720;
      canvas.height = 880;
      const ctx = canvas.getContext('2d');

      // White poster background
      ctx.fillStyle = '#FFFFFF';
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      // Header text
      ctx.fillStyle = '#21324F';
      ctx.font = 'bold 34px Inter, Arial, sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(merchantName, 360, 100);

      // Blue corner brackets
      ctx.strokeStyle = '#1677F8';
      ctx.lineWidth = 8;
      ctx.lineCap = 'round';
      const fx = 140, fy = 155, fw = 440, fh = 440, c = 36;
      [
        [[fx, fy + c], [fx, fy], [fx + c, fy]],
        [[fx + fw - c, fy], [fx + fw, fy], [fx + fw, fy + c]],
        [[fx, fy + fh - c], [fx, fy + fh], [fx + c, fy + fh]],
        [[fx + fw - c, fy + fh], [fx + fw, fy + fh], [fx + fw, fy + fh - c]]
      ].forEach(pts => {
        ctx.beginPath();
        ctx.moveTo(pts[0][0], pts[0][1]);
        ctx.lineTo(pts[1][0], pts[1][1]);
        ctx.lineTo(pts[2][0], pts[2][1]);
        ctx.stroke();
      });

      // QR Matrix
      ctx.drawImage(img, 185, 200, 350, 350);

      // Footer captions
      ctx.fillStyle = '#21324F';
      ctx.font = 'bold 28px Inter, Arial, sans-serif';
      ctx.fillText('Scan untuk mulai belanja', 360, 675);

      ctx.fillStyle = '#1677F8';
      ctx.font = '500 22px Inter, Arial, sans-serif';
      ctx.fillText(pwaUrl, 360, 725);

      ctx.fillStyle = '#5E718B';
      ctx.font = '400 18px Inter, Arial, sans-serif';
      ctx.fillText('Tanpa akun · Bayar dengan QRIS', 360, 770);

      URL.revokeObjectURL(url);
      const a = document.createElement('a');
      a.download = 'qr-kantin-kejujuran.png';
      a.href = canvas.toDataURL('image/png');
      a.click();
      toast('QR Kantin berhasil diunduh (PNG).');
    };
    img.src = url;
  });
});
</script>
<?php admin_page_end('pengaturan'); ?>
