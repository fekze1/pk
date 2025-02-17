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
$total_score = $input['total_score'] ?? '';

if (!$type_application || !$faculty_id || !$total_score) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны']);
    exit;
}

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("INSERT INTO Application (type_application, applicant_id, faculty_id, total_score, status_application) VALUES (?, ?, ?, ?, 'ACTIVE')");
    if ($stmt->execute([$type_application, $applicant_id, $faculty_id, $total_score])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении заявки']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>