<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';

$pdo = db();
$id  = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

$produk = $id ? ambil_produk_by_id($pdo, $id) : null;
if (!$produk) {
    flash_set('gagal', 'Produk yang ingin diubah tidak ditemukan.');
    redirect('index.php');
}

$daftarKategori = daftar_kategori($pdo);
$daftarSupplier = daftar_supplier($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check('edit.php?id=' . $id);
    [$data, $errors] = validasi_produk($pdo, $_POST, $id);

    if ($errors) {
        form_state_set($data, $errors);
        flash_set('gagal', 'Data belum valid. Periksa isian yang ditandai merah.');
        redirect('edit.php?id=' . $id);
    }

    // Susun catatan perubahan (nilai lama -> nilai baru) untuk log.
    $namaKategori = array_column($daftarKategori, 'nama_kategori', 'id_kategori');
    $namaSupplier = array_column($daftarSupplier, 'nama_supplier', 'id_supplier');
    $perubahan = [];
    $bandingkan = [
        'Kode'     => [$produk['kode_produk'], $data['kode_produk']],
        'Nama'     => [$produk['nama_produk'], $data['nama_produk']],
        'Kategori' => [$namaKategori[$produk['id_kategori']] ?? '?', $namaKategori[(int) $data['id_kategori']] ?? '?'],
        'Supplier' => [$namaSupplier[$produk['id_supplier']] ?? '?', $namaSupplier[(int) $data['id_supplier']] ?? '?'],
        'Harga'    => [(string) (int) $produk['harga'], (string) (int) $data['harga']],
        'Stok'     => [(string) $produk['stok'], (string) (int) $data['stok']],
    ];
    foreach ($bandingkan as $label => [$lama, $baru]) {
        if ($lama !== $baru) {
            $perubahan[] = "$label: $lama -> $baru";
        }
    }

    if (!$perubahan) {
        flash_set('info', 'Tidak ada perubahan yang disimpan.');
        redirect('index.php');
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare(
            'UPDATE produk
             SET kode_produk = :kode, nama_produk = :nama, id_kategori = :kategori,
                 id_supplier = :supplier, harga = :harga, stok = :stok
             WHERE id_produk = :id'
        );
        $stmt->execute([
            ':kode'     => $data['kode_produk'],
            ':nama'     => $data['nama_produk'],
            ':kategori' => (int) $data['id_kategori'],
            ':supplier' => (int) $data['id_supplier'],
            ':harga'    => $data['harga'],
            ':stok'     => (int) $data['stok'],
            ':id'       => $id,
        ]);
        catat_log($pdo, 'UBAH', $id, $data['kode_produk'], $data['nama_produk'], implode('; ', $perubahan));
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($e->getMessage());
        form_state_set($data, []);
        flash_set('gagal', $e->getCode() === '23000'
            ? 'Perubahan gagal disimpan: kode produk sudah dipakai.'
            : 'Perubahan gagal disimpan karena kesalahan database.');
        redirect('edit.php?id=' . $id);
    }

    flash_set('sukses', 'Produk "' . $data['nama_produk'] . '" berhasil diperbarui.');
    redirect('index.php');
}

// GET: form terisi data dari database, atau input terakhir jika validasi sebelumnya gagal.
[$old, $errors] = form_state_get();
$data = $old ?: $produk;
$data['harga'] = $old ? $data['harga'] : (string) (int) $produk['harga'];

$judul = 'Ubah Produk';
$menu  = 'produk';
require __DIR__ . '/partials/header.php';
?>
<section class="panel narrow">
    <h1>Ubah produk</h1>
    <p class="subtle">Data di bawah sudah terisi sesuai kondisi saat ini.</p>
    <?php
    $aksi = 'edit.php?id=' . $id;
    $labelTombol = 'Simpan perubahan';
    require __DIR__ . '/partials/_form.php';
    ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
