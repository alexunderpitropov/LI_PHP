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
<body class="home-body">

    <header class="main-header">
        <div class="logo">C<span>AR</span>SHARE</div>
        <nav>
            <a href="dashboard.php">Кабинет</a>
            <a href="logout.php">Выход</a>
        </nav>
    </header>

    <main style="padding: 40px;">
        <h1 style="color: white; text-align: center; margin-bottom: 40px;">Выберите автомобиль для бронирования</h1>

        <?php if (count($cars) === 0): ?>
            <div class="error">Нет доступных автомобилей.</div>
        <?php else: ?>
            <div class="car-grid">
                <?php foreach ($cars as $car): ?>
                    <div class="card">
                        <?php if (!empty($car['photo'])): ?>
                            <img src="uploads/<?= htmlspecialchars($car['photo']) ?>" alt="Фото авто">
                        <?php endif; ?>
                        <div class="card-body">
                            <p><strong>Модель:</strong> <?= htmlspecialchars($car['model']) ?></p>
                            <p><strong>Номер:</strong> <?= htmlspecialchars($car['number_plate']) ?></p>
                            <p><strong>Местоположение:</strong> <?= htmlspecialchars($car['location']) ?></p>
                            <p><strong>Цена:</strong> <?= htmlspecialchars($car['price_per_hour']) ?> лей/час</p>
                            <a href="book.php?id=<?= $car['id'] ?>" class="card-button">Забронировать</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="dashboard.php">Назад</a>
        </div>
    </main>

</body>
</html>
