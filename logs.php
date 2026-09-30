<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';

$pdo = db();
$halaman    = (int) filter_var($_GET['hal'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$perHalaman = 15;

$stmt = $pdo->prepare('SELECT COUNT(*) FROM log_aktivitas');
$stmt->execute();
$total        = (int) $stmt->fetchColumn();
$totalHalaman = max(1, (int) ceil($total / $perHalaman));
$halaman      = min($halaman, $totalHalaman);

$stmt = $pdo->prepare(
    'SELECT aksi, kode_produk, nama_produk, detail, created_at
     FROM log_aktivitas ORDER BY id_log DESC LIMIT :limit OFFSET :offset'
);
$stmt->bindValue(':limit', $perHalaman, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($halaman - 1) * $perHalaman, PDO::PARAM_INT);
$stmt->execute();
$daftarLog = $stmt->fetchAll();

$judul = 'Riwayat Aktivitas';
$menu  = 'log';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <h1>Riwayat aktivitas</h1>
</div>
<section class="panel">
    <?php if (!$daftarLog): ?>
        <div class="empty">
            <p><strong>Belum ada aktivitas.</strong></p>
            <p>Menambah, mengubah, atau menghapus produk akan tercatat di sini.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Aksi</th>
                        <th scope="col">Kode</th>
                        <th scope="col">Produk</th>
                        <th scope="col">Detail</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($daftarLog as $l): ?>
                    <tr>
                        <td class="nowrap"><?= e(date('d M Y H:i', strtotime($l['created_at']))) ?></td>
                        <td><span class="badge badge-<?= e(strtolower($l['aksi'])) ?>"><?= e($l['aksi']) ?></span></td>
                        <td class="kode"><?= e($l['kode_produk']) ?></td>
                        <td><?= e($l['nama_produk']) ?></td>
                        <td class="detail"><?= e($l['detail']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="pager-row">
            <p class="subtle"><?= e($total) ?> aktivitas tercatat</p>
            <?php if ($totalHalaman > 1): ?>
                <nav class="pager" aria-label="Halaman">
                    <?php if ($halaman > 1): ?>
                        <a href="<?= e(url_dengan('logs.php', ['hal' => $halaman - 1])) ?>">Sebelumnya</a>
                    <?php endif; ?>
                    <?php foreach (daftar_halaman($halaman, $totalHalaman) as $n): ?>
                        <?php if ($n === null): ?>
                            <span class="gap">&hellip;</span>
                        <?php elseif ($n === $halaman): ?>
                            <span class="current" aria-current="page"><?= e($n) ?></span>
                        <?php else: ?>
                            <a href="<?= e(url_dengan('logs.php', ['hal' => $n])) ?>"><?= e($n) ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($halaman < $totalHalaman): ?>
                        <a href="<?= e(url_dengan('logs.php', ['hal' => $halaman + 1])) ?>">Berikutnya</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/partials/footer.php'; ?>
