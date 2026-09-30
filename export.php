<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';

$pdo = db();
$q        = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100, 'UTF-8');
$kategori = (int) filter_var($_GET['kategori'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);

$data = ambil_produk($pdo, $q, $kategori); // tanpa limit: seluruh hasil filter

/** Cegah CSV/formula injection saat file dibuka di Excel. */
function csv_aman($nilai): string
{
    $teks = (string) $nilai;
    return ($teks !== '' && strpbrk($teks[0], "=+-@\t\r") !== false) ? "'" . $teks : $teks;
}

$namaFile = 'laporan_inventaris_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $namaFile . '"');
header('Cache-Control: no-store');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8 dengan benar
$pemisah = ';';               // pemisah titik koma cocok untuk Excel berlokal Indonesia

fputcsv($out, ['Laporan Inventaris'], $pemisah);
fputcsv($out, ['Dibuat', date('d-m-Y H:i')], $pemisah);
fputcsv($out, ['Filter pencarian', $q !== '' ? csv_aman($q) : '-'], $pemisah);
fputcsv($out, [], $pemisah);
fputcsv($out, ['No', 'Kode', 'Nama Produk', 'Kategori', 'Supplier', 'Harga (Rp)', 'Stok', 'Nilai Stok (Rp)'], $pemisah);

$no = 0;
$totalStok = 0;
$totalNilai = 0;
foreach ($data as $p) {
    $nilai = (int) $p['harga'] * (int) $p['stok'];
    $totalStok  += (int) $p['stok'];
    $totalNilai += $nilai;
    fputcsv($out, [
        ++$no,
        csv_aman($p['kode_produk']),
        csv_aman($p['nama_produk']),
        csv_aman($p['nama_kategori']),
        csv_aman($p['nama_supplier']),
        (int) $p['harga'],
        (int) $p['stok'],
        $nilai,
    ], $pemisah);
}
fputcsv($out, ['', '', '', '', 'TOTAL', '', $totalStok, $totalNilai], $pemisah);
fclose($out);
exit;
