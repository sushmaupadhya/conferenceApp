<?php
// conference_registration_process.php
// Handles Form 2: Conference registration

require_once 'functions.php';
require_login(); // ensures $_SESSION['user_id'] is set

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: conference_registration.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$errors  = [];

// ----------------------------
// 1. Collect & validate inputs
// ----------------------------

$need_accommodation  = isset($_POST['need_accommodation'])  ? $_POST['need_accommodation']  : null;
$accompanying_person = isset($_POST['accompanying_person']) ? $_POST['accompanying_person'] : null;
$span_of_stay        = trim($_POST['span_of_stay'] ?? '');
$dietary_restrictions = trim($_POST['dietary_restrictions'] ?? '');
$mysore_trip         = isset($_POST['mysore_trip'])         ? $_POST['mysore_trip']         : null;
$passport_country    = $_POST['passport_country'] ?? '';

// Validate radio / select values
if (!in_array($need_accommodation, ['0','1'], true)) {
    $errors[] = "Please specify whether you need accommodation.";
}
if (!in_array($accompanying_person, ['0','1'], true)) {
    $errors[] = "Please specify whether you have an accompanying person.";
}
if ($span_of_stay === '') {
    $errors[] = "Please enter your span of stay.";
}
if (!in_array($mysore_trip, ['0','1'], true)) {
    $errors[] = "Please indicate your preference for the Mysore trip.";
}
if (!in_array($passport_country, ['India','Other'], true)) {
    $errors[] = "Please select a valid passport country.";
}


// ----------------------------
// 2. File upload validation
// ----------------------------

if (!isset($_FILES['payment_ack']) || $_FILES['payment_ack']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = "Please upload the payment acknowledgement.";
} else {
    $file = $_FILES['payment_ack'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error while uploading payment acknowledgement. Please try again.";
    } else {
        $max_size = 10 * 1024 * 1024; // 10 MB
        if ($file['size'] > $max_size) {
            $errors[] = "Payment acknowledgement file is too large (max 10 MB).";
        }

        $allowed_exts = ['pdf','doc','docx','jpg','jpeg','png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_exts, true)) {
            $errors[] = "Invalid file type for payment acknowledgement. Allowed: pdf, doc, docx, jpg, jpeg, png.";
        }
    }
}

// ----------------------------
// 3. If there are errors, show them
// ----------------------------

if (!empty($errors)) {
    $page_title = 'Form 2: Conference Registration - Errors';
    include "includes/header.php";
    ?>
    <div class="registration-wrapper">
        <h2>Form 2: Conference registration</h2>
        <div class="error-box">
            <h4>Please correct the following:</h4>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><a href="conference_registration.php" class="btn-primary">Back to Conference Registration</a></p>
        </div>
    </div>
    <?php
    include "includes/footer.php";
    exit;
}

// ----------------------------
// 4. Move uploaded file
// ----------------------------

$upload_dir = __DIR__ . '/uploads/payment_ack';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0775, true);
}

$ext = strtolower(pathinfo($_FILES['payment_ack']['name'], PATHINFO_EXTENSION));
$safe_base       = 'pay_' . $user_id . '_' . time();
$target_filename = $safe_base . '.' . $ext;
$target_path     = $upload_dir . '/' . $target_filename;

// Path stored in DB (relative to project root)
$db_file_path = 'uploads/payment_ack/' . $target_filename;

if (!move_uploaded_file($_FILES['payment_ack']['tmp_name'], $target_path)) {
    $page_title = 'Form 2: Conference Registration - Errors';
    include "includes/header.php";
    ?>
    <div class="registration-wrapper">
        <h2>Form 2: Conference registration</h2>
        <div class="error-box">
            <p>Failed to save the uploaded file. Please try again.</p>
            <p><a href="conference_registration.php" class="btn-primary">Back to Conference Registration</a></p>
        </div>
    </div>
    <?php
    include "includes_footer.php";
    exit;
}

// ----------------------------
// 5. Insert / update DB row
// ----------------------------

try {
    global $pdo; // from functions.php

    // Check if record already exists for this user
    $checkStmt = $pdo->prepare("
        SELECT id, payment_ack_file
        FROM conference_registration
        WHERE user_id = ?
    ");
    $checkStmt->execute([$user_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Delete old file if present
        if (!empty($existing['payment_ack_file'])) {
            $old_path = __DIR__ . '/' . $existing['payment_ack_file'];
            if (is_file($old_path)) {
                @unlink($old_path);
            }
        }

        // Update existing row
        $updateStmt = $pdo->prepare("
            UPDATE conference_registration
            SET need_accommodation  = :need_accommodation,
                accompanying_person = :accompanying_person,
                span_of_stay        = :span_of_stay,
                dietary_restrictions= :dietary_restrictions,
                payment_ack_file    = :payment_ack_file,
                mysore_trip         = :mysore_trip,
                passport_country    = :passport_country,
            WHERE user_id = :user_id
        ");

        $updateStmt->execute([
            ':need_accommodation'   => (int)$need_accommodation,
            ':accompanying_person'  => (int)$accompanying_person,
            ':span_of_stay'         => $span_of_stay,
            ':dietary_restrictions' => $dietary_restrictions ?: null,
            ':payment_ack_file'     => $db_file_path,
            ':mysore_trip'          => (int)$mysore_trip,
            ':passport_country'     => $passport_country,
            ':user_id'              => $user_id,
        ]);

    } else {
        // Insert new row
        $insertStmt = $pdo->prepare("
            INSERT INTO conference_registration
                (user_id, need_accommodation, accompanying_person, span_of_stay,
                 dietary_restrictions, payment_ack_file, mysore_trip,
                 passport_country)
            VALUES
                (:user_id, :need_accommodation, :accompanying_person, :span_of_stay,
                 :dietary_restrictions, :payment_ack_file, :mysore_trip,
                 :passport_country)
        ");

        $insertStmt->execute([
            ':user_id'              => $user_id,
            ':need_accommodation'   => (int)$need_accommodation,
            ':accompanying_person'  => (int)$accompanying_person,
            ':span_of_stay'         => $span_of_stay,
            ':dietary_restrictions' => $dietary_restrictions ?: null,
            ':payment_ack_file'     => $db_file_path,
            ':mysore_trip'          => (int)$mysore_trip,
            ':passport_country'     => $passport_country,
        ]);
    }

} catch (Exception $e) {
    // On DB error, show message
    $page_title = 'Form 2: Conference Registration - Errors';
    include "includes/header.php";
    ?>
    <div class="registration-wrapper">
        <h2>Form 2: Conference registration</h2>
        <div class="error-box">
            <p>There was a problem saving your details. Please try again.</p>
            <p class="small-text"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            <p><a href="conference_registration.php" class="btn-primary">Back to Conference Registration</a></p>
        </div>
    </div>
    <?php
    include "includes/footer.php";
    exit;
}

// ----------------------------
// 6. Redirect on success
// ----------------------------
header('Location: user_dashboard.php?msg=conf_reg_saved');
exit;
