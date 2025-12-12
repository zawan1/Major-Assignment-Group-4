<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
function require_role($role) {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: login.php');
        exit;
    }
}
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Online Appointment Token System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<!-- Navbar removed - each page has its own navbar now -->
<main class="container">
