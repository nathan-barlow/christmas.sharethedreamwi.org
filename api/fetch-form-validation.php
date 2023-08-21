<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');

require('includes/register-validation.php');

$problems = validateRegistration();

if(!$problems) {
    insertFamily();
    $emailReminders = registerEmail();

    // POSSIBLE OUTCOMES: error message, result, or opt-out
    if($emailReminders == "opt-out") {
        echo "opt-out";
    } else if($emailReminders) {
        echo "email-error";
    } else {
        echo "true";
    }

} else {
    echo json_encode($problems);
}

?>