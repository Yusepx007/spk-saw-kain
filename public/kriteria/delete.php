<?php
/**
 * public/kriteria/delete.php — Hapus Kriteria
 */
require_once __DIR__ . '/../../src/autoload.php';

use Middleware\AuthGuard;
use Models\Kriteria;
use Helpers\Validator;

AuthGuard::requireAdmin();

$id            = Validator::sanitizeInt($_GET['id'] ?? 0);
$modelKriteria = new Kriteria();
$kriteria      = $modelKriteria->getById($id);

if (!$kriteria) {
    $_SESSION['flash_error'] = 'Data kriteria tidak ditemukan.';
    header('Location: /spk-saw-kain/public/kriteria/index.php');
    exit;
}

try {
    $modelKriteria->delete($id);
    $_SESSION['flash_success'] = "Kriteria \"{$kriteria['nama_kriteria']}\" berhasil dihapus.";
} catch (\Exception $e) {
    error_log('[Delete Kriteria Error] ' . $e->getMessage());
    $_SESSION['flash_error'] = 'Gagal menghapus kriteria. Kriteria ini mungkin masih digunakan di data nilai bahan kain.';
}

header('Location: /spk-saw-kain/public/kriteria/index.php');
exit;
