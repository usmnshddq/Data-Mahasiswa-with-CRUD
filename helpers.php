<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* ========================================
   HTML ESCAPING
======================================== */

function e(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/* ========================================
   CSRF PROTECTION
======================================== */

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['_csrf'] ?? '';

    if (
        !is_string($submitted) ||
        !hash_equals(csrf_token(), $submitted)
    ) {
        http_response_code(419);

        exit(
            'Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan kembali dan coba lagi.'
        );
    }
}

/* ========================================
   FLASH MESSAGES
======================================== */

function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash(): ?array
{
    if (
        !isset($_SESSION['_flash']) ||
        !is_array($_SESSION['_flash'])
    ) {
        return null;
    }

    $flash = $_SESSION['_flash'];

    unset($_SESSION['_flash']);

    return $flash;
}

/* ========================================
   REDIRECT
======================================== */

function redirect(string $url): never
{
    header('Location: ' . $url);

    exit;
}

/* ========================================
   POST INPUT
======================================== */

function post_text(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

/* ========================================
   STUDENT DATA VALIDATION
======================================== */

function validate_student(array $data): array
{
    $errors = [];

    // Validasi NIM
    if ($data['nim'] === '') {
        $errors[] = 'NIM wajib diisi.';
    } elseif (strlen($data['nim']) > 12) {
        $errors[] = 'NIM maksimal 12 karakter.';
    }

    // Validasi nama
    if ($data['nama'] === '') {
        $errors[] = 'Nama wajib diisi.';
    } elseif (mb_or_strlen($data['nama']) > 100) {
        $errors[] = 'Nama maksimal 100 karakter.';
    }

    // Validasi program studi
    if ($data['prodi'] === '') {
        $errors[] = 'Program studi wajib diisi.';
    } elseif (mb_or_strlen($data['prodi']) > 50) {
        $errors[] = 'Program studi maksimal 50 karakter.';
    }

    // Validasi email
    if ($data['email'] === '') {
        $errors[] = 'Email wajib diisi.';
    } elseif (
        strlen($data['email']) > 100 ||
        !filter_var($data['email'], FILTER_VALIDATE_EMAIL)
    ) {
        $errors[] = 'Masukkan alamat email yang valid (maksimal 100 karakter).';
    }

    // Validasi nomor HP
    if (mb_or_strlen($data['no_hp']) > 20) {
        $errors[] = 'Nomor HP maksimal 20 karakter.';
    }

    // Validasi alamat
    if (mb_or_strlen($data['alamat']) > 255) {
        $errors[] = 'Alamat maksimal 255 karakter.';
    }

    return $errors;
}

/* ========================================
   MULTIBYTE STRING LENGTH
======================================== */

function mb_or_strlen(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}
