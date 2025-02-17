<?php
session_start();
// Настройка вывода ошибок
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log'); // Путь к файлу логов
error_reporting(E_ALL);

try {
    // Логирование входящего запроса
    $inputRaw = file_get_contents('php://input');
    $formattedInput = isJson($inputRaw) ? json_encode(json_decode($inputRaw), JSON_UNESCAPED_UNICODE) : $inputRaw;
    error_log("[" . date('Y-m-d H:i:s') . "] Запрос к auth.php: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
    error_log("[" . date('Y-m-d H:i:s') . "] Тело запроса:\n" . $formattedInput);

    $input = json_decode($inputRaw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Некорректный формат JSON");
    }

    require_once 'includes/db.php';
    $login = $input['login'] ?? '';
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? '';
    if (!$login || !$password || !$role) {
        $response = ['success' => false, 'message' => 'Необходимо заполнить все поля'];
        error_log("[" . date('Y-m-d H:i:s') . "] Ответ от auth.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
        echo json_encode($response);
        exit;
    }

    if ($role === 'applicant') {
        $stmt = $pdo->prepare("SELECT applicant_id, login_applicant, password_applicant FROM Applicant WHERE login_applicant = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_applicant'])) {
            $_SESSION['user_id'] = $user['applicant_id'];
            $_SESSION['role'] = 'applicant';
            $response = ['success' => true];
            error_log("[" . date('Y-m-d H:i:s') . "] Ответ от auth.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
            echo json_encode($response);
            exit;
        }
    } elseif ($role === 'employee') {
        $stmt = $pdo->prepare("SELECT employee_id, login_employee, password_employee FROM Employee WHERE login_employee = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && $user['password_employee'] === $password) {
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['role'] = 'employee';
            $response = ['success' => true];
            error_log("[" . date('Y-m-d H:i:s') . "] Ответ от auth.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
            echo json_encode($response);
            exit;
        }
    }

    $response = ['success' => false, 'message' => 'Неверный логин или пароль'];
    error_log("[" . date('Y-m-d H:i:s') . "] Ответ от auth.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
    echo json_encode($response);
} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Ошибка в auth.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Произошла ошибка']);
}

// Функция для проверки, является ли строка валидным JSON
function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}
?>