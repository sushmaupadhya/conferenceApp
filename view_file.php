<?php
// view_file.php
// Usage:
//   view_file.php?type=payment&id=USER_ID
//   view_file.php?type=abstract&id=ABSTRACT_ID
//   view_file.php?type=reg_proof&id=ABSTRACT_ID
//   view_file.php?type=award&id=ABSTRACT_ID

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

switch ($type) {
    case 'payment':
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
$filepath = $baseDir . $subDir . $filename;

if (!is_file($filepath)) {
    http_response_code(404);
    exit('File not found on server');
}

// Optional debug: view path instead of file
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Path: $filepath\n";
    echo "Exists: " . (file_exists($filepath) ? 'yes' : 'no') . "\n";
    echo "Size: " . filesize($filepath) . " bytes\n";
    exit;
}

// Detect mime type
$ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$mime = 'application/octet-stream';

switch ($ext) {
    case 'pdf':
        $mime = 'application/pdf';
        break;
    case 'jpg':
    case 'jpeg':
        $mime = 'image/jpeg';
        break;
    case 'png':
        $mime = 'image/png';
        break;
    case 'doc':
        $mime = 'application/msword';
        break;
    case 'docx':
        $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        break;
}

// Clear ALL remaining buffers, just in case
while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filepath));

// ✅ Key change:
// For PDFs and images we use `inline` (open in browser).
// For everything else we keep `attachment` (download).
$inlineTypes = ['pdf', 'jpg', 'jpeg', 'png'];

if (in_array($ext, $inlineTypes, true)) {
    header('Content-Disposition: inline; filename="' . basename($filename) . '"');
} else {
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
}

readfile($filepath);
exit;
