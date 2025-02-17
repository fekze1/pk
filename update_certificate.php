<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$certificate = $input['certificate'] ?? '';

if (!$certificate) {
    echo json_encode(['success' => false, 'message' => 'Поле аттестата не заполнено']);
    exit;
}

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("UPDATE Applicant SET certificate_id = ? WHERE applicant_id = ?");
    $stmt->execute([$certificate, $_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>