<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

$name                = trim($_POST['name'] ?? '');
$designation         = $_POST['designation'] ?? '';
$email               = trim($_POST['email'] ?? '');
$phone               = trim($_POST['phone'] ?? '');
$organization        = trim($_POST['organization'] ?? '');
$country_residence   = trim($_POST['country_residence'] ?? '');
$country_citizenship = trim($_POST['country_citizenship'] ?? '');
$gender              = $_POST['gender'] ?? '';

$errors = [];

// -------------------------
// 1. Basic required checks
// -------------------------
if (
    $name === '' ||
    $designation === '' ||
    $email === '' ||
    $phone === '' ||
    $organization === '' ||
    $country_residence === '' ||
    $country_citizenship === '' ||
    $gender === ''
) {
    $errors[] = "All required fields must be filled.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address.";
}

// You can add simple phone format check if you like:
// if (!preg_match('/^[0-9+\-\s()]{5,30}$/', $phone)) {
//     $errors[] = "Invalid phone number format.";
// }

// -------------------------
// 2. If errors, show them
// -------------------------
if (!empty($errors)) {
    echo "<!DOCTYPE html><html><head><title>Errors</title></head><body>";
    echo "<h3>Following errors occurred:</h3><ul>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul><p><a href='register.php'>Go back</a></p>";
    echo "</body></html>";
    exit;
}

// -------------------------
// 3. Check if email exists
// -------------------------
global $pdo;

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "<!DOCTYPE html><html><head><title>Email already registered</title></head><body>";
    echo "<p>This email is already registered. Please <a href='login.php'>login</a> or use another email.</p>";
    echo "</body></html>";
    exit;
}

// -------------------------
// 4. Generate password
// -------------------------
$password_plain = generate_random_password(10);
$password_hash  = password_hash($password_plain, PASSWORD_DEFAULT);

// -------------------------
// 5. Insert into users
// -------------------------
$sql = "INSERT INTO users
    (name, designation, email, phone, organization,
     country_residence, country_citizenship, gender, password_hash)
    VALUES (?,?,?,?,?,?,?,?,?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $name,
    $designation,
    $email,
    $phone,
    $organization,
    $country_residence,
    $country_citizenship,
    $gender,
    $password_hash
]);

// -------------------------
// 6. Send confirmation email
// -------------------------
global $BASE_URL;

$subject = "Conference Registration – Account Created";
$message = "
<p>Dear " . htmlspecialchars($name) . ",</p>
<p>Thank you for completing <strong>Form 1: Signup</strong> for the conference.</p>
<p>Your login details for conference registration and abstract submission are as follows:</p>
<ul>
  <li><b>Username:</b> " . htmlspecialchars($email) . "</li>
  <li><b>Password:</b> " . htmlspecialchars($password_plain) . "</li>
</ul>
<p>You can login here to complete <strong>Form 2: Conference registration</strong> and submit your abstract:<br>
<a href=\"{$BASE_URL}/login.php\">{$BASE_URL}/login.php</a></p>
<p>Best regards,<br>Conference Team</p>
";

send_mail_simple($email, $subject, $message);

// -------------------------
// 7. (Optional) On localhost, keep in session
// -------------------------
if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    $_SESSION['last_reg_email']    = $email;
    $_SESSION['last_reg_password'] = $password_plain;
}

// -------------------------
// 8. Redirect to thank you
// -------------------------
header("Location: thankyou.php");
exit;
