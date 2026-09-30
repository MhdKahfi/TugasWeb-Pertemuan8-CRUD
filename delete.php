<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';

$pdo = db();

/* ---------- POST: hapus (dalam transaksi) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$id) {
        flash_set('gagal', 'ID produk tidak valid.');
        redirect('index.php');
    }

    try {
        $pdo->beginTransaction();

        // Kunci baris agar tidak berubah selama transaksi berjalan.
        $produk = ambil_produk_by_id($pdo, $id, true);
        if (!$produk) {
            $pdo->rollBack();
            flash_set('gagal', 'Produk tidak ditemukan (mungkin sudah dihapus).');
            redirect('index.php');
        }

        $stmt = $pdo->prepare('SELECT nama_kategori FROM kategori WHERE id_kategori = :id');
        $stmt->execute([':id' => $produk['id_kategori']]);
        $kategori = (string) $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT nama_supplier FROM supplier WHERE id_supplier = :id');
        $stmt->execute([':id' => $produk['id_supplier']]);
        $supplier = (string) $stmt->fetchColumn();

        // 1) Catat log dulu, 2) hapus produk. Jika salah satu gagal, keduanya dibatalkan.
        catat_log($pdo, 'HAPUS', $id, $produk['kode_produk'], $produk['nama_produk'],
            sprintf('Kategori: %s; Supplier: %s; Harga: %s; Stok terakhir: %d',
                $kategori, $supplier, rupiah($produk['harga']), $produk['stok']));

        $stmt = $pdo->prepare('DELETE FROM produk WHERE id_produk = :id');
        $stmt->execute([':id' => $id]);

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log($e->getMessage());
        flash_set('gagal', 'Produk gagal dihapus dan tidak ada data yang berubah.');
        redirect('index.php');
    }

    flash_set('sukses', 'Produk "' . $produk['nama_produk'] . '" berhasil dihapus dan tercatat di riwayat.');
    redirect('index.php');
}

/* ---------- GET: halaman konfirmasi (berfungsi juga tanpa JavaScript) ---------- */
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$produk = $id ? ambil_produk_by_id($pdo, $id) : null;
if (!$produk) {
    flash_set('gagal', 'Produk yang ingin dihapus tidak ditemukan.');
    redirect('index.php');
}

$judul = 'Hapus Produk';
$menu  = 'produk';
require __DIR__ . '/partials/header.php';
?>
<section class="panel narrow">
    <h1>Hapus produk ini?</h1>
    <p>Anda akan menghapus <strong><?= e($produk['nama_produk']) ?></strong>
       (<span class="kode"><?= e($produk['kode_produk']) ?></span>). Tindakan ini tidak bisa dibatalkan,
       tetapi tercatat di riwayat aktivitas.</p>
    <form method="post" action="delete.php" class="form-actions">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($produk['id_produk']) ?>">
        <button type="submit" class="btn btn-danger">Ya, hapus produk</button>
        <a class="btn" href="index.php">Batal</a>
    </form>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
