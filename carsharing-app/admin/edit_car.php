<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: cars.php");
    exit;
}

// Обработка обновления
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = trim($_POST['model'] ?? '');
    $plate = trim($_POST['number_plate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = $_POST['price_per_hour'] ?? '';
    $status = $_POST['status'] ?? 'available';

    if ($model && $plate && $location && is_numeric($price)) {
        $stmt = $pdo->prepare("UPDATE cars SET model = ?, number_plate = ?, location = ?, price_per_hour = ?, status = ? WHERE id = ?");
        $stmt->execute([$model, $plate, $location, $price, $status, $id]);
        header("Location: cars.php");
        exit;
    } else {
        $error = "Заполните все поля корректно.";
    }
}

// Получение данных об авто
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$car) {
    header("Location: cars.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактирование автомобиля</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Редактировать автомобиль</h1>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="post">
        Модель: <input type="text" name="model" value="<?= htmlspecialchars($car['model']) ?>" required><br><br>
        Номерной знак: <input type="text" name="number_plate" value="<?= htmlspecialchars($car['number_plate']) ?>" required><br><br>
        Местоположение: <input type="text" name="location" value="<?= htmlspecialchars($car['location']) ?>" required><br><br>
        Цена за час: <input type="number" step="0.01" name="price_per_hour" value="<?= $car['price_per_hour'] ?>" required><br><br>
        Статус:
        <select name="status">
            <option value="available" <?= $car['status'] === 'available' ? 'selected' : '' ?>>Доступна</option>
            <option value="booked" <?= $car['status'] === 'booked' ? 'selected' : '' ?>>Забронирована</option>
            <option value="maintenance" <?= $car['status'] === 'maintenance' ? 'selected' : '' ?>>На обслуживании</option>
        </select><br><br>

        <button type="submit">Сохранить изменения</button>
    </form>

    <p><a href="cars.php">Назад к автопарку</a></p>
</body>
</html>
