<?php
function ensureUniquePriority($pdo, $priority, $applicant_id) {
    // Блокируем таблицу Applicant для чтения существующих приоритетов
    $stmt = $pdo->query("SELECT priority_applicant FROM Applicant WHERE priority_applicant IS NOT NULL FOR UPDATE");
    $existingPriorities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingPriorities[] = $row['priority_applicant'];
    }

    // Добавляем уникальный модификатор, если приоритет уже существует
    while (in_array($priority, $existingPriorities)) {
        $priority += 1; // Увеличиваем приоритет на 1, чтобы избежать конфликтов
    }

    return $priority;
}

session_start();
header('Content-Type: application/json');

// Проверяем, что пользователь авторизован
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Сессия истекла']);
    exit;
}

// Получаем данные из запроса
$input = json_decode(file_get_contents('php://input'), true);
$application_id = $input['application_id'] ?? null;
$new_status = $input['status'] ?? null;

// Проверяем, что все необходимые данные переданы
if (!$application_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Недостаточно данных']);
    exit;
}

require_once 'includes/db.php';

try {
    // Начинаем транзакцию
    $pdo->beginTransaction();

    // Проверяем, что заявка принадлежит текущему сотруднику
    $checkStmt = $pdo->prepare("
        SELECT 
            a.employee_id, 
            a.type_application, 
            c.average_grade AS school_subjects_score, 
            c.average_exam_grade AS exam_subjects_score, 
            ap.applicant_id
        FROM Application a
        JOIN Applicant ap ON a.applicant_id = ap.applicant_id
        LEFT JOIN Certificate c ON ap.certificate_id = c.certificate_id
        WHERE a.application_id = ?
        FOR UPDATE
    ");
    $checkStmt->execute([$application_id]);
    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['employee_id'] == $_SESSION['user_id']) {
        $type_application = $result['type_application'];
        $applicant_id = $result['applicant_id'];

        // Проверяем допустимость статуса для типа заявки
        if (
            ($type_application === 'PAID WITHOUT TESTS' || $type_application === 'BUDGET WITHOUT TESTS' || strpos($type_application, 'ENROLLMENT') !== false) &&
            $new_status === 'SENT TO EXAMS'
        ) {
            $pdo->rollBack(); // Откатываем транзакцию
            echo json_encode(['success' => false, 'message' => 'Недопустимый статус для данного типа заявки']);
            exit;
        }

        // Если статус ACCEPTED и тип заявки не содержит ENROLLMENT
        if ($new_status === 'ACCEPTED' && strpos($type_application, 'ENROLLMENT') === false) {
            // Рассчитываем приоритет абитуриента
            $school_subjects_score = $result['school_subjects_score'] ?? 0;
            $exam_subjects_score = $result['exam_subjects_score'] ?? 0;
            $priority = $school_subjects_score + $exam_subjects_score;

            // Проверяем уникальность приоритета
            $uniquePriority = ensureUniquePriority($pdo, $priority, $applicant_id);

            // Обновляем поле priority_applicant в таблице Applicant
            $updatePriorityStmt = $pdo->prepare("
                UPDATE Applicant 
                SET priority_applicant = ? 
                WHERE applicant_id = ?
            ");
            $updatePriorityStmt->execute([$uniquePriority, $applicant_id]);
        }

        // Обновляем статус заявки
        $stmt = $pdo->prepare("UPDATE Application SET status_application = ? WHERE application_id = ?");
        $stmt->execute([$new_status, $application_id]);

        // Фиксируем транзакцию
        $pdo->commit();

        echo json_encode(['success' => true]);
    } else {
        $pdo->rollBack(); // Откатываем транзакцию
        echo json_encode(['success' => false, 'message' => 'Заявка не найдена или недоступна для редактирования']);
    }
} catch (PDOException $e) {
    $pdo->rollBack(); // Откатываем транзакцию в случае ошибки
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>