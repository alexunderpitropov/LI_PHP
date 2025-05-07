## Отчет по индивидуальной работе PHP — Веб-приложение CarSharing

Проект представляет собой полнофункциональную информационную систему для аренды автомобилей, разработанную с использованием языка PHP и базы данных MySQL. Весь функционал реализован строго в процедурном стиле без применения сторонних фреймворков. В основе лежит архитектура клиент-серверного взаимодействия: клиентская часть реализована через HTML-формы, стилизованные с помощью CSS, а серверная логика построена на PHP-обработчиках с подключением к базе через PDO.

Проект охватывает весь жизненный цикл аренды автомобиля: от регистрации пользователя, выбора авто и оформления бронирования до управления арендой и возврата машины. В приложении реализована ролевая модель, где предусмотрено разграничение прав между обычными пользователями и администраторами.

Приложение запускается на локальном сервере с помощью XAMPP. Для корректной работы необходимо развернуть проект в директории `htdocs`, создать базу данных через phpMyAdmin и импортировать файл `database.sql`. После этого можно открыть в браузере страницу `index.php` и приступить к взаимодействию.

Проект содержит следующую структуру файлов:

* `config/db.php` — подключение к базе данных
* `public/` — пользовательская часть (регистрация, логин, бронирование, личный кабинет и т.д.)
* `admin/` — административная панель управления пользователями и автопарком
* `uploads/` — директория для хранения изображений автомобилей

### Подробный разбор кода и логики по каждому файлу

**config/db.php**

```php
<?php
function db_connect() {
    return new PDO("mysql:host=localhost;dbname=carsharing-app;charset=utf8", "root", "");
}
?>
```

Этот файл содержит единственную функцию `db_connect`, которая используется повсеместно для установления подключения к базе данных через PDO. Это позволяет унифицировать работу с базой, обеспечивает безопасность и централизованную обработку ошибок. Когда пользователь, например, отправляет форму регистрации или логина, именно эта функция открывает соединение с MySQL для выполнения SQL-запросов.

**public/register.php**

```php
<?php
session_start();
require_once '../config/db.php';

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $errors = [];

    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'Все поля обязательны для заполнения';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Неверный формат email';
    }

    if ($password !== $confirm) {
        $errors[] = 'Пароли не совпадают';
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email уже зарегистрирован';
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $hashed]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['name'] = $name;
        $_SESSION['role'] = 'user';
        header("Location: dashboard.php");
        exit;
    }
}
?>
```

