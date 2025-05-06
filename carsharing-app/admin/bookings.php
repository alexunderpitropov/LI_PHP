<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

$stmt = $pdo->query("
    SELECT b.*, c.model, c.number_plate, u.name AS user_name
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.start_time DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Все бронирования</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Все бронирования</h1>
    <p><a href="cars.php">Назад в автопарк</a></p>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Пользователь</th>
            <th>Модель</th>
            <th>Номер</th>
            <th>Начало</th>
            <th>Окончание</th>
            <th>Дата оформления</th>
        </tr>
        <?php foreach ($bookings as $b): ?>
        <tr>
            <td><?= htmlspecialchars($b['user_name']) ?></td>
            <td><?= htmlspecialchars($b['model']) ?></td>
            <td><?= htmlspecialchars($b['number_plate']) ?></td>
            <td><?= $b['start_time'] ?></td>
            <td><?= $b['end_time'] ?></td>
            <td><?= $b['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
