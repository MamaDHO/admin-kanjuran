<?php
declare(strict_types=1);

/**
 * Konfigurasi Dashboard Admin "Kantin Kejujuran"
 */
return [
    'nama_toko'         => 'Kantin Kejujuran',
    'tagline'           => 'Ambil, Bayar, Jujur',
    'app_name'          => 'Admin Dashboard',
    'timezone'          => 'Asia/Jakarta',
    
    // Direktori data (JSON) khusus admin
    'data_dir'          => __DIR__ . '/../data',
    'pwa_data_dir'      => __DIR__ . '/../../pwa/data',
    'pwa_img_dir'       => __DIR__ . '/../../pwa/assets/img',

    // Pengaturan bawaan
    'batas_stok_tipis'  => 5,
    'qris_durasi'       => 15 * 60, // 15 menit
    'nmid'              => 'ID1023456789012',
    'nama_merchant'     => 'Kantin Kejujuran Official',
    'toggle_qris'       => true,
    'toggle_notifikasi' => true,
    
    // Kredensial default admin (bisa diubah dari halaman Pengaturan)
    'default_admin'     => [
        'username'      => 'admin',
        'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
        'nama'          => 'Admin Kantin',
        'role'          => 'Administrator',
        'email'         => 'admin@kantinkejujuran.app',
    ],
];
