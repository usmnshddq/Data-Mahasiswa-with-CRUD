<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/koneksi.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    set_flash('error', 'ID mahasiswa tidak valid.');
    redirect('index.php');
}

$find = $conn->prepare(
    'SELECT id, nim, nama, prodi, email, no_hp, alamat
     FROM mahasiswa
     WHERE id = ?'
);

$find->bind_param('i', $id);
$find->execute();

$student = $find->get_result()->fetch_assoc();

if (!$student) {
    set_flash('error', 'Data mahasiswa tidak ditemukan.');
    redirect('index.php');
}

$values = [
    'nim'    => $student['nim'],
    'nama'   => $student['nama'],
    'prodi'  => $student['prodi'],
    'email'  => $student['email'],
    'no_hp'  => $student['no_hp'] ?? '',
    'alamat' => $student['alamat'] ?? ''
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
                'UPDATE mahasiswa
                 SET nim = ?, nama = ?, prodi = ?, email = ?, no_hp = ?, alamat = ?
                 WHERE id = ?'
            );

            $stmt->bind_param(
                'ssssssi',
                $values['nim'],
                $values['nama'],
                $values['prodi'],
                $values['email'],
                $values['no_hp'],
                $values['alamat'],
                $id
            );

            $stmt->execute();

            set_flash('success', 'Data mahasiswa berhasil diperbarui.');
            redirect('index.php');

        } catch (mysqli_sql_exception $e) {
            if ((int) $e->getCode() === 1062) {
                $errors[] = 'NIM tersebut sudah digunakan oleh mahasiswa lain.';
            } else {
                error_log('Update mahasiswa failed: ' . $e->getMessage());
                $errors[] = 'Perubahan belum berhasil disimpan. Coba lagi.';
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

    <title>Edit Mahasiswa | MahasiswaHub</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Header -->
    <header class="topbar">

     <div class="brand">
            <img src="logo.png" alt="Logo kampus">

            <div>
                <h1>Mahasiswa Hub</h1>
                <p>Administrasi dan Akademik</p>
            </div>
        </div>


    </header>

    <!-- Main Content -->
    <main class="container form-page">

        <div class="form-intro">
            <p class="eyebrow">
                ID MAHASISWA #<?= (int) $id ?>
            </p>

            <h1>Edit mahasiswa</h1>

            <p>
                Perbarui informasi yang dibutuhkan, kemudian simpan perubahan.
            </p>
        </div>

        <!-- Error Messages -->
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

        <!-- Edit Student Form -->
        <section class="panel form-panel">

            <form
                method="post"
                action="edit.php?id=<?= (int) $id ?>"
                class="student-form"
            >
                <input
                    type="hidden"
                    name="_csrf"
                    value="<?= e(csrf_token()) ?>"
                >

                <div class="form-grid">

                    <div class="field">
                        <label for="nim">
                            NIM <span>*</span>
                        </label>

                        <input
                            id="nim"
                            name="nim"
                            maxlength="12"
                            value="<?= e($values['nim']) ?>"
                            required
                            autocomplete="off"
                        >

                        <small>
                            NIM tidak boleh sama dengan mahasiswa lain.
                        </small>
                    </div>

                    <div class="field">
                        <label for="nama">
                            Nama lengkap <span>*</span>
                        </label>

                        <input
                            id="nama"
                            name="nama"
                            maxlength="100"
                            value="<?= e($values['nama']) ?>"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="prodi">
                            Program studi <span>*</span>
                        </label>

                        <input
                            id="prodi"
                            name="prodi"
                            maxlength="50"
                            value="<?= e($values['prodi']) ?>"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="email">
                            Email <span>*</span>
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            maxlength="100"
                            value="<?= e($values['email']) ?>"
                            required
                        >
                    </div>

                    <div class="field">
                        <label for="no_hp">
                            Nomor HP
                        </label>

                        <input
                            id="no_hp"
                            name="no_hp"
                            maxlength="20"
                            value="<?= e($values['no_hp']) ?>"
                        >
                    </div>

                    <div class="field">
                        <label for="alamat">
                            Alamat
                        </label>

                        <input
                            id="alamat"
                            name="alamat"
                            maxlength="255"
                            value="<?= e($values['alamat']) ?>"
                        >
                    </div>

                </div>

                <p class="required-note">
                    <span>*</span> Wajib diisi
                </p>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a class="button button-light" href="index.php">
                        Batal
                    </a>

                    <button
                        class="button button-primary"
                        type="submit"
                    >
                        Simpan perubahan
                    </button>
                </div>

            </form>

        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <span>MahasiswaHub</span>
        <span>Data divalidasi sebelum diperbarui.</span>
    </footer>

</body>

</html>
