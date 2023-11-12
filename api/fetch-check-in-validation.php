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

function getFamilyName($code) {
    $conn = dbConnect('read');

    $query_get_name = $conn->prepare("SELECT family_name, family_number FROM registered_families WHERE family_id = ?");
    $query_get_name->bind_param("s", $code);
    $query_get_name->execute();
    
    $result = $query_get_name->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        return ["name" => htmlspecialchars($row['family_name']), "number" => htmlspecialchars($row['family_number'])];
    } else {
        return false;
    }
}

function markHere($code) {
    $conn = dbConnect('read');

    $mark_here = $conn->prepare("UPDATE registered_families SET attended = NOW() WHERE family_id = ?");
    $mark_here->bind_param("s", $code);
    $mark_here->execute();
    

    if ($mark_here->affected_rows == 1) {
        return true;
    } else {
        return false;
    }
}

// Continue with your code validation logic
$code = strtoupper($_POST['code']);
$fam_name = getFamilyName($code);

if ($attemptCount > 20) {
    echo "Too many failed attempts. If you are not a bot, please contact us to unblock you.";
    logError("FAILED CODE", ("A user made more than 20 failed attempts to input a valid invite code. Most recent code: " . $code . ". IP address: " . $clientIP));
} else if($fam_name && $_POST['submitted']) {
    if(markHere($code)) {
        echo json_encode(["code" => htmlspecialchars($code), "name" => htmlspecialchars($fam_name['name']), "number" => htmlspecialchars($fam_name['number'])]);
    } else {
        echo "error";
    }
} else if($fam_name) {
    echo $fam_name['name'];
    addAttempt($clientIP);
    resetAttempts($clientIP);
} else {
    usleep($delay);
    echo "invalid";
    addAttempt($clientIP);
}
?>