<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/koneksi.php';

$values = [
    'nim' => '',
    'nama' => '',
    'prodi' => '',
    'email' => '',
    'no_hp' => '',
    'alamat' => ''
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach ($values as $key => $_) {
        $values[$key] = post_text($key);
    }

    $errors = validate_student($values);

    if (!$errors) {
        try {
            $stmt = $conn->prepare(
                'INSERT INTO mahasiswa (nim, nama, prodi, email, no_hp, alamat) VALUES (?, ?, ?, ?, ?, ?)'
            );

            $stmt->bind_param(
                'ssssss',
                $values['nim'],
                $values['nama'],
                $values['prodi'],
                $values['email'],
                $values['no_hp'],
                $values['alamat']
            );

            $stmt->execute();

            set_flash('success', 'Data mahasiswa berhasil ditambahkan.');
            redirect('index.php');
        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) {
                $errors[] = 'NIM tersebut sudah terdaftar. Gunakan NIM yang berbeda.';
            } else {
                error_log('Insert mahasiswa failed: ' . $e->getMessage());
                $errors[] = 'Data belum berhasil disimpan. Periksa kembali database atau coba lagi.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Mahasiswa | MahasiswaHub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <img src="logo.png" alt="Logo kampus">

            <div>
                <h1>Mahasiswa Hub</h1>
                <p>Administrasi dan Akademik</p>
            </div>
        </div>

    </header>

    <main class="container form-page">
        <div class="form-intro">
            <p class="eyebrow">DATA BARU</p>
            <h1>Tambah mahasiswa</h1>
            <p>Isi informasi mahasiswa pada formulir berikut.</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <strong>Periksa kembali input berikut:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="panel form-panel">
            <form method="post" action="tambah.php" class="student-form">
                <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

                <div class="form-grid">
                    <div class="field">
                        <label for="nim">NIM <span>*</span></label>
                        <input
                            id="nim"
                            name="nim"
                            maxlength="12"
                            value="<?= e($values['nim']) ?>"
                            required
                            autocomplete="off"
                        >
                        <small>Maksimal 12 karakter dan harus unik.</small>
                    </div>

                    <div class="field">
                        <label for="nama">Nama lengkap <span>*</span></label>
                        <input
                            id="nama"
                            name="nama"
                            maxlength="100"
                            value="<?= e($values['nama']) ?>"
                            required
                            autocomplete="name"
                        >
                    </div>

                    <div class="field">
                        <label for="prodi">Program studi <span>*</span></label>
                        <input
                            id="prodi"
                            name="prodi"
                            maxlength="50"
                            value="<?= e($values['prodi']) ?>"
                            required
                            placeholder="Contoh: Teknologi Informasi"
                        >
                    </div>

                    <div class="field">
                        <label for="email">Email <span>*</span></label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            maxlength="100"
                            value="<?= e($values['email']) ?>"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <div class="field">
                        <label for="no_hp">Nomor HP</label>
                        <input
                            id="no_hp"
                            name="no_hp"
                            maxlength="20"
                            value="<?= e($values['no_hp']) ?>"
                            autocomplete="tel"
                        >
                    </div>

                    <div class="field">
                        <label for="alamat">Alamat</label>
                        <input
                            id="alamat"
                            name="alamat"
                            maxlength="255"
                            value="<?= e($values['alamat']) ?>"
                            autocomplete="street-address"
                        >
                    </div>
                </div>

                <p class="required-note">
                    <span>*</span> Wajib diisi
                </p>

                <div class="form-actions">
                    <a class="button button-light" href="index.php">Batal</a>
                    <button class="button button-primary" type="submit">
                        Simpan mahasiswa
                    </button>
                </div>
            </form>
        </section>
    </main>

    <footer class="footer">
        <span>MahasiswaHub</span>
        <span>Data divalidasi sebelum disimpan.</span>
    </footer>
</body>
</html>