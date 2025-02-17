<?php
// db.php

// Параметры для подключения к базе данных
$host = 'localhost';    // Адрес сервера базы данных (обычно localhost)
$dbname = 'university'; // Имя базы данных
$username = 'root';     // Имя пользователя для подключения к базе данных
$password = '';         // Пароль для подключения

try {
    // Создаем подключение к базе данных с использованием PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Устанавливаем режим ошибок для PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Устанавливаем кодировку
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    // В случае ошибки подключения выводим сообщение
    echo "Ошибка подключения к базе данных: " . $e->getMessage();
    exit;
}
?>