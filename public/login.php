<?php
/**
 * public/login.php — Halaman Login
 */
require_once __DIR__ . '/../src/autoload.php';

use Middleware\AuthGuard;
use Models\Pengguna;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, redirect ke dashboard
if (!empty($_SESSION['pengguna_id'])) {
    header('Location: /spk-saw-kain/public/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    AuthGuard::validateCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $modelPengguna = new Pengguna();
        $pengguna = $modelPengguna->authenticate($username, $password);

        if ($pengguna) {
            AuthGuard::login($pengguna);
            header('Location: /spk-saw-kain/public/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}

$csrfToken = AuthGuard::generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login Sistem Pendukung Keputusan Pemilihan Desain dan Bahan Kain Pakaian — Rumah Jahit Eti">
    <title>Login — SPK Rumah Jahit Eti</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/spk-saw-kain/public/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <!-- Logo -->
        <div class="login-logo">
            <i class="bi bi-scissors"></i>
        </div>

        <h1 class="login-title">SPK Pemilihan Kain</h1>
        <p class="login-subtitle">Rumah Jahit Eti — Tasikmalaya<br>Sistem Pendukung Keputusan SAW</p>

        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert" id="login-error">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="/spk-saw-kain/public/login.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-person text-muted"></i>
                    </span>
                    <input
                        type="text"
                        class="form-control border-start-0"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input
                        type="password"
                        class="form-control border-start-0"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" id="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>LOGIN
            </button>
        </form>

        <p class="text-center text-muted mt-4 mb-0" style="font-size:12px;">
            &copy; <?= date('Y') ?> SPK SAW — Skripsi Informatika
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle show/hide password
document.getElementById('togglePassword')?.addEventListener('click', function () {
    const input = document.getElementById('password');
    const icon  = document.getElementById('togglePasswordIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
});
</script>
</body>
</html>
