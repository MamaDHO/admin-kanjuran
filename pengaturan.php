<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
auth_guard();

$settings = admin_get_settings();
$adminUser = admin_get_user();

admin_page_start('Pengaturan', 'pengaturan');
?>

<!-- Header Info Desktop -->
<div class="page-header-desktop">
    <div class="d-flex justify-between align-center">
        <div>
            <h1 class="page-title-lg">Pengaturan</h1>
            <p class="page-subtitle-lg">Atur profil admin, data merchant QRIS, dan preferensi kantin.</p>
        </div>
    </div>
</div>

<form action="aksi.php" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="act" value="simpan_pengaturan">

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">

        <!-- Kolom Kiri: Profil & Kredensial -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Profil Admin Card -->
            <div class="card">
                <div class="d-flex align-center gap-14" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--line);">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--blue); color: #fff; font-size: 20px; font-weight: 700; display: grid; place-items: center;">
                        AD
                    </div>
                    <div>
                        <h3 class="fs-16 fw-700 text-navy"><?= e($adminUser['nama']) ?></h3>
                        <span class="badge badge-blue"><?= e($adminUser['role']) ?></span>
                    </div>
                </div>

                <h4 class="fs-15 fw-700 text-navy" style="margin-bottom: 14px;">Data Diri Admin</h4>
                
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="admin_nama" class="form-control" value="<?= e($adminUser['nama']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Email</label>
                    <input type="email" name="admin_email" class="form-control" value="<?= e($adminUser['email'] ?? 'admin@kantinkejujuran.app') ?>" required>
                </div>
            </div>

            <!-- Ganti Password Card -->
            <div class="card">
                <h4 class="fs-15 fw-700 text-navy" style="margin-bottom: 4px;">Keamanan & Password</h4>
                <p class="fs-13 text-muted" style="margin-bottom: 16px;">Biarkan kosong jika tidak ingin mengubah password.</p>

                <div class="form-group">
                    <label class="form-label">Password Saat Ini</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon-left"><?= icon('lock', 18) ?></span>
                        <input type="password" name="pass_lama" id="passLama" class="form-control has-right" placeholder="Password lama">
                        <button type="button" class="input-icon-right btn-toggle-password" data-target="passLama">
                            <?= icon('eye', 18) ?>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon-left"><?= icon('lock', 18) ?></span>
                        <input type="password" name="pass_baru" id="passBaru" class="form-control has-right" placeholder="Minimal 6 karakter">
                        <button type="button" class="input-icon-right btn-toggle-password" data-target="passBaru">
                            <?= icon('eye', 18) ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pintasan QR Akses & Keluar -->
            <div class="card" style="padding: 14px;">
                <a href="qr.php" class="d-flex justify-between align-center" style="padding: 12px 14px; border-radius: 10px; background: var(--tint); margin-bottom: 8px;">
                    <div class="d-flex align-center gap-12">
                        <?= icon('qr', 22, 'text-blue') ?>
                        <div>
                            <div class="fs-14 fw-700 text-navy">QR Akses Pelanggan</div>
                            <div class="fs-12 text-muted">Lihat, unduh, dan cetak QR toko</div>
                        </div>
                    </div>
                    <span class="badge badge-success">Baru</span>
                </a>

                <a href="aksi.php?act=logout" class="d-flex justify-between align-center text-red" style="padding: 12px 14px; border-radius: 10px;" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun admin?');">
                    <div class="d-flex align-center gap-12">
                        <?= icon('keluar', 22) ?>
                        <span class="fs-14 fw-700">Keluar dari Akun</span>
                    </div>
                    <?= icon('chevron-right', 18) ?>
                </a>
            </div>

        </div>

        <!-- Kolom Kanan: Pengaturan Kantin & QRIS -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Info Kantin -->
            <div class="card">
                <h4 class="fs-15 fw-700 text-navy" style="margin-bottom: 14px;">Informasi Kantin</h4>

                <div class="form-group">
                    <label class="form-label">Nama Kantin</label>
                    <input type="text" name="nama_toko" class="form-control" value="<?= e($settings['nama_toko'] ?? 'Kantin Kejujuran') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Slogan / Tagline</label>
                    <input type="text" name="tagline" class="form-control" value="<?= e($settings['tagline'] ?? 'Ambil, Bayar, Jujur') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Batas Stok Menipis (Default)</label>
                    <input type="number" name="batas_stok_tipis" class="form-control" value="<?= (int)($settings['batas_stok_tipis'] ?? 5) ?>" min="1" required>
                    <div class="fs-12 text-muted" style="margin-top: 4px;">Peringatan stok menipis otomatis aktif jika stok produk &le; batas ini.</div>
                </div>
            </div>

            <!-- Pengaturan QRIS Merchant -->
            <div class="card">
                <div class="d-flex align-center gap-10" style="margin-bottom: 14px;">
                    <?= icon('qr', 22, 'text-blue') ?>
                    <h4 class="fs-15 fw-700 text-navy">Data Merchant QRIS</h4>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Merchant (Tampil di QRIS)</label>
                    <input type="text" name="nama_merchant" class="form-control" value="<?= e($settings['nama_merchant'] ?? 'Kantin Kejujuran Official') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">NMID (National Merchant ID)</label>
                    <input type="text" name="nmid" class="form-control" value="<?= e($settings['nmid'] ?? 'ID1023456789012') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Masa Berlaku QRIS (detik)</label>
                    <input type="number" name="qris_durasi" class="form-control" value="<?= (int)($settings['qris_durasi'] ?? 900) ?>" min="60" step="60" required>
                    <div class="fs-12 text-muted" style="margin-top: 4px;">Standar: 900 detik (15 menit). QRIS kedaluwarsa setelah waktu ini.</div>
                </div>

                <!-- Toggles -->
                <div style="border-top: 1px solid var(--line); margin-top: 16px; padding-top: 8px;">
                    <div class="toggle-switch">
                        <div class="switch-label-wrap">
                            <div class="fw-600 fs-14">Pembayaran QRIS Aktif</div>
                            <div class="fs-12 text-muted">Izinkan pelanggan checkout melalui QRIS</div>
                        </div>
                        <input type="checkbox" name="toggle_qris" class="switch-input" <?= !empty($settings['toggle_qris']) ? 'checked' : '' ?>>
                    </div>

                    <div class="toggle-switch">
                        <div class="switch-label-wrap">
                            <div class="fw-600 fs-14">Notifikasi Pesanan & Stok</div>
                            <div class="fs-12 text-muted">Tampilkan pop-up peringatan pembayaran dan stok tipis</div>
                        </div>
                        <input type="checkbox" name="toggle_notifikasi" class="switch-input" <?= !empty($settings['toggle_notifikasi']) ? 'checked' : '' ?>>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <button type="submit" class="btn btn-primary btn-block" style="height: 48px; font-size: 15px;">
                <?= icon('check', 18) ?>
                <span>Simpan Semua Perubahan</span>
            </button>

        </div>

    </div>
</form>

<?php
admin_page_end('pengaturan');
