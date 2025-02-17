<?php
session_start();

$response = ['is_authenticated' => false, 'fullname' => '', 'role' => ''];

if (isset($_SESSION['user_id']) || (isset($_COOKIE['user_id']) && isset($_COOKIE['role']))) {
    $response['is_authenticated'] = true;
    $response['fullname'] = $_SESSION['fullname'] ?? 'Пользователь';
    $response['role'] = $_SESSION['role'] ?? ($_COOKIE['role'] ?? '');
}

header('Content-Type: application/json');
echo json_encode($response);
?>