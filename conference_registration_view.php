<?php
require_once 'functions.php';
require_login();

// Get this user's conference registration
$stmt = $pdo->prepare("
    SELECT *
    FROM conference_registration
    WHERE user_id = ?
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$conf = $stmt->fetch(PDO::FETCH_ASSOC);

// If nothing submitted yet, send them to the form
if (!$conf) {
    header('Location: conference_registration.php');
    exit;
}

$page_title = 'Conference Registration – View';
$page_css   = '<link rel="stylesheet" href="assets/css/conference_form.css">';
include "includes/header.php";
?>

<div class="registration-wrapper">

    <h2>Conference registration – Submitted details</h2>
    <p><a href="user_dashboard.php" class="back-link">&larr; Back to Dashboard</a></p>

    <p class="registration-intro">
        Below are the details you submitted. This page is <strong>read-only</strong>.
        If you need to change anything, please contact the conference organisers.
    </p>

    <div class="form-card registration-form">
        <div class="detail-grid">
            <div>
                <span class="label">Need accommodation?</span>
                <span class="value"><?php echo $conf['need_accommodation'] ? 'Yes' : 'No'; ?></span>
            </div>

            <div>
                <span class="label">Accompanying person?</span>
                <span class="value"><?php echo $conf['accompanying_person'] ? 'Yes' : 'No'; ?></span>
            </div>

            <div>
                <span class="label">Span of stay</span>
                <span class="value"><?php echo htmlspecialchars($conf['span_of_stay']); ?></span>
            </div>

            <div>
                <span class="label">Dietary restrictions</span>
                <span class="value">
                    <?php echo $conf['dietary_restrictions']
                        ? htmlspecialchars($conf['dietary_restrictions'])
                        : 'None specified'; ?>
                </span>
            </div>

            <div>
                <span class="label">Payment acknowledgement</span>
                <span class="value">
                    <?php if (!empty($conf['payment_ack_file'])): ?>
                        <a href="<?php echo htmlspecialchars($conf['payment_ack_file']); ?>"
                           class="view-link" target="_blank">
                            View / Download
                        </a>
                    <?php else: ?>
                        Not uploaded
                    <?php endif; ?>
                </span>
            </div>

            <div>
                <span class="label">Mysore trip</span>
                <span class="value"><?php echo $conf['mysore_trip'] ? 'Yes' : 'No'; ?></span>
            </div>

            <div>
                <span class="label">Passport country</span>
                <span class="value"><?php echo htmlspecialchars($conf['passport_country']); ?></span>
            </div>

            <div>
                <span class="label">Submitted at</span>
                <span class="value"><?php echo htmlspecialchars($conf['created_at']); ?></span>
            </div>
        </div>
    </div>

</div>

<?php include "includes/footer.php"; ?>
