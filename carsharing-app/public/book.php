<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

$car_id = $_GET['id'] ?? null;
$car = null;
$message = '';

if ($car_id) {
    // Только доступные машины
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND status = 'available'");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $car) {
    $user_id = $_SESSION['user_id'];
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';

    if (!$start || !$end) {
        $message = "Пожалуйста, укажите дату и время.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $car_id, $start, $end]);

        $stmt = $pdo->prepare("UPDATE cars SET status = 'booked' WHERE id = ?");
        $stmt->execute([$car_id]);

        $message = "Автомобиль успешно забронирован!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Бронирование</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="centered">
    <div class="form-container">
        <h1>Бронирование авто</h1>

        <?php if ($car): ?>
            <p><strong>Модель:</strong> <?= htmlspecialchars($car['model']) ?></p>
            <p><strong>Номер:</strong> <?= htmlspecialchars($car['number_plate']) ?></p>
            <p><strong>Цена:</strong> <?= htmlspecialchars($car['price_per_hour']) ?> лей/час</p>

            <?php if ($message): ?>
                <div class="success"><?= $message ?></div>
            <?php endif; ?>

            <form method="post">
                <label>Начало аренды:</label>
                <input type="datetime-local" name="start_time" required>

                <label>Окончание аренды:</label>
                <input type="datetime-local" name="end_time" required>

                <button type="submit">Забронировать</button>
            </form>

        <?php else: ?>
            <div class="error">Автомобиль недоступен для бронирования. Возможно, он уже занят или на обслуживании.</div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 15px;">
            <a href="dashboard.php">Назад</a>
        </div>
    </div>
</body>
</html>
