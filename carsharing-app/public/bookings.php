<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT b.*, c.model, c.number_plate
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.start_time DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Мои бронирования</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Мои бронирования</h1>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Модель</th>
            <th>Номер</th>
            <th>Начало</th>
            <th>Окончание</th>
            <th>Дата оформления</th>
        </tr>
        <?php foreach ($bookings as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['model']) ?></td>
            <td><?= htmlspecialchars($b['number_plate']) ?></td>
            <td><?= $b['start_time'] ?></td>
            <td><?= $b['end_time'] ?></td>
            <td><?= $b['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="dashboard.php">Назад</a></p>
</body>
</html>
