<?php
require('../includes/db-connection.php');

function getFamilies() {
    $conn = dbConnect('read');

    $query_members = mysqli_query($conn,
        "SELECT
            registered_families.family_id as FAMILY_CODE,
            registered_families.family_name as LAST_NAME,
            registered_families.phone as PHONE,
            registered_families.email as EMAIL,
            registered_families.family_number as FAMILY_NUMBER,
            registered_families.date_registered as DATE_REGISTERED,
            registered_families.family_gift as FAMILY_GIFT,
            registered_families.packed as PACKED,
            registered_members.member_id as MEMBER_ID,
            registered_members.first_name as FIRST_NAME,
            registered_members.age as AGE,
            registered_members.gift_preference as GIFT
        FROM registered_families
        JOIN registered_members ON (registered_families.family_id = registered_members.family_id)
        ORDER BY family_number, registered_members.age DESC");

    $data = array();
    $i = 0;
    while($row = mysqli_fetch_array($query_members)){
        $data[$row["FAMILY_NUMBER"]]["fam_number"] = $row["FAMILY_NUMBER"];
        $data[$row["FAMILY_NUMBER"]]["fam_code"] = htmlspecialchars($row["FAMILY_CODE"]);
        $data[$row["FAMILY_NUMBER"]]["fam_name"] = htmlspecialchars($row["LAST_NAME"]);
        $data[$row["FAMILY_NUMBER"]]["fam_phone"] = htmlspecialchars($row["PHONE"]);
        $data[$row["FAMILY_NUMBER"]]["fam_email"] = htmlspecialchars($row["EMAIL"]);
        $data[$row["FAMILY_NUMBER"]]["fam_gift"] = htmlspecialchars($row["FAMILY_GIFT"]);
        $data[$row["FAMILY_NUMBER"]]["packed"] = htmlspecialchars($row["PACKED"]);
        $data[$row["FAMILY_NUMBER"]]["register_date"] = ($row["DATE_REGISTERED"]);
        $data[$row["FAMILY_NUMBER"]]["members"][$i] = array(
            "name"=>htmlspecialchars($row["FIRST_NAME"]),
            "age"=>htmlspecialchars($row["AGE"]),
            "gift"=>htmlspecialchars($row["GIFT"]),
        );
        $i++;
    }

    $data = array_values($data);

    for($i = 0; $i < count($data); $i++) {
        $data[$i]['members'] = array_values($data[$i]['members']);
    }

    $conn->close();

    return $data;
}

function getFamily($number) {
    $conn = dbConnect('read');

    $query = ("SELECT
            registered_families.family_id as FAMILY_CODE,
            registered_families.family_name as LAST_NAME,
            registered_families.phone as PHONE,
            registered_families.email as EMAIL,
            registered_families.family_number as FAMILY_NUMBER,
            registered_families.date_registered as DATE_REGISTERED,
            registered_families.family_gift as FAMILY_GIFT,
            registered_families.packed as PACKED,
            registered_families.email_reminders as EMAIL_REMINDERS,
            registered_members.member_id as MEMBER_ID,
            registered_members.first_name as FIRST_NAME,
            registered_members.age as AGE,
            registered_members.gift_preference as GIFT
        FROM registered_families
        JOIN registered_members ON (registered_families.family_id = registered_members.family_id)
        WHERE family_number = ?");

    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $number);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $family = array();
        $i = 0;
        while($row = mysqli_fetch_array($result)){
            $family["fam_code"] = htmlspecialchars($row["FAMILY_CODE"]);
            $family["fam_number"] = $row["FAMILY_NUMBER"];
            $family["fam_name"] = htmlspecialchars($row["LAST_NAME"]);
            $family["fam_phone"] = htmlspecialchars($row["PHONE"]);
            $family["fam_email"] = htmlspecialchars($row["EMAIL"]);
            $family["fam_gift"] = htmlspecialchars($row["FAMILY_GIFT"]);
            $family["packed"] = htmlspecialchars($row["PACKED"]);
            $family["email_reminders"] = htmlspecialchars($row["EMAIL_REMINDERS"]);
            $family["register_date"] = ($row["DATE_REGISTERED"]);
            $family["members"][$i] = array(
                "name"=>htmlspecialchars($row["FIRST_NAME"]),
                "age"=>htmlspecialchars($row["AGE"]),
                "gift"=>htmlspecialchars($row["GIFT"]),
            );
            $i++;
        }

        mysqli_stmt_close($stmt);

        $conn->close();
    
        return $family;
    } else {
        return "Invalid family number.";
    }
}

// code: family number
// packed: true if doesn't need to be packed, false if needs to be packed
function togglePacked($number, $pack) {
    $conn = dbConnect('read');

    if($pack == '0') {
        $togglePacked = $conn->prepare("UPDATE registered_families
            SET packed = 1
            WHERE family_number = ?");
    } else {
        $togglePacked = $conn->prepare("UPDATE registered_families
            SET packed = 0
            WHERE family_number = ?");
    }

    $togglePacked->bind_param("s", $number);
    $togglePacked->execute();
    if($togglePacked->affected_rows == 1) {
        return "true";
    } else {
        return $togglePacked;
    }
    $togglePacked->close();
    $conn->close();
}

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

    return json_encode($gifts);

    $conn->close();
}

?>