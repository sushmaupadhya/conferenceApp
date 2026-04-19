<?php
require_once 'config.php';  // contains $BASE_URL

// ====== REGISTRATION CLOSE SWITCH ======
// Set to true to stop new registrations
$REGISTRATION_CLOSED = true;

$REG_CLOSED_MESSAGE = "🚫 Registration has been closed for ICESR Conference. Thank you for your interest.";
$REG_CONTACT_EMAIL  = "icesr.conf@iisc.ac.in"; // change this

if ($REGISTRATION_CLOSED) {
    $page_title = "Registration Closed";
    include "includes/header.php";
    ?>
    <div style="max-width:820px;margin:70px auto;padding:28px;background:#fff3f3;border:2px solid #b30000;border-radius:12px;text-align:center;">
        <div style="display:inline-block;background:#b30000;color:#fff;padding:6px 12px;border-radius:999px;font-weight:700;">
            Registration Closed
        </div>
        <h2 style="margin:14px 0 8px;color:#111;">ICESR Conference Registration</h2>
        <p style="margin:0;color:#222;font-size:16px;line-height:1.6;">
            <?php echo htmlspecialchars($REG_CLOSED_MESSAGE); ?>
        </p>
        <p style="margin:14px 0 0;color:#444;">
            For queries, please contact:
            <a href="mailto:<?php echo htmlspecialchars($REG_CONTACT_EMAIL); ?>" style="font-weight:700;color:#b30000;">
                <?php echo htmlspecialchars($REG_CONTACT_EMAIL); ?>
            </a>
        </p>
    </div>
    <?php
    include "includes/footer.php";
    exit;
}
// ====== END REGISTRATION CLOSE SWITCH ======

// Automatically fetch all country names
$countries = array_values(
    ResourceBundle::getLocales('') ?:
    ["en_US"] // fallback (won't be used normally)
);

$countries = array_unique(array_map(function($locale) {
    $parts = explode('_', $locale);
    return isset($parts[1]) ? Locale::getDisplayRegion('-' . $parts[1], 'en') : null;
}, $countries));

$countries = array_filter($countries);
sort($countries);

$page_title = "Signup";
$page_css = '<link rel="stylesheet" href="'.$BASE_URL.'/assets/css/register.css">';
include "includes/header.php";
?>

<div class="registration-wrapper">
    
    <p class="registration-info">
        Already registered?
        <a href="/conference_app/login.php" class="login-link">Click here to login</a>
    </p>
    

    <h2>Signup</h2>
    <p class="registration-intro">
        Please complete <strong>Signup</strong> with your personal details.
        After your account is created and you login, you can fill
        <strong>Conference registration</strong> and submit your abstract.
        Fields marked with <span style="color:#b91c1c">*</span> are mandatory.
    </p>

    <form action="register_process.php" method="post" class="form-card registration-form">

        <h3 class="form-section-title">Personal Details</h3>

        <div class="form-grid">

            <div>
                <label>Name*<br>
                    <input type="text" name="name" required>
                </label>
            </div>

            <div>
                <label>Designation*<br>
                    <select name="designation" required>
                        <option value="">--Select--</option>
                        <option value="Professor/Scientist">Professor/Scientist</option>
                        <option value="Post-Doc">Post-Doc</option>
                        <option value="Graduate Student">Graduate Student</option>
                        <option value="MS/MSc/UG">MS/MSc/UG</option>
                    </select>
                </label>
            </div>

            <div>
                <label>Email (Institute)*<br>
                    <input type="email" name="email" required>
                </label>
            </div>

            <div>
                <label for="phone">Contact Number (WhatsApp)<br>
                    <input 
                        type="text" 
                        name="phone"
                        placeholder="+91 9876543210"
                        pattern="^\+?[0-9\s\-()]{6,20}$"
                        required>
                </label>
            </div>

            <div>
                <label>Organization<br>
                    <input type="text" name="organization">
                </label>
            </div>

            <div>
                <label>Country of Residence*<br>
                    <select name="country_residence" required class="form-select">
                        <option value="">-- Select Country --</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>">
                                <?php echo htmlspecialchars($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        
            <div>
                <label>Country of Citizenship*<br>
                    <select name="country_citizenship" required class="form-select">
                        <option value="">-- Select Country --</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>">
                                <?php echo htmlspecialchars($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div>
                <label>What gender do you identify as?*<br></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="Male" required> Male</label>
                    <label><input type="radio" name="gender" value="Female"> Female</label>
                    <label><input type="radio" name="gender" value="Prefer not to disclose"> Prefer not to disclose</label>
                </div>
            </div>

        </div> <!-- .form-grid -->

        <p class="registration-note">
            Once your registration is successful, you will receive login details by email.
            Please login to complete <strong>Conference registration</strong> and
            submit your abstract.
        </p>

        <button type="submit" class="btn-primary" style="margin-top: 20px;">
            Create Account
        </button>

    </form>

</div>

<?php include "includes/footer.php"; ?>
