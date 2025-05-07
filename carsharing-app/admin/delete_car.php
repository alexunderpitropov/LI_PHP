<?php
session_start();
require_once '../config/db.php';

/**
 * Проверка доступа: только администратор.
 */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

// Получение ID автомобиля из GET-запроса
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: cars.php");
    exit;
}

// Получение информации об автомобиле для отображения
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

// Если авто не найден — редирект
if (!$car) {
    header("Location: cars.php");
    exit;
}

// Удаление при подтверждении
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Удаление записи
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->execute([$id]);

    // Удаление фото (если файл существует)
    $photoPath = "../public/uploads/" . $car['photo'];
    if (file_exists($photoPath)) {
        unlink($photoPath);
    }

    header("Location: cars.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Удаление автомобиля</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>
<body>
    <h1>Удаление автомобиля</h1>
    <p>Вы уверены, что хотите удалить автомобиль <strong><?= htmlspecialchars($car['model']) ?></strong>?</p>

    <form method="post">
        <button type="submit">Удалить</button>
        <a href="cars.php" style="margin-left: 20px;">Отмена</a>
    </form>
</body>
</html>
