<?php
require_once 'functions.php';
require_login();

// REMOVE header include for this script – it outputs its own HTML
// $page_css = '<link rel="stylesheet" href="/conference_app/assets/css/admin-dashboard.css">';
// include "../includes/header.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: abstract_form.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$institute = trim($_POST['institute'] ?? '');
$email = trim($_POST['email'] ?? '');
$presentation_choice = $_POST['presentation_choice'] ?? '';
$travel_award_applied = isset($_POST['travel_award_applied']) ? (int)$_POST['travel_award_applied'] : 0;

$errors = [];
if ($name === '' || $institute === '' || $email === '' || $presentation_choice === '') {
    $errors[] = "All required fields must be filled.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email.";
}

$max_size = 10 * 1024 * 1024;

// ---------------------------
// Registration proof (OPTIONAL)
// ---------------------------
$reg = null;
if (isset($_FILES['reg_proof']) && $_FILES['reg_proof']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['reg_proof']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading registration proof. Please try again.";
    } else {
        $reg = $_FILES['reg_proof'];
        if ($reg['size'] > $max_size) {
            $errors[] = "Registration proof size exceeds 10 MB.";
        }
    }
}

// Abstract file
if (!isset($_FILES['abstract_file']) || $_FILES['abstract_file']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = "Abstract file is required.";
} else {
    $abs = $_FILES['abstract_file'];
    if ($abs['size'] > $max_size) {
        $errors[] = "Abstract file size exceeds 10 MB.";
    }
}

$award_pdf_file = null;
if ($travel_award_applied === 1) {
    if (!isset($_FILES['award_pdf']) || $_FILES['award_pdf']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Award PDF is required if you apply for travel award.";
    } else {
        $aw = $_FILES['award_pdf'];
        if ($aw['size'] > $max_size) {
            $errors[] = "Award PDF size exceeds 10 MB.";
        }
    }
}

// ---- SHOW ERROR PAGE IF NEEDED ----
if (!empty($errors)) {
    echo "<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <title>Submission Error</title>
  <link rel='stylesheet' href='/conference_app/assets/css/abstract-process.css'>
</head>
<body>
  <div class='ap-container'>
    <h2 class='ap-title'>Submission Failed</h2>
    <div class='ap-error-box'>There were some issues with your abstract submission:</div>
    <ul class='ap-error-list'>";
        foreach ($errors as $e) {
            echo "<li>" . htmlspecialchars($e) . "</li>";
        }
    echo "</ul>
    <a href='abstract_form.php' class='ap-btn'>Go back to Abstract Form</a>
  </div>
</body>
</html>";
    exit;
}

// ---- UPLOAD FILES ----
$base_dir  = __DIR__ . '/uploads/';
$dir_reg   = $base_dir . 'registration_proof/';
$dir_abs   = $base_dir . 'abstracts/';
$dir_award = $base_dir . 'awards/';

foreach ([$dir_reg, $dir_abs, $dir_award] as $d) {
    if (!is_dir($d)) {
        mkdir($d, 0755, true);
    }
}

$reg_name = null; // optional
$abs_name = time() . '_abs_' . clean_filename($abs['name']);

// Upload registration proof only if provided
if ($reg) {
    $reg_name = time() . '_reg_' . clean_filename($reg['name']);
    if (!move_uploaded_file($reg['tmp_name'], $dir_reg . $reg_name)) {
        die("Failed to upload registration proof.");
    }
}
if (!move_uploaded_file($abs['tmp_name'], $dir_abs . $abs_name)) {
    die("Failed to upload abstract file.");
}

if ($travel_award_applied === 1) {
    $aw = $_FILES['award_pdf'];
    $award_pdf_file = time() . '_award_' . clean_filename($aw['name']);
    if (!move_uploaded_file($aw['tmp_name'], $dir_award . $award_pdf_file)) {
        die("Failed to upload award PDF.");
    }
}

// ---- INSERT OR UPDATE DB ----
$stmt = $pdo->prepare("SELECT id FROM abstracts WHERE user_id = ?");
$stmt->execute([$user_id]);
$existing = $stmt->fetch();

if ($existing) {

    // ✅ Keep old registration proof if new one is NOT uploaded
    if ($reg_name === null) {
        $stmt2 = $pdo->prepare("SELECT reg_proof_file FROM abstracts WHERE user_id = ?");
        $stmt2->execute([$user_id]);
        $old = $stmt2->fetch();
        $reg_name = $old['reg_proof_file'] ?? '';
    }

    $sql = "UPDATE abstracts SET 
                institute = ?, email = ?, reg_proof_file = ?, abstract_file = ?,
                presentation_choice = ?, travel_award_applied = ?, award_pdf = ?, 
                status = 'pending', updated_at = NOW()
            WHERE user_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $institute,
        $email,
        $reg_name,          // optional
        $abs_name,          // mandatory
        $presentation_choice,
        $travel_award_applied,
        $award_pdf_file,    // unchanged logic
        $user_id
    ]);

} else {

    // ✅ New submission – reg proof may be empty
    if ($reg_name === null) {
        $reg_name = '';
    }

    $sql = "INSERT INTO abstracts 
                (user_id, institute, email, reg_proof_file, abstract_file, presentation_choice,
                 travel_award_applied, award_pdf, status, submitted_at)
            VALUES (?,?,?,?,?,?,?,?,'pending',NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $user_id,
        $institute,
        $email,
        $reg_name,          // optional
        $abs_name,          // mandatory
        $presentation_choice,
        $travel_award_applied,
        $award_pdf_file     // unchanged
    ]);
}


// ---- SUCCESS PAGE ----
echo "<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <title>Abstract Submitted</title>
  <link rel='stylesheet' href='/conference_app/assets/css/abstract-process.css'>
</head>
<body>
  <div class='ap-container'>
    <h2 class='ap-title'>Abstract Submitted Successfully</h2>
    <div class='ap-success'>Your abstract has been submitted.</div>
    <p>Status: <strong>pending</strong></p>
    <a href='user_dashboard.php' class='ap-btn'>Back to Dashboard</a>
  </div>
</body>
</html>";
exit;
