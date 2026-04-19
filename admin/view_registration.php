<?php
require_once '../functions.php';

// 2) Optional: extra admin check AFTER login
if (!is_admin_logged_in()) {
    // either block them:
    die("Access denied. Admin only.");

    // OR redirect somewhere else like dashboard:
    // header("Location: /conference_app/index.php");
    // exit;
}

$regs = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$page_title = "Registrations";
$page_css   = '<link rel="stylesheet" href="/conference_app/assets/css/registrations.css">';
include "../includes/header.php";

?>

<div class="container">
  <h2>Registrations</h2>
  <p><a href="dashboard.php" class="back-link">Back to Dashboard</a></p>

  <div class="table-wrapper">
    <table class="data-table">
      <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Designation</th>
        <th>Org</th><th>Payment Ack</th><th>Reg Date</th><th>Action</th>
      </tr>
      <?php foreach ($regs as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['name']); ?></td>
          <td><?php echo htmlspecialchars($r['email']); ?></td>
          <td><?php echo htmlspecialchars($r['designation']); ?></td>
          <td><?php echo htmlspecialchars($r['organization']); ?></td>
          <td>
            <a class="view-link"
             href="preview.php?user_id=<?php echo (int)$r['id']; ?>"
             target="_blank">View</a>
          </td>
          <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                    <!-- DELETE BUTTON -->
          <td>
            <form action="delete_user.php" method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this registration?');">
              <input type="hidden" name="user_id" value="<?= (int)$r['id']; ?>">
              <button class="delete-btn">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<?php include "../includes/footer.php"; ?>
