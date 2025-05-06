<?php
require_once '../config/db.php';

$token = $_GET['token'] ?? '';
$pdo = db_connect();

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        die("Ссылка устарела или недействительна.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['new_password'] ?? '';
        if (strlen($new_password) >= 4) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $reset['user_id']]);

            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$reset['user_id']]);

            echo "Пароль успешно обновлён. <a href='login.php'>Войти</a>";
            exit;
        } else {
            $error = "Пароль слишком короткий.";
        }
    }
} else {
    die("Токен не указан.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Сброс пароля</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Сброс пароля</h1>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="post">
        Новый пароль: <input type="password" name="new_password" required><br><br>
        <button type="submit">Сбросить пароль</button>
    </form>
</body>
</html>
