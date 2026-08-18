<?php

namespace Middleware;

/**
 * AuthGuard — proteksi halaman dengan session check + role check
 */
class AuthGuard
{
    /**
     * Pastikan user sudah login. Kalau belum, redirect ke login.
     * Panggil di baris paling atas setiap file public/ (selain login.php).
     */
    public static function check(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['pengguna_id'])) {
            header('Location: /spk-saw-kain/public/login.php');
            exit;
        }
    }

    /**
     * Pastikan user sudah login DAN role-nya admin.
     * Kalau tidak, redirect ke dashboard dengan pesan error.
     */
    public static function requireAdmin(): void
    {
        self::check();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            $_SESSION['flash_error'] = 'Akses ditolak. Halaman ini hanya untuk Admin.';
            header('Location: /spk-saw-kain/public/dashboard.php');
            exit;
        }
    }

    /**
     * Simpan data user ke session setelah login berhasil.
     */
    public static function login(array $pengguna): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true); // cegah session fixation

        $_SESSION['pengguna_id']   = $pengguna['id'];
        $_SESSION['nama']          = $pengguna['nama'];
        $_SESSION['username']      = $pengguna['username'];
        $_SESSION['role']          = $pengguna['role'];
    }

    /**
     * Hapus semua session (logout).
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();
    }

    /**
     * Generate CSRF token dan simpan ke session.
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validasi CSRF token dari form POST.
     */
    public static function validateCsrf(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = $_POST['csrf_token'] ?? '';

        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            die('Token tidak valid. Refresh halaman dan coba lagi.');
        }

        // Regenerate token setelah dipakai (one-time use)
        unset($_SESSION['csrf_token']);
    }
}
