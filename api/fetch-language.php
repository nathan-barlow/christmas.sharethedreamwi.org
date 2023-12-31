<?php
header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = ['https://staging2.christmas.sharethedreamwi.org', 'https://christmas.sharethedreamwi.org'];
if (in_array($origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $origin");
}

require('includes/register-validation.php');
require_once('includes/log-error.php');

$clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// Continue with your code validation logic
$language = $_POST['language'];

$language_errors = addLanguage($clientIP, $language);

if($language_errors) {
    logError("REGISTRATION", ("Error adding language change to database. Error message: " . $language_errors));
}

?>