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

    $query_get_name = $conn->prepare("SELECT
            rf.family_number as family_number,
            rf.family_name as family_name,
            COUNT(CASE WHEN rm.age < 18 THEN 1 END) as children,
            COUNT(CASE WHEN rm.age >= 18 THEN 1 END) as adults
        FROM registered_families rf
        LEFT JOIN registered_members rm ON rf.family_id = rm.family_id
        WHERE rf.family_id = ?");
    $query_get_name->bind_param("s", $code);
    $query_get_name->execute();
    
    $result = $query_get_name->get_result();
    $row = $result->fetch_assoc();

    if($row) {
        return [
            "name"    => htmlspecialchars($row['family_name']),
            "number"  => htmlspecialchars($row['family_number']),
            "children"  => htmlspecialchars($row['children']),
            "adults"  => htmlspecialchars($row['adults'])];
    } else {
        return false;
    }
}

function markHere($code) {
    $conn = dbConnect('read');

    $mark_here = $conn->prepare("UPDATE registered_families SET attended = NOW(), checked_in_online = 1 WHERE family_id = ?");
    $mark_here->bind_param("s", $code);
    $mark_here->execute();
    

    if ($mark_here->affected_rows == 1) {
        return true;
    } else {
        return false;
    }
}

$code = strtoupper($_POST['code']);
$fam_name = getFamilyName($code);

if ($attemptCount > 20) {
    echo "blocked";
    logError("FAILED CODE", ("A user made more than 20 failed attempts to input a valid invite code. Most recent code: " . $code . ". IP address: " . $clientIP));
} else if($fam_name && $_POST['submitted']) {
    if(markHere($code)) {
        echo json_encode(["code" => htmlspecialchars($code), "name" => $fam_name['name'], "number" => $fam_name['number']]);
    } else {
        echo "error";
    }
} else if($fam_name['name']) {
    echo json_encode(["name" => $fam_name['name'], "children" => $fam_name['children'], "adults" => $fam_name['adults']]);
    addAttempt($clientIP);
    resetAttempts($clientIP);
} else {
    usleep($delay);
    echo "invalid";
    addAttempt($clientIP);
}
?>