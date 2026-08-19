<?php

namespace Config;

use PDO;
use PDOException;

/**
 * Database — Singleton PDO connection
 */
class Database
{
    private static ?PDO $instance = null;

    // Konfigurasi koneksi — MySQL berjalan di 127.0.0.1:3306
    private static string $host     = '127.0.0.1';
    private static int    $port     = 3306;
    private static string $dbname   = 'spk_saw_kain';
    private static string $username = 'root';
    private static string $password = '';
    private static string $charset  = 'utf8mb4';

    /** Jangan izinkan instantiasi langsung */
    private function __construct() {}
    private function __clone() {}

    /**
     * Ambil instance PDO (dibuat sekali, dipakai ulang)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::$host,
                self::$port,
                self::$dbname,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                // Log error ke server, jangan expose ke user
                error_log('[DB Error] ' . $e->getMessage());
                die('Koneksi database gagal. Hubungi administrator.');
            }
        }

        return self::$instance;
    }
}
