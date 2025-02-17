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
    // Получаем заявки для сотрудника
    $stmt = $pdo->prepare("
        SELECT 
            a.application_id,
            a.type_application,
            a.status_application,
            a.total_score,
            f.name_faculty,
            ap.fullname AS applicant_fullname,
            p.series_and_number AS passport_series_number,
            p.issue_date AS passport_issue_date,
            c.number_certificate AS certificate_number,
            c.average_grade AS school_subjects_score,
            c.average_exam_grade AS exam_subjects_score,
            GROUP_CONCAT(b.name_benefit SEPARATOR ', ') AS benefits
        FROM Application a
        JOIN Faculty f ON a.faculty_id = f.faculty_id
        JOIN Applicant ap ON a.applicant_id = ap.applicant_id
        LEFT JOIN Passport p ON ap.passport_id = p.passport_id
        LEFT JOIN Certificate c ON ap.certificate_id = c.certificate_id
        LEFT JOIN Benefit b ON ap.applicant_id = b.applicant_id
        WHERE a.employee_id = ? AND a.status_application IN ('UNDER CONSIDERATION', 'SENT TO EXAMS')
        GROUP BY a.application_id
    ");
    $stmt->execute([$employee_id]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($applications) {
        echo json_encode([
            'success' => true,
            'applications' => $applications
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Нет заявок для данного сотрудника',
            'applications' => []
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
}
?>