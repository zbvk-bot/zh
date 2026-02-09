<?php
session_start();
require '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
if ($book_id <= 0) {
    die('Неверный ID книги');
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();
if (!$book) {
    die('Книга не найдена');
}

$stmt = $pdo->prepare("SELECT * FROM borrowings WHERE user_id = ? AND book_id = ? AND status = 'taken'");
$stmt->execute([$_SESSION['user_id'], $book_id]);
if ($stmt->fetch()) {
    die('Вы уже взяли эту книгу');
}

$stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, status) VALUES (?, ?, 'taken')");
$stmt->execute([$_SESSION['user_id'], $book_id]);

