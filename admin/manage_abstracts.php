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

$abstracts = $pdo->query(
    "SELECT a.*, u.name AS user_name 
     FROM abstracts a 
     JOIN users u ON a.user_id = u.id
     ORDER BY a.submitted_at DESC"
)->fetchAll();

$page_title = "Manage Abstracts";
$page_css   = '<link rel="stylesheet" href="/conference_app/assets/css/abstracts.css">';
include "../includes/header.php";
?>

<div class="container">
  <h2>Manage Abstracts</h2>
  <p><a href="dashboard.php" class="back-link">Back to Dashboard</a></p>

  <div class="table-wrapper">
    <table class="data-table">
      <tr>
        <th>ID</th><th>User</th><th>Email</th><th>Institute</th>
        <th>Abstract</th><th>Reg Proof</th><th>Award PDF</th>
        <th>Choice</th><th>Status</th><th>Actions</th>
      </tr>

      <?php foreach ($abstracts as $a): ?>
        <tr>
          <td><?php echo (int)$a['id']; ?></td>
          <td><?php echo htmlspecialchars($a['user_name']); ?></td>
          <td><?php echo htmlspecialchars($a['email']); ?></td>
          <td><?php echo htmlspecialchars($a['institute']); ?></td>

          <!-- Reg proof -->
          <td>
            <?php if (!empty($a['abstract_file'])): ?>
              <a href="../view_file.php?type=abstract&id=<?php echo (int)$a['id']; ?>" target="_blank" class="view-link">
                View
              </a>
            <?php else: ?>
              <span>-</span>
            <?php endif; ?>
          </td>

          <!-- Abstract file -->
          <td>
            <?php if (!empty($a['reg_proof_file'])): ?>
              <a href="../view_file.php?type=reg_proof&id=<?php echo (int)$a['id']; ?>" target="_blank" class="view-link">
                View
              </a>
            <?php else: ?>
              <span>-</span>
            <?php endif; ?>
          </td>

          <!-- Award PDF -->
          <td>
            <?php if (!empty($a['award_pdf'])): ?>
              <a href="../view_file.php?type=award&id=<?php echo (int)$a['id']; ?>" target="_blank" class="view-link">
                View
              </a>
            <?php else: ?>
              <span>-</span>
            <?php endif; ?>
          </td>

          <td><?php echo htmlspecialchars($a['presentation_choice']); ?></td>
          <td><?php echo htmlspecialchars($a['status']); ?></td>

          <td>
            <?php if ($a['status'] === 'Accepted'): ?>
              <span class="status-pill status-accepted">CONFIRMED</span>
            <?php elseif ($a['status'] === 'Rejected'): ?>
              <span class="status-pill status-rejected">Rejected</span>
            <?php else: ?>
              <div class="action-buttons">
                <form action="update_abstract_status.php" method="post">
                  <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                  <input type="hidden" name="status" value="Accepted">
                  <button type="submit" class="btn-small success">Accept</button>
                </form>
                <form action="update_abstract_status.php" method="post" class="reject-form">
                  <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
                  <input type="hidden" name="status" value="Rejected">
                  <input type="hidden" name="reason" value="">
                  <button type="submit" class="btn-small danger">Reject</button>
                </form>
              </div>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.reject-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            // Ask admin for rejection reason
            let reason = prompt("Please enter the reason for rejection:");

            // If they click Cancel, don't submit
            if (reason === null) {
                e.preventDefault();
                return;
            }

            reason = reason.trim();

            if (!reason) {
                alert("Reason is required for rejection.");
                e.preventDefault();
                return;
            }

            // Put the reason into the hidden input
            const reasonInput = form.querySelector('input[name="reason"]');
            reasonInput.value = reason;
        });
    });
});
</script>

<?php include "../includes/footer.php"; ?>
