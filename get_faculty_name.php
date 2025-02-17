<?php
header('Content-Type: application/json');

if (!isset($_GET['faculty_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID факультета не указан']);
    exit;
}

$faculty_id = $_GET['faculty_id'];

require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("SELECT name_faculty FROM Faculty WHERE faculty_id = ?");
    $stmt->execute([$faculty_id]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($faculty) {
        echo json_encode(['success' => true, 'name_faculty' => $faculty['name_faculty']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Факультет не найден']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>