<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$passport = $input['passport'] ?? '';

if (!$passport) {
    echo json_encode(['success' => false, 'message' => 'Поле паспорта не заполнено']);
    exit;
}

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("UPDATE Applicant SET passport_id = ? WHERE applicant_id = ?");
    $stmt->execute([$passport, $_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>