<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');

$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = ['https://staging2.christmas.sharethedreamwi.org', 'https://christmas.sharethedreamwi.org'];
if (in_array($origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $origin");
}

require('includes/register-validation.php');
require_once('includes/log-error.php');

if($_POST['limit'] == "all") {
    $limit = false;
} else {
    $limit = true;
}

echo json_encode(getTimeframes($limit));

?>