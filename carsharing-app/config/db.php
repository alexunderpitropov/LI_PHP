<?php

/**
 * Создает подключение к базе данных с использованием PDO.
 *
 * @return PDO Подключение к базе данных.
 * @throws PDOException В случае ошибки подключения.
 */
function db_connect() {
    $host = 'localhost';
    $dbname = 'php_individual';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}
?>
