<?php
header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = ['https://staging2.christmas.sharethedreamwi.org', 'https://christmas.sharethedreamwi.org'];
if (in_array($origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $origin");
}

require('includes/register-validation.php');
require_once('includes/log-error.php');

$problems = validateRegistration();

if(!$problems) {
    insertFamily();
    $emailReminders = registerEmail();

    // POSSIBLE OUTCOMES: error message, result, or opt-out
    if($emailReminders == "opt-out") {
        echo "opt-out";
    } else if($emailReminders) {
        echo "email-error";
        $fam_email = trim($_POST['fam-email']);
        logError("EMAIL", ("Error adding " . $fam_email . " to email reminder service (Brevo). Error message: " . $emailReminders));
    } else {
        echo "true";
    }

} else {
    echo json_encode($problems);
}

?>