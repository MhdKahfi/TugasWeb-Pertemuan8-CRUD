<?php
declare(strict_types=1);

/* Semua query yang membawa input pengguna memakai prepared statement. */

const SQL_DASAR_PRODUK =
    'FROM produk p
     JOIN kategori k ON k.id_kategori = p.id_kategori
     JOIN supplier s ON s.id_supplier = p.id_supplier';

/** Susun klausa WHERE + parameter untuk pencarian dan filter kategori. */
function filter_produk(string $q, int $kategori): array
{
    $where = [];
    $params = [];

    if ($q !== '') {
        // Dengan emulate prepares OFF, nama placeholder tidak boleh dipakai dua kali.
        $where[] = '(p.nama_produk LIKE :q1 OR p.kode_produk LIKE :q2 OR s.nama_supplier LIKE :q3)';
        $like = '%' . addcslashes($q, '%_\\') . '%';
        $params[':q1'] = $params[':q2'] = $params[':q3'] = $like;
    }
    if ($kategori > 0) {
        $where[] = 'p.id_kategori = :kategori';
        $params[':kategori'] = $kategori;
    }

    return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
}

function hitung_produk(PDO $pdo, string $q, int $kategori): int
{
    [$where, $params] = filter_produk($q, $kategori);
    $stmt = $pdo->prepare('SELECT COUNT(*) ' . SQL_DASAR_PRODUK . " $where");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/** Ambil produk (JOIN produk + kategori + supplier); $limit null = ambil semua. */
function ambil_produk(PDO $pdo, string $q, int $kategori, ?int $limit = null, int $offset = 0): array
{
    [$where, $params] = filter_produk($q, $kategori);

    $sql = 'SELECT p.id_produk, p.kode_produk, p.nama_produk, p.harga, p.stok,
                   k.nama_kategori, s.nama_supplier '
         . SQL_DASAR_PRODUK . " $where ORDER BY p.id_produk DESC";
    if ($limit !== null) {
        $sql .= ' LIMIT :limit OFFSET :offset';
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $nama => $nilai) {
        $stmt->bindValue($nama, $nilai, is_int($nilai) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    if ($limit !== null) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function ambil_produk_by_id(PDO $pdo, int $id, bool $kunci = false): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id_produk, kode_produk, nama_produk, id_kategori, id_supplier, harga, stok
         FROM produk WHERE id_produk = :id' . ($kunci ? ' FOR UPDATE' : '')
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function daftar_kategori(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori');
    $stmt->execute();
    return $stmt->fetchAll();
}

function daftar_supplier(PDO $pdo): array
{
    $stmt = $pdo->prepare('SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier');
    $stmt->execute();
    return $stmt->fetchAll();
}

function ringkasan_inventaris(PDO $pdo, int $batasMenipis): array
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS total,
                COALESCE(SUM(harga * stok), 0) AS nilai,
                COALESCE(SUM(stok <= :batas), 0) AS menipis
         FROM produk'
    );
    $stmt->bindValue(':batas', $batasMenipis, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
}

/**
 * Validasi input produk. Mengembalikan [data_bersih, errors].
 * $idAbaikan dipakai saat edit agar kode milik produk itu sendiri tidak dianggap duplikat.
 */
function validasi_produk(PDO $pdo, array $in, int $idAbaikan = 0): array
{
    $data = [
        'kode_produk' => strtoupper(trim((string) ($in['kode_produk'] ?? ''))),
        'nama_produk' => trim((string) ($in['nama_produk'] ?? '')),
        'id_kategori' => trim((string) ($in['id_kategori'] ?? '')),
        'id_supplier' => trim((string) ($in['id_supplier'] ?? '')),
        'harga'       => trim((string) ($in['harga'] ?? '')),
        'stok'        => trim((string) ($in['stok'] ?? '')),
    ];
    $errors = [];

    if ($data['kode_produk'] === '') {
        $errors['kode_produk'] = 'Kode produk wajib diisi.';
    } elseif (!preg_match('/^[A-Z0-9-]{3,20}$/', $data['kode_produk'])) {
        $errors['kode_produk'] = 'Kode 3-20 karakter: huruf, angka, atau tanda hubung (contoh: ELK-010).';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE kode_produk = :kode AND id_produk <> :id');
        $stmt->execute([':kode' => $data['kode_produk'], ':id' => $idAbaikan]);
        if ((int) $stmt->fetchColumn() > 0) {
            $errors['kode_produk'] = 'Kode produk sudah dipakai produk lain.';
        }
    }

    $panjang = mb_strlen($data['nama_produk'], 'UTF-8');
    if ($panjang === 0) {
        $errors['nama_produk'] = 'Nama produk wajib diisi.';
    } elseif ($panjang < 3 || $panjang > 100) {
        $errors['nama_produk'] = 'Nama produk harus 3-100 karakter.';
    }

    foreach ([
        'id_kategori' => ['kategori', 'kategori', 'id_kategori'],
        'id_supplier' => ['supplier', 'supplier', 'id_supplier'],
    ] as $field => [$label, $tabel, $kolom]) {
        if ($data[$field] === '') {
            $errors[$field] = "Pilih $label terlebih dahulu.";
        } elseif (!ctype_digit($data[$field])) {
            $errors[$field] = "Pilihan $label tidak valid.";
        } else {
            // Nama tabel/kolom berasal dari daftar tetap di atas, bukan dari input pengguna.
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $tabel WHERE $kolom = :id");
            $stmt->execute([':id' => (int) $data[$field]]);
            if ((int) $stmt->fetchColumn() === 0) {
                $errors[$field] = "Pilihan $label tidak ditemukan.";
            }
        }
    }

    if ($data['harga'] === '') {
        $errors['harga'] = 'Harga wajib diisi.';
    } elseif (!preg_match('/^\d{1,10}$/', $data['harga'])) {
        $errors['harga'] = 'Harga harus berupa angka bulat tanpa titik/koma (maks. 10 digit).';
    }

    if ($data['stok'] === '') {
        $errors['stok'] = 'Stok wajib diisi.';
    } elseif (!preg_match('/^\d{1,9}$/', $data['stok'])) {
        $errors['stok'] = 'Stok harus berupa angka bulat 0 atau lebih.';
    }

    return [$data, $errors];
}

/** Catat aktivitas ke log_aktivitas (dipanggil di dalam transaksi pemanggil). */
function catat_log(PDO $pdo, string $aksi, ?int $idProduk, string $kode, string $nama, string $detail): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO log_aktivitas (aksi, id_produk_ref, kode_produk, nama_produk, detail)
         VALUES (:aksi, :id, :kode, :nama, :detail)'
    );
    $stmt->execute([
        ':aksi'   => $aksi,
        ':id'     => $idProduk,
        ':kode'   => $kode,
        ':nama'   => $nama,
        ':detail' => $detail,
    ]);
}
