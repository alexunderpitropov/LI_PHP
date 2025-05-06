<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $errors = [];

    if (!$name || !$email || !$password || !$confirm) {
        $errors[] = "Все поля обязательны.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Некорректный email.";
    } elseif ($password !== $confirm) {
        $errors[] = "Пароли не совпадают.";
    } elseif (strlen($password) < 4) {
        $errors[] = "Пароль должен быть не короче 4 символов.";
    } else {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Пользователь с таким email уже существует.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['role'] = 'user';
            $_SESSION['name'] = $name;
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="centered">
    <div class="form-container">
        <h1>Регистрация</h1>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?= implode("<br>", $errors) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="name" placeholder="Имя" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
            <button type="submit">Зарегистрироваться</button>
        </form>

        <p style="text-align:center; margin-top:10px;">
            <a href="login.php">Уже есть аккаунт?</a><br>
            <a href="index.php">На главную</a>
        </p>
    </div>
</body>
</html>
