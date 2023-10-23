<?php
header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = ['https://staging2.christmas.sharethedreamwi.org', 'https://christmas.sharethedreamwi.org'];
if (in_array($origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $origin");
}

require('includes/register-validation.php');
require_once('includes/log-error.php');

$problems = validateRegistration(true);

if(!$problems) {
    if(backupFamily()) {
        insertFamily();
        echo "edit-true";
    } else {
        echo json_encode(["Error submitting new family information into database. Please try again or contact site administrator."]);
        logError("ADMIN", ("Error editing family. Family ID: " . strtoupper(trim($_POST['fam-id']))));
    }

} else {
    echo json_encode($problems);
}
?>