<?php
declare(strict_types=1);

/**
 * Di-include di paling atas setiap halaman:
 * session, autoload sederhana, helper, dan penanganan error yang ramah.
 */
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/produk_repo.php';

set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);
    $pesan = $e instanceof RuntimeException
        ? $e->getMessage()
        : 'Terjadi kesalahan pada server. Silakan coba lagi.';
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>Terjadi kesalahan</title>'
       . '<link rel="stylesheet" href="assets/style.css"></head><body>'
       . '<main class="container"><div class="panel error-panel"><h1>Terjadi kesalahan</h1>'
       . '<p>' . htmlspecialchars($pesan, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>'
       . '<p><a class="btn" href="index.php">Kembali ke daftar produk</a></p>'
       . '</div></main></body></html>';
});

function db(): PDO
{
    return Database::getInstance()->getConnection();
}

function config(string $key, $default = null)
{
    static $cfg = null;
    $cfg ??= require __DIR__ . '/../config/config.php';
    return $cfg[$key] ?? $default;
}
