<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

// Получаем список доступных автомобилей
$stmt = $pdo->prepare("SELECT * FROM cars WHERE status = 'available'");
$stmt->execute();
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id     = $_POST['car_id'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time   = $_POST['end_time'] ?? '';
    $user_id    = $_SESSION['user_id'];

    // Валидация
    if (!$car_id || !$start_time || !$end_time) {
        $error = "Пожалуйста, заполните все поля.";
    } elseif (strtotime($start_time) >= strtotime($end_time)) {
        $error = "Дата окончания должна быть позже даты начала.";
    } elseif (strtotime($start_time) < time()) {
        $error = "Дата начала не может быть в прошлом.";
    } else {
        // Проверка: не забронирована ли машина повторно
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE car_id = ? AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))");
        $stmt->execute([$car_id, $end_time, $start_time, $start_time, $end_time]);

        if ($stmt->fetch()) {
            $error = "Этот автомобиль уже забронирован на выбранное время.";
        } else {
            // Бронирование
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $car_id, $start_time, $end_time]);

            // Обновляем статус машины
            $stmt = $pdo->prepare("UPDATE cars SET status = 'booked' WHERE id = ?");
            $stmt->execute([$car_id]);

            $success = "Бронирование успешно оформлено!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Бронирование автомобиля</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Бронирование автомобиля</h1>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form method="post">
        <label>Выберите автомобиль:</label><br>
        <select name="car_id" required>
            <option value="">-- Выберите --</option>
            <?php foreach ($cars as $car): ?>
                <option value="<?= $car['id'] ?>">
                    <?= htmlspecialchars($car['model']) ?> (<?= htmlspecialchars($car['number_plate']) ?>) — <?= htmlspecialchars($car['price_per_hour']) ?> лей/час
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Дата и время начала:</label><br>
        <input type="datetime-local" name="start_time" required><br><br>

        <label>Дата и время окончания:</label><br>
        <input type="datetime-local" name="end_time" required><br><br>

        <button type="submit">Забронировать</button>
    </form>

    <p><a href="dashboard.php">Назад в кабинет</a></p>
</body>
</html>
