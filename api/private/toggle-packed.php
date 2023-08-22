<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');
header('Cache-Control:no-cache');

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
    // Require Basic Authentication for the actual GET request
    $username = 'registrationapi'; // Replace with your actual username
    $password = 'ARpw930jkN9Lldkdn23JK'; // Replace with your actual password

    if (
        !isset($_SERVER['PHP_AUTH_USER']) ||
        !isset($_SERVER['PHP_AUTH_PW']) ||
        $_SERVER['PHP_AUTH_USER'] !== $username ||
        $_SERVER['PHP_AUTH_PW'] !== $password
    ) {
        header('WWW-Authenticate: Basic realm="Authorization Required"');
        header('HTTP/1.0 401 Unauthorized');
        //echo $_SERVER['PHP_AUTH_USER'];
        error_log('Username: ' . $_SERVER['PHP_AUTH_USER']);
        error_log('Password: ' . $_SERVER['PHP_AUTH_PW']);
        echo 'Authorization required';
        exit;
    }

    // If the credentials are valid, continue with the API logic
    require('private-functions.php');

    $number = $_POST['number'];
    $pack = $_POST['pack'];

    echo togglePacked($number, $pack);
}
?>