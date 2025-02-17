<?php
session_start();

// Удаляем сессию
session_unset();
session_destroy();

// Удаляем куки
setcookie('user_id', '', time() - 3600, "/");
setcookie('role', '', time() - 3600, "/");

header('Location: /PK/assets/html/index.html');
exit;
?>