<?php
header('Content-Type: application/json');
header('Cache-Control:no-cache');

$origin = $_SERVER['HTTP_ORIGIN'];
$allowed_domains = ['https://staging2.christmas.sharethedreamwi.org', 'https://christmas.sharethedreamwi.org'];
if (in_array($origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $origin");
}

require_once('../includes/log-error.php');
require_once('parse-env.php');

// Handle preflight requests (OPTIONS method)
if($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    // Set CORS headers to allow cross-origin requests from any origin (*)
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Authorization, Content-Type");
    header("Content-Length: 0");
    http_response_code(204); // 204 No Content status for preflight requests
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_SERVER['PHP_AUTH_USER']) ||
        !isset($_SERVER['PHP_AUTH_PW']) ||
        $_SERVER['PHP_AUTH_USER'] !== $username ||
        !password_verify($_SERVER['PHP_AUTH_PW'], $password)
    ) {
        header('WWW-Authenticate: Basic realm="Authorization Required"');
        header('HTTP/1.0 401 Unauthorized');

        logError("AUTHORIZATION", ("Failed authorization attempt on [toggle-packed.php]. Username: [" . $_SERVER['PHP_AUTH_USER'] . "] / Password: [" . $_SERVER['PHP_AUTH_PW'] . "]"));
        exit;
    }

    // If the credentials are valid, continue with the API logic
    require('private-functions.php');

    $number = $_POST['number'];
    $action = $_POST['action'];

    try {
        toggleFamily($number, $action);
    } catch (Exception $e) {
        logError("ADMIN", ("toggleFamily() function error. Number: " . htmlspecialchars($number) . ". Action: " . htmlspecialchars($action) . ". Error Message: '" . $e->getMessage() . "'"));
        echo $e->getMessage();
    }
}
?>