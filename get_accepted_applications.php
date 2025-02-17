<?php
session_start();
header('Content-Type: application/json');

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла']);
    exit;
}

$user_id = $_SESSION['user_id'];

require_once 'includes/db.php';

try {
    // SQL-запрос с фильтрацией по статусу и типам заявок
    $stmt = $pdo->prepare("
        SELECT type_application, faculty_id
        FROM Application 
        WHERE applicant_id = ? 
          AND status_application = 'ACCEPTED'
          AND type_application IN ('PAID WITHOUT TESTS', 'PAID WITH TESTS', 'BUDGET WITH TESTS', 'BUDGET WITHOUT TESTS')
    ");
    $stmt->execute([$user_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'applications' => $applications
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>