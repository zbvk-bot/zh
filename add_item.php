<?php
require '../db.php';
require 'check_admin.php';

$message = '';

$authors = $pdo->query("SELECT * FROM authors")->fetchAll();
$genres  = $pdo->query("SELECT * FROM genres")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title     = trim($_POST['title']);
    $author_id = $_POST['author_id'];
    $genre_id  = $_POST['genre_id'];
    $isbn      = trim($_POST['isbn']);
    $desc      = trim($_POST['description']);
    $img       = trim($_POST['image_url']);
    $status    = $_POST['status'] ?? 'В наличии';

    if (empty($title)) {
        $message = '<div class="alert alert-danger">Заполните название!</div>';
    } else {
        $sql = "INSERT INTO books (title, author_id, genre_id, isbn, description, image_url, status) 
                VALUES (:t, :a, :g, :i, :d, :img, :s)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':t'   => $title,
            ':a'   => $author_id,
            ':g'   => $genre_id,
            ':i'   => $isbn,
            ':d'   => $desc,
            ':img' => $img,
            ':s'   => $status
        ]);
        $message = '<div class="alert alert-success">Книга успешно добавлена!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Добавить книгу</h1>
    <a href="admin_panel.php" class="btn btn-secondary mb-3">← Назад</a>
    <?= $message ?>
    <form method="POST" class="card p-4">
        <input type="text" name="title" class="form-control mb-2" placeholder="Название книги" required>

        <label>Автор:</label>
        <select name="author_id" class="form-control mb-2" required>
            <?php foreach ($authors as $a): ?>
                <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Жанр:</label>
        <select name="genre_id" class="form-control mb-2" required>
            <?php foreach ($genres as $g): ?>
                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="isbn" class="form-control mb-2" placeholder="ISBN книги">
        <input type="text" name="image_url" class="form-control mb-2" placeholder="URL обложки книги">
        <textarea name="description" class="form-control mb-2" placeholder="Описание книги"></textarea>

        <label>Статус:</label>
        <select name="status" class="form-control mb-2">
            <option value="В наличии">В наличии</option>
            <option value="На руках">На руках</option>
        </select>

        <button type="submit" class="btn btn-success">Сохранить</button>
    </form>
</div>
</body>
</html>