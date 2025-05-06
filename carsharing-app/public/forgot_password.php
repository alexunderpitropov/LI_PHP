<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email) {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 час

            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires_at]);

            // Для демонстрации просто выводим ссылку
            $reset_link = "http://localhost/reset_password.php?token=$token";
            $message = "Ссылка для сброса пароля (демо): <a href=\"$reset_link\">Сбросить пароль</a>";
        } else {
            $error = "Пользователь не найден.";
        }
    } else {
        $error = "Введите email.";
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Забыли пароль?</title>
        <link rel="stylesheet" href="styles.css">
    </head>
<body>
    <h1>Восстановление пароля</h1>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="post">
        Введите email: <input type="email" name="email" required><br><br>
        <button type="submit">Отправить ссылку</button>
    </form>

    <p><a href="login.php">Назад</a></p>
</body>
</html>
