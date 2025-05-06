<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "ID бронирования не указан.";
    exit;
}

$booking_id = (int) $_GET['id'];

// Проверка: принадлежит ли бронирование этому пользователю
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Бронирование не найдено или доступ запрещён.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';

    if ($start && $end && strtotime($start) < strtotime($end)) {
        $update = $pdo->prepare("UPDATE bookings SET start_time = ?, end_time = ? WHERE id = ?");
        $update->execute([$start, $end, $booking_id]);
        header("Location: my_bookings.php");
        exit;
    } else {
        $error = "Введите корректные дату и время.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактировать бронирование</title>
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

    <main class="centered">
        <div class="form-container" style="max-width: 500px;">
            <h1>Изменить бронирование</h1>

            <?php if (!empty($error)): ?>
                <div class="error"><?= $error ?></div>
            <?php endif; ?>

            <form method="post">
                <label>Начало аренды:</label>
                <input type="datetime-local" name="start_time" value="<?= htmlspecialchars($booking['start_time']) ?>" required>

                <label>Конец аренды:</label>
                <input type="datetime-local" name="end_time" value="<?= htmlspecialchars($booking['end_time']) ?>" required>

                <button type="submit">Сохранить изменения</button>
            </form>

            <p style="margin-top: 12px; text-align: center;"><a href="my_bookings.php">Назад</a></p>
        </div>
    </main>

</body>
</html>
