<?php
require_once 'functions.php';
require_login();

// Load current user from users table
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$currentUser = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("User record not found.");
}

// Load existing visa info (if any)
$visaStmt = $pdo->prepare("SELECT * FROM visa_info WHERE user_id = ?");
$visaStmt->execute([$_SESSION['user_id']]);
$visa = $visaStmt->fetch(PDO::FETCH_ASSOC);

$visa_msg    = '';
$visa_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Name and phone can come from form (passport name may differ)
    $name_passport     = trim($_POST['name_passport'] ?? '');
    $phone             = trim($_POST['phone'] ?? '');
    $office_address    = trim($_POST['office_address'] ?? '');
    $date_of_birth     = trim($_POST['date_of_birth'] ?? '');
    $country_of_birth  = trim($_POST['country_of_birth'] ?? '');
    $professional      = trim($_POST['professional_details'] ?? '');
    $passport_details  = trim($_POST['passport_details'] ?? '');
    $sensitive_details = trim($_POST['sensitive_details'] ?? '');

    // Email MUST match users table
    $email = $currentUser['email'];

    // Validation
    if ($name_passport === '')        $visa_errors[] = "Name (as in Passport) is required.";
    if ($phone === '')                $visa_errors[] = "Phone Number is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $visa_errors[] = "Valid Email Address is required.";
    }
    if ($office_address === '')       $visa_errors[] = "Office address is required.";
    if ($date_of_birth === '')        $visa_errors[] = "Date of Birth is required.";
    if ($country_of_birth === '')     $visa_errors[] = "Country of Birth is required.";
    if ($professional === '')         $visa_errors[] = "Professional details are required.";
    if ($passport_details === '')     $visa_errors[] = "Passport details are required.";

    if (empty($visa_errors)) {
        // Insert or update
        if ($visa) {
            // update
            $upd = $pdo->prepare("
                UPDATE visa_info
                SET name_passport = ?, phone = ?, email = ?, office_address = ?,
                    date_of_birth = ?, country_of_birth = ?, professional_details = ?,
                    passport_details = ?, sensitive_details = ?
                WHERE id = ?
            ");
            $upd->execute([
                $name_passport,
                $phone,
                $email,
                $office_address,
                $date_of_birth,
                $country_of_birth,
                $professional,
                $passport_details,
                $sensitive_details,
                $visa['id']
            ]);
            $visa_msg = "Your visa/approval information has been updated successfully.";
        } else {
            // insert
            $ins = $pdo->prepare("
                INSERT INTO visa_info
                (user_id, name_passport, phone, email, office_address,
                 date_of_birth, country_of_birth, professional_details,
                 passport_details, sensitive_details)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $_SESSION['user_id'],
                $name_passport,
                $phone,
                $email,
                $office_address,
                $date_of_birth,
                $country_of_birth,
                $professional,
                $passport_details,
                $sensitive_details
            ]);
            $visa_msg = "Your visa/approval information has been saved successfully.";
        }

        // reload record
        $visaStmt->execute([$_SESSION['user_id']]);
        $visa = $visaStmt->fetch(PDO::FETCH_ASSOC);
    }
}
include "includes/header.php";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visa Information</title>
    <link rel="stylesheet" href="assets/css/visa_form.css">
</head>
<body>
<div class="container">

    <h2>Visa / Government of India Approval Information</h2>
    <p><a href="user_dashboard.php" class="btn-secondary">← Back to Dashboard</a></p>

    <p>
        The information in this form is needed for Visa and Government of India approvals for the conference.
        When you submit this form, it will not automatically collect your details like name and email address unless you provide it yourself.
    </p>

    <?php if ($visa_msg): ?>
        <p style="color: green;"><?php echo htmlspecialchars($visa_msg); ?></p>
    <?php endif; ?>

    <?php if (!empty($visa_errors)): ?>
        <ul style="color: red;">
            <?php foreach ($visa_errors as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" action="visa_info_form.php" class="visa-form">

        <label>1. Name (as in Passport) *</label>
        <input type="text" name="name_passport"
               value="<?php echo htmlspecialchars($visa['name_passport'] ?? $currentUser['name']); ?>"
               required>

        <label>2. Phone Number (with ISD or International Country Code) *</label>
        <input type="text" name="phone"
               value="<?php echo htmlspecialchars($visa['phone'] ?? $currentUser['phone']); ?>"
               required>

        <label>3. Email Address for Communication *</label>
        <!-- read-only, always from users table -->
        <input type="email" name="email"
               value="<?php echo htmlspecialchars($currentUser['email']); ?>"
               readonly>

        <label>4. Office address including country and ZIP/PIN Code *</label>
        <textarea name="office_address" rows="3" required><?php
            echo htmlspecialchars($visa['office_address'] ?? '');
        ?></textarea>

        <label>5. Date of Birth (as in Passport) *</label>
        <input type="date" name="date_of_birth"
               value="<?php echo htmlspecialchars($visa['date_of_birth'] ?? ''); ?>"
               required>

        <label>6. Country of Birth *</label>
        <input type="text" name="country_of_birth"
               value="<?php echo htmlspecialchars($visa['country_of_birth'] ?? ''); ?>"
               required>

        <label>7. 1–2 sentences of professional details including current position (&lt;100 words) *</label>
        <textarea name="professional_details" rows="3" maxlength="800" required><?php
            echo htmlspecialchars($visa['professional_details'] ?? '');
        ?></textarea>

        <label>8. Passport Details: Passport Number, nationality, Date of Issuance and Date of Expiry *</label>
        <textarea name="passport_details" rows="3" required><?php
            echo htmlspecialchars($visa['passport_details'] ?? '');
        ?></textarea>

        <label>9. If you are from Afghanistan, Iran, Pakistan, Iraq, Sudan, or are a foreigner of Pakistani origin, please provide the following additional details: father’s name, parentage, nationality, date and place of birth, passport number, date and place of issue of passport, validity, address (Ignore, if not applicable)</label>
        <textarea name="sensitive_details" rows="3"><?php
            echo htmlspecialchars($visa['sensitive_details'] ?? '');
        ?></textarea>

        <button type="submit" class="btn-primary">
            <?php echo $visa ? 'Update Details' : 'Submit Details'; ?>
        </button>
    </form>

</div>
</body>
</html>
<?php include "includes/footer.php"; ?>
