<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo "<p>Email and password are required. <a href='login.php'>Try again</a></p>";
    exit;
}

// DEBUG: show we reached here on icesr
// REMOVE AFTER TESTING
// echo "DEBUG: login_process reached on icesr for email: " . htmlspecialchars($email) . "<br>";

/*
 * 1) TRY ADMIN LOGIN FIRST
 */
$stmt = $pdo->prepare("SELECT id, password_hash FROM admins WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch();

// DEBUG
// if ($admin) { echo "DEBUG: admin row found<br>"; } else { echo "DEBUG: admin row NOT found<br>"; }

if ($admin && password_verify($password, $admin['password_hash'])) {
    $_SESSION['admin_id'] = $admin['id'];
    unset($_SESSION['user_id'], $_SESSION['user_name']);

    global $BASE_URL;
    // DEBUG
    // echo "DEBUG: redirecting to admin dashboard: {$BASE_URL}/admin/dashboard.php"; exit;

    header("Location: {$BASE_URL}/admin/dashboard.php");
    exit;
}

/*
 * 2) IF NOT ADMIN, TRY USER LOGIN
 */
$stmt = $pdo->prepare("SELECT id, password_hash, name FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// DEBUG
// if ($user) { echo "DEBUG: user row found<br>"; } else { echo "DEBUG: user row NOT found<br>"; }

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    unset($_SESSION['admin_id']);

    global $BASE_URL;
    // DEBUG
    // echo "DEBUG: redirecting to user dashboard: {$BASE_URL}/user_dashboard.php"; exit;

    header("Location: {$BASE_URL}/user_dashboard.php");
    exit;
}

echo "<p>Invalid login. <a href='login.php'>Try again</a></p>";
