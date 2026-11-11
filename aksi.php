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
        $aktif = isset($_POST['aktif']) ? (bool) $_POST['aktif'] : true;

        if ($nama === '' || $harga <= 0) {
            flash('Nama barang dan harga harus diisi dengan benar.', 'error');
            redirect('barang.php');
        }

        admin_save_product([
            'id'       => $id,
            'nama'     => $nama,
            'harga'    => $harga,
            'kategori' => $kategori,
            'stok'     => $stok,
            'batas'    => $batas,
            'aktif'    => $aktif,
            'gambar'   => $gambar,
        ]);

        $pesan = ($act === 'tambah_barang') ? 'Barang baru berhasil ditambahkan.' : 'Perubahan barang berhasil disimpan.';
        flash($pesan, 'success');
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
            admin_delete_product($id);
            flash('Barang berhasil dihapus.', 'success');
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

        admin_restock_product($id, $qty, $hargaBeli, $catatan);
        flash('Restock berhasil disimpan. Stok barang bertambah ' . $qty . ' pcs.', 'success');
        redirect('stok.php');
        break;

    case 'simpan_pengaturan':
        auth_guard();
        if (!csrf_valid()) {
            flash('Sesi form tidak valid.', 'error');
            redirect('pengaturan.php');
        }

        admin_save_settings([
            'nama_toko'         => $_POST['nama_toko'] ?? null,
            'tagline'           => $_POST['tagline'] ?? null,
            'nama_merchant'     => $_POST['nama_merchant'] ?? null,
            'nmid'              => $_POST['nmid'] ?? null,
            'batas_stok_tipis'  => $_POST['batas_stok_tipis'] ?? null,
            'toggle_qris'       => isset($_POST['toggle_qris']),
            'toggle_notifikasi' => isset($_POST['toggle_notifikasi']),
        ]);

        // Cek jika update profil
        if (!empty($_POST['admin_nama']) && !empty($_POST['admin_email'])) {
            admin_update_profile((string) $_POST['admin_nama'], (string) $_POST['admin_email']);
        }

        // Cek jika ganti password
        if (!empty($_POST['pass_lama']) && !empty($_POST['pass_baru'])) {
            $res = admin_update_password((string) $_POST['pass_lama'], (string) $_POST['pass_baru']);
            if (!$res['success']) {
                flash($res['error'], 'error');
                redirect('pengaturan.php');
            }
        }

        flash('Pengaturan berhasil diperbarui.', 'success');
        redirect('pengaturan.php');
        break;

    default:
        redirect('index.php');
}
