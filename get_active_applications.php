<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла']);
    exit;
}

$employee_id = $_SESSION['user_id'];

require_once 'includes/db.php';

try {
    // Получаем ID факультетов, где работает сотрудник
    $facultyStmt = $pdo->prepare("
        SELECT faculty_id 
        FROM Faculty_Employee 
        WHERE employee_id = ?
    ");
    $facultyStmt->execute([$employee_id]);
    $facultyIds = $facultyStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($facultyIds)) {
        echo json_encode(['success' => true, 'applications' => []]);
        exit;
    }

    // Формируем плейсхолдеры для IN-запроса
    $placeholders = implode(',', array_fill(0, count($facultyIds), '?'));

    // Получаем активные заявки на этих факультетах
    $query = "
        SELECT 
            a.application_id,
            a.type_application,
            a.status_application,
            a.total_score,
            f.name_faculty,
            ap.fullname AS applicant_fullname,
            c.average_grade AS school_subjects_score,
            c.average_exam_grade AS exam_subjects_score
        FROM Application a
        JOIN Faculty f ON a.faculty_id = f.faculty_id
        JOIN Applicant ap ON a.applicant_id = ap.applicant_id
        LEFT JOIN Certificate c ON ap.certificate_id = c.certificate_id
        WHERE a.faculty_id IN ($placeholders) AND a.status_application = 'ACTIVE'
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($facultyIds);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'applications' => $applications
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>