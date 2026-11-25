<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$act = $_REQUEST['act'] ?? $_POST['act'] ?? '';

switch ($act) {
    case 'login':
        if (!csrf_valid()) {
            flash('Sesi form tidak valid. Silakan coba lagi.', 'error');
            redirect('login.php');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            flash('Username dan password wajib diisi.', 'error');
            redirect('login.php');
        }

        if (auth_login($username, $password)) {
            flash('Selamat datang kembali di Dashboard Admin!', 'success');
            $target = $_SESSION['redirect_after_login'] ?? 'index.php';
            unset($_SESSION['redirect_after_login']);
            redirect($target);
        } else {
            flash('Username atau password salah.', 'error');
            redirect('login.php');
        }
        break;

    case 'logout':
        auth_logout();
        flash('Anda telah berhasil keluar.', 'info');
        redirect('login.php');
        break;

    case 'baca_notif':
        auth_guard();
        admin_mark_notifications_read();
        $kembali = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        redirect($kembali);
        break;

    case 'hapus_notif':
        auth_guard();
        $id = trim((string) ($_REQUEST['id'] ?? ''));
        if ($id !== '') {
            admin_delete_notification($id);
            flash('Notifikasi berhasil dihapus.', 'info');
        }
        $kembali = $_SERVER['HTTP_REFERER'] ?? 'notifikasi.php';
        redirect($kembali);
        break;

    case 'hapus_semua_notif':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('notifikasi.php');
        }
        admin_clear_all_notifications();
        flash('Semua notifikasi berhasil dihapus.', 'info');
        redirect('notifikasi.php');
        break;

    case 'tambah_barang':
    case 'edit_barang':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('barang.php');
        }

        $id = trim((string) ($_POST['id'] ?? ''));
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $harga = (int) ($_POST['harga'] ?? 0);
        $kategori = trim((string) ($_POST['kategori'] ?? 'Makanan'));
        $stok = (int) ($_POST['stok'] ?? 0);
        $batas = (int) ($_POST['batas'] ?? 5);
        $gambar = trim((string) ($_POST['gambar'] ?? 'mie-instan.png'));
        $penitipId = isset($_POST['penitip_id']) && $_POST['penitip_id'] !== '' ? (int) $_POST['penitip_id'] : null;
        $komisi = (int) ($_POST['komisi'] ?? 0);
        $aktif = isset($_POST['aktif']) ? (bool) $_POST['aktif'] : true;

        if ($nama === '' || $harga <= 0) {
            flash('Nama barang dan harga harus diisi dengan benar.', 'error');
            redirect('barang.php');
        }

        admin_save_product([
            'id'         => $id,
            'nama'       => $nama,
            'harga'      => $harga,
            'kategori'   => $kategori,
            'stok'       => $stok,
            'batas'      => $batas,
            'penitip_id' => $penitipId,
            'komisi'     => $komisi,
            'aktif'      => $aktif,
            'gambar'     => $gambar,
        ]);

        $pesan = ($act === 'tambah_barang') ? 'Barang baru berhasil ditambahkan.' : 'Perubahan barang berhasil disimpan.';
        flash($pesan, 'success');
        redirect('barang.php');
        break;

    case 'nonaktifkan_barang':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('barang.php');
        }
        $id = trim((string) ($_POST['id'] ?? ''));
        if ($id !== '') {
            admin_toggle_active_product($id, false);
            flash('Barang berhasil dinonaktifkan.', 'success');
        }
        redirect('barang.php');
        break;

    case 'aktifkan_barang':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('barang.php');
        }
        $id = trim((string) ($_POST['id'] ?? ''));
        if ($id !== '') {
            admin_toggle_active_product($id, true);
            flash('Barang berhasil diaktifkan kembali.', 'success');
        }
        redirect('barang.php');
        break;

    case 'hapus_barang':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('barang.php');
        }

        $id = trim((string) ($_POST['id'] ?? ''));
        if ($id !== '') {
            // Per R-34: Menghapus barang = menonaktifkan agar data transaksi lama tetap utuh
            admin_toggle_active_product($id, false);
            flash('Barang berhasil dinonaktifkan.', 'success');
        }
        redirect('barang.php');
        break;

    case 'restock':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('stok.php');
        }

        $id = trim((string) ($_POST['id'] ?? ''));
        $qty = (int) ($_POST['jumlah'] ?? 0);
        $hargaBeli = (int) ($_POST['harga_beli'] ?? 0);
        $catatan = trim((string) ($_POST['catatan'] ?? ''));

        if ($id === '' || $qty <= 0) {
            flash('Pilih barang dan jumlah restock yang valid.', 'error');
            redirect('stok.php');
        }

        admin_restock_product($id, $qty, $catatan);
        flash('Restock berhasil disimpan. Stok barang bertambah ' . $qty . ' pcs.', 'success');
        redirect('stok.php');
        break;

    case 'koreksi_stok':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('stok.php');
        }

        $id = trim((string) ($_POST['id'] ?? ''));
        $stokBaru = (int) ($_POST['stok_baru'] ?? 0);
        $alasan = trim((string) ($_POST['alasan'] ?? ''));
        $catatanDetail = trim((string) ($_POST['catatan_detail'] ?? ''));
        if ($catatanDetail !== '') {
            $alasan = $alasan !== '' ? ($alasan . ' (' . $catatanDetail . ')') : $catatanDetail;
        }

        if ($id === '') {
            flash('Pilih barang yang ingin dikoreksi.', 'error');
            redirect('stok.php');
        }
        if ($alasan === '') {
            flash('Alasan koreksi stok wajib diisi.', 'error');
            redirect('stok.php');
        }

        $res = admin_koreksi_stok($id, $stokBaru, $alasan);
        if (!$res) {
            flash('Gagal melakukan koreksi stok.', 'error');
        } else {
            flash('Koreksi stok berhasil disimpan.', 'success');
        }
        redirect('stok.php');
        break;

    case 'tambah_penitip':
    case 'edit_penitip':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('penitip.php');
        }

        $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $noHp = trim((string) ($_POST['hp'] ?? $_POST['no_hp'] ?? ''));
        $catatan = trim((string) ($_POST['catatan'] ?? ''));

        if ($nama === '' || $noHp === '') {
            flash('Nama penitip dan nomor HP wajib diisi.', 'error');
            redirect('penitip.php');
        }

        admin_save_penitip([
            'id'      => $id,
            'nama'    => $nama,
            'hp'      => $noHp,
            'catatan' => $catatan,
        ]);

        flash('Data penitip berhasil disimpan.', 'success');
        redirect('penitip.php');
        break;

    case 'hapus_penitip':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('penitip.php');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('ID penitip tidak valid.', 'error');
            redirect('penitip.php');
        }

        $res = admin_delete_penitip($id);
        if (!$res['success']) {
            flash($res['error'], 'error');
        } else {
            flash('Penitip berhasil dihapus.', 'success');
        }
        redirect('penitip.php');
        break;

    case 'catat_setoran':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('penitip.php');
        }

        $penitipId = (int) ($_POST['penitip_id'] ?? 0);
        $jumlah = (int) ($_POST['jumlah'] ?? 0);
        $tanggal = trim((string) ($_POST['tanggal'] ?? date('Y-m-d')));
        $catatan = trim((string) ($_POST['catatan'] ?? ''));

        if ($penitipId <= 0 || $jumlah <= 0) {
            flash('Pilih penitip dan masukkan jumlah nominal setoran yang valid.', 'error');
            redirect('penitip.php');
        }

        $res = admin_add_setoran_penitip([
            'penitip_id' => $penitipId,
            'jumlah'     => $jumlah,
            'tanggal'    => $tanggal,
            'catatan'    => $catatan,
        ]);
        if (!$res['success']) {
            flash($res['error'], 'error');
        } else {
            flash('Setoran sebesar ' . format_rupiah($jumlah) . ' berhasil dicatat.', 'success');
        }
        redirect('penitip.php');
        break;

    case 'simpan_pengaturan':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('pengaturan.php');
        }

        $stokMin = isset($_POST['stok_min']) ? (int) $_POST['stok_min'] : (int) ($_POST['batas_stok_tipis'] ?? 5);
        $notifAktif = isset($_POST['notif_aktif']) || isset($_POST['toggle_notifikasi']);

        admin_save_settings([
            'nama_merchant' => $_POST['nama_merchant'] ?? null,
            'nmid'          => $_POST['nmid'] ?? null,
            'stok_min'      => $stokMin,
            'notif_aktif'   => $notifAktif,
        ]);

        flash('Pengaturan berhasil diperbarui.', 'success');
        redirect('pengaturan.php');
        break;

    default:
        redirect('index.php');
}
