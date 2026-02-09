<?php
session_start();
require '../db.php'; 

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Доступ запрещён');
}

$sql = "
SELECT 
    borrowings.id AS borrow_id,
    borrowings.book_id,
    users.email AS user_email,
    books.title AS book_title,
    borrowings.status,
    borrowings.taken_at,
    borrowings.returned_at
FROM borrowings
JOIN users ON borrowings.user_id = users.id
JOIN books ON borrowings.book_id = books.id
ORDER BY borrowings.taken_at DESC
";

$stmt = $pdo->query($sql);
$borrowings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ — Взятие книг</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Список всех взятых книг</h2>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Пользователь</th>
                <th>Книга</th>
                <th>Статус</th>
                <th>Дата взятия</th>
                <th>Дата возврата</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($borrowings as $b): ?>
            <tr>
                <td><?= $b['borrow_id'] ?></td>
                <td><?= h($b['user_email']) ?></td>
                <td><?= h($b['book_title']) ?></td>
                <td><?= h($b['status']) ?></td>
                <td><?= $b['taken_at'] ?></td>
                <td><?= $b['returned_at'] ?? '-' ?></td>
                <td>
                    <?php if ($b['status'] === 'taken'): ?>
                        <a href="return_book.php?book_id=<?= $b['book_id'] ?>" class="btn btn-warning btn-sm">
                            Вернуть
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="admin_panel.php" class="btn btn-secondary">Назад</a>
</div>
</body>
</html>