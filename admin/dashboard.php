<?php
require_once '../functions.php';
require_admin();

$reg_count = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$abs_count = $pdo->query("SELECT COUNT(*) AS c FROM abstracts")->fetch()['c'];

// Page-specific settings
$page_title = 'Admin Dashboard';
$page_css   = '<link rel="stylesheet" href="/conference_app/assets/css/admin-dashboard.css">';

include "../includes/header.php";
?>

<div class="admin-page">

    <div class="admin-header-row">
        <h2 class="admin-title">Admin Dashboard</h2>
        <a href="admin_logout.php" class="admin-logout">Logout</a>
    </div>

    <div class="admin-stat-grid">
        <div class="admin-card">
            <div class="admin-card-label">Total Registrations</div>
            <div class="admin-card-value"><?php echo (int)$reg_count; ?></div>
            <a href="view_registration.php" class="admin-card-link">View Details →</a>
        </div>

        <div class="admin-card">
            <div class="admin-card-label">Total Abstracts</div>
            <div class="admin-card-value"><?php echo (int)$abs_count; ?></div>
            <a href="manage_abstracts.php" class="admin-card-link">Manage Abstracts →</a>
        </div>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
