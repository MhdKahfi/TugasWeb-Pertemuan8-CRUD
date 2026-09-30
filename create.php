<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('create.php');
    [$data, $errors] = validasi_produk($pdo, $_POST);

    if ($errors) {
        form_state_set($data, $errors);
        flash_set('gagal', 'Data belum valid. Periksa isian yang ditandai merah.');
        redirect('create.php');
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'INSERT INTO produk (kode_produk, nama_produk, id_kategori, id_supplier, harga, stok)
             VALUES (:kode, :nama, :kategori, :supplier, :harga, :stok)'
        );
        $stmt->execute([
            ':kode'     => $data['kode_produk'],
            ':nama'     => $data['nama_produk'],
            ':kategori' => (int) $data['id_kategori'],
            ':supplier' => (int) $data['id_supplier'],
            ':harga'    => $data['harga'],
            ':stok'     => (int) $data['stok'],
        ]);
        $idBaru = (int) $pdo->lastInsertId();
        catat_log($pdo, 'TAMBAH', $idBaru, $data['kode_produk'], $data['nama_produk'],
            'Produk baru: harga ' . rupiah($data['harga']) . ', stok ' . $data['stok']);
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($e->getMessage());
        form_state_set($data, []);
        flash_set('gagal', $e->getCode() === '23000'
            ? 'Produk gagal disimpan: kode produk sudah dipakai.'
            : 'Produk gagal disimpan karena kesalahan database.');
        redirect('create.php');
    }

    flash_set('sukses', 'Produk "' . $data['nama_produk'] . '" berhasil ditambahkan.');
    redirect('index.php');
}

[$data, $errors] = form_state_get();
$daftarKategori = daftar_kategori($pdo);
$daftarSupplier = daftar_supplier($pdo);

$judul = 'Tambah Produk';
$menu  = 'tambah';
require __DIR__ . '/partials/header.php';
?>
<section class="panel narrow">
    <h1>Tambah produk</h1>
    <p class="subtle">Semua kolom wajib diisi. Kode produk harus unik.</p>
    <?php
    $aksi = 'create.php';
    $labelTombol = 'Simpan produk';
    require __DIR__ . '/partials/_form.php';
    ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
