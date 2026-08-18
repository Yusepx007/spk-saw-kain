<?php
// Tentukan menu aktif berdasarkan URL saat ini
$currentUri = $_SERVER['REQUEST_URI'] ?? '';

function isActive(string $path): string {
    global $currentUri;
    return str_contains($currentUri, $path) ? 'active' : '';
}

$role = $_SESSION['role'] ?? 'pengguna';
?>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar d-flex flex-column">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-scissors"></i>
        </div>
        <div class="brand-text">
            <span class="brand-title">SPK Kain</span>
            <span class="brand-subtitle">Rumah Jahit Eti</span>
        </div>
    </div>

    <!-- User Info -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></span>
            <span class="user-role badge <?= $role === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                <?= $role === 'admin' ? 'Admin' : 'Pengguna' ?>
            </span>
        </div>
    </div>

    <hr class="sidebar-divider">

    <!-- Navigation Menu -->
    <ul class="sidebar-menu flex-grow-1">
        <li class="menu-label">Menu Utama</li>

        <li>
            <a href="/spk-saw-kain/public/dashboard.php" class="menu-item <?= isActive('dashboard') ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php if ($role === 'admin'): ?>
        <li class="menu-label">Data Master</li>

        <li>
            <a href="/spk-saw-kain/public/desain/index.php" class="menu-item <?= isActive('/desain/') ?>">
                <i class="bi bi-palette2"></i>
                <span>Data Desain</span>
            </a>
        </li>

        <li>
            <a href="/spk-saw-kain/public/bahan-kain/index.php" class="menu-item <?= isActive('/bahan-kain/') ?>">
                <i class="bi bi-layers"></i>
                <span>Data Bahan Kain</span>
            </a>
        </li>

        <li>
            <a href="/spk-saw-kain/public/kriteria/index.php" class="menu-item <?= isActive('/kriteria/') ?>">
                <i class="bi bi-sliders"></i>
                <span>Data Kriteria</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="menu-label">Rekomendasi</li>

        <li>
            <a href="/spk-saw-kain/public/rekomendasi/form.php" class="menu-item <?= isActive('rekomendasi/form') ?>">
                <i class="bi bi-magic"></i>
                <span>Form Rekomendasi</span>
            </a>
        </li>

        <li>
            <a href="/spk-saw-kain/public/rekomendasi/hasil.php" class="menu-item <?= isActive('rekomendasi/hasil') ?>">
                <i class="bi bi-trophy"></i>
                <span>Hasil Rekomendasi</span>
            </a>
        </li>
    </ul>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="/spk-saw-kain/public/logout.php" class="menu-item menu-logout">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>
<!-- /Sidebar -->
