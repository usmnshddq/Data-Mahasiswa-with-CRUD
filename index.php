<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/koneksi.php';

$q = isset($_GET['q']) && is_string($_GET['q'])
    ? trim($_GET['q'])
    : '';

$page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
$page = ($page !== false && $page > 0) ? $page : 1;

$perPage = 7;
$like = '%' . $q . '%';

if ($q !== '') {
    $countStmt = $conn->prepare(
        'SELECT COUNT(*) AS total
         FROM mahasiswa
         WHERE nim LIKE ? OR nama LIKE ? OR prodi LIKE ? OR email LIKE ?'
    );

    $countStmt->bind_param(
        'ssss',
        $like,
        $like,
        $like,
        $like
    );

    $countStmt->execute();

    $total = (int) $countStmt->get_result()->fetch_assoc()['total'];
} else {
    $total = (int) $conn->query(
        'SELECT COUNT(*) AS total FROM mahasiswa'
    )->fetch_assoc()['total'];
}

$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

if ($q !== '') {
    $stmt = $conn->prepare(
        'SELECT id, nim, nama, prodi, email, no_hp, alamat, created_at
         FROM mahasiswa
         WHERE nim LIKE ? OR nama LIKE ? OR prodi LIKE ? OR email LIKE ?
         ORDER BY id DESC LIMIT ? OFFSET ?'
    );

    $stmt->bind_param(
        'ssssii',
        $like,
        $like,
        $like,
        $like,
        $perPage,
        $offset
    );
} else {
    $stmt = $conn->prepare(
        'SELECT id, nim, nama, prodi, email, no_hp, alamat, created_at
         FROM mahasiswa ORDER BY id DESC LIMIT ? OFFSET ?'
    );

    $stmt->bind_param('ii', $perPage, $offset);
}

$stmt->execute();

$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$flash = get_flash();

function page_url(int $page, string $q): string
{
    $params = ['page' => $page];

    if ($q !== '') {
        $params['q'] = $q;
    }

    return 'index.php?' . http_build_query($params);
}

?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Mahasiswa | MahasiswaHub</title>

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
    <main class="container">

        <!-- Hero Section -->
        <section class="hero">

            <div>
                <p class="eyebrow">SISTEM INFORMASI AKADEMIK MINI</p>

                <h1>DATA MAHASISWA</h1>

                <p class="hero-copy">
                    Kelola data mahasiswa dalam satu tempat yang sederhana, rapi, dan mudah digunakan.
                </p>
            </div>

            <a class="button button-primary" href="tambah.php">
                <span aria-hidden="true">＋</span>
                Tambah Mahasiswa
            </a>

        </section>

        <!-- Flash Message -->
        <?php if ($flash): ?>
            <div
                class="alert alert-<?= e($flash['type']) ?>"
                role="status"
            >
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Student Data Panel -->
        <section class="panel">

            <!-- Panel Heading & Search -->
            <div class="panel-heading">

                <div>
                    <h2>Daftar mahasiswa</h2>

                    <p>
                        <?= number_format($total, 0, ',', '.') ?> data ditemukan
                    </p>
                </div>

                <form
                    class="search-form"
                    method="get"
                    action="index.php"
                    role="search"
                >
                    <label class="sr-only" for="q">
                        Cari mahasiswa
                    </label>

                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="<?= e($q) ?>"
                        placeholder="Cari NIM, nama, prodi, email..."
                    >

                    <button class="button button-dark" type="submit">
                        Cari
                    </button>

                    <?php if ($q !== ''): ?>
                        <a class="button button-light" href="index.php">
                            Reset
                        </a>
                    <?php endif; ?>
                </form>

            </div>

            <!-- Student Data Table -->
            <div class="table-wrap">

                <table>

                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>NIM</th>
                            <th>Program Studi</th>
                            <th>Email</th>
                            <th>Kontak</th>
                            <th class="actions-heading">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!$students): ?>

                            <tr>
                                <td colspan="6" class="empty-state">
                                    <span class="empty-icon">⌕</span>

                                    <strong>Data belum ditemukan</strong>

                                    <span>
                                        Coba kata kunci lain atau tambahkan data mahasiswa baru.
                                    </span>
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($students as $student): ?>

                                <tr>

                                    <td>
                                        <div class="student-cell">

                                            <span class="avatar">
                                                <?= e(strtoupper(substr($student['nama'], 0, 1))) ?>
                                            </span>

                                            <span>
                                                <strong>
                                                    <?= e($student['nama']) ?>
                                                </strong>

                                                <small>
                                                    ID #<?= (int) $student['id'] ?>
                                                </small>
                                            </span>

                                        </div>
                                    </td>

                                    <td>
                                        <span class="nim-pill">
                                            <?= e($student['nim']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= e($student['prodi']) ?>
                                    </td>

                                    <td>
                                        <?= e($student['email']) ?>
                                    </td>

                                    <td>
                                        <?= e(($student['no_hp'] ?? '') !== '' ? $student['no_hp'] : '-') ?>
                                    </td>

                                    <td>
                                        <div class="action-group">

                                            <a
                                                class="button button-small button-edit"
                                                href="edit.php?id=<?= (int) $student['id'] ?>"
                                                aria-label="Edit <?= e($student['nama']) ?>"
                                            >
                                                Edit
                                            </a>

                                            <form
                                                method="post"
                                                action="hapus.php"
                                                onsubmit="return confirm('Yakin ingin menghapus data mahasiswa ini?');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="_csrf"
                                                    value="<?= e(csrf_token()) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $student['id'] ?>"
                                                >

                                                <button
                                                    class="button button-small button-delete"
                                                    type="submit"
                                                >
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

            <!-- Table Footer & Pagination -->
            <div class="table-footer">

                <span>
                    Halaman <?= $page ?> dari <?= $totalPages ?>
                </span>

                <?php if ($totalPages > 1): ?>

                    <nav class="pagination" aria-label="Navigasi halaman">

                        <?php if ($page > 1): ?>
                            <a href="<?= e(page_url($page - 1, $q)) ?>">
                                ← Sebelumnya
                            </a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="<?= e(page_url($page + 1, $q)) ?>">
                                Berikutnya →
                            </a>
                        <?php endif; ?>

                    </nav>

                <?php endif; ?>

            </div>

        </section>

        <!-- Page Note -->
        <p class="page-note">
            Tips: pastikan NIM unik dan data kontak sudah benar sebelum disimpan.
        </p>

    </main>

    <!-- Footer -->
    <footer class="footer">
        <span>MahasiswaHub</span>
        <span>Modul CRUD Mahasiswa · Pemrograman Web</span>
    </footer>

</body>

</html>
