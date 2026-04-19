<?php
require_once '../functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_abstracts.php");
    exit;
}

$id     = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? 'Pending';

// IMPORTANT: same spelling / case as used in manage_abstracts.php
$allowed = ['Pending', 'Accepted', 'Rejected'];

if (!in_array($status, $allowed, true)) {
    die("Invalid status");
}

$stmt = $pdo->prepare("SELECT a.id, a.user_id, u.email, u.name 
                       FROM abstracts a 
                       JOIN users u ON a.user_id = u.id
                       WHERE a.id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    die("Abstract not found");
}

// Decide what comment to store
$admin_comment = null;
if ($status === 'rejected' && $reason !== '') {
    $admin_comment = $reason;
}

$upd = $pdo->prepare("UPDATE abstracts SET status = ?, updated_at = NOW() WHERE id = ?");
$upd->execute([$status, $id]);

$email   = $row['email'];
$name    = $row['name'];
$subject = "Abstract Review Result";

$niceStatus = ucfirst($status);   // "Accepted" or "Rejected"

$message  = "<p>Dear " . htmlspecialchars($name) . ",</p>";

if ($status === 'Accepted') {

    $message .= "
    <p>Your abstract status has been updated to: <b>{$niceStatus}</b>.</p>
    <p>We are pleased to inform you that your abstract has been accepted for presentation
    at the conference. Further details regarding the programme and instructions will be
    communicated to you in due course.</p>
    ";

} elseif ($status === 'Rejected') {

    $message .= "
    <p>Your abstract status has been updated to: <b>{$status}</b>.</p>
    ";

    if (!empty($admin_comment)) {
        $message .= "
        <p><strong>Reason for rejection:</strong><br>"
        . nl2br(htmlspecialchars($admin_comment)) . "</p>";
    } else {
        $message .= "
        <p>We regret to inform you that your abstract could not be accepted for this
        conference.</p>";
    }
} else {
    // fallback for 'pending' or other statuses (optional)
    $message .= "
    <p>Your abstract status has been updated to: <b>{$status}</b>.</p>";
}

$message .= "<p>Best regards,<br>Conference Committee</p>";


send_mail_simple($email, $subject, $message);

header("Location: manage_abstracts.php");
exit;
