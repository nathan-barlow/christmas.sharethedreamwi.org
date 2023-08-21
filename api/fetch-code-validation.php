<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');
header('Cache-Control:no-cache');

// If the credentials are valid, continue with the API logic
require('includes/register-validation.php');

$code = $_POST['code'];
$problems = checkCode($code);

if(!$problems) {
    echo "true";
} else {
    echo $problems[0];
}
?>