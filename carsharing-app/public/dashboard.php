<?php
session_start();

/**
 * Страница личного кабинета пользователя.
 * Предоставляет доступ к действиям в зависимости от роли (user/admin).
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'Гость';
$role = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="home-body">

    <header class="main-header">
        <div class="logo">C<span>AR</span>SHARE</div>
        <nav>
            <a href="index.php">Главная</a>
            <a href="logout.php">Выход</a>
        </nav>
    </header>

    <main style="padding: 40px; max-width: 900px; margin: 0 auto;">
        <h1 style="color: white;">Добро пожаловать, <?= htmlspecialchars($name) ?>!</h1>
        <p style="color: #ccc;">Вы вошли как: <strong><?= htmlspecialchars($role) ?></strong></p>

        <ul style="list-style: none; padding: 0; margin-top: 25px;">
            <li style="margin-bottom: 10px;"><a href="select_car.php" class="btn">Забронировать автомобиль</a></li>

            <?php if ($role === 'admin'): ?>
                <li style="margin-bottom: 10px;"><a href="../admin/cars.php" class="card-button">Управление автопарком</a></li>
                <li style="margin-bottom: 10px;"><a href="../admin/users.php" class="card-button">Пользователи</a></li>
            <?php else: ?>
                <li style="margin-bottom: 10px;"><a href="my_bookings.php" class="card-button">Мои бронирования</a></li>
            <?php endif; ?>

            <li><a href="logout.php" class="card-button" style="background:#555;">Выйти</a></li>
        </ul>

    </main>

</body>
</html>
