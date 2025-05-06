<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

// Удаление машины
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header("Location: cars.php");
    exit;
}

// Получаем список всех машин
$stmt = $pdo->query("SELECT * FROM cars");
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Управление автопарком</title>
    <link rel="stylesheet" href="../public/styles.css">

</head>
<body>
    <h1>Автопарк</h1>
    <p><a href="add_car.php">Добавить автомобиль</a> | <a href="../public/dashboard.php">Назад</a></p>

    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Модель</th>
            <th>Номер</th>
            <th>Местоположение</th>
            <th>Цена (лей/час)</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($cars as $car): ?>
        <tr>
            <td><?= $car['id'] ?></td>
            <td><?= htmlspecialchars($car['model']) ?></td>
            <td><?= htmlspecialchars($car['number_plate']) ?></td>
            <td><?= htmlspecialchars($car['location']) ?></td>
            <td><?= htmlspecialchars($car['price_per_hour']) ?></td>
            <td><?= htmlspecialchars($car['status']) ?></td>
            <td style="text-align: center;">
            <div style="margin-bottom: 10px;">
                <img src="../public/uploads/<?= htmlspecialchars($car['photo']) ?>" alt="Фото"
                    style="height: 110px; width: auto; border-radius: 6px; box-shadow: 0 0 5px rgba(0,0,0,0.5);">
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px; align-items: center;">
                <a href="../public/book.php?id=<?= $car['id'] ?>" class="btn">Забронировать</a>
                <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn">Редактировать</a>
                <a href="delete_car.php?id=<?= $car['id'] ?>" class="btn delete">Удалить</a>
            </div>

        </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
