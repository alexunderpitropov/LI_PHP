<?php
session_start();
require_once '../config/db.php'; // <--- добавлен до использования $pdo

$pdo = db_connect(); // <--- перенесён сюда ДО логики с prepare

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['end_booking_id'])) {
    $bookingId = $_POST['end_booking_id'];

    // Получаем car_id для обновления статуса машины
    $stmt = $pdo->prepare("SELECT car_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookingId, $_SESSION['user_id']]);
    $car = $stmt->fetch();

    if ($car) {
        $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?")->execute([$car['car_id']]);
        $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$bookingId]);

        // После завершения бронирования перенаправим, чтобы обновить страницу
        header("Location: my_bookings.php");
        exit;
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

// Шаг 1. Найдём id машин, аренда которых истекла
$expiredStmt = $pdo->query("SELECT DISTINCT car_id FROM bookings WHERE end_time < NOW()");
$expiredCars = $expiredStmt->fetchAll(PDO::FETCH_COLUMN);

// Шаг 2. Обновим статус машин, если есть истёкшие аренды
if (!empty($expiredCars)) {
    $inPlaceholders = implode(',', array_fill(0, count($expiredCars), '?'));
    $updateStmt = $pdo->prepare("UPDATE cars SET status = 'available' WHERE id IN ($inPlaceholders)");
    $updateStmt->execute($expiredCars);
}

// Получаем бронирования текущего пользователя
$stmt = $pdo->prepare("SELECT b.*, c.model, c.number_plate, c.location, c.photo 
                       FROM bookings b 
                       JOIN cars c ON b.car_id = c.id 
                       WHERE b.user_id = ? AND b.end_time > NOW()
                       ORDER BY b.start_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Мои бронирования</title>
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

    <main style="padding: 60px;">
        <h1 style="color: white; margin-bottom: 30px;">Мои бронирования</h1>

        <div class="car-grid">
            <?php foreach ($bookings as $booking): ?>
                <div class="card">
                    <img src="uploads/<?= htmlspecialchars($booking['photo']) ?>" alt="Car">
                    <div class="card-body">
                        <h2><?= htmlspecialchars($booking['model']) ?></h2>
                        <p><strong>Номер:</strong> <?= htmlspecialchars($booking['number_plate']) ?></p>
                        <p><strong>Местоположение:</strong> <?= htmlspecialchars($booking['location']) ?></p>
                        <p><strong>Начало:</strong> <?= htmlspecialchars($booking['start_time']) ?></p>
                        <p><strong>Конец:</strong> <?= htmlspecialchars($booking['end_time']) ?></p>
                        <div style="display: flex; gap: 10px; margin-top: 10px;">
                            <a href="edit_booking.php?id=<?= $booking['id'] ?>" class="btn">Изменить</a>
                            <form method="post">
                                <input type="hidden" name="end_booking_id" value="<?= $booking['id'] ?>">
                                <button class="btn delete" type="submit">Закончить</button>
                            </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
