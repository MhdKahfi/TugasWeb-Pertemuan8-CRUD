<?php
declare(strict_types=1);

/**
 * Koneksi PDO dengan Singleton pattern.
 * - constructor private  -> tidak bisa di-new dari luar
 * - __clone private      -> tidak bisa di-clone
 * - __wakeup melempar    -> tidak bisa di-unserialize
 * - getInstance()        -> satu-satunya pintu masuk, selalu objek yang sama
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $cfg = (require __DIR__ . '/../config/config.php')['db'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // prepared statement asli dari MySQL
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Koneksi database gagal. Pastikan MySQL sudah berjalan, database '
                . '"inventaris_db" sudah diimport, dan user/password di config/config.php benar.',
                0,
                $e
            );
        }
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new LogicException('Singleton tidak boleh di-unserialize.');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
