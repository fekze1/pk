<?php

// Подключение к базе данных
require_once 'includes/db.php';
// Заголовки для JSON-ответа
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL-запрос для получения данных о факультетах
    $query = "SELECT faculty_id, name_faculty, avggrade_for_budget, avggrade_for_paid, 
                     examgrade_for_budget, examgrade_for_paid 
              FROM Faculty";
    $stmt = $pdo->query($query);
    $faculties = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Возврат данных в формате JSON
    echo json_encode([
        'status' => 'success',
        'data' => $faculties
    ]);

} catch (PDOException $e) {
    // Обработка ошибок и возврат JSON с ошибкой
    echo json_encode([
        'status' => 'error',
        'message' => 'Ошибка подключения к базе данных: ' . $e->getMessage()
    ]);
}


?>