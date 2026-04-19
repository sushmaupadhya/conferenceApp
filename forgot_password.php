<?php
require_once 'functions.php';
require_once 'config.php';   // for $BASE_URL

$page_css = '<link rel="stylesheet" href="assets/css/forgot-password.css">';

$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $error = "Please enter your registered email address.";
    } else {
        // Look up user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate 32-char token
            $token   = bin2hex(random_bytes(16)); // 32 hex characters
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Store in users table
            $upd = $pdo->prepare("
                UPDATE users
                SET reset_token = ?, reset_token_expires = ?
                WHERE id = ?
            ");
            $upd->execute([$token, $expires, $user['id']]);

            // Build link using same base URL you use for the site
            // e.g. $BASE_URL = 'https://icesr2026.com/conference_app';
            $base = rtrim($BASE_URL, '/');
            $link = $base . '/reset_password.php?token=' . urlencode($token);

            // Email body
            $body  = "<p>Dear User,</p>";
            $body .= "<p>You requested to reset your password.</p>";
            $body .= "<p>Please click the following link (valid for 1 hour):</p>";
            $body .= "<p><a href='{$link}'>{$link}</a></p>";
            $body .= "<p>If you did not request this, you can ignore this message.</p>";

            // Send email
            send_mail_simple($email, "ICESR 2026 – Password Reset", $body);

            // DEBUG: also log this on the server so we can cross-check
            file_put_contents(
                __DIR__ . '/reset_debug.log',
                date('Y-m-d H:i:s') . " NEW TOKEN for {$email}: {$token} (expires {$expires})\n",
                FILE_APPEND
            );

            // For security, generic message
            $msg = "If this email is registered, a password reset link has been sent.";
        } else {
            // Generic message – do not reveal if email exists
            $msg = "If this email is registered, a password reset link has been sent.";
        }
    }
}

$page_title = 'Forgot Password';
// If you want a separate CSS, set $page_css here
$page_css = ''; 

include "includes/header.php";
?>

<div class="reset-container">
    <h2 class="reset-title">Forgot Password</h2>

    <?php if (!empty($error)): ?>
        <p class="reset-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if (!empty($msg)): ?>
        <p class="reset-msg"><?php echo htmlspecialchars($msg); ?></p>
    <?php endif; ?>

    <form method="post" action="forgot_password.php" class="reset-form">
        <div class="form-group">
            <label>Email (registered)*</label>
            <input type="email" name="email" required>
        </div>

        <button type="submit" class="btn-primary">Send Reset Link</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
