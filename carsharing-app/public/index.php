<?php
session_start();
require_once '../config/db.php';

$pdo = db_connect();

/**
 * Извлекает список автомобилей со статусом 'available'
 * для отображения на главной странице (публичный доступ).
 *
 * @return array $cars
 */
$stmt = $pdo->prepare("SELECT * FROM cars WHERE status = 'available'");
$stmt->execute();
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная — CarSharing</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="home-body">

    <header class="main-header">
        <div class="logo">C<span>AR</span>SHARE</div>
        <nav>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php">Кабинет</a>
        <a href="logout.php">Выход</a>
    <?php else: ?>
        <a href="login.php">Вход</a>
        <a href="register.php">Регистрация</a>
    <?php endif; ?>
    <a href="search.php">Поиск</a>
</nav>
    </header>

    <main class="car-grid"> <!-- заменили class="content-grid" на нужный grid -->
        <?php foreach ($cars as $car): ?>
        <article class="card">
            <img src="uploads/<?= htmlspecialchars($car['photo']) ?>" alt="Фото автомобиля">
            <div class="card-body">
                <span class="tag">ДОСТУПНО</span>
                <h2><?= htmlspecialchars($car['model']) ?></h2>
                <p><?= htmlspecialchars($car['location']) ?> — <strong><?= htmlspecialchars($car['price_per_hour']) ?> лей/час</strong></p>
                <a href="book.php?id=<?= $car['id'] ?>" class="card-button">Забронировать</a>
            </div>
        </article>
        <?php endforeach; ?>
    </main>
</body>
</html>

