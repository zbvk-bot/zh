<?php
require 'check_admin.php';
require '../db.php'; 

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $pdo->query("
    SELECT b.*, a.name AS author, g.name AS genre
    FROM books b
    JOIN authors a ON b.author_id = a.id
    JOIN genres g ON b.genre_id = g.id
    ORDER BY b.id DESC
");
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админка</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<div class="alert alert-success">
    <h1>Панель Администратора</h1>
    <p>Добро пожаловать в систему управления.</p>
    <a href="add_item.php" class="btn btn-primary">Добавить запись</a>
    <a href="logout.php" class="btn btn-danger">Выйти</a>
</div>

<h2 class="mt-4">Список книг</h2>

<?php if (!empty($_GET['deleted'])): ?>
    <div class="alert alert-warning">Книга удалена.</div>
<?php endif; ?>
<?php if (!empty($_GET['updated'])): ?>
    <div class="alert alert-success">Книга обновлена.</div>
<?php endif; ?>

<?php if (empty($books)): ?>
    <p>Книг пока нет.</p>
<?php else: ?>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название</th>
                <th>Автор</th>
                <th>Жанр</th>
                <th>ISBN</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $b): ?>
            <tr>
                <td><?= $b['id'] ?></td>
                <td><?= h($b['title']) ?></td>
                <td><?= h($b['author']) ?></td>
                <td><?= h($b['genre']) ?></td>
                <td><?= h($b['isbn']) ?></td>
                <td><?= h($b['status']) ?></td>
                <td>
                    <a href="edit_book.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>

                    <form action="delete_book.php" method="POST" style="display:inline-block;">
                        <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить книгу?')">Удалить</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>