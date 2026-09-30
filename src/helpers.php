<?php
declare(strict_types=1);

/** Escape output HTML (wajib dipakai untuk semua data yang ditampilkan). */
function e($nilai): string
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/* ---------- Flash message (Post/Redirect/Get) ---------- */

function flash_set(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan]; // sukses | gagal | info
}

function flash_get(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/** Simpan input lama + error validasi supaya form terisi ulang setelah redirect. */
function form_state_set(array $old, array $errors): void
{
    $_SESSION['form_state'] = ['old' => $old, 'errors' => $errors];
}

function form_state_get(): array
{
    $state = $_SESSION['form_state'] ?? ['old' => [], 'errors' => []];
    unset($_SESSION['form_state']);
    return [$state['old'], $state['errors']];
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(string $kembali = 'index.php'): void
{
    $kirim = $_POST['csrf'] ?? '';
    if (!is_string($kirim) || !hash_equals(csrf_token(), $kirim)) {
        flash_set('gagal', 'Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan coba lagi.');
        redirect($kembali);
    }
}

/* ---------- URL & pagination ---------- */

function url_dengan(string $file, array $params): string
{
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null && $v !== 0);
    return $file . ($params ? '?' . http_build_query($params) : '');
}

function field_error(array $errors, string $kunci): string
{
    return isset($errors[$kunci])
        ? '<p class="field-error" role="alert">' . e($errors[$kunci]) . '</p>'
        : '';
}

/** Daftar nomor halaman dengan "..." (null) agar tidak terlalu panjang. */
function daftar_halaman(int $sekarang, int $total): array
{
    $hasil = [];
    $tampil = array_unique(array_filter([
        1, $total, $sekarang - 1, $sekarang, $sekarang + 1,
    ], fn($p) => $p >= 1 && $p <= $total));
    sort($tampil);
    $prev = 0;
    foreach ($tampil as $p) {
        if ($p - $prev > 1) {
            $hasil[] = null;
        }
        $hasil[] = $p;
        $prev = $p;
    }
    return $hasil;
}

/* ---------- Cadangan jika ekstensi mbstring belum aktif ---------- */

if (!function_exists('mb_strlen')) {
    function mb_strlen(string $teks, ?string $encoding = null): int
    {
        return preg_match_all('/./us', $teks) ?: 0;
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr(string $teks, int $mulai, ?int $panjang = null, ?string $encoding = null): string
    {
        preg_match_all('/./us', $teks, $huruf);
        return implode('', array_slice($huruf[0], $mulai, $panjang));
    }
}
