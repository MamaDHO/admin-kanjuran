<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/ui.php';

$emptyMode = ($_GET['empty'] ?? '') === '1';
$bundle = mock_seed_bundle($emptyMode);

admin_page_start('Pengaturan', 'pengaturan', [
    'seed_bundle' => $bundle,
    'mobile_right' => 'bell',
]);

render_page_header(
    'Pengaturan',
    'Atur batas stok, merchant QRIS, dan notifikasi.'
);
?>

<form id="settingsForm" novalidate>
  <div class="settings-grid">
    <div class="settings-left-stack">
      <!-- Batas Stok Menipis -->
      <section class="card card-lg" aria-label="Batas Stok Menipis">
        <h2 class="settings-card-title">Batas Stok Menipis</h2>
        <p class="settings-card-sub">Barang dengan stok sama atau di bawah angka ini ditandai Menipis.</p>
        <div class="stepper-row">
          <button type="button" class="stepper-btn" id="setThreshMinus" aria-label="Kurangi batas stok menipis">
            <?= icon('minus', 20, '', 2.2) ?>
          </button>
          <div class="stepper-val-wrap">
            <span class="stepper-val" id="setThreshVal">5</span>
            <span class="stepper-unit">pcs</span>
          </div>
          <button type="button" class="stepper-btn stepper-btn-solid" id="setThreshPlus" aria-label="Tambah batas stok menipis">
            <?= icon('plus', 20, '', 2.2) ?>
          </button>
        </div>
      </section>

      <!-- Toggle Notifikasi -->
      <section class="card card-lg" aria-label="Pengaturan Notifikasi">
        <div class="settings-switch-row">
          <div>
            <h2 class="settings-card-title" style="margin-bottom:4px;">Notifikasi</h2>
            <p class="settings-card-sub" style="margin-bottom:0;">Tampilkan peringatan stok menipis dan status pembayaran.</p>
          </div>
          <button type="button"
                  role="switch"
                  id="setNotifSwitch"
                  class="switch-toggle is-on"
                  aria-checked="true"
                  aria-label="Aktifkan notifikasi">
            <span class="switch-thumb"></span>
          </button>
        </div>
      </section>
    </div>

    <!-- Merchant QRIS -->
    <section class="card card-lg" aria-label="Merchant QRIS">
      <h2 class="settings-card-title">Merchant QRIS</h2>
      <p class="settings-card-sub">Data yang ditampilkan saat pelanggan membayar.</p>

      <div class="form-group">
        <label for="setMerchantName" class="form-label">Nama Merchant</label>
        <input type="text" id="setMerchantName" class="form-control" required>
      </div>

      <div class="form-group" style="margin-bottom:0;">
        <label for="setNmid" class="form-label">NMID</label>
        <input type="text" id="setNmid" class="form-control" required>
      </div>
    </section>
  </div>

  <!-- QR Akses Pelanggan -->
  <section class="card card-lg" aria-label="QR Akses Pelanggan">
    <div class="settings-qr-banner">
      <div class="settings-qr-left">
        <span class="settings-qr-icon" aria-hidden="true"><?= icon('qr', 24) ?></span>
        <div>
          <h2 class="card-title" style="margin-bottom:3px;">QR Akses Pelanggan</h2>
          <p class="settings-card-sub" style="margin-bottom:0;">Tampilkan, cetak, atau unduh QR agar pelanggan bisa membuka menu.</p>
        </div>
      </div>
      <a href="qr.php" class="btn btn-outline-primary btn-lg">Buka QR Kantin</a>
    </div>
  </section>

  <div id="settingsError" class="form-error" role="alert" style="margin-top:16px;" hidden></div>

  <div class="settings-save-bar">
    <button type="submit" class="btn btn-primary btn-lg" style="min-width:200px;">Simpan Perubahan</button>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const { getState, updateState, toast } = window.KKApp;

  const s = getState().pengaturan || {};
  let threshold = Number(s.batas_stok_menipis || 5);
  let notifOn = s.notifikasi_aktif !== false;

  const threshValEl = document.getElementById('setThreshVal');
  const minusBtn = document.getElementById('setThreshMinus');
  const plusBtn = document.getElementById('setThreshPlus');
  const notifSwitch = document.getElementById('setNotifSwitch');
  const merchantEl = document.getElementById('setMerchantName');
  const nmidEl = document.getElementById('setNmid');
  const errEl = document.getElementById('settingsError');

  function syncUi() {
    threshValEl.textContent = String(threshold);
    minusBtn.disabled = threshold <= 1;
    notifSwitch.classList.toggle('is-on', notifOn);
    notifSwitch.setAttribute('aria-checked', notifOn ? 'true' : 'false');
  }

  merchantEl.value = s.nama_merchant || 'Kantin Kejujuran';
  nmidEl.value = s.nmid || 'ID1023456789012';
  syncUi();

  minusBtn.addEventListener('click', () => { if (threshold > 1) { threshold--; syncUi(); } });
  plusBtn.addEventListener('click', () => { if (threshold < 100) { threshold++; syncUi(); } });
  notifSwitch.addEventListener('click', () => { notifOn = !notifOn; syncUi(); });

  document.getElementById('settingsForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const nama = merchantEl.value.trim();
    const nmid = nmidEl.value.trim();
    if (!nama || !nmid) {
      errEl.textContent = 'Nama Merchant dan NMID wajib diisi.';
      errEl.hidden = false;
      return;
    }
    errEl.hidden = true;
    updateState(st => {
      st.pengaturan = st.pengaturan || {};
      st.pengaturan.batas_stok_menipis = threshold;
      st.pengaturan.notifikasi_aktif = notifOn;
      st.pengaturan.nama_merchant = nama;
      st.pengaturan.nmid = nmid;
    });
    toast('Pengaturan berhasil disimpan.');
  });
});
</script>
<?php admin_page_end('pengaturan'); ?>
