<?php
/**
 * Konfigurasi aplikasi.
 * Sesuaikan user/password dengan MySQL di komputer Anda.
 * (XAMPP default: user "root", password kosong.)
 */
return [
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => (int) (getenv('DB_PORT') ?: 3306),
        'name'    => getenv('DB_NAME') ?: 'inventaris_db',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
        'charset' => 'utf8mb4',
    ],
    'per_page'     => 10,   // jumlah produk per halaman
    'stok_menipis' => 5,    // stok <= angka ini dianggap menipis
];
