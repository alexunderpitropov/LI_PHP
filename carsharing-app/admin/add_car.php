<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

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
            $destination = '../uploads/' . $filename;

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
<html>
<head>
    <meta charset="UTF-8">
    <title>Добавление автомобиля</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Добавить автомобиль</h1>

    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="post" enctype="multipart/form-data">
        Модель: <input type="text" name="model" required><br><br>
        Номерной знак: <input type="text" name="number_plate" required><br><br>
        Местоположение: <input type="text" name="location" required><br><br>
        Цена за час: <input type="number" step="0.01" name="price_per_hour" required><br><br>
        Фото (JPG/PNG): <input type="file" name="photo" accept="image/*" required><br><br>
        <button type="submit">Сохранить</button>
    </form>

    <p><a href="cars.php">Назад к автопарку</a></p>
</body>
</html>
