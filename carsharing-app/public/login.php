<?php
session_start();
require_once '../config/db.php';

/**
 * Обрабатывает форму входа пользователя:
 * - извлекает email и пароль;
 * - проверяет пользователя в базе;
 * - устанавливает сессионные переменные;
 * - перенаправляет на dashboard.
 *
 * @param string $_POST['email'] Email пользователя.
 * @param string $_POST['password'] Пароль (в незахешированном виде).
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $pdo = db_connect();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Неверный логин или пароль.";
        }
    } else {
        $error = "Пожалуйста, заполните все поля.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<body class="centered">
    <div class="form-container">
        <h1>Вход в систему</h1>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <a href="forgot_password.php">Забыли пароль?</a><br>
        <a href="index.php">Назад</a>
    </div>
</body>
</html>
