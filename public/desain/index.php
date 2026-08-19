<?php
/**
 * public/desain/index.php — Daftar Desain
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Desain;

AuthGuard::check();

$pageTitle    = 'Data Desain';
$modelDesain  = new Desain();
$desainList   = $modelDesain->getAll();

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
                    <i class="bi bi-palette2 me-2 text-primary"></i>Data Desain Pakaian
                </h2>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/spk-saw-kain/public/desain/create.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>+ Tambah Desain
                </a>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <?php if (empty($desainList)): ?>
                    <div class="empty-state">
                        <i class="bi bi-palette2"></i>
                        <p>Belum ada data desain.</p>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="/spk-saw-kain/public/desain/create.php" class="btn btn-primary btn-sm">+ Tambah Desain</a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                                <tr>
                                    <th width="50">No.</th>
                                    <th width="70">Foto</th>
                                    <th>Nama Desain</th>
                                    <th>Kategori</th>
                                    <th>Tanggal Dibuat</th>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <th width="120" class="text-center">Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($desainList as $i => $d): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <?php
                                        $fotoSrc = '';
                                        if ($d['foto']) {
                                            $fotoSrc = str_contains($d['foto'], '/')
                                                ? '/spk-saw-kain/public/assets/img/' . $d['foto']
                                                : '/spk-saw-kain/public/assets/img/uploads/' . $d['foto'];
                                        }
                                        ?>
                                        <?php if ($fotoSrc): ?>
                                        <img src="<?= htmlspecialchars($fotoSrc) ?>"
                                            class="desain-foto"
                                            alt="<?= htmlspecialchars($d['nama_desain']) ?>"
                                        >
                                        <?php else: ?>
                                        <div class="foto-placeholder">
                                            <i class="bi bi-image"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-600"><?= htmlspecialchars($d['nama_desain']) ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <?= htmlspecialchars($d['kategori']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">
                                        <?= date('d/m/Y', strtotime($d['created_at'])) ?>
                                    </td>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <td class="text-center">
                                        <a href="/spk-saw-kain/public/desain/edit.php?id=<?= $d['id'] ?>"
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="bi bi-pencil"></i> Ubah
                                        </a>
                                        <a href="/spk-saw-kain/public/desain/delete.php?id=<?= $d['id'] ?>"
                                           class="btn btn-sm btn-outline-danger confirm-delete"
                                           data-nama="<?= htmlspecialchars($d['nama_desain']) ?>">
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
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
