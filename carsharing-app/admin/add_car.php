<?php
session_start();
require_once '../config/db.php';

// Проверка авторизации администратора
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/**
 * Обрабатывает форму добавления автомобиля:
 * - проверяет поля;
 * - загружает изображение;
 * - сохраняет автомобиль в базу данных.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = trim($_POST['model'] ?? '');
    $plate = trim($_POST['number_plate'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $price = $_POST['price_per_hour'] ?? '';
    $photo = $_FILES['photo'] ?? null;

    if ($model && $plate && $location && is_numeric($price) && $photo && $photo['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png'];
        if (!in_array($photo['type'], $allowed)) {
            $error = "Только изображения JPEG или PNG.";
        } else {
            $extension = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $filename = uniqid('car_') . '.' . $extension;
            $destination = '../public/uploads/' . $filename;

            if (move_uploaded_file($photo['tmp_name'], $destination)) {
                $pdo = db_connect();
                $stmt = $pdo->prepare("INSERT INTO cars (model, number_plate, location, price_per_hour, photo) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$model, $plate, $location, $price, $filename]);
                header("Location: cars.php");
                exit;
            } else {
                $error = "Ошибка при загрузке изображения.";
            }
        }
    } else {
        $error = "Пожалуйста, заполните все поля корректно.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление автомобиля</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>
<body class="centered">
    <div class="form-container">
        <h1>Добавить автомобиль</h1>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label>Модель:</label>
            <input type="text" name="model" required>

            <label>Номерной знак:</label>
            <input type="text" name="number_plate" required>

            <label>Местоположение:</label>
            <input type="text" name="location" required>

            <label>Цена за час:</label>
            <input type="number" step="0.01" name="price_per_hour" required>

            <label>Фото (JPG/PNG):</label>
            <input type="file" name="photo" accept="image/*" required>

            <button type="submit">Сохранить</button>
        </form>

        <div style="text-align: center; margin-top: 15px;">
            <a href="cars.php">Назад к автопарку</a>
        </div>
    </div>
</body>
</html>
