<?php
// Ambil dan hapus flash message dari session
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- Top Navbar -->
<nav class="top-navbar">
    <!-- Mobile toggle -->
    <button class="sidebar-toggle d-lg-none" id="sidebarToggle" type="button">
        <i class="bi bi-list"></i>
    </button>

    <!-- Page Title -->
    <div class="navbar-title">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    </div>

    <!-- Right side -->
    <div class="navbar-right">
        <div class="navbar-user">
            <i class="bi bi-person-circle me-1"></i>
            <span><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></span>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php if ($flashSuccess): ?>
<div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
    <i class="bi bi-check-circle me-2"></i>
    <?= htmlspecialchars($flashSuccess) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($flashError): ?>
<div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i>
    <?= htmlspecialchars($flashError) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<!-- /Top Navbar -->
