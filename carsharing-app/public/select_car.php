<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();
$stmt = $pdo->query("SELECT * FROM cars WHERE status = 'available'");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Выбор авто</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="centered">
    <div class="form-container">
        <h1>Выберите автомобиль для бронирования</h1>

        <?php if (count($cars) === 0): ?>
            <div class="error">Нет доступных автомобилей.</div>
        <?php else: ?>
            <?php foreach ($cars as $car): ?>
                <div class="car-card">
                    <?php if (!empty($car['photo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($car['photo']) ?>" alt="Фото авто" style="max-width: 200px; border-radius: 5px;">
                    <?php endif; ?>
                    <p><strong>Модель:</strong> <?= htmlspecialchars($car['model']) ?></p>
                    <p><strong>Номер:</strong> <?= htmlspecialchars($car['number_plate']) ?></p>
                    <p><strong>Местоположение:</strong> <?= htmlspecialchars($car['location']) ?></p>
                    <p><strong>Цена:</strong> <?= htmlspecialchars($car['price_per_hour']) ?> лей/час</p>
                    <a href="book.php?id=<?= $car['id'] ?>" class="btn">Забронировать</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php">Назад</a>
        </div>
    </div>
</body>
</html>
