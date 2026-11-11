<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Jika sudah login, langsung ke dashboard
if (auth_check()) {
    redirect('index.php');
}

$flash = flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1677F8">
    <title>Login Admin · Kantin Kejujuran</title>
    <base href="<?= e(base_url('/')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        @media (max-width: 1023px) {
            .login-page-wrap {
                flex-direction: column;
                background: linear-gradient(180deg, var(--blue) 0%, var(--blue-dark) 45%, var(--bg-page) 45%, var(--bg-page) 100%);
            }
            .mobile-login-brand {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                color: #fff;
                padding: 48px 24px 28px;
            }
            .login-form-panel {
                background: transparent;
                padding: 0 20px 40px;
            }
            .login-card {
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(33, 50, 79, 0.12);
            }
        }
        @media (min-width: 1024px) {
            .mobile-login-brand {
                display: none;
            }
        }
    </style>
</head>
<body>

<?php if ($flash): ?>
    <div class="toast-container">
        <div class="toast toast-<?= e($flash['type']) ?>" role="alert">
            <?= icon($flash['type'] === 'error' ? 'alert-circle' : 'check', 20) ?>
            <span><?= e($flash['msg']) ?></span>
        </div>
    </div>
<?php endif; ?>

<div class="login-page-wrap">

    <!-- Left Brand Panel (Desktop) -->
    <div class="login-brand-panel">
        <div style="margin-bottom: 28px;">
            <?= icon('logo', 72, '', '#fff') ?>
        </div>
        <h1 style="font-size: 38px; font-weight: 700; line-height: 1.2;">Kantin Kejujuran</h1>
        <div style="font-size: 18px; color: #D6E6FF; margin-top: 8px;">Admin Dashboard</div>
        
        <p style="font-size: 16px; color: #D6E6FF; max-width: 380px; margin-top: 40px; line-height: 1.6;">
            Kelola transaksi, produk, stok, dan laporan kantin kejujuran dengan mudah.
        </p>

        <!-- Decorative SVG waves at bottom -->
        <svg class="login-brand-wave" viewBox="0 0 768 200" fill="none" preserveAspectRatio="none">
            <path d="M0 80C140 30 280 40 420 80C560 120 680 90 768 50V200H0Z" fill="#FFFFFF" fill-opacity="0.08"/>
            <path d="M0 130C160 80 320 90 480 130C600 160 700 130 768 100V200H0Z" fill="#FFFFFF" fill-opacity="0.1"/>
        </svg>
    </div>

    <!-- Mobile Top Brand Section (<= 1023px) -->
    <div class="mobile-login-brand">
        <div style="margin-bottom: 16px;">
            <?= icon('logo', 52, '', '#fff') ?>
        </div>
        <h1 style="font-size: 26px; font-weight: 700;">Kantin Kejujuran</h1>
        <div style="font-size: 14px; color: #D6E6FF; margin-top: 4px;">Admin Dashboard</div>
        <p style="font-size: 13px; color: #D6E6FF; max-width: 300px; margin-top: 12px; line-height: 1.4;">
            Kelola transaksi, produk, stok, dan laporan kantin dengan mudah.
        </p>
    </div>

    <!-- Right Form Panel -->
    <div class="login-form-panel">
        <div class="login-card">
            <h2 style="font-size: 26px; font-weight: 700; color: var(--navy); margin-bottom: 6px;">Login Admin</h2>
            <p style="font-size: 14px; color: var(--grey); margin-bottom: 28px;">Masuk untuk mengelola kantin.</p>

            <form action="aksi.php" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="act" value="login">

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon-left"><?= icon('user', 18) ?></span>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 8px;">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon-left"><?= icon('lock', 18) ?></span>
                        <input type="password" id="password" name="password" class="form-control has-right" placeholder="Masukkan password" required autocomplete="current-password">
                        <button type="button" class="input-icon-right btn-toggle-password" data-target="password" aria-label="Lihat Password">
                            <?= icon('eye', 18) ?>
                        </button>
                    </div>
                </div>

                <div style="text-align: right; margin-bottom: 24px;">
                    <a href="javascript:alert('Gunakan username: admin dan password: admin123 (kredensial bawaan).')" style="font-size: 13px; font-weight: 600; color: var(--blue);">Lupa password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="height: 50px; font-size: 16px;">
                    Masuk
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; font-size: 12.5px; color: var(--grey);">
                Hanya untuk admin. Pelanggan tidak perlu login.
            </div>
        </div>
    </div>

</div>

<script src="assets/js/admin.js"></script>
</body>
</html>
