<?php
session_start();
require 'check_admin.php';
require '../db.php';

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Ошибка CSRF: неверный токен');
}

$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
if ($book_id <= 0) {
    die('Неверный ID книги');
}

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    die('Книга не найдена');
}

$stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
$stmt->execute([$book_id]);

header('Location: admin_panel.php?deleted=1');
exit;