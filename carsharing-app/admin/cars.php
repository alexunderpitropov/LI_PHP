<?php
session_start();
require_once '../../config/db.php';

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
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Автопарк</h1>
    <p><a href="add_car.php">Добавить автомобиль</a> | <a href="../dashboard.php">Назад</a></p>

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
            <td>
                <a href="edit_car.php?id=<?= $car['id'] ?>">Редактировать</a> |
                <form method="post" style="display:inline;" onsubmit="return confirm('Удалить автомобиль?');">
                    <input type="hidden" name="delete_id" value="<?= $car['id'] ?>">
                    <button type="submit">Удалить</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
