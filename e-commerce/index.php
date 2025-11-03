<?php
// Entry point and basic router
session_start();

require_once __DIR__ . '/config/db.php'; // $pdo may be null if not configured

include __DIR__ . '/includes/header.php';

$page = $_GET['page'] ?? 'home';
$allowed = ['home','product','cart','checkout','login','register'];
if (!in_array($page, $allowed, true)) {
    $page = 'home';
}

echo "<main class=\"container\">";
include __DIR__ . "/pages/{$page}.php";
echo "</main>";

include __DIR__ . '/includes/footer.php';