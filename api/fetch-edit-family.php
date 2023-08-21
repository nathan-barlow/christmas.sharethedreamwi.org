<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');

require('includes/register-validation.php');

$problems = validateRegistration(true);

if(!$problems) {
    if(backupFamily()) {
        insertFamily();
        echo "edit-true";
    } else {
        echo json_encode(["Error submitting new family information into database. Please try again or contact site administrator."]);
    }

} else {
    echo json_encode($problems);
}
?>