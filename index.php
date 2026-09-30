<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';

$pdo = db();

// Input pencarian, filter, dan halaman (divalidasi sebelum dipakai).
$q        = trim((string) ($_GET['q'] ?? ''));
$q        = mb_substr($q, 0, 100, 'UTF-8');
$kategori = (int) filter_var($_GET['kategori'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
$halaman  = (int) filter_var($_GET['hal'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);

$perHalaman   = (int) config('per_page', 10);
$batasMenipis = (int) config('stok_menipis', 5);

$total        = hitung_produk($pdo, $q, $kategori);
$totalHalaman = max(1, (int) ceil($total / $perHalaman));
$halaman      = min($halaman, $totalHalaman);
$offset       = ($halaman - 1) * $perHalaman;

$daftarProduk   = ambil_produk($pdo, $q, $kategori, $perHalaman, $offset);
$daftarKategori = daftar_kategori($pdo);
$ringkasan      = ringkasan_inventaris($pdo, $batasMenipis);

$filter = ['q' => $q, 'kategori' => $kategori];
$dari   = $total ? $offset + 1 : 0;
$sampai = $offset + count($daftarProduk);

$judul = 'Daftar Produk';
$menu  = 'produk';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <h1>Daftar produk</h1>
    <a class="btn btn-primary" href="create.php">Tambah produk</a>
</div>

<div class="stats">
    <div class="stat"><span class="stat-label">Jumlah produk</span><span class="stat-value"><?= e(number_format((int) $ringkasan['total'], 0, ',', '.')) ?></span></div>
    <div class="stat"><span class="stat-label">Nilai seluruh stok</span><span class="stat-value"><?= e(rupiah($ringkasan['nilai'])) ?></span></div>
    <div class="stat"><span class="stat-label">Stok <?= e($batasMenipis) ?> atau kurang</span><span class="stat-value"><?= e((int) $ringkasan['menipis']) ?> produk</span></div>
</div>

<section class="panel">
    <form method="get" action="index.php" class="toolbar" role="search">
        <div class="toolbar-fields">
            <label class="sr-only" for="q">Cari produk</label>
            <input type="search" id="q" name="q" value="<?= e($q) ?>" maxlength="100"
                   placeholder="Cari nama, kode, atau supplier">
            <label class="sr-only" for="kategori">Filter kategori</label>
            <select id="kategori" name="kategori">
                <option value="0">Semua kategori</option>
                <?php foreach ($daftarKategori as $k): ?>
                    <option value="<?= e($k['id_kategori']) ?>" <?= $kategori === (int) $k['id_kategori'] ? 'selected' : '' ?>>
                        <?= e($k['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
            <?php if ($q !== '' || $kategori > 0): ?>
                <a class="btn" href="index.php">Reset</a>
            <?php endif; ?>
        </div>
        <a class="btn" href="<?= e(url_dengan('export.php', $filter)) ?>">Export laporan CSV</a>
    </form>

    <?php if (!$daftarProduk): ?>
        <div class="empty">
            <?php if ($q !== '' || $kategori > 0): ?>
                <p><strong>Tidak ada produk yang cocok.</strong></p>
                <p>Ubah kata kunci atau pilih kategori lain.</p>
                <a class="btn" href="index.php">Tampilkan semua produk</a>
            <?php else: ?>
                <p><strong>Belum ada produk.</strong></p>
                <p>Tambahkan produk pertama untuk mulai mencatat inventaris.</p>
                <a class="btn btn-primary" href="create.php">Tambah produk</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama produk</th>
                        <th scope="col">Kategori</th>
                        <th scope="col">Supplier</th>
                        <th scope="col" class="num">Harga</th>
                        <th scope="col" class="num">Stok</th>
                        <th scope="col" class="aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($daftarProduk as $p): ?>
                    <tr>
                        <td class="kode"><?= e($p['kode_produk']) ?></td>
                        <td><?= e($p['nama_produk']) ?></td>
                        <td><?= e($p['nama_kategori']) ?></td>
                        <td><?= e($p['nama_supplier']) ?></td>
                        <td class="num"><?= e(rupiah($p['harga'])) ?></td>
                        <td class="num">
                            <?php $stok = (int) $p['stok']; ?>
                            <?php if ($stok === 0): ?>
                                <span class="badge badge-habis">Habis</span>
                            <?php elseif ($stok <= $batasMenipis): ?>
                                <span class="badge badge-menipis"><?= e($stok) ?> &middot; menipis</span>
                            <?php else: ?>
                                <?= e($stok) ?>
                            <?php endif; ?>
                        </td>
                        <td class="aksi">
                            <a class="btn btn-sm" href="edit.php?id=<?= e($p['id_produk']) ?>">Ubah</a>
                            <a class="btn btn-sm btn-danger-outline btn-hapus"
                               href="delete.php?id=<?= e($p['id_produk']) ?>"
                               data-id="<?= e($p['id_produk']) ?>"
                               data-nama="<?= e($p['nama_produk']) ?>"
                               data-kode="<?= e($p['kode_produk']) ?>">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pager-row">
            <p class="subtle">Menampilkan <?= e($dari) ?>-<?= e($sampai) ?> dari <?= e($total) ?> produk</p>
            <?php if ($totalHalaman > 1): ?>
                <nav class="pager" aria-label="Halaman">
                    <?php if ($halaman > 1): ?>
                        <a href="<?= e(url_dengan('index.php', $filter + ['hal' => $halaman - 1])) ?>">Sebelumnya</a>
                    <?php endif; ?>
                    <?php foreach (daftar_halaman($halaman, $totalHalaman) as $n): ?>
                        <?php if ($n === null): ?>
                            <span class="gap">&hellip;</span>
                        <?php elseif ($n === $halaman): ?>
                            <span class="current" aria-current="page"><?= e($n) ?></span>
                        <?php else: ?>
                            <a href="<?= e(url_dengan('index.php', $filter + ['hal' => $n])) ?>"><?= e($n) ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($halaman < $totalHalaman): ?>
                        <a href="<?= e(url_dengan('index.php', $filter + ['hal' => $halaman + 1])) ?>">Berikutnya</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<dialog id="dialog-hapus" aria-labelledby="dialog-judul">
    <form method="post" action="delete.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="hapus-id" value="">
        <h2 id="dialog-judul">Hapus produk ini?</h2>
        <p><strong id="hapus-nama"></strong> (<span class="kode" id="hapus-kode"></span>) akan dihapus dan
           tercatat di riwayat aktivitas. Tindakan ini tidak bisa dibatalkan.</p>
        <div class="form-actions">
            <button type="submit" class="btn btn-danger">Ya, hapus produk</button>
            <button type="button" class="btn" id="hapus-batal">Batal</button>
        </div>
    </form>
</dialog>
<?php require __DIR__ . '/partials/footer.php'; ?>
