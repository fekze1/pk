<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла']);
    exit;
}

$employee_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$application_id = $input['application_id'] ?? null;

if (!$application_id) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
    exit;
}

require_once 'includes/db.php';

try {
    // Начинаем блокировку таблицы
    $pdo->exec("LOCK TABLES Application WRITE");

    // Начинаем транзакцию
    $pdo->beginTransaction();

    // Выполняем SELECT с FOR UPDATE для блокировки строки
    $stmt = $pdo->prepare("
        SELECT * 
        FROM Application 
        WHERE application_id = ? 
        FOR UPDATE
    ");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        $pdo->rollBack();
        $pdo->exec("UNLOCK TABLES");
        echo json_encode(['success' => false, 'message' => 'Заявка не найдена']);
        exit;
    }

    // Проверяем, не присвоена ли заявка другому сотруднику
    if ($application['employee_id'] !== null) {
        $pdo->rollBack();
        $pdo->exec("UNLOCK TABLES");
        echo json_encode(['success' => false, 'message' => 'Заявка уже взята на рассмотрение другим сотрудником']);
        exit;
    }

    // Имитация задержки для проверки блокировки
    // sleep(10);

    // Обновляем заявку: присваиваем её текущему сотруднику и меняем статус
    $updateStmt = $pdo->prepare("
        UPDATE Application 
        SET employee_id = ?, status_application = 'UNDER CONSIDERATION' 
        WHERE application_id = ?
    ");
    $updateStmt->execute([$employee_id, $application_id]);

    // Фиксируем транзакцию
    $pdo->commit();

    // Снимаем блокировку таблицы
    $pdo->exec("UNLOCK TABLES");

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Откатываем транзакцию в случае ошибки
    $pdo->rollBack();
    $pdo->exec("UNLOCK TABLES");
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>