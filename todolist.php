<?php
session_start();

// Inisialisasi array tugas di session
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}
$tasks = &$_SESSION['tasks'];

// Fungsi untuk menambahkan tugas
function addTask($title, &$tasks) {
    $tasks[] = [
        'title' => htmlspecialchars($title),
        'done' => false
    ];
}

// Tambah tugas baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task']) && !isset($_POST['edit_index'])) {
    $newTask = trim($_POST['task']);
    if ($newTask !== '') {
        addTask($newTask, $tasks);
    }
    header("Location: todolist.php");
    exit;
}

// Edit tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_index'])) {
    $editIndex = (int)$_POST['edit_index'];
    $editTitle = trim($_POST['edit_task']);
    if ($editTitle !== '' && isset($tasks[$editIndex])) {
        $tasks[$editIndex]['title'] = htmlspecialchars($editTitle);
    }
    header("Location: todolist.php");
    exit;
}

// Tandai selesai
if (isset($_GET['done'])) {
    $doneIndex = (int)$_GET['done'];
    if (isset($tasks[$doneIndex])) {
        $tasks[$doneIndex]['done'] = !$tasks[$doneIndex]['done'];
    }
    header("Location: todolist.php");
    exit;
}

// Hapus tugas
if (isset($_GET['delete'])) {
    $deleteIndex = (int)$_GET['delete'];
    if (isset($tasks[$deleteIndex])) {
        array_splice($tasks, $deleteIndex, 1);
    }
    header("Location: todolist.php");
    exit;
}

// Mode edit
$editMode = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tugas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%);
            min-height: 100vh;
        }
        .card {
            border-radius: 18px;
        }
        .list-group-item {
            transition: background 0.2s, box-shadow 0.2s;
            border-radius: 8px !important;
            margin-bottom: 8px;
        }
        .list-group-item:hover {
            background: #eaf6fb;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.08);
        }
        .done-task {
            text-decoration: line-through;
            color: #b2bec3;
            background: #f1f2f6;
        }
        .btn-action {
            border: none;
            background: none;
            padding: 0 6px;
            color: #5e72e4;
            font-size: 1.2rem;
        }
        .btn-action:hover {
            color: #1abc9c;
        }
        .badge-status {
            font-size: 0.8rem;
        }
        .edit-form {
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container my-5" style="max-width: 600px;">
        <header class="text-center mb-4">
            <h1 class="fw-bold"><i class="bi bi-list-check"></i> Manajemen Tugas</h1>
            <p class="text-muted">Kelola tugas harianmu dengan mudah dan rapi.</p>
        </header>

        <!-- Form Tambah Tugas -->
        <section class="card p-4 shadow-sm mb-4">
            <form method="post" action="">
                <div class="row g-2 align-items-center">
                    <div class="col-9">
                        <input type="text" name="task" class="form-control" placeholder="Tambahkan tugas baru..." required autocomplete="off">
                    </div>
                    <div class="col-3 d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Tambah
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Daftar Tugas -->
        <section>
            <h4 class="mb-3"><i class="bi bi-card-checklist"></i> Daftar Tugas</h4>
            <ul class="list-group">
                <?php if (empty($tasks)): ?>
                    <li class="list-group-item text-muted text-center">Belum ada tugas.</li>
                <?php else: ?>
                    <?php foreach ($tasks as $index => $task): ?>
                        <li class="list-group-item d-flex flex-column <?php if ($task['done']) echo 'done-task'; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span><?= $index + 1 ?>. <?= $task['title'] ?></span>
                                    <?php if ($task['done']): ?>
                                        <span class="badge bg-success badge-status ms-2"><i class="bi bi-check-circle"></i> Selesai</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark badge-status ms-2"><i class="bi bi-hourglass-split"></i> Aktif</span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="?done=<?= $index ?>" class="btn-action" title="Tandai <?= $task['done'] ? 'Belum Selesai' : 'Selesai' ?>">
                                        <i class="bi <?= $task['done'] ? 'bi-arrow-counterclockwise' : 'bi-check2-circle' ?>"></i>
                                    </a>
                                    <a href="?edit=<?= $index ?>" class="btn-action text-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="?delete=<?= $index ?>" class="btn-action text-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus tugas ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Form Edit -->
                            <?php if ($editMode === $index): ?>
                                <form method="post" class="edit-form" action="">
                                    <div class="input-group">
                                        <input type="text" name="edit_task" class="form-control" value="<?= $task['title'] ?>" required>
                                        <input type="hidden" name="edit_index" value="<?= $index ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-save"></i> Simpan
                                        </button>
                                        <a href="todolist.php" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-x"></i> Batal
                                        </a>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </section>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
