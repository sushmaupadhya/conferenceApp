<?php
require_once 'functions.php';
require_login();

// Page-specific settings
$page_title = 'Abstract Submission';
$page_css = '<link rel="stylesheet" href="assets/css/abstract-form.css">';


include "includes/header.php";   // if file is in /admin/, use: include "../includes/header.php";
?>

<div class="abstract-container">
    <h2 class="abstract-title">Abstract Submission Form</h2>
    
    <p><a href="user_dashboard.php" class="back-link">&larr; Back to Dashboard</a></p>

    <form action="abstract_process.php" method="post" enctype="multipart/form-data" class="abstract-form">

        <div class="form-group">
            <label>Name*</label>
            <input type="text" name="name"
                   value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Name of the Institute / Industry*</label>
            <input type="text" name="institute" required>
        </div>

        <div class="form-group">
            <label>Email (Institute)*</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Submit your registration proof</label>
            <input type="file" name="reg_proof">
            <small>Upload 1 supported file. Max 10 MB.</small>
        </div>

        <div class="form-group">
            <label>Submit your abstract*</label>
            <input type="file" name="abstract_file" accept=".pdf,.doc,.docx" required>
            <small>PDF or document, Max 10 MB.</small>
        </div>

        <div class="form-group">
            <label>You want your poster to be selected for*</label>
            <select name="presentation_choice" required>
                <option value="">--Select--</option>
                <option>Oral presentation</option>
                <option>Poster presentation</option>
            </select>
        </div>

        <div class="form-group">
            <label>Do you want to apply for student travel award?*</label>
            <select name="travel_award_applied" id="travel_award" required>
                <option value="">--Select--</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>

        <div id="award_section" class="award-section" style="display:none;">
            <div class="form-group">
                <label>Upload combined PDF (abstract, CV, recommendation letter from PI, cover letter)*</label>
                <input type="file" name="award_pdf" accept=".pdf">
                <small>Single PDF, Max 10 MB.</small>
            </div>
        </div>

        <button type="submit" class="btn-primary">Submit Abstract</button>
    </form>
</div>

<script>
document.getElementById('travel_award').addEventListener('change', function () {
    var val = this.value;
    document.getElementById('award_section').style.display = (val === '1') ? 'block' : 'none';
});
</script>

<?php include "includes/footer.php"; ?>
