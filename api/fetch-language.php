<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');
header('Cache-Control:no-cache');

require('includes/register-validation.php');

$clientIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// Continue with your code validation logic
$language = $_POST['language'];

addLanguage($clientIP, $language);

?>