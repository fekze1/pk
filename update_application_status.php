<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$application_id = $input['application_id'] ?? null;
$new_status = $input['status'] ?? null;

if (!$application_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
    exit;
}

require_once 'includes/db.php';

try {
    // Проверяем, что заявка принадлежит текущему сотруднику
    $checkStmt = $pdo->prepare("
        SELECT employee_id, type_application 
        FROM Application 
        WHERE application_id = ?
    ");
    $checkStmt->execute([$application_id]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['employee_id'] == $_SESSION['user_id']) {
        // Проверяем допустимость статуса для типа заявки
        $type_application = $result['type_application'];
        if (
            ($type_application === 'PAID WITHOUT TESTS' || $type_application === 'BUDGET WITHOUT TESTS' || strpos($type_application, 'ENROLLMENT') !== false) &&
            $new_status === 'SENT TO EXAMS'
        ) {
            echo json_encode(['success' => false, 'message' => 'Недопустимый статус для данного типа заявки']);
            exit;
        }

        // Обновляем статус заявки
        $stmt = $pdo->prepare("UPDATE Application SET status_application = ? WHERE application_id = ?");
        $stmt->execute([$new_status, $application_id]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Заявка не найдена или недоступна для редактирования']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>