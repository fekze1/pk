<?php
session_start();
// Настройка вывода ошибок
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log'); // Путь к файлу логов
error_reporting(E_ALL);
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$certificate_number = $input['number'] ?? '';
$issue_date = $input['issue_date'] ?? '';
$average_grade = $input['school_grade'] ?? null;
$average_exam_grade = $input['exam_grade'] ?? null;

if (!$certificate_number || !$issue_date || $average_grade === null || $average_exam_grade === null) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны']);
    exit;
}

require_once 'includes/db.php';

try {
    // Блокируем таблицы для записи
    $pdo->exec("LOCK TABLES Certificate WRITE");
    error_log("[" . date('Y-m-d H:i:s') . "] Таблица Certificate заблокирована для записи");

    // Проверяем, существует ли аттестат с таким же number_certificate
    $stmt = $pdo->prepare("SELECT certificate_id FROM Certificate WHERE number_certificate = ?");
    $stmt->execute([$certificate_number]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception("Аттестат с номером '$certificate_number' уже существует");
    }

    // // Добавляем задержку для демонстрации блокировки
    // error_log("[" . date('Y-m-d H:i:s') . "] Добавлена задержка 10 секунд для демонстрации блокировки");
    // sleep(10); // Задержка в 10 секунд

    // Добавляем аттестат
    $stmt = $pdo->prepare("INSERT INTO Certificate (number_certificate, issue_date, average_grade, average_exam_grade) VALUES (?, ?, ?, ?)");
    if (!$stmt->execute([$certificate_number, $issue_date, $average_grade, $average_exam_grade])) {
        throw new Exception("Не удалось добавить аттестат");
    }

    // Снимаем блокировку
    $pdo->exec("UNLOCK TABLES");
    error_log("[" . date('Y-m-d H:i:s') . "] Таблица Certificate разблокирована");

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Логируем ошибку
    error_log("[" . date('Y-m-d H:i:s') . "] Ошибка в add_certificate.php: " . $e->getMessage());

    // Снимаем блокировку в случае ошибки
    try {
        $pdo->exec("UNLOCK TABLES");
        error_log("[" . date('Y-m-d H:i:s') . "] Таблица Certificate разблокирована после ошибки");
    } catch (PDOException $unlockError) {
        error_log("[" . date('Y-m-d H:i:s') . "] Ошибка при разблокировке таблиц: " . $unlockError->getMessage());
    }

    // Возвращаем сообщение об ошибке клиенту
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>