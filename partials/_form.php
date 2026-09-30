<?php
/** Variabel: $aksi (URL), $data, $errors, $daftarKategori, $daftarSupplier, $labelTombol */
$nilai = fn(string $k) => $data[$k] ?? '';
?>
<form method="post" action="<?= e($aksi) ?>" class="form-grid">
    <?= csrf_field() ?>

    <div class="field">
        <label for="kode_produk">Kode produk</label>
        <input type="text" id="kode_produk" name="kode_produk" maxlength="20" required
               placeholder="Contoh: ELK-010" value="<?= e($nilai('kode_produk')) ?>"
               class="<?= isset($errors['kode_produk']) ? 'invalid' : '' ?>">
        <?= field_error($errors, 'kode_produk') ?>
    </div>

    <div class="field">
        <label for="nama_produk">Nama produk</label>
        <input type="text" id="nama_produk" name="nama_produk" maxlength="100" required
               placeholder="Contoh: Monitor LED 24 inci" value="<?= e($nilai('nama_produk')) ?>"
               class="<?= isset($errors['nama_produk']) ? 'invalid' : '' ?>">
        <?= field_error($errors, 'nama_produk') ?>
    </div>

    <div class="field">
        <label for="id_kategori">Kategori</label>
        <select id="id_kategori" name="id_kategori" required
                class="<?= isset($errors['id_kategori']) ? 'invalid' : '' ?>">
            <option value="">Pilih kategori</option>
            <?php foreach ($daftarKategori as $k): ?>
                <option value="<?= e($k['id_kategori']) ?>"
                    <?= (string) $k['id_kategori'] === (string) $nilai('id_kategori') ? 'selected' : '' ?>>
                    <?= e($k['nama_kategori']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'id_kategori') ?>
    </div>

    <div class="field">
        <label for="id_supplier">Supplier</label>
        <select id="id_supplier" name="id_supplier" required
                class="<?= isset($errors['id_supplier']) ? 'invalid' : '' ?>">
            <option value="">Pilih supplier</option>
            <?php foreach ($daftarSupplier as $s): ?>
                <option value="<?= e($s['id_supplier']) ?>"
                    <?= (string) $s['id_supplier'] === (string) $nilai('id_supplier') ? 'selected' : '' ?>>
                    <?= e($s['nama_supplier']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'id_supplier') ?>
    </div>

    <div class="field">
        <label for="harga">Harga satuan (Rp)</label>
        <input type="number" id="harga" name="harga" min="0" step="1" required inputmode="numeric"
               value="<?= e($nilai('harga')) ?>" class="<?= isset($errors['harga']) ? 'invalid' : '' ?>">
        <?= field_error($errors, 'harga') ?>
    </div>

    <div class="field">
        <label for="stok">Stok</label>
        <input type="number" id="stok" name="stok" min="0" step="1" required inputmode="numeric"
               value="<?= e($nilai('stok')) ?>" class="<?= isset($errors['stok']) ? 'invalid' : '' ?>">
        <?= field_error($errors, 'stok') ?>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= e($labelTombol) ?></button>
        <a class="btn" href="index.php">Batal</a>
    </div>
</form>
