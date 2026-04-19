<?php
// admin/view_registration.php
require_once '../functions.php';
require_admin();

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($user_id <= 0) {
    die("Invalid request.");
}

// -----------------------------
// Fetch user + conference data
// -----------------------------
$sql = "
    SELECT 
        u.id,
        u.name,
        u.designation,
        u.email,
        u.phone,
        u.organization,
        u.country_residence,
        u.country_citizenship,
        u.gender,
        u.created_at,

        cr.need_accommodation,
        cr.accompanying_person,
        cr.span_of_stay,
        cr.dietary_restrictions,
        cr.payment_ack_file,
        cr.mysore_trip,
        cr.passport_country,
        cr.created_at AS conf_created_at
    FROM users u
    LEFT JOIN conference_registration cr
        ON cr.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$reg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reg) {
    die("Registration not found.");
}

// ---------------------------------------
// NEW: Fetch visa information for user
// ---------------------------------------
$visaStmt = $pdo->prepare("SELECT * FROM visa_info WHERE user_id = ?");
$visaStmt->execute([$user_id]);
$visa = $visaStmt->fetch(PDO::FETCH_ASSOC);

$page_title = "View Registration – " . htmlspecialchars($reg['name']);
$page_css   = '<link rel="stylesheet" href="/conference_app/assets/css/preview.css">';
include "../includes/header.php";
?>

<div class="container view-registration">

    <h2>Registration Details</h2>
    <p><a href="view_registration.php" class="back-link">&larr; Back to Registrations</a></p>

    <!-- Personal / Signup Details -->
    <div class="detail-card">
        <h3>Form 1: Signup (User Details)</h3>
        <div class="detail-grid">
            <div>
                <span class="label">Name</span>
                <span class="value"><?php echo htmlspecialchars($reg['name']); ?></span>
            </div>
            <div>
                <span class="label">Designation</span>
                <span class="value"><?php echo htmlspecialchars($reg['designation']); ?></span>
            </div>
            <div>
                <span class="label">Email</span>
                <span class="value"><?php echo htmlspecialchars($reg['email']); ?></span>
            </div>
            <div>
                <span class="label">Phone</span>
                <span class="value"><?php echo htmlspecialchars($reg['phone']); ?></span>
            </div>
            <div>
                <span class="label">Organization</span>
                <span class="value"><?php echo htmlspecialchars($reg['organization']); ?></span>
            </div>
            <div>
                <span class="label">Country of Residence</span>
                <span class="value"><?php echo htmlspecialchars($reg['country_residence']); ?></span>
            </div>
            <div>
                <span class="label">Country of Citizenship</span>
                <span class="value"><?php echo htmlspecialchars($reg['country_citizenship']); ?></span>
            </div>
            <div>
                <span class="label">Gender</span>
                <span class="value"><?php echo htmlspecialchars($reg['gender']); ?></span>
            </div>
            <div>
                <span class="label">Registered At</span>
                <span class="value"><?php echo htmlspecialchars($reg['created_at']); ?></span>
            </div>
        </div>
    </div>

    <!-- *******************************
         NEW: Visa Information section
    ******************************** -->
    <div class="detail-card">
        <h3>Visa Information</h3>

        <?php if (!$visa): ?>
            <p>This participant has not submitted the visa information form yet.</p>
        <?php else: ?>
            <div class="detail-grid">
                <div>
                    <span class="label">Name (as in Passport)</span>
                    <span class="value"><?php echo htmlspecialchars($visa['name_passport']); ?></span>
                </div>
                <div>
                    <span class="label">Phone</span>
                    <span class="value"><?php echo htmlspecialchars($visa['phone']); ?></span>
                </div>
                <div>
                    <span class="label">Email</span>
                    <span class="value"><?php echo htmlspecialchars($visa['email']); ?></span>
                </div>
                <div>
                    <span class="label">Office Address</span>
                    <span class="value"><?php echo nl2br(htmlspecialchars($visa['office_address'])); ?></span>
                </div>
                <div>
                    <span class="label">Date of Birth</span>
                    <span class="value"><?php echo htmlspecialchars($visa['date_of_birth']); ?></span>
                </div>
                <div>
                    <span class="label">Country of Birth</span>
                    <span class="value"><?php echo htmlspecialchars($visa['country_of_birth']); ?></span>
                </div>
                <div>
                    <span class="label">Professional Details</span>
                    <span class="value"><?php echo nl2br(htmlspecialchars($visa['professional_details'])); ?></span>
                </div>
                <div>
                    <span class="label">Passport Details</span>
                    <span class="value"><?php echo nl2br(htmlspecialchars($visa['passport_details'])); ?></span>
                </div>
                <?php if (!empty($visa['sensitive_details'])): ?>
                <div>
                    <span class="label">Additional Details</span>
                    <span class="value"><?php echo nl2br(htmlspecialchars($visa['sensitive_details'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- End Visa Information section -->

    <!-- Conference Registration Details -->
    <div class="detail-card">
        <h3>Conference registration</h3>

        <?php if ($reg['conf_created_at'] === null): ?>
            <p>This participant has not submitted the conference registration form yet.</p>
        <?php else: ?>
            <div class="detail-grid">
                <div>
                    <span class="label">Need Accommodation?</span>
                    <span class="value">
                        <?php echo ($reg['need_accommodation'] ? 'Yes' : 'No'); ?>
                    </span>
                </div>
                <div>
                    <span class="label">Accompanying Person?</span>
                    <span class="value">
                        <?php echo ($reg['accompanying_person'] ? 'Yes' : 'No'); ?>
                    </span>
                </div>
                <div>
                    <span class="label">Span of Stay</span>
                    <span class="value"><?php echo htmlspecialchars($reg['span_of_stay']); ?></span>
                </div>
                <div>
                    <span class="label">Dietary Restrictions</span>
                    <span class="value">
                        <?php echo $reg['dietary_restrictions']
                            ? htmlspecialchars($reg['dietary_restrictions'])
                            : 'None specified'; ?>
                    </span>
                </div>
                <div>
                    <span class="label">Payment Acknowledgement</span>
                    <span class="value">
                        <?php if (!empty($reg['payment_ack_file'])): ?>
                            <?php
                            // Build full relative path
                            $fileRel  = '../' . $reg['payment_ack_file'];
                            $fileHref = '../' . htmlspecialchars($reg['payment_ack_file']);
                            $ext      = strtolower(pathinfo($fileRel, PATHINFO_EXTENSION));

                            // File types that browser can preview nicely
                            $previewExts = ['pdf', 'jpg', 'jpeg', 'png'];
                            ?>
                            <?php if (in_array($ext, $previewExts)): ?>
                                <a href="<?php echo $fileHref; ?>" class="view-link" target="_blank">
                                    View / Download
                                </a>
                            <?php else: ?>
                                <a href="<?php echo $fileHref; ?>" class="view-link" download>
                                    Download (Word file)
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            Not uploaded
                        <?php endif; ?>
                    </span>
                </div>

                <div>
                    <span class="label">Passport Country</span>
                    <span class="value"><?php echo htmlspecialchars($reg['passport_country']); ?></span>
                </div>
                <div>
                    <span class="label">Submitted At</span>
                    <span class="value"><?php echo htmlspecialchars($reg['conf_created_at']); ?></span>
                </div>
                <div>
                    <span class="label">Mysore Trip</span>
                    <span class="value">
                        <?php echo ($reg['mysore_trip'] ? 'Yes' : 'No'); ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include "../includes/footer.php"; ?>
