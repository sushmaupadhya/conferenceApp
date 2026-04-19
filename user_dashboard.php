<?php
require_once 'functions.php';
require_login();

// Fetch abstract info
$stmt = $pdo->prepare("SELECT * FROM abstracts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$abstract = $stmt->fetch();

// Fetch conference registration if exists
$stmt2 = $pdo->prepare("SELECT * FROM conference_registration WHERE user_id = ?");
$stmt2->execute([$_SESSION['user_id']]);
$conf = $stmt2->fetch();

// Fetch visa / Govt info if exists
$stmt3 = $pdo->prepare("SELECT * FROM visa_info WHERE user_id = ?");
$stmt3->execute([$_SESSION['user_id']]);
$visa = $stmt3->fetch();

$page_title = "User Dashboard";
include "includes/header.php";
?>

<link rel="stylesheet" href="assets/css/user_dashboard.css">

<div class="ud-container">

    <div class="ud-header">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
        <a href="logout.php" class="ud-logout-btn">Logout</a>
    </div>

    <!-- ==========================
         Conference Registration
    =========================== -->
    <div class="ud-card">
        <h3 class="ud-card-title">Conference Registration</h3>
        <p class="ud-card-desc">
            Please fill your accommodation details, span of stay, payment acknowledgement
            and Mysore trip preference.
        </p>

        <?php if (!$conf): ?>
            <a href="conference_registration.php" class="ud-btn">Fill Conference Registration</a>
        <?php else: ?>
            <p class="ud-status ud-success">Conference registration submitted.</p>
            <a href="conference_registration_view.php" class="ud-btn-secondary">
                View submitted details
            </a>
        <?php endif; ?>
    </div>

    <!-- ==========================
         Abstract Submission
    =========================== -->
    <div class="ud-card">
        <h3 class="ud-card-title">Abstract Submission</h3>

        <?php if (!$abstract): ?>
            <p>You have not submitted an abstract yet.</p>
            <a href="abstract_form.php" class="ud-btn">Submit Abstract</a>
        <?php else: ?>
            <p>Abstract status:
                <b><?php echo htmlspecialchars($abstract['status']); ?></b>
            </p>

            <?php if (!empty($abstract['admin_comment'])): ?>
                <p class="ud-remarks">
                    Remarks:<br>
                    <?php echo nl2br(htmlspecialchars($abstract['admin_comment'])); ?>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- ==========================
         Visa / Govt Approval Info
    =========================== -->
    <div class="ud-card">
        <h3 class="ud-card-title">Visa / Government of India Approval Information</h3>
        <p class="ud-card-desc">
            The information in this form is needed for Visa and Government of India approvals
            for the conference. When you submit this form, it will not automatically collect
            your details like name and email address unless you provide it yourself.
        </p>

        <?php if (!$visa): ?>
            <p>You have not submitted your visa / approval information yet.</p>
            <a href="visa_info_form.php" class="ud-btn">Fill Visa Information Form</a>
        <?php else: ?>
            <p class="ud-status ud-success">Visa / approval information submitted.</p>
        <?php endif; ?>
    </div>

</div>

<?php include "includes/footer.php"; ?>
