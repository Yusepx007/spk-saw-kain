<?php
/**
 * public/desain/delete.php — Hapus Desain
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Desain;
use Helpers\Validator;

AuthGuard::requireAdmin();

$id          = Validator::sanitizeInt($_GET['id'] ?? 0);
$modelDesain = new Desain();
$desain      = $modelDesain->getById($id);

if (!$desain) {
    $_SESSION['flash_error'] = 'Data desain tidak ditemukan.';
    header('Location: /spk-saw-kain/public/desain/index.php');
    exit;
}

try {
    // Hapus file foto jika ada
    if ($desain['foto']) {
        $fotoPath = __DIR__ . '/../assets/img/uploads/' . $desain['foto'];
        if (file_exists($fotoPath)) {
            @unlink($fotoPath);
        }
    }

    $modelDesain->delete($id);
    $_SESSION['flash_success'] = "Desain \"{$desain['nama_desain']}\" berhasil dihapus.";
} catch (\Exception $e) {
    error_log('[Delete Desain Error] ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Gagal menghapus desain. Mungkin data masih digunakan di riwayat rekomendasi.';
}

header('Location: /spk-saw-kain/public/desain/index.php');
exit;
