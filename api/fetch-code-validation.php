<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');
header('Cache-Control:no-cache');

define('INITIAL_DELAY', 50000); // 50ms in microseconds
define('INCREMENT_FACTOR', 2);   // Double the delay each time
define('MAX_DELAY', 5000000);    // 5 seconds in microseconds

require('includes/register-validation.php');

$clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$attemptCount = getAttempts($clientIP);

// Calculate the delay for this attempt
$delay = min(INITIAL_DELAY * pow(INCREMENT_FACTOR, $attemptCount), MAX_DELAY);

// Continue with your code validation logic
$code = $_POST['code'];
$problems = checkCode($code);

if ($attemptCount > 20) {
    echo "Too many failed attempts. If you are not a bot, please contact us to unblock you.";
} else if(!$problems) {
    echo "true";
    resetAttempts($clientIP);
} else {
    usleep($delay);
    echo $problems[0];
    addAttempt($clientIP);
}
?>