<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

require_once 'includes/db.php'; // Подключение к базе данных

try {
    if ($role === 'applicant') {
        $stmt = $pdo->prepare("
            SELECT 
                a.fullname AS fullname, 
                a.email AS email, 
                p.series_and_number AS passport_series_number,
                p.issue_date AS passport_issue_date,
                c.number_certificate AS certificate_number,
                c.issue_date AS certificate_issue_date,
                c.average_grade AS certificate_school_grade,
                c.average_exam_grade AS certificate_exam_grade
            FROM Applicant a
            LEFT JOIN Passport p ON a.passport_id = p.passport_id
            LEFT JOIN Certificate c ON a.certificate_id = c.certificate_id
            WHERE a.applicant_id = ?
        ");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Дополнительный запрос для получения льгот
        $benefits_stmt = $pdo->prepare("
            SELECT name_benefit, number_benefit 
            FROM Benefit 
            WHERE applicant_id = ?
        ");
        $benefits_stmt->execute([$user_id]);
        $benefits = $benefits_stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($user_data) {
            echo json_encode([
                'success' => true,
                'fullname' => $user_data['fullname'],
                'email' => $user_data['email'],
                'passport' => [
                    'series_number' => $user_data['passport_series_number'] ?? null,
                    'issue_date' => $user_data['passport_issue_date'] ?? null
                ],
                'certificate' => [
                    'number' => $user_data['certificate_number'] ?? null,
                    'issue_date' => $user_data['certificate_issue_date'] ?? null,
                    'school_grade' => $user_data['certificate_school_grade'] ?? null,
                    'exam_grade' => $user_data['certificate_exam_grade'] ?? null
                ],
                'benefits' => $benefits, // Список льгот
                'role' => $role
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Данные пользователя не найдены']);
        }
    } elseif ($role === 'employee') {
        // Запрос для сотрудника
        $stmt = $pdo->prepare("SELECT fullname, email FROM Employee WHERE employee_id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            // Запрос для получения факультетов, на которых работает сотрудник
            $faculty_stmt = $pdo->prepare("
                SELECT f.name_faculty 
                FROM Faculty_Employee fe
                JOIN Faculty f ON fe.faculty_id = f.faculty_id
                WHERE fe.employee_id = ?
            ");
            $faculty_stmt->execute([$user_id]);
            $faculties = $faculty_stmt->fetchAll(PDO::FETCH_COLUMN); // Получаем только названия факультетов

            echo json_encode([
                'success' => true,
                'fullname' => $user_data['fullname'],
                'email' => $user_data['email'],
                'faculties' => $faculties, // Добавляем список факультетов
                'role' => $role
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Данные пользователя не найдены']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>