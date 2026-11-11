<?php
declare(strict_types=1);

/**
 * Bootstrap Inisialisasi Dashboard Admin
 */

const BASE_DIR = __DIR__ . '/..';

function cfg(?string $key = null)
{
    static $c = null;
    if ($c === null) {
        $c = require __DIR__ . '/config.php';
    }
    return $key === null ? $c : ($c[$key] ?? null);
}

date_default_timezone_set((string) (cfg('timezone') ?? 'Asia/Jakarta'));

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/qr.php';
require_once __DIR__ . '/layout.php';

/* ------------------------------------------------------------------ */
/* Helper Umum & Format                                               */
/* ------------------------------------------------------------------ */

// Blokir request otomatis browser .well-known agar tidak salah memicu router fallback
if (str_contains($_SERVER['REQUEST_URI'] ?? '', '.well-known')) {
    http_response_code(404);
    exit;
}

function base_url(string $path = ''): string
{
    $scriptDir = rtrim(str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $base = ($scriptDir !== '') ? $scriptDir : '';
    if ($path === '' || $path === '/') {
        return ($base !== '' ? $base : '') . '/';
    }
    return ($base !== '' ? $base : '') . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rupiah(int $n): string
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

function tanggal_id(int $ts, bool $withTime = true): string
{
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $h = $hari[(int) date('w', $ts)];
    $tgl = date('j', $ts) . ' ' . $bulan[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
    
    if ($withTime) {
        return $tgl . ', ' . date('H:i', $ts);
    }
    return $h . ', ' . $tgl;
}

function tanggal_hari_ini(): string
{
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $ts = time();
    $h = $hari[(int) date('w', $ts)];
    $b = $bulan[(int) date('n', $ts)];
    return $h . ', ' . date('j', $ts) . ' ' . $b . ' ' . date('Y', $ts);
}

function is_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function redirect(string $url): void
{
    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://') && !str_starts_with($url, '/')) {
        $url = base_url($url);
    }
    if (is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['redirect' => $url]);
    } else {
        header('Location: ' . $url);
    }
    exit;
}

function flash(?string $msg = null, string $type = 'info')
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}
