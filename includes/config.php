<?php
declare(strict_types=1);

define('APP_NAME', 'Kantin Kejujuran');
define('APP_TAGLINE', 'Ambil, Bayar, Jujur');
define('APP_VERSION', 'Versi 1.0 · Kantin Kejujuran');
define('DEMO_TODAY', '2026-11-24');

const PAGE_SIZE_DASHBOARD = 5;
const PAGE_SIZE_TRANSAKSI_DESKTOP = 10;
const PAGE_SIZE_TRANSAKSI_MOBILE = 5;
const PAGE_SIZE_LIST_DESKTOP = 10;
const PAGE_SIZE_NOTIF_DESKTOP = 8;
const PAGE_SIZE_NOTIF_MOBILE = 5;

function esc(?string $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rupiah(int $amount): string
{
    $abs = abs($amount);
    $formatted = 'Rp ' . number_format($abs, 0, ',', '.');
    return $amount < 0 ? '− ' . $formatted : $formatted;
}

function tanggal_id(string $dateYmd): string
{
    $hariMap = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
    $bulanMap = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $ts = strtotime($dateYmd);
    if ($ts === false) {
        return $dateYmd;
    }
    $hari = $hariMap[date('l', $ts)] ?? '';
    $tgl = (int)date('j', $ts);
    $bln = $bulanMap[(int)date('n', $ts)] ?? '';
    $thn = date('Y', $ts);
    return trim("$hari, $tgl $bln $thn", ', ');
}

function tanggal_pendek(string $dateYmd): string
{
    $bulanSingkat = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agt',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];
    $ts = strtotime($dateYmd);
    if ($ts === false) {
        return $dateYmd;
    }
    $tgl = (int)date('j', $ts);
    $bln = $bulanSingkat[(int)date('n', $ts)] ?? '';
    $thn = date('Y', $ts);
    return "$tgl $bln $thn";
}

function tanggal_lengkap_no_hari(string $dateYmd): string
{
    $bulanMap = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $ts = strtotime($dateYmd);
    if ($ts === false) {
        return $dateYmd;
    }
    return (int)date('j', $ts) . ' ' . ($bulanMap[(int)date('n', $ts)] ?? '') . ' ' . date('Y', $ts);
}
