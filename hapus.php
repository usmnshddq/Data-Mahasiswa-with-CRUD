<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    header('Allow: POST');

    exit('Metode tidak diizinkan.');
}

verify_csrf();

$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    set_flash('error', 'ID mahasiswa tidak valid.');

    redirect('index.php');
}

try {
    $stmt = $conn->prepare(
        'DELETE FROM mahasiswa WHERE id = ?'
    );

    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        set_flash(
            'success',
            'Data mahasiswa berhasil dihapus.'
        );
    } else {
        set_flash(
            'error',
            'Data tidak ditemukan atau sudah dihapus.'
        );
    }
} catch (mysqli_sql_exception $e) {
    error_log('Delete mahasiswa failed: ' . $e->getMessage());

    set_flash(
        'error',
        'Data belum berhasil dihapus. Silakan coba lagi.'
    );
}

redirect('index.php');
