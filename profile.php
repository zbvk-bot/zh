<?php
session_start();
require '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
    b.id AS book_id,
    b.title,
    a.name AS author,
    g.name AS genre,
    b.isbn,
    b.image_url,
    b.description,
    br.status,
    br.taken_at,
    br.returned_at
FROM borrowings br
JOIN books b ON br.book_id = b.id
JOIN authors a ON b.author_id = a.id
JOIN genres g ON b.genre_id = g.id
WHERE br.user_id = ?
ORDER BY br.taken_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-light bg-light px-4 mb-4">
    <span class="navbar-brand">Личный кабинет</span>
    <div>
        <a href="index.php" class="btn btn-secondary btn-sm">Главная</a>
        <a href="logout.php" class="btn btn-dark btn-sm">Выйти</a>
    </div>
</nav>

<div class="container">
    <h2 class="mb-4">Ваши книги</h2>
    <?php if (empty($books)): ?>
        <p>Вы пока не брали книги.</p>
    <?php else: ?>
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
                            <p class="card-text"><?= h($b['description']) ?></p>
                            <p class="card-text"><strong>Статус:</strong> <?= h($b['status']) ?></p>
                            <p class="card-text"><strong>Взято:</strong> <?= $b['taken_at'] ?></p>
                            <?php if ($b['returned_at']): ?>
                                <p class="card-text"><strong>Возвращено:</strong> <?= $b['returned_at'] ?></p>
                            <?php endif; ?>

                            <?php if ($b['status'] === 'taken'): ?>
                                <form action="return_book.php" method="POST" class="mt-2">
                                    <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <button type="submit" class="btn btn-warning">Вернуть книгу</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>