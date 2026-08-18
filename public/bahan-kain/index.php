<?php
/**
 * public/bahan-kain/index.php — Daftar Bahan Kain
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\BahanKain;
use Models\Kriteria;
use Models\NilaiBahan;

AuthGuard::check();

$pageTitle    = 'Data Bahan Kain';
$modelBahan   = new BahanKain();
$modelKriteria = new Kriteria();
$modelNilai   = new NilaiBahan();

$bahanList    = $modelBahan->getAll();
$kriteriaList = $modelKriteria->getAll();

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
                    <i class="bi bi-layers me-2 text-primary"></i>Data Bahan Kain
                </h2>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/spk-saw-kain/public/bahan-kain/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>+ Tambah Bahan Kain
                </a>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($bahanList)): ?>
                    <div class="empty-state">
                        <i class="bi bi-layers"></i>
                        <p>Belum ada data bahan kain.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Bahan</th>
                                    <?php foreach ($kriteriaList as $k): ?>
                                    <th class="text-center" title="Bobot: <?= $k['bobot'] ?> | <?= $k['atribut'] ?>">
                                        <?= htmlspecialchars($k['nama_kriteria']) ?>
                                        <br><small class="text-muted fw-normal">W=<?= $k['bobot'] ?></small>
                                    </th>
                                    <?php endforeach; ?>
                                    <th>Dibuat</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <th class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bahanList as $i => $b):
                                    $nilaiMap = $modelNilai->getByBahanId($b['id']);
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td class="fw-600"><?= htmlspecialchars($b['nama_bahan']) ?></td>
                                    <?php foreach ($kriteriaList as $k): ?>
                                    <td class="text-center">
                                        <?php $nilai = $nilaiMap[$k['id']] ?? null; ?>
                                        <?php if ($nilai !== null): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <?= number_format($nilai, 2) ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="text-danger">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                    <td class="text-muted" style="font-size:12px;">
                                        <?= date('d/m/Y', strtotime($b['created_at'])) ?>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td class="text-center">
                                        <a href="/spk-saw-kain/public/bahan-kain/edit.php?id=<?= $b['id'] ?>"
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i> Ubah
                                        </a>
                                        <a href="/spk-saw-kain/public/bahan-kain/delete.php?id=<?= $b['id'] ?>"
                                           class="btn btn-sm btn-outline-danger confirm-delete"
                                           data-nama="<?= htmlspecialchars($b['nama_bahan']) ?>">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($kriteriaList)): ?>
            <div class="alert alert-warning mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Belum ada data kriteria. Tambahkan kriteria terlebih dahulu agar bisa mengisi nilai bahan kain.
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
