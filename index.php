<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/icons.php';

$loggedOut = ($_GET['action'] ?? '') === 'logout';
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#1677F8">
<title>Login Admin — <?= esc(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
<main class="login-page">
  <section class="login-brand-panel" aria-label="Informasi Aplikasi">
    <svg class="login-wave" viewBox="0 0 768 244" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <path d="M0 80C120 20 260 10 400 50C540 90 660 60 768 0V244H0Z" fill="#FFFFFF" fill-opacity="0.08"/>
      <path d="M0 150C140 110 280 120 420 150C560 180 680 150 768 110V244H0Z" fill="#FFFFFF" fill-opacity="0.10"/>
    </svg>
    <div class="login-brand-content">
      <div class="login-brand-icon"><?= icon('logo', 120, '', 1.4) ?></div>
      <h1 class="login-brand-title"><?= esc(APP_NAME) ?></h1>
      <p class="login-brand-subtitle">Admin Dashboard</p>
      <p class="login-brand-desc">Kelola transaksi, barang, stok, dan laporan kantin kejujuran dengan mudah.</p>
    </div>
  </section>

  <section class="login-form-panel" aria-label="Formulir Masuk Admin">
    <div class="login-card">
      <h2 class="login-card-title">Login Admin</h2>
      <p class="login-card-sub">Masuk untuk mengelola kantin.</p>

      <?php if ($loggedOut): ?>
        <div class="badge badge-primary" style="width:100%; height:auto; padding:10px 12px; margin-bottom:14px; justify-content:flex-start;" role="status">
          Anda telah keluar dari sesi demo admin.
        </div>
      <?php endif; ?>

      <form id="loginForm" novalidate>
        <div class="form-group">
          <label for="usernameInput" class="form-label">Username</label>
          <div class="input-icon-wrap">
            <span class="input-left-icon"><?= icon('user', 18) ?></span>
            <input type="text"
                   id="usernameInput"
                   name="username"
                   class="form-control input-with-icons"
                   placeholder="Username"
                   autocomplete="username"
                   required>
          </div>
        </div>

        <div class="form-group">
          <label for="passwordInput" class="form-label">Password</label>
          <div class="input-icon-wrap">
            <span class="input-left-icon"><?= icon('lock', 18) ?></span>
            <input type="password"
                   id="passwordInput"
                   name="password"
                   class="form-control input-with-icons"
                   placeholder="Password"
                   autocomplete="current-password"
                   required>
            <button type="button"
                    id="togglePwBtn"
                    class="input-right-btn"
                    aria-label="Tampilkan password"
                    aria-pressed="false">
              <?= icon('eye', 18) ?>
            </button>
          </div>
        </div>

        <div id="loginError" class="form-error" role="alert" hidden></div>

        <button type="submit" class="btn btn-primary btn-full login-submit-btn">Masuk</button>
      </form>

      <p class="login-footer-hint">Hanya untuk admin. Pelanggan tidak perlu login.</p>
    </div>
  </section>
</main>

<script>
(function () {
  const form = document.getElementById('loginForm');
  const userEl = document.getElementById('usernameInput');
  const passEl = document.getElementById('passwordInput');
  const errEl = document.getElementById('loginError');
  const toggleBtn = document.getElementById('togglePwBtn');

  const eyeSvg = <?= json_encode(icon('eye', 18)) ?>;
  const eyeOffSvg = <?= json_encode(icon('eye-off', 18)) ?>;

  toggleBtn.addEventListener('click', function () {
    const show = passEl.type === 'password';
    passEl.type = show ? 'text' : 'password';
    toggleBtn.setAttribute('aria-pressed', show ? 'true' : 'false');
    toggleBtn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    toggleBtn.innerHTML = show ? eyeOffSvg : eyeSvg;
  });

  function showError(msg) {
    errEl.textContent = msg;
    errEl.hidden = false;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const u = userEl.value.trim();
    const p = passEl.value;
    if (!u || !p) {
      showError('Username dan password wajib diisi.');
      (!u ? userEl : passEl).focus();
      return;
    }
    if (u !== 'admin' || p !== 'admin123') {
      showError('Username atau password salah. Demo: admin / admin123');
      passEl.focus();
      return;
    }
    errEl.hidden = true;
    try { sessionStorage.setItem('kk_dashboard_new_auth_v1', '1'); } catch (_) {}
    window.location.href = 'dashboard.php';
  });
})();
</script>
</body>
</html>
