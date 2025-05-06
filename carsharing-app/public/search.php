<?php
require_once '../config/db.php';
session_start();

$cars = [];
$search = trim($_GET['query'] ?? '');
$status = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($search || $status)) {
    $pdo = db_connect();
    $sql = "SELECT * FROM cars WHERE 1=1";
    $params = [];

    if ($search) {
        $sql .= " AND (model LIKE ? OR location LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($status && in_array($status, ['available', 'booked', 'maintenance'])) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Поиск автомобиля</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="home-body">

<header class="main-header">
    <div class="logo">C<span>AR</span>SHARE</div>
    <nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="color:white; margin-right:15px;">Привет, <?= htmlspecialchars($_SESSION['name']) ?>!</span>
            <a href="dashboard.php">Кабинет</a>
            <a href="logout.php">Выход</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
        <?php endif; ?>
        <a href="index.php">Главная</a>
    </nav>
</header>


    <div class="centered" style="flex-direction: column; padding: 20px;">
        <div class="form-container" style="max-width: 600px; margin-bottom: 30px;">
            <h1>Поиск автомобиля</h1>
            <form method="get">
                <input type="text" name="query" placeholder="Введите модель или город" value="<?= htmlspecialchars($search) ?>">
                <select name="status">
                    <option value="">Любой статус</option>
                    <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Доступен</option>
                    <option value="booked" <?= $status === 'booked' ? 'selected' : '' ?>>Забронирован</option>
                    <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>На обслуживании</option>
                </select>
                <button type="submit">Найти</button>
            </form>
        </div>

        <?php if ($cars): ?>
            <div class="content-grid">
                <?php foreach ($cars as $car): ?>
                    <article class="card">
                        <img src="uploads/<?= htmlspecialchars($car['photo']) ?>" alt="Авто">
                        <div class="card-body">
                            <span class="tag"><?= strtoupper($car['status']) ?></span>
                            <h2><?= htmlspecialchars($car['model']) ?></h2>
                            <p><?= htmlspecialchars($car['location']) ?> — <strong><?= htmlspecialchars($car['price_per_hour']) ?> лей/час</strong></p>
                            <a href="book.php?id=<?= $car['id'] ?>" class="card-button">Забронировать</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && ($search || $status)): ?>
            <p style="color: #ccc; margin-top: 30px;">Автомобили не найдены.</p>
        <?php endif; ?>
    </div>

</body>
</html>
