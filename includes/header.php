<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        <?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ICESR' : 'ICESR Conference'; ?>
    </title>

    <!-- Global CSS for all pages -->
    <link rel="stylesheet" href="/conference_app/assets/css/style.css">

    <!-- Page-specific CSS (optional) -->
    <?php
    if (!empty($page_css)) {
        echo $page_css;   // e.g. <link rel="stylesheet" href="/conference_app/assets/css/login.css">
    }
    ?>
</head>
<body>

<header class="fixed-header">
    <div class="header-stack">
        <img src="/conference_app/assets/images/logo.png"
             alt="ICESR Logo"
             class="site-logo-banner">
        <h1 class="site-title">International Conference</h1>
    </div>
</header>

<div class="page-content">
