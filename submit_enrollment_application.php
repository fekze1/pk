<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$type_application = $input['type_application'] ?? '';
$applicant_id = $_SESSION['user_id'];
$faculty_id = $input['faculty_id'] ?? '';

if (!$type_application || !$faculty_id) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
    exit;
}

require_once 'includes/db.php';

try {
    // Создаем новую заявку на зачисление
    $stmt = $pdo->prepare("INSERT INTO Application (type_application, applicant_id, faculty_id, status_application) VALUES (?, ?, ?, 'ACTIVE')");
    $stmt->execute([$type_application, $applicant_id, $faculty_id]);

    // Закрываем исходную заявку
    $closeStmt = $pdo->prepare("UPDATE Application SET status_application = 'CLOSED' WHERE applicant_id = ? AND faculty_id = ? AND status_application = 'ACCEPTED'");
    $closeStmt->execute([$applicant_id, $faculty_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>