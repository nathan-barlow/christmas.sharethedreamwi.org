<?php
$dotenv = parse_ini_file(__DIR__ . '/.env');

$username = $dotenv['API_USERNAME'];
$password = $dotenv['API_PASSWORD'];
$brevo_api = $dotenv['BREVO_API'];
?>