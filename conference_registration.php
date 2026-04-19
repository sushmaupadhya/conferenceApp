<?php
require_once 'functions.php';
require_login();

$page_title = 'Conference Registration';
$page_css   = '<link rel="stylesheet" href="assets/css/conference_form.css">'; 
include "includes/header.php";
?>

<div class="registration-wrapper">

    <h2>Conference registration</h2>
    <p class="registration-intro">
        Please provide your accommodation, stay and payment details below.
        Fields marked with <span style="color:#b91c1c">*</span> are mandatory.
    </p>
    
    <p><a href="user_dashboard.php" class="back-link">&larr; Back to Dashboard</a></p>

    <form action="conference_registration_process.php" method="post"
          enctype="multipart/form-data"
          class="form-card registration-form">

        <div class="form-grid">

            <div>
                <label>Do you need accommodation inside Indian Institute of Science?*<br>
                    <span class="small-text">
                        [Check-in: 20/03/2026, 11:00 AM; Check-out: 25/03/2026, 11:00 AM]
                    </span>
                </label>
                <div class="radio-group">
                    <label><input type="radio" name="need_accommodation" value="1" required> Yes</label>
                    <label><input type="radio" name="need_accommodation" value="0"> No</label>
                </div>
            </div>

            <div>
                <label>Do you have any accompanying person?*<br></label>
                <div class="radio-group">
                    <label><input type="radio" name="accompanying_person" value="1" required> Yes</label>
                    <label><input type="radio" name="accompanying_person" value="0"> No</label>
                </div>
            </div>

            <div>
                <label>Your span of stay*<br>
                    <input type="text" name="span_of_stay" placeholder="e.g., 20–25 March 2026" required>
                </label>
            </div>

            <div>
                <label>Dietary restrictions (If any)<br>
                    <input type="text" name="dietary_restrictions" placeholder="e.g., Vegetarian, Vegan, Allergies">
                </label>
            </div>

            <div>
                <label>Please upload the payment acknowledgement*<br>
                    <input type="file" name="payment_ack"
                           accept=".pdf,.jpg,.jpeg,.png" required>
                </label>
            </div>

            <div>
                <label>Will you be joining us for the 1 day Mysore trip?
                    (Spot registration will not be allowed)*<br>
                </label>
                <div class="radio-group">
                    <label><input type="radio" name="mysore_trip" value="1" required> Yes</label>
                    <label><input type="radio" name="mysore_trip" value="0"> No</label>
                </div>
            </div>

            <div>
                <label>Which country's passport are you holding?*<br>
                    <select name="passport_country" required>
                        <option value="">--Select--</option>
                        <option value="India">India</option>
                        <option value="Other">Other</option>
                    </select>
                </label>
            </div>
        </div> <!-- .form-grid -->

        <button type="submit" class="btn-primary" style="margin-top: 20px;">
            Submit Conference Registration
        </button>

    </form>

</div>

<?php include "includes/footer.php"; ?>
