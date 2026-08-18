<?php
/**
 * public/kriteria/index.php — Daftar Kriteria
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Kriteria;
use Helpers\Validator;

AuthGuard::check();

$pageTitle     = 'Data Kriteria';
$modelKriteria = new Kriteria();
$kriteriaList  = $modelKriteria->getAll();
$totalBobot    = $modelKriteria->getTotalBobot();
$bobotValid    = abs($totalBobot - 1.0) <= 0.001;

require_once __DIR__ . '/../partials/header.php';
?>

<div class="layout-wrapper">
    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
        <?php require_once __DIR__ . '/../partials/navbar.php'; ?>

        <div class="content-wrapper">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="bi bi-sliders me-2 text-primary"></i>Data Kriteria Penilaian
                </h2>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/spk-saw-kain/public/kriteria/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>+ Tambah Kriteria
                </a>
                <?php endif; ?>
            </div>

            <!-- Total Bobot Alert -->
            <div class="alert <?= $bobotValid ? 'alert-success' : 'alert-danger' ?> d-flex align-items-center mb-4">
                <i class="bi <?= $bobotValid ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2 fs-5"></i>
                <div>
                    <strong>Total Bobot Kriteria: <?= number_format($totalBobot, 3) ?></strong>
                    <?php if ($bobotValid): ?>
                        — Valid ✓ (sistem SAW siap dijalankan)
                    <?php else: ?>
                        — <strong>Tidak valid!</strong> Total harus = 1.000.
                        Selisih: <?= number_format(abs(1.0 - $totalBobot), 3) ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($kriteriaList)): ?>
                    <div class="empty-state">
                        <i class="bi bi-sliders"></i>
                        <p>Belum ada data kriteria.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Kriteria</th>
                                    <th class="text-center">Bobot (W)</th>
                                    <th class="text-center">Atribut</th>
                                    <th>Dibuat</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kriteriaList as $i => $k): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-600"><?= htmlspecialchars($k['nama_kriteria']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                                            <?= number_format($k['bobot'], 3) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($k['atribut'] === 'benefit'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-arrow-up-circle me-1"></i>Benefit
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-arrow-down-circle me-1"></i>Cost
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">
                                        <?= date('d/m/Y', strtotime($k['created_at'])) ?>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td class="text-center">
                                        <a href="/spk-saw-kain/public/kriteria/edit.php?id=<?= $k['id'] ?>"
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i> Ubah
                                        </a>
                                        <a href="/spk-saw-kain/public/kriteria/delete.php?id=<?= $k['id'] ?>"
                                           class="btn btn-sm btn-outline-danger confirm-delete"
                                           data-nama="<?= htmlspecialchars($k['nama_kriteria']) ?>">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="2" class="text-end fw-700">Total Bobot:</td>
                                    <td class="text-center fw-700">
                                        <span class="<?= $bobotValid ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($totalBobot, 3) ?>
                                        </span>
                                    </td>
                                    <td colspan="<?= $_SESSION['role'] === 'admin' ? 3 : 2 ?>"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
