<?php

namespace Models;

use Config\Database;
use PDO;

/**
 * Model Pengguna — akses tabel `pengguna`
 */
class Pengguna
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Cari pengguna berdasarkan username (untuk login).
     */
    public function findByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, nama, username, password, role FROM pengguna WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /**
     * Verifikasi password + return data pengguna jika cocok.
     */
    public function authenticate(string $username, string $password): array|false
    {
        $pengguna = $this->findByUsername($username);

        if (!$pengguna) {
            return false;
        }

        if (!password_verify($password, $pengguna['password'])) {
            return false;
        }

        return $pengguna;
    }

    /**
     * Ambil semua pengguna.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT id, nama, username, role, created_at FROM pengguna ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu pengguna by ID.
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT id, nama, username, role, created_at FROM pengguna WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Buat pengguna baru.
     */
    public function create(string $nama, string $username, string $password, string $role = 'pengguna'): bool
    {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO pengguna (nama, username, password, role) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$nama, $username, $hashed, $role]);
    }

    /**
     * Update pengguna (tanpa password).
     */
    public function update(int $id, string $nama, string $username, string $role): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE pengguna SET nama = ?, username = ?, role = ? WHERE id = ?'
        );
        return $stmt->execute([$nama, $username, $role, $id]);
    }

    /**
     * Update password pengguna.
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare('UPDATE pengguna SET password = ? WHERE id = ?');
        return $stmt->execute([$hashed, $id]);
    }

    /**
     * Hapus pengguna.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM pengguna WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Cek apakah username sudah dipakai (exclude ID tertentu untuk edit).
     */
    public function isUsernameTaken(string $username, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM pengguna WHERE username = ? AND id != ?');
        $stmt->execute([$username, $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
