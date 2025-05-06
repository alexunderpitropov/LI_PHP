<?php
require_once '../config/db.php';
$pdo = db_connect();

if (!isset($_GET['token'])) {
    die('Токен не передан.');
}

$token = $_GET['token'];

$stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die('Недействительный или истекший токен.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['password'] ?? '';
    if (strlen($newPassword) < 6) {
        $error = 'Пароль слишком короткий.';
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->execute([$hashed, $user['user_id']]);

        $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

        echo "Пароль успешно изменён. <a href='login.php'>Войти</a>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Сброс пароля</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="centered">
    <div class="form-container">
        <h1>Сброс пароля</h1>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Новый пароль" required>
            <button type="submit">Сменить пароль</button>
        </form>
    </div>
</body>
</html>
