<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$settings = admin_get_settings();
$stokMin = (int) ($settings['stok_min'] ?? 5);
$notifAktif = !empty($settings['notif_aktif']);
$namaMerchant = (string) ($settings['nama_merchant'] ?? 'Kantin Kejujuran');
$nmid = (string) ($settings['nmid'] ?? 'ID1023456789012');

admin_page_start('Pengaturan', 'pengaturan');
?>

<!-- Header Info Desktop Sesuai Mockup SVG #20 -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="page-title-lg">Pengaturan</h1>
            <p class="page-subtitle-lg">Atur batas stok, merchant QRIS, dan notifikasi.</p>
        </div>
    </div>
</div>

<form action="aksi.php" method="POST" style="max-width: 680px;">
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="simpan_pengaturan">

    <div style="display: flex; flex-direction: column; gap: 20px;">

        <!-- KARTU 1: Batas Stok Menipis Sesuai Mockup SVG #20 -->
        <div class="card" style="border-radius: 14px; padding: 22px;">
            <div style="margin-bottom: 14px;">
                <h3 class="fs-16 fw-700 text-navy" style="margin-bottom: 4px;">Batas Stok Menipis</h3>
                <p class="fs-13 text-slate" style="margin: 0;">Barang dengan stok sama atau di bawah angka ini ditandai Menipis.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; max-width: 180px;">
                <input type="number" name="stok_min" class="form-control" value="<?= $stokMin ?>" min="1" max="100" required style="height: 42px; border-radius: 8px; font-weight: 700; font-size: 16px; text-align: center;">
                <span class="fs-14 fw-600 text-slate">pcs</span>
            </div>
        </div>

        <!-- KARTU 2: Notifikasi Sesuai Mockup SVG #20 -->
        <div class="card" style="border-radius: 14px; padding: 22px;">
            <div class="d-flex justify-between align-center">
                <div style="padding-right: 20px;">
                    <h3 class="fs-16 fw-700 text-navy" style="margin-bottom: 4px;">Notifikasi</h3>
                    <p class="fs-13 text-slate" style="margin: 0;">Tampilkan peringatan stok menipis dan status pembayaran.</p>
                </div>
                <label style="position: relative; display: inline-block; width: 48px; height: 26px; margin: 0; cursor: pointer; flex-shrink: 0;">
                    <input type="checkbox" name="notif_aktif" value="1" <?= $notifAktif ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;" id="toggleNotif">
                    <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?= $notifAktif ? 'var(--blue)' : '#CBD5E1' ?>; transition: .2s; border-radius: 34px;" id="sliderNotif"></span>
                    <span style="position: absolute; content: ''; height: 20px; width: 20px; left: <?= $notifAktif ? '25px' : '3px' ?>; bottom: 3px; background-color: white; transition: .2s; border-radius: 50%;" id="knobNotif"></span>
                </label>
            </div>
        </div>

        <!-- KARTU 3: Merchant QRIS Sesuai Mockup SVG #20 -->
        <div class="card" style="border-radius: 14px; padding: 22px;">
            <div style="margin-bottom: 16px;">
                <h3 class="fs-16 fw-700 text-navy" style="margin-bottom: 4px;">Merchant QRIS</h3>
                <p class="fs-13 text-slate" style="margin: 0;">Data yang ditampilkan saat pelanggan membayar.</p>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">Nama Merchant</label>
                <input type="text" name="nama_merchant" class="form-control" value="<?= e($namaMerchant) ?>" required style="height: 42px; border-radius: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 13px; font-weight: 600; margin-bottom: 6px; display: block;">NMID</label>
                <input type="text" name="nmid" class="form-control" value="<?= e($nmid) ?>" required style="height: 42px; border-radius: 8px; font-family: monospace;">
            </div>
        </div>

        <!-- KARTU 4: QR Akses Pelanggan Sesuai Mockup SVG #20 -->
        <div class="card" style="border-radius: 14px; padding: 22px;">
            <div class="d-flex justify-between align-center" style="flex-wrap: wrap; gap: 16px;">
                <div style="flex: 1; min-width: 240px;">
                    <h3 class="fs-16 fw-700 text-navy" style="margin-bottom: 4px;">QR Akses Pelanggan</h3>
                    <p class="fs-13 text-slate" style="margin: 0;">Tampilkan, cetak, atau unduh QR agar pelanggan bisa membuka menu.</p>
                </div>
                <a href="qr.php" class="btn btn-secondary" style="height: 40px; padding: 0 18px; font-size: 13.5px; font-weight: 600; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                    <?= icon('qr', 18) ?>
                    <span>Buka QR Kantin</span>
                </a>
            </div>
        </div>

        <!-- Tombol Simpan Perubahan Sesuai Mockup SVG #20 -->
        <div style="margin-top: 8px;">
            <button type="submit" class="btn btn-primary" style="height: 44px; padding: 0 24px; font-size: 14px; font-weight: 600; border-radius: 8px;">
                Simpan Perubahan
            </button>
        </div>

    </div>
</form>

<script>
document.getElementById('toggleNotif').addEventListener('change', function() {
    const slider = document.getElementById('sliderNotif');
    const knob = document.getElementById('knobNotif');
    if (this.checked) {
        slider.style.backgroundColor = 'var(--blue)';
        knob.style.left = '25px';
    } else {
        slider.style.backgroundColor = '#CBD5E1';
        knob.style.left = '3px';
    }
});
</script>

<?php
admin_page_end('pengaturan');
