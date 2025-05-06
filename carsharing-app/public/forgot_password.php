<?php
require_once '../config/db.php';
$pdo = db_connect();

$error = '';
$success = '';
$tokenLink = '';

/**
 * Обработка формы восстановления пароля:
 * - проверка email;
 * - генерация токена;
 * - сохранение токена в таблице password_resets;
 * - отображение ссылки на сброс.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный email.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // удалим старые токены
            $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

            // сохраним новый токен
            $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
                ->execute([$user['id'], $token, $expires]);

            $tokenLink = "http://localhost/LI_PHP/carsharing-app/reset_password.php?token=" . $token;
        } else {
            $error = "Пользователь с таким email не найден.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Восстановление пароля</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>
<body style="padding: 40px;">
    <h1>Восстановление пароля</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
        <p>Ссылка для сброса (демо): <a href="<?= htmlspecialchars($tokenLink) ?>">Сбросить пароль</a></p>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Введите email:</label><br>
        <input type="email" name="email" required><br><br>
        <button type="submit">Отправить ссылку</button>
    </form>

    <p style="margin-top: 15px;"><a href="login.php">Назад</a></p>
</body>
</html>
