<?php
declare(strict_types=1);

/**
 * Halaman Penjualan dihapus dari navigasi utama dan digabung ke Laporan per SPESIFIKASI.md.
 * File ini mengarahkan (redirect) ke laporan.php.
 */
require_once __DIR__ . '/includes/bootstrap.php';
redirect('laporan.php');
