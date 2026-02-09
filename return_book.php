<?php
session_start();
require '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Ошибка CSRF: неверный токен');
}

$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
if ($book_id <= 0) {
    die('Неверный ID книги');
}

$stmt = $pdo->prepare("SELECT * FROM borrowings WHERE user_id = ? AND book_id = ? AND status = 'taken'");
$stmt->execute([$_SESSION['user_id'], $book_id]);
$borrow = $stmt->fetch();

if (!$borrow) {
    die('Эта книга не взята или уже возвращена');
}

$stmt = $pdo->prepare("UPDATE borrowings SET status = 'returned', returned_at = NOW() WHERE id = ?");
$stmt->execute([$borrow['id']]);

header('Location: profile.php?returned=1');
exit;