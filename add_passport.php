<?php
session_start();
// Настройка вывода ошибок
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log'); // Путь к файлу логов
error_reporting(E_ALL);
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$fullname = $input['fullname'] ?? '';
$issue_date = $input['issue_date'] ?? '';
$series_number = $input['series_number'] ?? '';

if (!$fullname || !$issue_date || !$series_number) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны']);
    exit;
}

require_once 'includes/db.php';

try {
    // Блокируем таблицы для записи
    $pdo->exec("LOCK TABLES Passport WRITE, Applicant WRITE");
    error_log("[" . date('Y-m-d H:i:s') . "] Таблицы Passport и Applicant заблокированы для записи");

    // Проверяем, существует ли паспорт с таким же series_and_number
    $stmt = $pdo->prepare("SELECT passport_id FROM Passport WHERE series_and_number = ?");
    $stmt->execute([$series_number]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception("Паспорт с серия/номером '$series_number' уже существует");
    }

    // // Добавляем задержку для демонстрации блокировки
    // error_log("[" . date('Y-m-d H:i:s') . "] Добавлена задержка 10 секунд для демонстрации блокировки");
    // sleep(10); // Задержка в 10 секунд

    // Добавляем паспорт
    $stmt = $pdo->prepare("INSERT INTO Passport (fullname, issue_date, series_and_number) VALUES (?, ?, ?)");
    if (!$stmt->execute([$fullname, $issue_date, $series_number])) {
        throw new Exception("Не удалось добавить паспорт");
    }
    $passport_id = $pdo->lastInsertId();

    // Привязываем паспорт к пользователю
    $stmt = $pdo->prepare("UPDATE Applicant SET passport_id = ? WHERE applicant_id = ?");
    if (!$stmt->execute([$passport_id, $_SESSION['user_id']])) {
        throw new Exception("Не удалось обновить данные пользователя");
    }

    // // Проверяем, что обновление прошло успешно
    // if ($stmt->rowCount() === 0) {
    //     throw new Exception("Не удалось обновить данные пользователя");
    // }

    // Снимаем блокировку
    $pdo->exec("UNLOCK TABLES");
    error_log("[" . date('Y-m-d H:i:s') . "] Таблицы Passport и Applicant разблокированы");

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Логируем ошибку
    error_log("[" . date('Y-m-d H:i:s') . "] Ошибка в add_passport.php: " . $e->getMessage());

    // Снимаем блокировку в случае ошибки
    try {
        $pdo->exec("UNLOCK TABLES");
        error_log("[" . date('Y-m-d H:i:s') . "] Таблицы разблокированы после ошибки");
    } catch (PDOException $unlockError) {
        error_log("[" . date('Y-m-d H:i:s') . "] Ошибка при разблокировке таблиц: " . $unlockError->getMessage());
    }

    // Возвращаем сообщение об ошибке клиенту
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>