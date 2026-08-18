<?php
/**
 * public/desain/create.php — Tambah Desain Baru
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Desain;
use Helpers\Validator;

AuthGuard::requireAdmin();

$pageTitle   = 'Tambah Desain';
$errors      = [];
$oldInput    = [];
$csrfToken   = AuthGuard::generateCsrfToken();

// Daftar kategori preset
$kategoriList = ['Atasan', 'Bawahan', 'Terusan', 'Aksesoris', 'Lainnya'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthGuard::validateCsrf();

    $namaDesain = Validator::sanitizeString($_POST['nama_desain'] ?? '');
    $kategori   = Validator::sanitizeString($_POST['kategori']   ?? '');
    $oldInput   = ['nama_desain' => $namaDesain, 'kategori' => $kategori];

    $v = new Validator();
    $v->required('nama_desain', $namaDesain, 'Nama Desain')
      ->maxLength('nama_desain', $namaDesain, 100, 'Nama Desain')
      ->required('kategori', $kategori, 'Kategori')
      ->inList('kategori', $kategori, $kategoriList, 'Kategori');
    $errors = $v->getErrors();

    // Handle upload foto
    $fotoName = null;
    if (!empty($_FILES['foto']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext     = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if (!in_array($ext, $allowed)) {
            $errors['foto'] = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WebP.';
        } elseif ($_FILES['foto']['size'] > $maxSize) {
            $errors['foto'] = 'Ukuran foto maksimal 2 MB.';
        } elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $errors['foto'] = 'Upload foto gagal.';
        } else {
            $uploadDir = __DIR__ . '/../assets/img/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fotoName = uniqid('desain_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoName)) {
                $errors['foto'] = 'Gagal menyimpan file foto.';
                $fotoName = null;
            }
        }
    }

    if (empty($errors)) {
        $modelDesain = new Desain();
        $modelDesain->create($namaDesain, $kategori, $fotoName);

        $_SESSION['flash_success'] = "Desain \"$namaDesain\" berhasil ditambahkan.";
        header('Location: /spk-saw-kain/public/desain/index.php');
        exit;
    }
}

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
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Desain Baru
                </h2>
                <a href="/spk-saw-kain/public/desain/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>

            <div class="card" style="max-width:600px;">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <!-- Nama Desain -->
                        <div class="mb-4">
                            <label for="nama_desain" class="form-label">Nama Desain <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control <?= isset($errors['nama_desain']) ? 'is-invalid' : '' ?>"
                                id="nama_desain"
                                name="nama_desain"
                                value="<?= htmlspecialchars($oldInput['nama_desain'] ?? '') ?>"
                                placeholder="cth. Kemeja Polos, Blouse Casual"
                                required
                            >
                            <?php if (isset($errors['nama_desain'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['nama_desain']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Kategori -->
                        <div class="mb-4">
                            <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['kategori']) ? 'is-invalid' : '' ?>"
                                    id="kategori" name="kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategoriList as $kat): ?>
                                <option value="<?= htmlspecialchars($kat) ?>"
                                    <?= ($oldInput['kategori'] ?? '') === $kat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['kategori'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['kategori']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Foto -->
                        <div class="mb-4">
                            <label for="foto" class="form-label">Foto Desain</label>
                            <input
                                type="file"
                                class="form-control <?= isset($errors['foto']) ? 'is-invalid' : '' ?>"
                                id="foto"
                                name="foto"
                                accept="image/jpeg,image/png,image/webp"
                            >
                            <div class="form-text">Format: JPG, PNG, WebP. Maks. 2 MB. (Opsional)</div>
                            <?php if (isset($errors['foto'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($errors['foto']) ?></div>
                            <?php endif; ?>

                            <!-- Preview -->
                            <img id="foto-preview" src="#" alt="Preview foto"
                                 class="mt-3 desain-foto" style="display:none;width:120px;height:120px;">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Simpan
                            </button>
                            <a href="/spk-saw-kain/public/desain/index.php" class="btn btn-outline-secondary">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/spk-saw-kain/public/assets/js/main.js"></script>
</body>
</html>
