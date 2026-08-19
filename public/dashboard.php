<?php
/**
 * public/dashboard.php — Halaman Dashboard
 */
require_once __DIR__ . '/../src/autoload.php';

use Middleware\AuthGuard;
use Models\Desain;
use Models\BahanKain;
use Models\Kriteria;
use Models\Rekomendasi;

AuthGuard::check();

$pageTitle     = 'Dashboard';
$modelDesain   = new Desain();
$modelBahan    = new BahanKain();
$modelKriteria = new Kriteria();
$modelRek      = new Rekomendasi();

$jmlDesain    = $modelDesain->count();
$jmlBahan     = $modelBahan->count();
$jmlKriteria  = $modelKriteria->count();
$totalBobot   = $modelKriteria->getTotalBobot();
$isAdmin     = $_SESSION['role'] === 'admin';
$filterUser  = $isAdmin ? null : (int)$_SESSION['pengguna_id'];
$recentRek   = $modelRek->getRecent(5, $filterUser);

require_once __DIR__ . '/partials/header.php';
?>

<div class="layout-wrapper">
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <?php require_once __DIR__ . '/partials/navbar.php'; ?>

        <div class="content-wrapper">

            <!-- Warning total bobot -->
            <?php if (abs($totalBobot - 1.0) > 0.001 && $jmlKriteria > 0): ?>
            <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>
                    <strong>Perhatian:</strong> Total bobot kriteria = <strong><?= number_format($totalBobot, 3) ?></strong>
                    (seharusnya 1.000). Sistem SAW tidak bisa dijalankan.
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/spk-saw-kain/public/kriteria/index.php" class="alert-link">Perbaiki sekarang →</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stat Cards -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <a href="/spk-saw-kain/public/desain/index.php" class="stat-card">
                        <div class="stat-icon blue">
                            <i class="bi bi-palette2"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $jmlDesain ?></div>
                            <div class="stat-label">Data Desain</div>
                        </div>
                    </a>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <a href="/spk-saw-kain/public/bahan-kain/index.php" class="stat-card">
                        <div class="stat-icon green">
                            <i class="bi bi-layers"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $jmlBahan ?></div>
                            <div class="stat-label">Data Bahan Kain</div>
                        </div>
                    </a>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <a href="/spk-saw-kain/public/kriteria/index.php" class="stat-card">
                        <div class="stat-icon amber">
                            <i class="bi bi-sliders"></i>
                        </div>
                        <div>
                            <div class="stat-value"><?= $jmlKriteria ?></div>
                            <div class="stat-label">Data Kriteria</div>
                        </div>
                    </a>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <a href="/spk-saw-kain/public/rekomendasi/form.php" class="stat-card">
                        <div class="stat-icon sky">
                            <i class="bi bi-magic"></i>
                        </div>
                        <div>
                            <div class="stat-value" style="font-size:22px;">SAW</div>
                            <div class="stat-label">Form Rekomendasi</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Bobot Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-primary"></i>
                            Status Sistem
                        </div>
                        <div class="card-body py-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="fw-600">Total Bobot Kriteria:</span>
                                    <span class="ms-2 badge <?= abs($totalBobot - 1.0) <= 0.001 ? 'bg-success' : 'bg-danger' ?> fs-6">
                                        <?= number_format($totalBobot, 3) ?>
                                    </span>
                                    <?php if (abs($totalBobot - 1.0) <= 0.001): ?>
                                        <span class="text-success ms-2"><i class="bi bi-check-circle-fill"></i> Siap digunakan</span>
                                    <?php else: ?>
                                        <span class="text-danger ms-2"><i class="bi bi-x-circle-fill"></i> Tidak valid</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-clock-history me-2 text-primary"></i>Ringkasan Aktivitas Terbaru</span>
                    <a href="/spk-saw-kain/public/rekomendasi/hasil.php" class="btn btn-sm btn-outline-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($recentRek)): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>Belum ada riwayat rekomendasi.</p>
                        <a href="/spk-saw-kain/public/rekomendasi/form.php" class="btn btn-primary btn-sm">
                            Buat Rekomendasi Pertama
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pengguna</th>
                                    <th>Jenis Pakaian</th>
                                    <th>Aktivitas</th>
                                    <th>Rekomendasi Terbaik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRek as $r): ?>
                                <tr>
                                    <td>
                                        <span class="text-muted" style="font-size:12px;">
                                            <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-600"><?= htmlspecialchars($r['nama_pengguna']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($r['jenis_pakaian']) ?></td>
                                    <td><?= htmlspecialchars($r['aktivitas']) ?></td>
                                    <td>
                                        <?php if ($r['top_bahan']): ?>
                                        <span class="badge-terbaik">
                                            <i class="bi bi-award-fill"></i>
                                            <?= htmlspecialchars($r['top_bahan']) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /content-wrapper -->
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
