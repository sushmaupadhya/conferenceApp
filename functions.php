<?php
// functions.php
require_once __DIR__ . '/config.php';
date_default_timezone_set('Asia/Kolkata');
$pdo->exec("SET time_zone = '+05:30'");

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function require_admin() {
    if (!is_admin_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function generate_random_password($length = 10) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function clean_filename($name) {
    return preg_replace('/[^A-Za-z0-9_\.-]/', '_', $name);
}


// Simple mail wrapper (update From address in production)
function send_mail_simple($to, $subject, $message) {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Conference <no-reply@icesr2026.com>\r\n";

    return mail($to, $subject, $message, $headers);
}
