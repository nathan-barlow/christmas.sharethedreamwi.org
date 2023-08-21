<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://staging2.communitychristmasfoxcities.org');
header('Cache-Control: no-cache');

require('includes/db-connection.php');

function getGifts() {
    $conn = dbConnect('read');

    $query_familyGifts = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_family'");
    $query_0x3 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_0-3'");
    $query_4x7 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_4-7'");
    $query_8x11 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_8-11'");
    $query_12x17 = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_12-17'");
    $query_18x = mysqli_query($conn, "SELECT value as gift FROM event_settings WHERE name = 'gifts_18+'");

    $gifts = [];
    $gifts['Family'] = [];
    $gifts['Age 0-3'] = [];
    $gifts['Age 4-7'] = [];
    $gifts['Age 8-11'] = [];
    $gifts['Age 12-17'] = [];
    $gifts['Age 18+'] = [];
    while($row = mysqli_fetch_array($query_familyGifts)) {
        array_push($gifts['Family'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_0x3)) {
        array_push($gifts['Age 0-3'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_4x7)) {
        array_push($gifts['Age 4-7'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_8x11)) {
        array_push($gifts['Age 8-11'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_12x17)) {
        array_push($gifts['Age 12-17'], $row["gift"]);
    }
    while($row = mysqli_fetch_array($query_18x)) {
        array_push($gifts['Age 18+'], $row["gift"]);
    }

    $query_familyGifts->close();
    $query_0x3->close();
    $query_4x7->close();
    $query_8x11->close();
    $query_12x17->close();
    $query_18x->close();
    $conn->close();

    return json_encode($gifts);
}

echo getGifts();

?>