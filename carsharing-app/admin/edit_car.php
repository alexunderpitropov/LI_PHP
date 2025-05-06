<?php
session_start();
require_once '../config/db.php';

// Проверка авторизации администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();
$car_id = $_GET['id'] ?? null;

if (!$car_id) {
    header("Location: cars.php");
    exit;
}

/**
 * Получает данные автомобиля по ID для редактирования.
 *
 * @param int $car_id ID автомобиля.
 * @return array|null $car Данные автомобиля или null, если не найден.
 */
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: cars.php");
    exit;
}

/**
 * Обрабатывает отправку формы редактирования:
 * - проверяет и валидирует поля;
 * - обновляет данные в базе.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = trim($_POST['model'] ?? '');
    $plate = trim($_POST['number_plate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = $_POST['price_per_hour'] ?? '';
    $status = $_POST['status'] ?? 'available';

    if ($model && $plate && $location && is_numeric($price)) {
        $stmt = $pdo->prepare("UPDATE cars SET model = ?, number_plate = ?, location = ?, price_per_hour = ?, status = ? WHERE id = ?");
        $stmt->execute([$model, $plate, $location, $price, $status, $car_id]);
        header("Location: cars.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактировать авто</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>
<body class="centered">
    <div class="form-container">
        <h1>Редактировать автомобиль</h1>

        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Модель:</label>
            <input type="text" name="model" value="<?= htmlspecialchars($car['model']) ?>" required>

            <label>Номерной знак:</label>
            <input type="text" name="number_plate" value="<?= htmlspecialchars($car['number_plate']) ?>" required>

            <label>Местоположение:</label>
            <input type="text" name="location" value="<?= htmlspecialchars($car['location']) ?>" required>

            <label>Цена за час:</label>
            <input type="number" step="0.01" name="price_per_hour" value="<?= htmlspecialchars($car['price_per_hour']) ?>" required>

            <label>Статус:</label>
            <select name="status">
                <option value="available" <?= $car['status'] === 'available' ? 'selected' : '' ?>>Доступен</option>
                <option value="booked" <?= $car['status'] === 'booked' ? 'selected' : '' ?>>Забронирован</option>
                <option value="maintenance" <?= $car['status'] === 'maintenance' ? 'selected' : '' ?>>На обслуживании</option>
            </select>

            <button type="submit">Сохранить изменения</button>
        </form>

        <div style="text-align:center; margin-top: 15px;">
            <a href="cars.php">Назад к автопарку</a>
        </div>
    </div>
</body>
</html>