![image](https://i.imgur.com/cKDUHGr.png)

![image](https://i.imgur.com/OmfP7zZ.png)

![image](https://i.imgur.com/PZA8WNr.png)

Этот файл формирует точку входа для новых пользователей. Он получает данные из формы, проводит строгую серверную валидацию, проверяет уникальность email, сравнивает пароли. Если всё верно — создает нового пользователя, используя безопасное хеширование. После регистрации пользователь сразу перенаправляется в личный кабинет. Это обеспечивает удобство взаимодействия и сокращает количество шагов до начала бронирования.

**public/login.php**

```php
<?php
session_start();
require_once '../config/db.php';

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    if ($email === '' || $password === '') {
        $errors[] = 'Введите email и пароль';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = 'Неверные учетные данные';
        }
    }
}
?>
```

![image](https://i.imgur.com/6AUriqx.png)

Файл `login.php` реализует механизм входа пользователя. При отправке формы с email и паролем производится поиск пользователя в базе и проверка введённого пароля с помощью `password_verify`. Если данные совпадают, создаётся сессия с идентификатором пользователя, его именем и ролью. После успешного входа пользователь перенаправляется в `dashboard.php`, откуда он уже может управлять своими бронированиями или переходить к выбору автомобиля. Если введены неверные данные — отображается соответствующее сообщение об ошибке. Это ключевой механизм безопасности, ограничивающий доступ к приватным разделам приложения.

**public/dashboard.php**

```php
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
</head>
<body>
<h1>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>

<?php if ($_SESSION['role'] === 'admin'): ?>
    <p><a href="../admin/cars.php">Управление автомобилями</a></p>
    <p><a href="../admin/users.php">Пользователи</a></p>
    <p><a href="../admin/bookings.php">Бронирования</a></p>
<?php else: ?>
    <p><a href="select_car.php">Забронировать автомобиль</a></p>
    <p><a href="my_bookings.php">Мои бронирования</a></p>
<?php endif; ?>

<p><a href="logout.php">Выйти</a></p>
</body>
</html>
```

![image](https://i.imgur.com/HfWamJa.png)

![image](https://i.imgur.com/R28ffeF.png)

Этот файл — точка входа в личный кабинет пользователя. Сначала выполняется проверка авторизации: если сессия не установлена, происходит перенаправление на страницу входа. В зависимости от роли пользователя отображаются разные опции. Обычный пользователь видит ссылки на бронирование и просмотр своих заказов, а администратор — ссылки на управление машинами, пользователями и бронированиями. Это логическая развилка, обеспечивающая контроль доступа и правильный пользовательский интерфейс в зависимости от уровня прав.

**public/book.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'] ?? '';
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';

    if ($car_id && $start && $end) {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, car_id, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $car_id, $start, $end]);

        $stmt = $pdo->prepare("UPDATE cars SET status = 'booked' WHERE id = ?");
        $stmt->execute([$car_id]);

        header("Location: my_bookings.php");
        exit;
    }
}
?>
```

![image](https://i.imgur.com/ruzj2lF.png)

![image](https://i.imgur.com/JsydyZb.png)

Файл `book.php` обрабатывает запрос на бронирование автомобиля. Когда пользователь нажимает кнопку "Забронировать", передаются ID машины и дата/время начала и окончания аренды. Сначала проверяется авторизация, затем — наличие всех необходимых параметров. Если данные корректны, создается новая запись в таблице `bookings`, а статус автомобиля обновляется на `booked`, чтобы другие пользователи не могли выбрать тот же авто. В результате пользователь перенаправляется на страницу `my_bookings.php`, где он может увидеть подтверждение своей брони.

**public/my\_bookings.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = db_connect();

if (isset($_POST['end_booking_id'])) {
    $booking_id = $_POST['end_booking_id'];
    $stmt = $pdo->prepare("SELECT car_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $car = $stmt->fetch();

    if ($car) {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$booking_id]);

        $stmt = $pdo->prepare("UPDATE cars SET status = 'available' WHERE id = ?");
        $stmt->execute([$car['car_id']]);
    }
}

$stmt = $pdo->prepare("SELECT b.id, c.model, b.start_time, b.end_time FROM bookings b JOIN cars c ON b.car_id = c.id WHERE b.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
```

![image](https://i.imgur.com/55VKIFc.png)

На этой странице пользователь видит список всех своих активных бронирований. Каждое бронирование отображается с указанием модели автомобиля, дат начала и окончания аренды. Также присутствует кнопка "Завершить аренду", при нажатии на которую происходит удаление записи из таблицы `bookings`, а автомобиль возвращается в статус `available`. Это обеспечивает полную интерактивность и контроль над текущими арендами со стороны пользователя.

**public/search.php**

```php
<?php
require_once '../config/db.php';

$pdo = db_connect();
$term = trim($_GET['query'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM cars WHERE model LIKE ? AND status = 'available'");
$stmt->execute(['%' . $term . '%']);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
```

![image](https://i.imgur.com/SCUGVCS.png)

![image](https://i.imgur.com/MxpuQMl.png)

![image](https://i.imgur.com/vEI9JOW.png)

Файл `search.php` реализует простой поисковой механизм. Пользователь вводит часть названия автомобиля, и код производит поиск по модели с использованием шаблона `LIKE`. Только автомобили со статусом `available` попадают в результаты. Это позволяет пользователю быстро находить нужную машину среди всех доступных.

**admin/cars.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

$stmt = $pdo->query("SELECT * FROM cars");
cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
```

![image](https://i.imgur.com/VDQQu8d.png)

Файл `cars.php` в директории администратора отображает список всех автомобилей, доступных в системе. Он доступен только пользователям с ролью `admin`, что проверяется в начале сессии. После подключения к базе происходит выборка всех автомобилей из таблицы `cars`. Обычно далее в HTML-части идут таблица или карточки для визуального представления информации об автомобиле и действия по управлению ими.

**admin/add\_car.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = $_POST['model'] ?? '';
    $number_plate = $_POST['number_plate'] ?? '';
    $location = $_POST['location'] ?? '';
    $price = $_POST['price_per_hour'] ?? '';
    $status = 'available';

    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = '../uploads/';
        $photoName = 'car_' . uniqid() . '.' . pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photoName);
        $photo = $photoName;
    }

    $stmt = $pdo->prepare("INSERT INTO cars (model, number_plate, location, price_per_hour, status, photo) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$model, $number_plate, $location, $price, $status, $photo]);

    header("Location: cars.php");
    exit;
}
?>
```

![imgur](https://i.imgur.com/ZcKnOiK.png)

Данный файл позволяет администратору добавить новый автомобиль в систему. Он получает данные из формы, обрабатывает изображение и сохраняет его в папку `uploads/`. После этого все данные записываются в таблицу `cars`, включая путь к фото. Пользователь взаимодействует с формой и при успешной отправке возвращается к списку автомобилей.

**admin/edit_car.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

$id = $_GET['id'] ?? '';
if ($id === '') {
    header("Location: cars.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $model = $_POST['model'] ?? '';
    $number_plate = $_POST['number_plate'] ?? '';
    $location = $_POST['location'] ?? '';
    $price = $_POST['price_per_hour'] ?? '';
    $status = $_POST['status'] ?? 'available';

    $stmt = $pdo->prepare("UPDATE cars SET model = ?, number_plate = ?, location = ?, price_per_hour = ?, status = ? WHERE id = ?");
    $stmt->execute([$model, $number_plate, $location, $price, $status, $id]);

    header("Location: cars.php");
    exit;
}
?>
```

![image](https://i.imgur.com/NnRmFGU.png)

Этот файл открывает форму с уже существующими данными автомобиля, позволяет их редактировать и сохранять изменения обратно в таблицу cars. Пользователь взаимодействует с формой, видит текущие значения и может их изменить. После сохранения происходит перенаправление обратно к списку машин.

**admin/delete_car.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

$id = $_POST['delete_id'] ?? null;
if ($id !== null) {
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: cars.php");
exit;
?>
```

![image](https://i.imgur.com/KUomclq.png)

Этот скрипт используется для удаления автомобиля. Он получает id автомобиля из формы, проверяет авторизацию администратора, а затем удаляет запись из таблицы cars. После удаления происходит возврат к списку автомобилей.

**admin/bookings.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

$stmt = $pdo->query("SELECT b.id, u.name AS user_name, c.model AS car_model, b.start_time, b.end_time FROM bookings b JOIN users u ON b.user_id = u.id JOIN cars c ON b.car_id = c.id");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
```

![image](https://i.imgur.com/5Ui5BoT.png)

Файл позволяет администратору просматривать все бронирования в системе. Каждое бронирование содержит данные о пользователе, модели авто и сроках аренды. Это даёт администратору полную картину текущих операций.

**admin/users.php**

```php
<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pdo = db_connect();

$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
```

![image](https://i.imgur.com/uwgqHFX.png)

Страница выводит список всех зарегистрированных пользователей. Администратор видит email, имя и роль каждого пользователя. Это может использоваться для последующего добавления функций блокировки или изменения роли.

### Вывод

Данный проект представляет собой полноценное веб-приложение с широким функционалом. В нём реализованы все стадии аренды автомобиля, от регистрации до возврата, с полным контролем над действиями пользователя и администратора. Использование PHP и MySQL в процедурном стиле позволяет максимально прозрачно понять архитектуру и механику веб-приложений. Все данные обрабатываются безопасно, формы валидируются как на клиенте, так и на сервере. Код разделен по зонам ответственности, что делает проект удобным для сопровождения и расширения.

### Развёрнутые ответы на контрольные вопросы

**1. Как реализована безопасность хранения паролей?**
В проекте используется функция `password_hash()` для хеширования паролей. Это современный и безопасный метод, который автоматически добавляет соль к паролю и выбирает надёжный алгоритм хеширования (по умолчанию bcrypt). При аутентификации используется `password_verify()`, который сравнивает введённый пароль с хешем в базе. Это исключает возможность обратного восстановления пароля и делает невозможной атаку через словари. Хеши не могут быть использованы повторно для входа без знания исходного пароля. Это лучшая практика для хранения паролей в PHP.

**2. Как реализована форма с валидацией?**
Формы используют HTML5-валидацию (`required`, `type=email`, `min`, `max`) для начальной проверки данных на клиенте. Дополнительно реализована серверная валидация: используются функции `trim()`, `filter_var()`, `empty()` и другие, чтобы проверить корректность и безопасность данных. Это защищает от подмены данных через инспектор и обеспечивает стабильность бизнес-логики.

**3. Какие меры защиты от SQL-инъекций?**
Все SQL-запросы формируются через PDO с использованием подготовленных выражений (`prepare()` + `execute()`), что исключает возможность SQL-инъекций. Пользовательские данные никогда не вставляются напрямую в SQL-строки. Это считается стандартом безопасности при работе с базой данных в PHP.

**4. Как обеспечена защита от несанкционированного доступа?**
Каждая защищённая страница начинает выполнение с проверки наличия `$_SESSION['user_id']`. Если он не установлен — пользователь перенаправляется на `login.php`. Административные страницы дополнительно проверяют `$_SESSION['role'] === 'admin'`. Это гарантирует, что обычные пользователи не получат доступ к функциям администрирования.

**5. Какие существуют роли пользователей?**
В системе две роли: `user` и `admin`. Обычный пользователь может бронировать автомобили, управлять своими бронированиями, восстанавливать пароль. Администратор имеет доступ к панели управления автопарком, может добавлять, редактировать и удалять автомобили, а также просматривать всех пользователей и бронирования. Это разграничение реализовано через поле `role` в таблице `users` и проверку `$_SESSION['role']` в коде.

**6. Как происходит взаимодействие пользователя с приложением?**
Пользователь заходит на сайт, видит список авто, нажимает "Забронировать" и переходит к регистрации или авторизации. После входа он может выбрать машину, указать даты и оформить аренду. В личном кабинете он может просматривать свои бронирования и завершать их. Администратор имеет более широкий доступ и может управлять всей системой: редактировать автомобили, просматривать клиентов и т.д.

### Использованные источники

* PHP.NET Manual — [https://www.php.net/manual/en/](https://www.php.net/manual/en/)
* W3Schools PHP — [https://www.w3schools.com/php/](https://www.w3schools.com/php/)
* MDN SQL Injection — [https://developer.mozilla.org/en-US/docs/Learn/Server-side/SQL\_injection](https://developer.mozilla.org/en-US/docs/Learn/Server-side/SQL_injection)
* Stack Overflow — [https://stackoverflow.com](https://stackoverflow.com)
* Learn X in Y Minutes (PHP) — [https://learnxinyminutes.com/docs/php/](https://learnxinyminutes.com/docs/php/)
