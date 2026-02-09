<?php
session_start();
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
    <title>Главная — Онлайн-библиотека</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-light bg-light px-4 mb-4">
    <span class="navbar-brand">Онлайн-библиотека</span>
    <div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="admin_panel.php" class="btn btn-danger btn-sm">Админка</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-sm">Войти</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <div class="row">
        <?php foreach ($books as $b): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="<?= htmlspecialchars($b['image_url'] ?: 'https://via.placeholder.com/300') ?>" 
                         class="card-img-top" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= h($b['title']) ?></h5>
                        <p class="card-text"><strong>Автор:</strong> <?= h($b['author']) ?></p>
                        <p class="card-text"><strong>Жанр:</strong> <?= h($b['genre']) ?></p>
                        <p class="card-text"><strong>ISBN:</strong> <?= h($b['isbn']) ?></p>
                        <p class="card-text"><strong>Статус:</strong> <?= h($b['status']) ?></p>
                        <p class="card-text"><?= h($b['description']) ?></p>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php
                            // Проверяем, взял ли пользователь эту книгу и ещё не вернул
                            $stmt2 = $pdo->prepare("SELECT * FROM borrowings WHERE user_id = ? AND book_id = ? AND status = 'taken'");
                            $stmt2->execute([$_SESSION['user_id'], $b['id']]);
                            $borrowed = $stmt2->fetch();
                            ?>
                            <?php if ($borrowed): ?>
                                <form action="return_book.php" method="POST" class="mt-2">
                                    <input type="hidden" name="book_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <button type="submit" class="btn btn-warning">Вернуть книгу</button>
                                </form>
                            <?php else: ?>
                                <a href="take_book.php?book_id=<?= $b['id'] ?>" class="btn btn-primary mt-2">Взять книгу</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-secondary mt-2">Войдите, чтобы взять книгу</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>