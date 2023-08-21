<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');
header('Pragma: no-cache'); 

require('private-functions.php');

$number = $_POST['number'];
$pack = $_POST['pack'];

try {
    togglePacked($number, $pack);
    echo "true";
}

catch(Exception $e) {
    echo 'Error: ' .$e->getMessage();
}



?>