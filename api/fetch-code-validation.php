<?php
header('Content-Type: application/json');
header('Cache-Control:no-cache');

$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = ['https://staging2.christmas.sharethedreamwi.org', 'https://christmas.sharethedreamwi.org'];
if (in_array($origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $origin");
}

define('INITIAL_DELAY', 50000); // 50ms in microseconds
define('INCREMENT_FACTOR', 2);   // Double the delay each time
define('MAX_DELAY', 5000000);    // 5 seconds in microseconds

require('includes/register-validation.php');
require_once('includes/log-error.php');

$clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$attemptCount = getAttempts($clientIP);

// Calculate the delay for this attempt
$delay = min(INITIAL_DELAY * pow(INCREMENT_FACTOR, $attemptCount), MAX_DELAY);

// Continue with your code validation logic
$code = $_POST['code'];
$problems = checkCode($code);

if ($attemptCount > 20) {
    echo "Too many failed attempts. If you are not a bot, please contact us to unblock you.";
    logError("FAILED CODE", ("A user made more than 20 failed attempts to input a valid invite code. Most recent code: " . $code . ". IP address: " . $clientIP));
} else if(!$problems) {
    echo "true";
    addAttempt($clientIP);
    resetAttempts($clientIP);
} else {
    usleep($delay);
    echo $problems[0];
    addAttempt($clientIP);
}
?>