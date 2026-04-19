<?php
require_once 'functions.php';

$token = $_GET['token'] ?? '';
$error = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($token === '') {
        $error = "Invalid or expired token. Please use the link from your email.";
    } elseif ($pass1 !== '' && $pass1 === $pass2) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users 
                                  SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL 
                                  WHERE id = ?");
            $upd->execute([$hash, $user['id']]);

            header("Location: login.php?msg=" . urlencode("Password reset successfully"));
            exit;
        } else {
            $error = "Invalid or expired token.";
        }
    } else {
        $error = "Passwords do not match.";
    }
}

// Page info + per-page CSS
$page_title = 'Reset Password';
$page_css   = '<link rel="stylesheet" href="/conference_app/assets/css/reset-password.css">';

include "includes/header.php";
?>

<div class="reset-container">
    <h2 class="reset-title">Reset Password</h2>

    <?php if (!empty($error)): ?>
        <p class="reset-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" action="reset_password.php" class="reset-form">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div class="form-group">
            <label>New Password*</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirm Password*</label>
            <input type="password" name="password2" required>
        </div>

        <button type="submit" class="btn-primary">Update Password</button>
    </form>
</div>

<?php include "includes/footer.php"; ?>
