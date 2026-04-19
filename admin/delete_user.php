<?php
require_once '../functions.php';

if (!is_admin_logged_in()) {
    die("Access denied.");
}

if (!isset($_POST['user_id'])) {
    die("Invalid request.");
}

$user_id = (int) $_POST['user_id'];

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// 🔥 IMPORTANT: redirect back to THIS page
header("Location: /conference_app/admin/view_registration.php?deleted=1");
exit;
