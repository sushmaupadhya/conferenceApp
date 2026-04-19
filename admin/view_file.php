<?php
if (!is_logged_in()) {
    header("Location: /conference_app/login.php");
    exit;
}
// view_file.php
// Usage:
//   view_file.php?type=payment&id=USER_ID
//   view_file.php?type=abstract&id=ABSTRACT_ID
//   view_file.php?type=reg_proof&id=ABSTRACT_ID
//   view_file.php?type=award&id=ABSTRACT_ID

// Optional: &debug=1  -> show path info instead of file
// Optional: &download=1 -> force download for any file

// Start a buffer so any accidental output from includes is swallowed
ob_start();
require_once 'functions.php';
ob_end_clean();

if (!isset($_GET['type'], $_GET['id'])) {
    http_response_code(400);
    exit('Bad request');
}

$type = $_GET['type'];
$id   = (int) $_GET['id'];

$baseDir = __DIR__ . '/uploads/';
$subDir  = '';
$stmt    = null;

switch ($type) {
    case 'payment':
        // Payment acknowledgement stored for user
        $stmt   = $pdo->prepare("SELECT payment_ack_file AS filename FROM users WHERE id = ?");
        $subDir = 'payment_ack/';
        break;

    case 'reg_proof':
        $stmt   = $pdo->prepare("SELECT reg_proof_file AS filename FROM abstracts WHERE id = ?");
        $subDir = 'registration_proof/';
        break;

    case 'abstract':
        $stmt   = $pdo->prepare("SELECT abstract_file AS filename FROM abstracts WHERE id = ?");
        $subDir = 'abstracts/';
        break;

    case 'award':
        $stmt   = $pdo->prepare("SELECT award_pdf AS filename FROM abstracts WHERE id = ?");
        $subDir = 'awards/';
        break;

    default:
        http_response_code(400);
        exit('Invalid type');
}

$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row || empty($row['filename'])) {
    http_response_code(404);
    exit('File not found in database');
}

$filename = $row['filename'];

// If DB stores only the name, prepend upload directory.
// If DB already stores a relative path, you can adapt accordingly.
$filepath = $baseDir . $subDir . $filename;

if (!is_file($filepath)) {
    http_response_code(404);
    exit('File not found on server');
}

// ---- DEBUG MODE -------------------------------------------------------
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Path: $filepath\n";
    echo "Exists: " . (file_exists($filepath) ? 'yes' : 'no') . "\n";
    echo "Size: " . filesize($filepath) . " bytes\n";
    exit;
}
// ----------------------------------------------------------------------

// Detect mime type
$ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';

if ($ext === 'pdf') {
    $mime = 'application/pdf';
} elseif (in_array($ext, ['jpg','jpeg'], true)) {
    $mime = 'image/jpeg';
} elseif ($ext === 'png') {
    $mime = 'image/png';
} elseif (in_array($ext, ['doc','docx'], true)) {
    $mime = 'application/msword';
}

// Clear ALL remaining buffers, just in case
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Decide whether to show inline or force download
$forceDownload = isset($_GET['download']) && $_GET['download'] === '1';

// For PDFs and images we want to VIEW in browser by default (inline)
// For other types (doc/docx) browser will usually download anyway.
if ($forceDownload) {
    $disposition = 'attachment';
} else {
    if (in_array($ext, ['pdf','jpg','jpeg','png'], true)) {
        $disposition = 'inline';
    } else {
        $disposition = 'attachment'; // safe fallback for unknown types
    }
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: ' . $disposition . '; filename="' . basename($filename) . '"');

readfile($filepath);
exit;
