<?php
session_start();
require '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Ищем пользователя по email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Проверяем пароль
    if ($user && password_verify($pass, $user['password_hash'])) {
        
        // --- ВАЖНЫЕ ИЗМЕНЕНИЯ ---
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role']; // Сохраняем "браслет" (роль)
        // ------------------------

        // Маршрутизация: Админа — в панель, остальных — на главную
        if ($user['role'] === 'admin') {
            header("Location: admin_panel.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        echo "Неверный логин или пароль";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
</head>
<body>

<h2>Вход</h2>

<?php if (!empty($error)): ?>
    <p style="color:red"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Пароль:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Войти</button>
</form>

</body>
</html>