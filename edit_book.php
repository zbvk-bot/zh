<?php
session_start();
require 'check_admin.php';
require '../db.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($book_id <= 0) {
    die('Неверный ID книги');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка CSRF: неверный токен');
    }

    $title = trim($_POST['title']);
    $author_id = (int)$_POST['author_id'];
    $genre_id = (int)$_POST['genre_id'];
    $isbn = trim($_POST['isbn']);
    $status = trim($_POST['status']);
    $description = trim($_POST['description']);
    $image_url = trim($_POST['image_url']);

    $stmt = $pdo->prepare("
        UPDATE books SET
            title = ?,
            author_id = ?,
            genre_id = ?,
            isbn = ?,
            status = ?,
            description = ?,
            image_url = ?
        WHERE id = ?
    ");
    $stmt->execute([$title, $author_id, $genre_id, $isbn, $status, $description, $image_url, $book_id]);

    header('Location: admin_panel.php?updated=1');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();
if (!$book) {
    die('Книга не найдена');
}

$authors = $pdo->query("SELECT * FROM authors ORDER BY name ASC")->fetchAll();
$genres = $pdo->query("SELECT * FROM genres ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать книгу</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <h1>Редактировать книгу</h1>
    <a href="admin_panel.php" class="btn btn-secondary mb-3">Назад в админку</a>

    <form method="POST" class="w-50">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label class="form-label">Название</label>
            <input type="text" name="title" class="form-control" value="<?= h($book['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Автор</label>
            <select name="author_id" class="form-select" required>
                <?php foreach ($authors as $a): ?>
                    <option value="<?= $a['id'] ?>" <?= $a['id'] == $book['author_id'] ? 'selected' : '' ?>>
                        <?= h($a['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Жанр</label>
            <select name="genre_id" class="form-select" required>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= $g['id'] == $book['genre_id'] ? 'selected' : '' ?>>
                        <?= h($g['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control" value="<?= h($book['isbn']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Статус</label>
            <select name="status" class="form-select" required>
                <option value="available" <?= $book['status'] === 'available' ? 'selected' : '' ?>>Доступна</option>
                <option value="taken" <?= $book['status'] === 'taken' ? 'selected' : '' ?>>Взята</option>
            </select>
        </div>
<div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-control" rows="4"><?= h($book['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">URL изображения</label>
            <input type="text" name="image_url" class="form-control" value="<?= h($book['image_url']) ?>">
        </div>

        <button type="submit" class="btn btn-success">Сохранить изменения</button>
    </form>
</body>
</html>