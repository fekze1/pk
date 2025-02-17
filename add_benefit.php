<?php
session_start();
// Настройка вывода ошибок
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log'); // Путь к файлу логов
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    // Проверяем авторизацию
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'applicant') {
        echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name_benefit'] ?? '';
    $number = $input['number_benefit'] ?? '';

    if (!$name || !$number) {
        echo json_encode(['success' => false, 'message' => 'Все поля обязательны']);
        exit;
    }

    require_once 'includes/db.php';

    // Проверяем количество льгот
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM Benefit WHERE applicant_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    if ($count >= 10) {
        echo json_encode(['success' => false, 'message' => 'Вы можете добавить не более 10 льгот']);
        exit;
    }

    // Блокируем таблицы для записи
    $pdo->exec("LOCK TABLES Benefit WRITE");
    error_log("[" . date('Y-m-d H:i:s') . "] Таблица Benefit заблокирована для записи");

    // Проверяем, существует ли аттестат с таким же number_benefit
    $stmt = $pdo->prepare("SELECT benefit_id FROM Benefit WHERE number_benefit = ?");
    $stmt->execute([$number]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception("Аттестат с номером '$number' уже существует");
    }

    // // Добавляем задержку для демонстрации блокировки
    // error_log("[" . date('Y-m-d H:i:s') . "] Добавлена задержка 10 секунд для демонстрации блокировки");
    // sleep(10); // Задержка в 10 секунд

    // Добавляем льготу
    $stmt = $pdo->prepare("INSERT INTO Benefit (applicant_id, name_benefit, number_benefit) VALUES (?, ?, ?)");
    if (!$stmt->execute([$_SESSION['user_id'], $name, $number])) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении льготы']);
    }

    // Снимаем блокировку
    $pdo->exec("UNLOCK TABLES");
    error_log("[" . date('Y-m-d H:i:s') . "] Таблица Benefit разблокирована");

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Логируем ошибку
    error_log("[" . date('Y-m-d H:i:s') . "] Ошибка в add_benefit.php: " . $e->getMessage());

    // Снимаем блокировку в случае ошибки
    try {
        $pdo->exec("UNLOCK TABLES");
        error_log("[" . date('Y-m-d H:i:s') . "] Таблицы разблокированы после ошибки");
    } catch (PDOException $unlockError) {
        error_log("[" . date('Y-m-d H:i:s') . "] Ошибка при разблокировке таблиц: " . $unlockError->getMessage());
    }
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка: ' . $e->getMessage()]);
}
?>