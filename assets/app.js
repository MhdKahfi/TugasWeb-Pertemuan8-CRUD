// Dialog konfirmasi hapus. Tanpa JavaScript, tombol "Hapus" tetap berfungsi
// karena mengarah ke halaman konfirmasi delete.php?id=...
(function () {
    'use strict';

    var dialog = document.getElementById('dialog-hapus');
    if (dialog && typeof dialog.showModal === 'function') {
        var idField = document.getElementById('hapus-id');
        var namaEl  = document.getElementById('hapus-nama');
        var kodeEl  = document.getElementById('hapus-kode');

        document.querySelectorAll('.btn-hapus').forEach(function (tombol) {
            tombol.addEventListener('click', function (ev) {
                ev.preventDefault();
                idField.value = tombol.dataset.id;
                namaEl.textContent = tombol.dataset.nama;
                kodeEl.textContent = tombol.dataset.kode;
                dialog.showModal();
            });
        });

        document.getElementById('hapus-batal').addEventListener('click', function () {
            dialog.close();
        });

        // Klik di area gelap (backdrop) menutup dialog.
        dialog.addEventListener('click', function (ev) {
            if (ev.target === dialog) { dialog.close(); }
        });
    }

    // Tombol tutup pada flash message.
    document.querySelectorAll('.flash-close').forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            tombol.closest('.flash').remove();
        });
    });
})();
