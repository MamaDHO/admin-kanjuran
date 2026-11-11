<?php
declare(strict_types=1);

/**
 * Autentikasi & Guard Admin Dashboard
 */

function auth_check(): bool
{
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['admin_user']);
}

function auth_user(): ?array
{
    if (!auth_check()) {
        return null;
    }
    return $_SESSION['admin_user'];
}

function auth_guard(): void
{
    if (!auth_check()) {
        // Simpan url target untuk redirect setelah login jika bukan ajax
        if (!is_ajax() && !empty($_SERVER['REQUEST_URI'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        }
        flash('Silakan login terlebih dahulu untuk mengakses dashboard admin.', 'error');
        redirect('login.php');
    }
}

function auth_login(string $username, string $password): bool
{
    $admin = admin_get_user();
    $uname = trim($username);

    if (strcasecmp($uname, (string) ($admin['username'] ?? 'admin')) === 0) {
        if (password_verify($password, (string) ($admin['password_hash'] ?? ''))) {
            // Sukses login
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = [
                'username' => $admin['username'],
                'nama'     => $admin['nama'] ?? 'Admin Kantin',
                'role'     => $admin['role'] ?? 'Administrator',
                'email'    => $admin['email'] ?? 'admin@kantinkejujuran.app',
                'login_at' => time(),
            ];
            return true;
        }
    }

    return false;
}

function auth_logout(): void
{
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_user']);
    session_destroy();
}
