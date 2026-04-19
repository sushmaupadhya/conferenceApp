<?php
require_once 'config.php';

$page_title = 'Login';
$page_css   = '<link rel="stylesheet" href="/conference_app/assets/css/login.css">';

include "includes/header.php";
?>


<div class="login-container">

    <h2 class="login-title">Login</h2>

    <?php if (!empty($_GET['msg'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <form action="login_process.php" method="post" class="login-form">

        <div class="form-group">
            <label>Email*</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Password*</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn-primary">Login</button>
    </form>

    <p class="forgot-link">
        <a href="forgot_password.php">Forgot password?</a>
    </p>

</div>

<?php include "includes/footer.php"; ?>
