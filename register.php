<?php
require_once 'includes/db.php';
// Настройка вывода ошибок
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Логирование входящего запроса
    $inputRaw = file_get_contents('php://input');
    $formattedInput = json_encode(json_decode($inputRaw), JSON_UNESCAPED_UNICODE) ?: $inputRaw;
    error_log("[" . date('Y-m-d H:i:s') . "] Запрос к register.php: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
    error_log("[" . date('Y-m-d H:i:s') . "] Тело запроса:\n" . $formattedInput);

    $data = json_decode($inputRaw, true);
    if (!isset($data['login'], $data['password'], $data['email'], $data['fullname'])) {
        $response = ['success' => false, 'message' => 'Все поля обязательны'];
        error_log("[" . date('Y-m-d H:i:s') . "] Ответ от register.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
        echo json_encode($response);
        exit;
    }

    $login_applicant = trim($data['login']);
    $password_applicant = $data['password'];
    $email = trim($data['email']);
    $fullname = trim($data['fullname']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['success' => false, 'message' => 'Неверный формат email'];
        error_log("[" . date('Y-m-d H:i:s') . "] Ответ от register.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
        echo json_encode($response);
        exit;
    }

    if (strlen($password_applicant) < 6) {
        $response = ['success' => false, 'message' => 'Пароль должен содержать минимум 6 символов'];
        error_log("[" . date('Y-m-d H:i:s') . "] Ответ от register.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
        echo json_encode($response);
        exit;
    }

    $password_applicant = password_hash($password_applicant, PASSWORD_BCRYPT);

    try {
        // Блокируем таблицу applicant для записи
        $pdo->exec("LOCK TABLES applicant WRITE");
        error_log("[" . date('Y-m-d H:i:s') . "] Таблица applicant заблокирована для записи");

        // // Добавляем задержку для демонстрации блокировки
        // error_log("[" . date('Y-m-d H:i:s') . "] Добавлена задержка 10 секунд для демонстрации блокировки");
        // sleep(10); // Задержка в 10 секунд

        // Проверяем, существует ли пользователь с таким логином
        $checkLoginStmt = $pdo->prepare("SELECT COUNT(*) FROM applicant WHERE login_applicant = ?");
        $checkLoginStmt->execute([$login_applicant]);
        if ($checkLoginStmt->fetchColumn() > 0) {
            throw new Exception("Логин уже используется");
        }

        // Проверяем, существует ли пользователь с таким email
        $checkEmailStmt = $pdo->prepare("SELECT COUNT(*) FROM applicant WHERE email = ?");
        $checkEmailStmt->execute([$email]);
        if ($checkEmailStmt->fetchColumn() > 0) {
            throw new Exception("Email уже используется");
        }

        // Добавляем нового пользователя
        $sql = "INSERT INTO applicant (login_applicant, password_applicant, email, fullname) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login_applicant, $password_applicant, $email, $fullname]);

        // Снимаем блокировку
        $pdo->exec("UNLOCK TABLES");
        error_log("[" . date('Y-m-d H:i:s') . "] Таблица applicant разблокирована");

        $response = ['success' => true];
        error_log("[" . date('Y-m-d H:i:s') . "] Ответ от register.php:\n" . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n");
        echo json_encode($response);
    } catch (Exception $e) {
        // Логируем ошибку
        error_log("[" . date('Y-m-d H:i:s') . "] Ошибка в register.php: " . $e->getMessage());

        // Снимаем блокировку в случае ошибки
        try {
            $pdo->exec("UNLOCK TABLES");
            error_log("[" . date('Y-m-d H:i:s') . "] Таблица applicant разблокирована после ошибки");
        } catch (PDOException $unlockError) {
            error_log("[" . date('Y-m-d H:i:s') . "] Ошибка при разблокировке таблиц: " . $unlockError->getMessage());
        }

        // Возвращаем сообщение об ошибке клиенту
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
}
?>